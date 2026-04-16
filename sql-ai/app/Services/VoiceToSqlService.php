<?php

namespace App\Services;

use App\Ai\Agents\MySqlExpert;
use App\Services\Token\TokenManager;
use App\Services\Transcription\WhisperClient;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Transcription;
use Exception;

class VoiceToSqlService
{
    public function __construct(
        protected TokenManager $tokenManager
    ) {}

    public function handle(Request $request): JsonResponse
    {
        $audioPath = null;
        $userQuestion = null;
        $sql = null;

        Log::info('VoiceToSql process started', [
            'ip' => $request->ip()
        ]);

        try {
            // 1. Validation
            Log::info('Step 1: Validating request');
            $request->validate([
                'text_query' => 'nullable|string|max:5000',
                'audio_file' => 'nullable|file|mimes:mp3,wav,webm,ogg|max:20480',
            ]);

            if (
                (! $request->filled('text_query') || trim($request->text_query) === '') &&
                ! $request->hasFile('audio_file')
            ) {
                throw new Exception("Please provide either text or audio input.");
            }

            $userQuestion = null;

            // TEXT FIRST
            if ($request->filled('text_query') && trim($request->text_query) !== '') {
                Log::info('Step 2: Using text query');
                $userQuestion = trim($request->text_query);
            }
            // AUDIO
                elseif ($request->hasFile('audio_file')) {
                    $file = $request->hasFile('audio_file')
                    ? $request->file('audio_file')
                    : $request->file('audio');

                if (!$file) {
                    return response()->json(['error' => 'No audio file provided'], 422);
                }

                $whisper = new WhisperClient();
                $result  = $whisper->transcribe($file);
                $userQuestion  = $result['text'] ?? '' ;       
            }

            // FINAL CHECK
            if (empty(trim($userQuestion))) {
                throw new Exception('No valid input provided.');
            }

            Log::info('Transcription successful', ['question_length' => strlen($userQuestion)]);

            // 4. Token Limit Check
            Log::info('Step 4: Checking token limit');
            $tokenResult = $this->tokenManager->validate($userQuestion, $request->ip());
            if (!$tokenResult['allowed']) {
                throw new Exception('Token limit exceeded.');
            }

            // 5. Generate SQL
            Log::info('Step 5: Generating SQL');
            $agentResponse = $this->generateSql($userQuestion);

            if(empty($agentResponse)){
                throw new Exception('MySql Agent, Failed!');
            }
            
            Log::info('SQL generated', ['agentResponse' => $agentResponse]);

            // 6. Safety Check
            Log::info('Step 6: Validating SQL safety');
            $this->validateSqlSafety($agentResponse['sql']);

            // 7. Cleanup
            // ==================== IMPORTANT: RECORD TOKEN USAGE ====================
            Log::info('Step 7: Recording token usage');
            $this->tokenManager->record(
                $request->ip(),
                $agentResponse['tokens']['input'],
                $agentResponse['tokens']['output']
            );
            // =====================================================================

            // 8. Cleanup
            Log::info('Step 9: Cleaning up audio file');
            $this->cleanupAudio($audioPath);

            Log::info('VoiceToSql process completed successfully');
            return $this->successResponse($userQuestion, $agentResponse['sql']);

        } catch (Exception $e) {
            $this->cleanupAudio($audioPath);

            Log::error('VoiceToSql failed', [
                'error'         => $e->getMessage(),
                'user_question' => $userQuestion,
                'sql'           => $sql
            ]);

            return $this->errorResponse($e, $userQuestion, $sql);
        }
    }

    // ====================== Private Methods (unchanged) ======================

    private function uploadAudio(Request $request): string
    {
        $file = $request->hasFile('audio_file') 
            ? $request->file('audio_file') 
            : $request->file('audio');

        return $file->store('voice-input/' . now()->format('Y-m-d'));
    }

    private function transcribeAudioFromDefault(string $path): string
    {
        $transcript = Transcription::fromStorage($path)->generate();
        return trim((string) $transcript);
    }

    private function generateSql(string $userQuestion)
    {
        // Step 1: Init agent
        $agent = new MySqlExpert();

        // Step 2: Call AI (LLM call)
        $agentResponse = $agent->prompt($userQuestion);

        // Step 3: Extract SQL
        $sql = (string) $agentResponse;

        // Step 4: Safe extraction
        $inputTokens = $agentResponse?->usage?->promptTokens ?? null;
        $outputTokens = $agentResponse?->usage?->completionTokens ?? null;

        return [
            'question' => $userQuestion,
            'sql' => $sql,

            // REAL tokens (no estimation)
            'tokens' => [
                'input' => $inputTokens,
                'output' => $outputTokens,
                'total' => $inputTokens + $outputTokens,
            ],
        ];
    }

    // will need below in futute
    // private function generateSql(string $userQuestion)
    // {
    //     // Step 1: Init agent
    //     $agent = new MySqlExpert();
    //     $tokenizer = new PythonTokenizerClient();

    //     // Step 2: Build FULL prompt (important)
    //     $fullPrompt = $agent->buildFullPrompt($userQuestion);


    //     // Step 3: Count REAL input tokens
    //     $inputTokens = $tokenizer->getTokens($fullPrompt);

    //     // Step 4: Call AI
    //     $agentResponse = $agent->prompt($userQuestion);
    //     $sql = str($agentResponse);

    //     // Step 5: Count output tokens
    //     $outputTokens = $tokenizer->getTokens((string)$sql);

    //     $response = [
    //         'full_prompt'   => $fullPrompt,
    //         'input_tokens'  => $inputTokens,
    //         'output_tokens' => $outputTokens,
    //         'sql'           => (string)$sql
    //     ];

    //     return $response;
    // }

    private function validateSqlSafety(string $sql): void
    {
        $upper = strtoupper($sql);
        $dangerous = ['INSERT', 'UPDATE', 'DELETE', 'DROP', 'TRUNCATE', 'ALTER', 'CREATE'];

        foreach ($dangerous as $word) {
            if (str_contains($upper, $word . ' ')) {
                throw new Exception("Unsafe SQL operation: {$word}");
            }
        }

        if (!str_starts_with($upper, 'SELECT ')) {
            throw new Exception("Only SELECT queries are allowed.");
        }
    }

    private function cleanupAudio(?string $path): void
    {
        if ($path && Storage::exists($path)) {
            Storage::delete($path);
        }
    }

    private function successResponse(string $userQuestion, string $sql): JsonResponse
    {
        return response()->json([
            'success'         => true,
            'user_question'   => $userQuestion,
            'generated_sql'   => $sql,
            'row_count'       => 0,
            // 'results_preview' => ,
            'spoken_summary'  => "Here are the results for: {$userQuestion}",
        ]);
    }

    private function errorResponse(Exception $e, ?string $userQuestion = null, ?string $sql = null): JsonResponse
    {
        return response()->json([
            'success'        => false,
            'user_question'  => $userQuestion,
            'generated_sql'  => $sql,
            'error'          => $e->getMessage(),
            'spoken_summary' => "Sorry, something went wrong: " . $e->getMessage(),
        ], 422);
    }
}