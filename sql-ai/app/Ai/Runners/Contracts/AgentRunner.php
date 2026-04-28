<?php

namespace App\Ai\Runners\Contracts;

use Laravel\Ai\Contracts\Agent;

/**
 * Template Method Pattern — defines the fixed algorithm skeleton.
 *
 * Subclasses implement:
 *   - createAgent()      → return the specific Agent instance
 *   - validateOutput()   → return [] on success, or string[] of error messages
 *   - buildResultKeys()  → (optional) rename/extend the result array keys
 */
abstract class AgentRunner
{
    // ── Abstract hooks ────────────────────────────────────────────────────────

    /**
     * Return the Agent instance for this runner.
     * e.g. new MySqlExpert()  or  new MyQAExpert()
     */
    abstract protected function createAgent(): Agent;

    /**
     * Validate the raw output string returned by the agent.
     *
     * @param  Agent  $agent   The same agent instance used during the run.
     * @param  string $output  Raw text returned by the LLM.
     * @return string[]        Empty array  = output is valid, proceed.
     *                         Non-empty    = list of error messages, trigger retry.
     */
    abstract protected function validateOutput(Agent $agent, string $output): array;

    // ── Optional hook ─────────────────────────────────────────────────────────

    /**
     * Override to rename or add keys in the returned result array.
     * Receives the assembled result; return a (modified) copy.
     *
     * Default implementation is a pass-through.
     */
    protected function buildResult(array $result): array
    {
        return $result;
    }

    // ── Template method (THE algorithm — never override this) ─────────────────

    /**
     * Run the agent with automatic retry + self-correction.
     *
     * @param  string $userQuestion  Plain-English question from the user.
     * @param  string $provider      LLM provider key (e.g. 'openai').
     * @param  string $model         Model name   (e.g. 'gpt-4o-mini').
     * @return array{
     *   instructions: string,
     *   question: string,
     *   output: string,
     *   attempts: int,
     *   tokens: array{input: int, output: int, total: int},
     *   validation_errors?: string[]
     * }
     */
    final public function run(
        string $userQuestion,
        string $provider = 'openai',
        string $model    = 'gpt-4o-mini'
    ): array {
        $agent       = $this->createAgent();
        $totalInput  = 0;
        $totalOutput = 0;
        $attempts    = 0;
        $lastOutput  = '';
        $lastErrors  = [];

        for ($attempt = 1; $attempt <= 3; $attempt++) {
            $attempts++;

            // On retries, append the validation errors so the agent self-corrects
            $prompt = $attempt === 1
                ? $userQuestion
                : $userQuestion
                  . "\n\n[CORRECTION REQUIRED] Your previous response had these errors:\n"
                  . implode("\n", array_map(fn($e) => "- {$e}", $lastErrors))
                  . "\nFix only those errors. Return only the corrected response.";

            $response = $agent->prompt($prompt, [], $provider, $model);

            $output       = trim((string) $response);
            $inputTokens  = $response?->usage?->promptTokens    ?? 0;
            $outputTokens = $response?->usage?->completionTokens ?? 0;
            $totalInput  += $inputTokens;
            $totalOutput += $outputTokens;

            $errors = $this->validateOutput($agent, $output);

            if (empty($errors)) {
                return $this->buildResult([
                    'instructions' => $agent->instructions(),
                    'question'     => $userQuestion,
                    'output'       => $output,
                    'attempts'     => $attempts,
                    'tokens'       => [
                        'input'  => $totalInput,
                        'output' => $totalOutput,
                        'total'  => $totalInput + $totalOutput,
                    ],
                ]);
            }

            $lastOutput = $output;
            $lastErrors = $errors;
        }

        // Exhausted retries — return best attempt with error flag
        return $this->buildResult([
            'instructions'     => $agent->instructions(),
            'question'         => $userQuestion,
            'output'           => $lastOutput,
            'attempts'         => $attempts,
            'validation_errors' => $lastErrors,
            'tokens'           => [
                'input'  => $totalInput,
                'output' => $totalOutput,
                'total'  => $totalInput + $totalOutput,
            ],
        ]);
    }
}
