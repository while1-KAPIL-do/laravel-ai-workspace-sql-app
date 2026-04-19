<?php
namespace App\Ai\Agents;

use App\Ai\Tools\GetDatabaseSchema;
use App\Ai\Support\SchemaParser;
use App\Models\SchemaModel;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;
use Stringable;

class MySqlExpert implements Agent
{
    use Promptable;

    private array  $parsedSchema  = [];
    private string $compactSchema = '';

    public function __construct()
    {
        $this->loadSchema();
    }

    private function loadSchema(): void
    {
        $sessionId    = session()->getId();
        $schemaRecord = SchemaModel::where('session_id', $sessionId)
            ->where('is_active', true)
            ->latest()
            ->first();

        // Use pre-parsed schema_json if available (fastest — no re-parsing)
        if ($schemaRecord?->schema_json) {
            $this->parsedSchema  = json_decode($schemaRecord->schema_json, true) ?? [];
            $this->compactSchema = SchemaParser::toCompactSummary($this->parsedSchema);
            return;
        }

        // Fall back to parsing raw_sql
        if ($schemaRecord?->raw_sql) {
            $this->parsedSchema  = SchemaParser::parse($schemaRecord->raw_sql);
            $this->compactSchema = SchemaParser::toCompactSummary($this->parsedSchema);
            return;
        }

        // Last resort: default schema file
        $path = storage_path('schemas/schooldb.sql');
        if (file_exists($path)) {
            $rawSql              = file_get_contents($path);
            $this->parsedSchema  = SchemaParser::parse($rawSql);
            $this->compactSchema = SchemaParser::toCompactSummary($this->parsedSchema);
        }
    }

    public function instructions(): Stringable|string
    {
        $schemaBlock = $this->compactSchema
            ? "DATABASE SCHEMA:\n```\n{$this->compactSchema}\n```"
            : "No schema loaded. Call get_database_schema to retrieve it.";

        return <<<EOT
You are MySqlExpert, a senior MySQL engineer. You write precise, optimised MySQL queries.

{$schemaBlock}

RULES — non-negotiable:
1. Use ONLY the tables and columns defined in DATABASE SCHEMA above.
   Never invent, guess, or assume any table or column name.
2. Wrap every table and column identifier in backticks.
3. Support all query types: SELECT, INSERT, UPDATE, DELETE.
   - SELECT: always add LIMIT 500 unless user specifies otherwise.
   - INSERT/UPDATE/DELETE: include WHERE clauses wherever logical to avoid full-table mutations.
4. Use foreign key relationships (shown as → in the schema) for JOINs.
5. Return ONLY the raw SQL. No markdown, no explanation, no comments.
6. If the request is impossible with the available schema, return exactly:
   ERROR: <brief reason>

AUDIENCE: Developer, tester, product manager, or analyst — translate plain English accurately.
EOT;
    }

    public function tools(): array
    {
        return [
            new GetDatabaseSchema(),
        ];
    }

    public function validateOutput(string $sql): array
    {
        if (empty($this->parsedSchema))          return [];
        if (str_starts_with(trim($sql), 'ERROR:')) return [];

        return SchemaParser::validateSql($sql, $this->parsedSchema);
    }
}