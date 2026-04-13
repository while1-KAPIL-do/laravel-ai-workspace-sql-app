<?php

namespace App\Services;

use App\Ai\Agents\MySqlExpert;
use App\Services\Token\TokenManager;
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
        $inputTokens = 0;
        $outputTokens = 0;

        Log::info('VoiceToSql process started', [
            'ip' => $request->ip()
        ]);

        try {
            // 1. Validation
            Log::info('Step 1: Validating request');
            $request->validate([
                'audio_file' => 'required_without:audio|file|mimes:mp3,wav,webm,ogg|max:20480',
                'audio'      => 'required_without:audio_file|file|mimes:mp3,wav,webm,ogg|max:20480',
            ]);

            // 2. Upload Audio
            Log::info('Step 2: Uploading audio file');
            $audioPath = $this->uploadAudio($request);

            // 3. Transcribe
            Log::info('Step 3: Transcribing audio');
            $userQuestion = $this->transcribeAudio($audioPath);

            if (empty(trim($userQuestion))) {
                throw new Exception('No speech detected in the audio.');
            }

            Log::info('Transcription successful', ['question_length' => strlen($userQuestion)]);

            // 4. Token Limit Check
            Log::info('Step 4: Checking token limit');
            $tokenResult = $this->tokenManager->validate($userQuestion, $request->ip());
            if (!$tokenResult['allowed']) {
                throw new Exception('Token limit exceeded.');
            }

            $inputTokens = $tokenResult['tokens'] ?? 0;

            // 5. Generate SQL
            Log::info('Step 5: Generating SQL');
            $sql = $this->generateSql($userQuestion);

            // Estimate output tokens (rough estimation)
            $outputTokens = (int) (strlen($sql) / 4);   // rough approximation

            Log::info('SQL generated', ['sql_length' => strlen($sql)]);

            // 6. Safety Check
            Log::info('Step 6: Validating SQL safety');
            $this->validateSqlSafety($sql);

            // 7. Execute SQL
            Log::info('Step 7: Executing SQL');
            $results = $this->executeSql($sql);

            Log::info('SQL executed', ['row_count' => count($results)]);

            // ==================== IMPORTANT: RECORD TOKEN USAGE ====================
            Log::info('Step 8: Recording token usage');
            $this->tokenManager->record(
                $request->ip(),
                $inputTokens,
                $outputTokens
            );
            // =====================================================================

            // 9. Cleanup
            Log::info('Step 9: Cleaning up audio file');
            $this->cleanupAudio($audioPath);

            Log::info('VoiceToSql process completed successfully');

            return $this->successResponse($userQuestion, $sql, $results);

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

    private function transcribeAudio(string $path): string
    {
        $transcript = Transcription::fromStorage($path)->generate();
        return trim((string) $transcript);
    }

    private function generateSql(string $userQuestion): string
    {
        $agent = new MySqlExpert();

        $prompt = <<<PROMPT
User asked: "{$userQuestion}"

Follow the mandatory workflow strictly:
1. First call the get_database_schema tool.
2. Analyze the real schema.
3. Generate the correct SELECT query using exact column names.
4. Return ONLY the final SQL query.
Do not explain.
PROMPT;

        $response = $agent->prompt($prompt);
        $sql = trim((string) $response);
        $sql = rtrim($sql, ';');

        if (!str_contains(strtoupper($sql), 'LIMIT')) {
            $sql .= ' LIMIT 500';
        }

        return $sql;
    }

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

    private function executeSql(string $sql): array
    {
        return DB::select($sql);
    }

    private function cleanupAudio(?string $path): void
    {
        if ($path && Storage::exists($path)) {
            Storage::delete($path);
        }
    }

    private function successResponse(string $userQuestion, string $sql, array $results): JsonResponse
    {
        return response()->json([
            'success'         => true,
            'user_question'   => $userQuestion,
            'generated_sql'   => $sql,
            'row_count'       => count($results),
            'results_preview' => array_slice($results, 0, 5),
            'spoken_summary'  => "Here are the results for: “{$userQuestion}”. " . count($results) . " row(s) found.",
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