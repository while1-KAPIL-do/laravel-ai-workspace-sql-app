<?php

namespace App\Services;

use App\Ai\Agents\MySqlExpert;
use App\Services\Token\TokenManager;
use App\Services\Transcription\WhisperClient;
use App\Support\Utils\LlmConfig;
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
            // 1. Fetch model
            Log::info('Step 1: Fetch model');
            $provider = LlmConfig::resolveProvider($request->input('provider'));
            $model = LlmConfig::resolveModel($provider, $request->input('model'));

            // 2. Restriction check
            if (LlmConfig::isRestricted($provider, $model)) {
                return response()->json([
                    'error' => 'This model is restricted. Please contact to admin or choose a cheaper model.'
                ], 403);
            }

            if (
                (! $request->filled('text_query') || trim($request->text_query) === '') &&
                ! $request->hasFile('audio_file')
            ) {
                throw new Exception("Please provide either text or audio input.");
            }


            // 3. Fetch User Query
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
                $result  = $whisper->transcribe($file); #TODO - Will handle provider and model for this also
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
            $agentResponse = $this->generateSql($userQuestion, $provider, $model);

            if(empty($agentResponse)){
                throw new Exception('MySql Agent, Failed!');
            }
            Log::info('SQL generated', ['agentResponse' => $agentResponse]);


            // 6. Safety Check
            // Log::info('Step 6: Validating SQL safety');
            // $this->validateSqlSafety($agentResponse['sql']); // Will inform user -> Query is unsafe if non SELECT Query come


            // 7. Cleanup
            // ==================== IMPORTANT: RECORD TOKEN USAGE ====================
            Log::info('Step 7: Recording token usage');
            $this->tokenManager->record(
                $request->ip(),
                $agentResponse['tokens']['input'],
                $agentResponse['tokens']['output'],
                $provider,
                $model
            );
            // =====================================================================


            // 8. Cleanup
            Log::info('Step 8: Cleaning up audio file');
            $this->cleanupAudio($audioPath);


            Log::info('VoiceToSql process completed successfully');
            return $this->successResponse($userQuestion, $agentResponse['sql']);

        } catch (Exception $e) {
            $this->cleanupAudio($audioPath);
            Log::error('VoiceToSql failed', [
                'error'         => $e->getMessage(),
                'user_question' => $userQuestion,
                'sql'           => $sql,
                'trace'         => $e->getTrace()
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

    
    public function generateSql(
        string $userQuestion,
        string $provider = 'openai',
        string $model    = 'gpt-4o-mini'
    ): array {
        $agent       = new MySqlExpert();
        $totalInput  = 0;
        $totalOutput = 0;
        $attempts    = 0;
        $lastSql     = '';
        $lastErrors  = [];

        $question = $userQuestion;

        for ($attempt = 1; $attempt <= 3; $attempt++) {
            $attempts++;

            // On retries, append the validation errors so the agent self-corrects
            $prompt = $attempt === 1
                ? $question
                : $question . "\n\n[CORRECTION REQUIRED] Your previous SQL had these errors:\n"
                  . implode("\n", array_map(fn($e) => "- {$e}", $lastErrors))
                  . "\nFix only those errors. Return only the corrected SQL.";

            $response = $agent->prompt($prompt, [], $provider, $model);

            $sql          = trim((string) $response);
            $inputTokens  = $response?->usage?->promptTokens    ?? 0;
            $outputTokens = $response?->usage?->completionTokens ?? 0;

            $totalInput  += $inputTokens;
            $totalOutput += $outputTokens;

            // Validate the SQL against the real schema
            $errors = $agent->validateOutput($sql);

            if (empty($errors)) {
                // Good SQL — return it
                return [
                    'instructions' => $agent->instructions(),
                    'question' => $userQuestion,
                    'sql'      => $sql,
                    'attempts' => $attempts,
                    'tokens'   => [
                        'input'  => $totalInput,
                        'output' => $totalOutput,
                        'total'  => $totalInput + $totalOutput,
                    ],
                ];
            }

            // Not valid yet — store for next retry prompt
            $lastSql    = $sql;
            $lastErrors = $errors;
        }

        // Exhausted retries — return best attempt with error flag
        return [
            'instructions' => $agent->instructions(),
            'question'        => $userQuestion,
            'sql'             => $lastSql,
            'attempts'        => $attempts,
            'validation_errors' => $lastErrors,
            'tokens'          => [
                'input'  => $totalInput,
                'output' => $totalOutput,
                'total'  => $totalInput + $totalOutput,
            ],
        ];
    }

    // will need below in futute for token calculations
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
            'results_preview' => 1,
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