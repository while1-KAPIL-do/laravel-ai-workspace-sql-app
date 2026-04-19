<?php
namespace App\Ai\Tools;

use App\Models\SchemaModel;
use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Stringable;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\JsonSchema;

class GetDatabaseSchema implements Tool
{
    public function description(): Stringable|string
    {
        return 'Returns the full database schema for the current session. Always call this first before generating any SQL.';
    }

    public function schema(JsonSchema $schema): array
    {
        return []; // No input required
    }

    public function handle(Request $request): Stringable|string
    {
        $sessionId = session()->getId();

        // 1. Try to find active schema for this session
        $schemaRecord = SchemaModel::where('session_id', $sessionId)
            ->where('is_active', true)
            ->latest()
            ->first();

        if ($schemaRecord && !empty($schemaRecord->raw_sql)) {
            return $schemaRecord->raw_sql;
        }

        // 2. Fallback to default schema
        $path = storage_path('schemas/schooldb.sql');

        if (!file_exists($path)) {
            return 'No schema available. Ask the user to upload their database schema file.';
        }

        return file_get_contents($path);
    }
}