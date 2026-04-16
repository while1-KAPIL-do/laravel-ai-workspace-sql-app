<?php

namespace App\Http\Controllers;

use App\Services\VoiceToSqlService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VoiceToSqlController extends Controller
{
    protected $voiceToSqlService;

    public function __construct(VoiceToSqlService $voiceToSqlService)
    {
        $this->voiceToSqlService = $voiceToSqlService;
    }

    public function process(Request $request)
    {
        $response = $this->voiceToSqlService->handle($request);
        $data     = $response->getData(true);

        if ($response->getStatusCode() === 200) {
            return redirect()->back()->with('result', $data);
        }

        if ($response->getStatusCode() === 422) {
            return redirect()->back()->with('error', $data);  // separate key
        }

        return $response;
    }

    public function executeSql(Request $request)
    {
        try {
            $sql = $request->input('sql');

            // only allow SELECT
            if (!str_starts_with(strtolower(trim($sql)), 'select')) {
                return response()->json([
                    'error' => 'Only SELECT queries are allowed'
                ], 400);
            }

            $result = DB::select($sql);

            return response()->json([
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function dbSchema(Request $request)
{
    $path = storage_path('schemas/schooldb.sql');

    if (!file_exists($path)) {
        return response()->json(['error' => 'Schema file not found'], 404);
    }

    $sql = file_get_contents($path);

    // Match CREATE TABLE blocks
    preg_match_all('/CREATE TABLE\s+(\w+)\s*\((.*?)\);/is', $sql, $matches, PREG_SET_ORDER);

    $schema = [];

    foreach ($matches as $match) {
        $tableName = $match[1];
        $columnsRaw = $match[2];

        $lines = explode(",", $columnsRaw);

        $columns = [];

        foreach ($lines as $line) {
            $line = trim($line);

            // Skip constraints
            if (str_starts_with(strtoupper($line), 'FOREIGN KEY')) continue;
            if (str_starts_with(strtoupper($line), 'PRIMARY KEY')) continue;

            preg_match('/^(\w+)\s+([a-zA-Z]+)/', $line, $colMatch);

            if (!$colMatch) continue;

            $columns[] = [
                'name' => $colMatch[1],
                'type' => strtoupper($colMatch[2]),
                'is_primary' => str_contains(strtoupper($line), 'PRIMARY KEY'),
            ];
        }

        $schema[] = [
            'name' => $tableName,
            'row_count' => 0, // since no real DB
            'columns' => $columns,
        ];
    }

    return response()->json($schema);
}

}