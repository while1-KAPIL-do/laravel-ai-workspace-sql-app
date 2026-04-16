<?php

namespace App\Ai\Agents;

use App\Ai\Tools\DescribeTable;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;
use Stringable;
use App\Ai\Tools\GetDatabaseSchema;

class MySqlExpert implements Agent
{
    use Promptable;

    public function instructions(): Stringable|string
    {
        return <<<'EOT'
You are a MySQL expert.

Workflow:
1. If schema is unknown, call get_database_schema.
2. Use exact column names from schema.
3. Generate a valid SELECT query.
4. Return only SQL.

Rules:
- Only SELECT queries allowed
- No guessing column names
- Use backticks for tables and columns
- Use correct date columns for filters
- Always add LIMIT 500
- No explanation, no markdown, no comments
EOT;
    }

    public function tools(): array
    {
        return [
            new GetDatabaseSchema(),
        ];
    }

    // public function buildFullPrompt(string $userQuestion): string
    // {
    //     $toolsText = '';

    //     foreach ($this->tools() as $tool) {
    //         $toolsText .= get_class($tool) . "\n";
    //     }

    //     return $this->instructions()
    //         . "\n\nTOOLS:\n" . $toolsText
    //         . "\n\nUSER:\n" . $userQuestion;
    // }
}