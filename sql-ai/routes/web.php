<?php

use Illuminate\Support\Facades\Route;
use App\Ai\Agents\MySqlExpert;
use App\Http\Controllers\TokenAnalyticsController;
use Laravel\Ai\Transcription;
use Laravel\Ai\Audio;
use App\Ai\Tools\GetDatabaseSchema;
use Laravel\Ai\Tools\Request as AIReq;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\VoiceToSqlController;
use App\Services\Token\PythonTokenizerClient;
use App\Http\Controllers\SchemaController;


Route::get('/', function () {
    return view('welcome');
});


Route::prefix('/schema')->group(function () {
    Route::post('/upload', [SchemaController::class, 'uploadSchema'])->name('schema.upload');
    Route::post('/execute-sql', [SchemaController::class, 'executeSql']);
    Route::get('/analytics/schema', [SchemaController::class, 'dbSchema']);
});


Route::prefix('ai')->group(function () {
    Route::post('/sql-assitance', [VoiceToSqlController::class, 'process'])->name('ai-sql-assitance');
    Route::get('/sql-assitance', [VoiceToSqlController::class, 'getPageData'])->name('ai-sql-assitance-index');
});


Route::get('/dashboard/tokens', function () {
    return view('token-dashboard.index');
});


///////////////////////////////// TEST ROUTES /////////////////////////////////

Route::get('/test-schema-tokens', function () {
    $tool = new GetDatabaseSchema();
    $request = new AIReq([]);

    $schema = $tool->handle($request);

    $tokens = (new PythonTokenizerClient())->getTokens($schema);

    return response()->json([
        'tokens' => $tokens,
        'characters' => strlen($schema),
    ]);
});

Route::get('/test/python-engine/token', function () {
    
    $tests = [
        [
            "input" => "Hello world",
            "expected_tokens" => 2
        ],
        [
            "input" => "Hello, world!",
            "expected_tokens" => 4
        ],
        [
            "input" => "This is a test sentence.",
            "expected_tokens" => 6
        ],
        [
            "input" => "The price is 100 dollars.",
            "expected_tokens" => 7
        ],
        [
            "input" => "Hello     world",
            "expected_tokens" => 2
        ],
        [
            "input" => "Hello\nworld",
            "expected_tokens" => 3
        ],
        [
            "input" => "My Name is Kapillllllllllllllllllll",
            "expected_tokens" => 8  // long word splits into multiple tokens
        ],
        [
            "input" => "🔥",
            "expected_tokens" => 2
        ],
        [
            "input" => "def add(a, b):\n    return a + b",
            "expected_tokens" => 16
        ],
        [
            "input" => "{\"name\": \"Kapil\", \"age\": 25}",
            "expected_tokens" => 14
        ],
        [
            "input" => "SELECT * FROM users WHERE age > 25;",
            "expected_tokens" => 13
        ],
        [
            "input" => "OpenAI provides powerful language models.",
            "expected_tokens" => 8
        ],
    ];

    $pythonClient = new PythonTokenizerClient();
    
    foreach ($tests as $test) {
        $actual = $pythonClient->getTokens($test['input']);

        echo "Input: " . $test['input'] . PHP_EOL;
        echo "Expected: " . $test['expected_tokens'] . " | Actual: " . $actual . PHP_EOL;
        echo "-----------------------------<br>" . PHP_EOL;
    }

    return $actual;
});


Route::get('/test-users', function () {
    $users = DB::table('users')
                ->select('id', 'name', 'email', 'created_at')
                ->orderBy('created_at', 'desc')
                ->get();

                dd($users->toArray());
});

Route::get('/test-sql-agent', function () {
    $response = (new MySqlExpert)->prompt('Get all users created today');
    dd($response);
});

// ELEVEN-LABS TTS
Route::get('/test-tts', function () {
    $audio = Audio::of("Get all users created today")
        ->voice('alloy')  // One of ElevenLabs' default voices; check their docs for more
        ->instructions('Speak in a warm, supportive, and clear tone like a helpful friend.')        
        ->generate()
        ->storeAs('sql_all_users_created_today.mp3');  // Saves to storage/app/public/test.mp3

    return response()->json(['audio_url' => Storage::url('sql_all_users_created_today.mp3')]);
});

Route::get('/test-stt', function (Request $request) {
    // 1. Validate the uploaded audio file
    // $request->validate([
    //     'audio' => 'required|file|mimes:mp3,wav,webm,ogg|max:10240', // max 10MB - adjust as needed
    // ]);

    try {
        // 2. Store the uploaded file temporarily
        // Better to use a dynamic name + original extension
        // $path = $request->file('audio')->store('voice-inputs'); // stores in storage/app/voice-inputs
        // $fullPath = Storage::path($path); // if fromPath() is needed (see notes)

        // 3. Transcribe using Laravel AI SDK
        // Most reliable & clean way: Transcription::fromUpload()
        // $transcript = Transcription::fromUpload(Storage::url('test.mp3'))
        //     ->generate();  // Uses your default STT provider (likely OpenAI Whisper)

        $transcript = Transcription::fromStorage('sql_q1.mp3')  // relative path in storage
            ->diarize()  // optional
            ->generate();

        // Optional: Add speaker diarization if multiple people might speak
        // $transcript = Transcription::fromUpload($request->file('audio'))
        //     ->diarize()
        //     ->generate();

        // 4. Optional: Clean up the file after transcription
        Storage::delete($path);

        // 5. Return the result
        return response()->json([
            'success'    => true,
            'transcript' => (string) $transcript,           // plain text
            'raw'        => $transcript->toArray(),         // if you want segments/timestamps
            // 'segments' => $transcript->segments ?? [],   // if diarize() used
        ]);

    } catch (\Exception $e) {
        // Log for debugging
        Log::error('Transcription failed: ' . $e->getMessage());

        return response()->json([
            'success' => false,
            'error'   => 'Transcription failed: ' . $e->getMessage(),
        ], 500);
    }
})->name('voice-to-text');

Route::get('/mp3-to-sql', function (Request $request) {
    
    // Transcribe voice to text
    $transcript = Transcription::fromStorage('sql_q1.mp3')
        ->diarize() // Optional: Detect speakers if multi-person
        ->generate();

    // Prompt the dedicated SQL agent with transcribed text
    $sqlResponse = (new MySqlExpert)->prompt((string) $transcript);

    // Optional: Synthesize response as voice (e.g., read back the SQL)
    $audioResponse = \Laravel\Ai\Audio::of((string) $sqlResponse)
        ->female() // Or ->male()
        ->instructions('Speak clearly and slowly') // Custom style
        ->generate()
        ->storeAs('response.mp3'); // Save audio file

    return response()->json([
        'transcript' => (string) $transcript,
        'sql_query' => (string) $sqlResponse,
        'audio_response_url' => Storage::url($audioResponse), // URL to synthesized audio
    ]);
});