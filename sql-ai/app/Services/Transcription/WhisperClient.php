<?php

namespace App\Services\Transcription;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\UploadedFile;

class WhisperClient
{
    // Supported providers — must match FastAPI SUPPORTED_PROVIDERS
    const PROVIDER_GROQ   = 'groq';
    const PROVIDER_OPENAI = 'openai';

    
    public function transcribe(
        UploadedFile $file,           // ← directly from $request->file()
        string  $provider = self::PROVIDER_GROQ,
        ?string $language = null
    ): ?array {
        try {
            $params = ['provider' => $provider];
            if ($language) {
                $params['language'] = $language;
            }

            $url = env('LLM_SERVICE_BASE_HOST') . '/transcribe?' . http_build_query($params);

            $response = Http::timeout(60)
                ->retry(2, 200)
                ->attach(
                    'file',
                    file_get_contents($file->getRealPath()),  // ← read from temp path
                    $file->getClientOriginalName()            // ← original filename with extension
                )
                ->post($url);
                

            if ($response->failed()) {
                Log::error('WhisperClient: transcription request failed', [
                    'provider' => $provider,
                    'status'   => $response->status(),
                    'body'     => $response->body(),
                ]);
                return null;
            }

            $data = $response->json();

            if (empty($data['text'])) {
                Log::warning('WhisperClient: empty transcription returned', ['response' => $data]);
                return null;
            }

            return [
                'text'             => $data['text'],
                'language'         => $data['language']         ?? null,
                'duration_seconds' => $data['duration_seconds'] ?? null,
                'provider'         => $data['provider']         ?? $provider,
            ];

        } catch (\Exception $e) {
            Log::error('WhisperClient: service failed', [
                'provider' => $provider,
                'error'    => $e->getMessage(),
            ]);
            return null;
        }
    }
}