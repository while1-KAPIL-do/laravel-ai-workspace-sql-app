<?php

namespace App\Services\Token;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PythonTokenizerClient
{
    public function getTokens(string $text): ?int
    {
        try {
            $response = Http::timeout(1.5)
                ->retry(2, 100)
                ->post(config('tokens.python_service_url'), [
                    'text' => $text
                ]);

            return $response->json()['tokens'] ?? null;

        } catch (\Exception $e) {
            Log::error('Tokenizer service failed', ['error' => $e->getMessage()]);
            return null; // fallback trigger
        }
    }
}