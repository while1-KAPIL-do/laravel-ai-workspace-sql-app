from fastapi import FastAPI, HTTPException, UploadFile, File
from pydantic import BaseModel
from dotenv import load_dotenv
import tiktoken
import tempfile
import os
import shutil
from groq import Groq

load_dotenv()

app = FastAPI()

# ---------------------------------------------------------------------------
# Supported STT providers
# Add new providers here as needed
# ---------------------------------------------------------------------------
SUPPORTED_PROVIDERS = {"groq", "openai"}  # extend as needed e.g. "assemblyai"

# ---------------------------------------------------------------------------
# Provider clients — initialized only if API key is set
# ---------------------------------------------------------------------------
groq_client = Groq(api_key=os.environ.get("GROQ_API_KEY")) if os.environ.get("GROQ_API_KEY") else None


# ---------------------------------------------------------------------------
# Existing token-counter endpoint (unchanged)
# ---------------------------------------------------------------------------
class TokenRequest(BaseModel):
    text: str
    model: str = "gpt-4"


@app.get("/health")
def health():
    return {
        "status": "ok",
        "providers_available": {
            "groq":   groq_client is not None,
            "openai": bool(os.environ.get("OPENAI_API_KEY")),
        }
    }


@app.post("/count-tokens")
def count_tokens(req: TokenRequest):
    print("Token API HIT:", req.text)
    try:
        encoding = tiktoken.encoding_for_model(req.model)
        tokens = len(encoding.encode(req.text))
        return {"tokens": tokens}
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))


# ---------------------------------------------------------------------------
# Speech-to-Text endpoint — multi-provider
# ---------------------------------------------------------------------------
SUPPORTED_FORMATS = {".mp3", ".mp4", ".wav", ".m4a", ".ogg", ".flac", ".webm"}
MAX_FILE_SIZE_MB   = 25


@app.post("/transcribe")
async def transcribe(
    file:     UploadFile = File(..., description="Audio file to transcribe"),
    provider: str        = "groq",   # "groq" | "openai" — default: groq
    language: str | None = None,     # e.g. "en", "hi" — None = auto-detect
):
    """
    Transcribes an audio file using the specified STT provider.

    - **file**: audio file (mp3, wav, m4a, ogg, flac, webm, mp4) — max 25 MB
    - **provider**: which STT backend to use — `groq` (default) or `openai`
    - **language**: optional BCP-47 language code (auto-detected if omitted)

    Example call:
        import requests
        with open("audio.mp3", "rb") as f:
            r = requests.post(
                "http://your-host/transcribe",
                files={"file": ("audio.mp3", f, "audio/mpeg")},
                params={"provider": "groq", "language": "en"},
            )
        print(r.json())
    """
    # Validate provider
    provider = provider.lower().strip()
    if provider not in SUPPORTED_PROVIDERS:
        raise HTTPException(
            status_code=400,
            detail=f"Unsupported provider '{provider}'. Supported: {', '.join(SUPPORTED_PROVIDERS)}",
        )

    # Validate file extension
    _, ext = os.path.splitext(file.filename or "")
    if ext.lower() not in SUPPORTED_FORMATS:
        raise HTTPException(
            status_code=415,
            detail=f"Unsupported file type '{ext}'. Supported: {', '.join(SUPPORTED_FORMATS)}",
        )

    # Save upload to a temp file
    try:
        with tempfile.NamedTemporaryFile(delete=False, suffix=ext) as tmp:
            shutil.copyfileobj(file.file, tmp)
            tmp_path = tmp.name
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Failed to save upload: {e}")

    try:
        # File size check
        file_size_mb = os.path.getsize(tmp_path) / (1024 * 1024)
        if file_size_mb > MAX_FILE_SIZE_MB:
            raise HTTPException(
                status_code=413,
                detail=f"File too large ({file_size_mb:.1f} MB). Limit is {MAX_FILE_SIZE_MB} MB.",
            )

        # Route to correct provider
        if provider == "groq":
            return await _transcribe_groq(tmp_path, file.filename, language)

        elif provider == "openai":
            return await _transcribe_openai(tmp_path, file.filename, language)

        # ↓ Add new providers here in the future
        # elif provider == "assemblyai":
        #     return await _transcribe_assemblyai(tmp_path, file.filename, language)

    except HTTPException:
        raise
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Transcription failed: {e}")
    finally:
        os.remove(tmp_path)


# ---------------------------------------------------------------------------
# Provider implementations
# ---------------------------------------------------------------------------

async def _transcribe_groq(tmp_path: str, filename: str, language: str | None) -> dict:
    if not groq_client:
        raise HTTPException(status_code=503, detail="Groq provider not configured. Set GROQ_API_KEY.")

    with open(tmp_path, "rb") as audio_file:
        params = {
            "file":            (filename, audio_file),
            "model":           "whisper-large-v3",
            "response_format": "verbose_json",
        }
        if language:
            params["language"] = language

        result = groq_client.audio.transcriptions.create(**params)

    return {
        "text":             result.text.strip(),
        "language":         getattr(result, "language", None),
        "duration_seconds": round(getattr(result, "duration", 0), 2),
        "provider":         "groq",
    }


async def _transcribe_openai(tmp_path: str, filename: str, language: str | None) -> dict:
    openai_api_key = os.environ.get("OPENAI_API_KEY")
    if not openai_api_key:
        raise HTTPException(status_code=503, detail="OpenAI provider not configured. Set OPENAI_API_KEY.")

    try:
        from openai import OpenAI
        openai_client = OpenAI(api_key=openai_api_key)
    except ImportError:
        raise HTTPException(status_code=503, detail="OpenAI package not installed. Run: pip install openai")

    with open(tmp_path, "rb") as audio_file:
        params = {
            "model":           "whisper-1",
            "file":            (filename, audio_file),
            "response_format": "verbose_json",
        }
        if language:
            params["language"] = language

        result = openai_client.audio.transcriptions.create(**params)

    return {
        "text":             result.text.strip(),
        "language":         getattr(result, "language", None),
        "duration_seconds": round(getattr(result, "duration", 0), 2),
        "provider":         "openai",
    }