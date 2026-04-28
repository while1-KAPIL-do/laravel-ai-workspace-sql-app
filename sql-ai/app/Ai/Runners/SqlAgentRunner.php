<?php

namespace App\Ai\Runners;

use App\Ai\Agents\MySqlExpert;
use App\Ai\Runners\Contracts\AgentRunner;
use Laravel\Ai\Contracts\Agent;

/**
 * Concrete runner for the SQL-ASSISTANT.
 *
 * Implements:
 *   - createAgent()      → MySqlExpert
 *   - validateOutput()   → schema-based SQL validation
 *   - buildResult()      → renames 'output' to 'sql' to preserve the
 *                          exact original response shape expected by callers
 */
class SqlAgentRunner extends AgentRunner
{
    protected function createAgent(): Agent
    {
        return new MySqlExpert();
    }

    /**
     * Delegates to MySqlExpert::validateOutput() — uses SchemaParser internally.
     *
     * @return string[]  Empty = valid SQL. Non-empty = list of schema violations.
     */
    protected function validateOutput(Agent $agent, string $output): array
    {
        /** @var MySqlExpert $agent */
        return $agent->validateOutput($output);
    }

    /**
     * Rename the generic 'output' key to 'sql' so existing callers
     * that expect $result['sql'] keep working without any changes.
     */
    protected function buildResult(array $result): array
    {
        $result['sql'] = $result['output'];
        unset($result['output']);

        return $result;
    }
}
