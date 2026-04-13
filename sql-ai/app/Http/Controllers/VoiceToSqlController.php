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

        // Convert JSON response to session so Blade can read it
        if ($response->getStatusCode() === 200 || $response->getStatusCode() === 422) {
            $data = $response->getData(true);
            return redirect()->back()->with('result', $data);
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
        $tables = DB::select('SHOW TABLES');
        $dbName = DB::getDatabaseName();
        $schema = [];

        foreach ($tables as $tableObj) {
            $tableName = array_values((array) $tableObj)[0];

            $rowCount = DB::table($tableName)->count();

            $columns = DB::select("
                SELECT COLUMN_NAME as name, 
                    DATA_TYPE   as type, 
                    COLUMN_KEY  as `key`
                FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
                ORDER BY ORDINAL_POSITION
            ", [$dbName, $tableName]);

            $schema[] = [
                'name'      => $tableName,
                'row_count' => $rowCount,
                'columns'   => array_map(fn($col) => [
                    'name'       => $col->name,
                    'type'       => strtoupper($col->type),
                    'is_primary' => $col->key === 'PRI',
                ], $columns),
            ];
        }

        return response()->json($schema);
    }

}