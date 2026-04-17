<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SchemaModel;
use App\Services\SqlSchemaParser;
use Illuminate\Support\Facades\Log;

class SchemaController extends Controller
{
    // public function __construct(
    //     protected TokenAnalyticsService $service
    // ) {}


    public function uploadSchema(Request $request)
    {
        $request->validate([
            'schema' => 'required|file|mimes:sql,txt|max:5120', // 5MB limit
        ]);

        try {
            $file = $request->file('schema');
            $sql = file_get_contents($file->getRealPath());

            // Basic validation
            if (empty(trim($sql))) {
                return response()->json(['error' => 'The SQL file is empty.'], 422);
            }

            // Logic check: If you want to limit for LLM processing, 
            // keep this, but 200,000 characters is quite small for a DB schema.
            if (strlen($sql) > 500000) { 
                throw new \Exception("Schema exceeds the maximum character limit for processing.");
            }

            $sessionId = session()->getId();

            // 1. Deactivate old schemas for this session
            SchemaModel::where('session_id', $sessionId)
                ->update(['is_active' => false]);

            // 2. Store the file on disk
            $path = $file->store('schemas');

            // 3. Create the record
            // Note: Cleaned up substr_count to be slightly more reliable
            $schema = SchemaModel::create([
                'user_id'       => null, // Good practice to link user if logged in
                'session_id'    => $sessionId,
                'file_path'     => $path,
                'schema_json'   => null,
                'raw_sql'       => $sql,
                'tables_count'  => preg_match_all('/CREATE\s+TABLE/i', $sql),
                'columns_count' => substr_count($sql, ','), 
                'is_active'     => true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Schema uploaded and stored successfully',
                'schema_id' => $schema->id,
            ]);

        } catch (\Throwable $e) {
            Log::error("Schema Upload Error: " . $e->getMessage());
            return response()->json([
                'error' => 'Upload failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function dbSchema(Request $request)
    {
        $sessionId = session()->getId();
        $sql = null;

        // 1. Try to find the active schema for this session in the DB
        $activeSchema = SchemaModel::where('session_id', $sessionId)
            ->where('is_active', true)
            ->latest()
            ->first();

        if ($activeSchema && !empty($activeSchema->raw_sql)) {
            // Use the SQL stored in the database
            $sql = $activeSchema->raw_sql;
        } else {
            // 2. Fallback to default local file
            $defaultPath = storage_path('app/schemas/schooldb.sql'); 
            
            if (file_exists($defaultPath)) {
                $sql = file_get_contents($defaultPath);
            }
        }

        // If no SQL found in DB or File, return error
        if (!$sql) {
            return response()->json(['error' => 'No schema available for this session.'], 404);
        }

        // 3. Parse the SQL
        // Refined Regex: Matches CREATE TABLE while handling multi-line and case sensitivity
        preg_match_all('/CREATE TABLE\s+[`"\[]?(\w+)[`"\]]?\s*\((.*?)\)\s*(?:ENGINE|DEFAULT| CHARSET|;|$)/is', $sql, $matches, PREG_SET_ORDER);

        $schema = [];

        foreach ($matches as $match) {
            $tableName = $match[1];
            $columnsRaw = $match[2];

            // Better splitting: split by newline instead of comma to avoid breaking on DECIMAL(10,2)
            $lines = preg_split('/\r\n|\r|\n/', $columnsRaw);

            $columns = [];

            foreach ($lines as $line) {
                $line = trim($line, " \t\n\r\0\x0B,"); // Clean trailing commas and whitespace

                if (empty($line)) continue;

                $upperLine = strtoupper($line);

                // Skip constraints and keys
                if (
                    str_starts_with($upperLine, 'FOREIGN KEY') || 
                    str_starts_with($upperLine, 'PRIMARY KEY') || 
                    str_starts_with($upperLine, 'KEY') || 
                    str_starts_with($upperLine, 'CONSTRAINT') ||
                    str_starts_with($upperLine, 'UNIQUE')
                ) {
                    continue;
                }

                // Match column name and type
                // Group 1: Name, Group 2: Type
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

}
