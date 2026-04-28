<?php

namespace App\Services;

use Illuminate\Http\Request;
use App\Models\SchemaModel;
use App\Ai\Support\SchemaParser;
use App\Services\Token\PythonTokenizerClient;
use Illuminate\Support\Facades\DB;

class SchemaService
{
    public function uploadSchema(Request $request)
    {
        $file = $request->file('schema');
        $sql = file_get_contents($file->getRealPath());

        if (empty(trim($sql))) {
            return response()->json(['error' => 'The SQL file is empty.'], 422);
        }

        if (strlen($sql) > 500000) { 
            throw new \Exception("Schema exceeds the maximum character limit for processing.");
        }

        $sessionId = session()->getId();

        SchemaModel::where('session_id', $sessionId)
            ->update(['is_active' => false]);

        $path = $file->store('schemas');

        $parsed = SchemaParser::parse($sql);
        $compactSummary = SchemaParser::toCompactSummary($parsed);

        $tokenizer = new PythonTokenizerClient();
        $estimatedSchemaTokens = $tokenizer->getTokens($compactSummary);

        $schema = SchemaModel::create([
            'user_id'       => null,
            'session_id'    => $sessionId,
            'file_path'     => $path,
            'schema_json'   => json_encode($parsed),
            'raw_sql'       => $sql,
            'tables_count'  => count($parsed),
            'columns_count' => array_sum(
                array_map(fn($t) => count($t['columns']), $parsed)
            ),
            'is_active'     => true,
            'estimated_tokens' => $estimatedSchemaTokens
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Schema uploaded and stored successfully',
            'data'    => [
                'schema_id'               => $schema->id,
                'tables_count'            => count($parsed),
                'estimated_upload_tokens' => $estimatedSchemaTokens,
            ]
        ]);
    }


    public function dbSchema()
    {
        $sessionId = session()->getId();
        $sql = null;

        $activeSchema = $this->getActiveSchema($sessionId);

        if ($activeSchema && !empty($activeSchema->raw_sql)) {
            $sql = $activeSchema->raw_sql;
        } else {
            $defaultPath = storage_path('app/schemas/schooldb.sql'); 
            
            if (file_exists($defaultPath)) {
                $sql = file_get_contents($defaultPath);
            }
        }

        if (!$sql) {
            return response()->json(['error' => 'No schema available for this session.'], 404);
        }

        preg_match_all('/CREATE TABLE\s+[`"\[]?(\w+)[`"\]]?\s*\((.*?)\)\s*(?:ENGINE|DEFAULT| CHARSET|;|$)/is', $sql, $matches, PREG_SET_ORDER);

        $schema = [];

        foreach ($matches as $match) {
            $tableName = $match[1];
            $columnsRaw = $match[2];

            $lines = preg_split('/\r\n|\r|\n/', $columnsRaw);

            $columns = [];

            foreach ($lines as $line) {
                $line = trim($line, " \t\n\r\0\x0B,");

                if (empty($line)) continue;

                $upperLine = strtoupper($line);

                if (
                    str_starts_with($upperLine, 'FOREIGN KEY') || 
                    str_starts_with($upperLine, 'PRIMARY KEY') || 
                    str_starts_with($upperLine, 'KEY') || 
                    str_starts_with($upperLine, 'CONSTRAINT') ||
                    str_starts_with($upperLine, 'UNIQUE')
                ) {
                    continue;
                }

                preg_match('/^[`"\[]?(\w+)[`"\]]?\s+([a-zA-Z]+)/', $line, $colMatch);

                if ($colMatch) {
                    $columns[] = [
                        'name' => $colMatch[1],
                        'type' => strtoupper($colMatch[2]),
                        'is_primary' => str_contains($upperLine, 'PRIMARY KEY'),
                    ];
                }
            }

            $schema[] = [
                'name' => $tableName,
                'row_count' => 0, 
                'columns' => $columns,
            ];
        }

        return response()->json($schema);
    }


    public function executeSql(Request $request)
    {
        $sql = $request->input('sql');

        if (!str_starts_with(strtolower(trim($sql)), 'select')) {
            return response()->json([
                'error' => 'Only SELECT queries are allowed'
            ], 400);
        }

        $result = DB::select($sql);

        return response()->json([
            'data' => $result
        ]);
    }

    public function getActiveSchema($sessionId)
    {
        return SchemaModel::where('session_id', $sessionId)
            ->where('is_active', true)
            ->latest()
            ->first();
    }

}