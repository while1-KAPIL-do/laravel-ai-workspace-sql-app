from fastapi import FastAPI, HTTPException
from pydantic import BaseModel
import tiktoken

app = FastAPI()

class TokenRequest(BaseModel):
    text: str
    model: str = "gpt-4"

@app.get("/health")
def health():
    return {"status": "ok"}

@app.post("/count-tokens")
def count_tokens(req: TokenRequest):
    print("Token API HIT:", req.text)
    try:
        encoding = tiktoken.encoding_for_model(req.model)
        tokens = len(encoding.encode(req.text))
        return {"tokens": tokens}
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))