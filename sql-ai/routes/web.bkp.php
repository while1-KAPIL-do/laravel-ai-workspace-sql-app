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
use App\Services\VoiceToSqlService;

Route::get('/', function () {
    return view('welcome');
});


# "throttle:ai" : AI route
Route::middleware(['throttle:ai'])->prefix('ai')->group(function () {

    # APP | AI | SQL Assitance
    Route::post('/sql-assitance', [VoiceToSqlController::class, 'process'])->name('ai-sql-assitance');
    Route::get('/sql-assitance', [VoiceToSqlController::class, 'getPageData'])->name('ai-sql-assitance-index');

    
    # APP | AI | TOKEN Dashboard
    Route::get('/token-dashboard', [TokenAnalyticsController::class, 'getPageData'])->name('ai-token-dashboard');

});


# "throttle:schema-upload" : Schema upload - 5/hour
Route::middleware(['throttle:schema-upload'])->prefix('/schema')->group(function () {
    Route::post('/upload', [SchemaController::class, 'uploadSchema'])->name('schema.upload');
    Route::post('/execute-sql', [SchemaController::class, 'executeSql']);
    Route::get('/analytics/schema', [SchemaController::class, 'dbSchema']);
});


# "throttle:web-general" : Analytics routes - 60/min
Route::middleware(['throttle:web-general'])->prefix('analytics')->group(function () {
    Route::get('/summary', [TokenAnalyticsController::class, 'summary']);
    Route::get('/daily', [TokenAnalyticsController::class, 'daily']);
    Route::get('/top-ips', [TokenAnalyticsController::class, 'topIps']);
    Route::get('/top-users', [TokenAnalyticsController::class, 'topUsers']);
    Route::get('/cost', [TokenAnalyticsController::class, 'cost']);
    Route::get('/cost-breakdown', [TokenAnalyticsController::class, 'costBreakdown']);
    Route::get('/filters', [TokenAnalyticsController::class, 'filters']);
});


///////////////////////////////// TEST ROUTES /////////////////////////////////

///////////////// SECURITY WEB ROUTES - STR /////////////////

/////////// 1. TEST -- IP Blocking
// Route::get('/test/ip-block', function () {
//     return response()->json([
//         'status' => 'allowed',
//         'your_ip' => request()->ip(),
//     ]);
// });

// Route::get('/test/throttle-ai', function () {
//     return response()->json([
//         'status'  => 'ok',
//         'message' => 'Request allowed',
//         'ip'      => request()->ip(),
//     ]);
// })->middleware('throttle:ai');

// Route::get('/test/throttle-web', function () {
//     return response()->json([
//         'status' => 'ok',
//         'ip'     => request()->ip(),
//     ]);
// })->middleware('throttle:web-general');


/////////// 2. TEST -- Session Blocking
// Route::get('/test/session', function () {
//     return response()->json([
//         'session_id'       => session()->getId(),
//         'bound_ip'         => session('_bound_ip'),
//         'session_created'  => session('_session_created'),
//         'last_regenerated' => session('_last_regenerated'),
//         'current_ip'       => request()->ip(),
//     ]);
// });

// Route::get('/test/session-regenerate', function () {
//     $oldId = session()->getId();
//     session(['_last_regenerated' => 0]); // force regeneration
//     return response()->json([
//         'old_session_id' => $oldId,
//         'message'        => 'Reload the /test/session route to see new ID',
//     ]);
// });


/////////// 3. TEST -- CSRF Blocking
// Route::get('/test/csrf-form', function () {
//     return response('<form method="POST" action="/test/csrf-post">
//         ' . csrf_field() . '
//         <button type="submit">Submit with valid CSRF</button>
//     </form>');
// });

// Route::get('/test/csrf-attack', function () {
//     return response('
//         <script>
//             fetch("/test/csrf-post", {
//                 method: "POST",
//                 headers: {
//                     "Content-Type": "application/json",
//                     "X-CSRF-TOKEN": "fake-bad-token-123",
//                 },
//                 body: JSON.stringify({ test: 1 })
//             })
//             .then(r => {
//                 document.body.innerHTML = "Status: " + r.status;
//                 return r.json();
//             })
//             .then(data => {
//                 document.body.innerHTML += "<pre>" + JSON.stringify(data, null, 2) + "</pre>";
//             });
//         </script>
//         <body>Sending bad CSRF request...</body>
//     ');
// });

// Route::post('/test/csrf-post', function () {
//     return response()->json(['status' => 'CSRF valid — request accepted']);
// });

/////////// 4. TEST -- Device Blocking
Route::get('/test/device', function () {
    return response()->json([
        'status'      => 'allowed',
        'fingerprint' => session('_device_fingerprint'),
        'ip'          => request()->ip(),
        'user_agent'  => request()->userAgent(),
    ]);
});

///////////////// SECURITY WEB ROUTES - END /////////////////

/////////////////////////////////////////////////////////////////////////////////

Route::get('/test/generate-sql', function () {
    // Check what VoiceToSqlService __construct() expects
    // and pass it here — e.g. if it expects a repository or another service:
    $service = app(VoiceToSqlService::class); // Laravel resolves dependencies automatically

    $result = $service->generateSql(
        userQuestion: 'show me all employees whos departments is tech',
        provider:     'openai',
        model:        'gpt-4o-mini'
    );

    return response()->json($result, 200, [], JSON_PRETTY_PRINT);
});

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