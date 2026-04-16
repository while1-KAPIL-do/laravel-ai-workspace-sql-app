<?php

namespace App\Ai\Tools;

use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Stringable;

class GetDatabaseSchema implements Tool
{
    public function description(): Stringable|string
    {
        return 'Returns database schema from SQL file';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            // No input required
        ];
    }

    public function handle(Request $request): Stringable|string
    {
        $path = storage_path('schemas/schooldb.sql');

        if (!file_exists($path)) {
            return 'Schema file not found';
        }

        return file_get_contents($path);
    }
}