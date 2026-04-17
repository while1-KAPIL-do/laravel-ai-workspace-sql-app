<?php

namespace App\Services\Token;

use Illuminate\Support\Facades\Log;

class TokenManager
{
    public function __construct(
        protected PHPEstimatorService $estimator,
        protected PythonTokenizerClient $pythonClient,
        protected TokenUsageService $usageService
    ) {}

    public function validate(string $input, string $ip): array
    {
        Log::info(__METHOD__, get_defined_vars());

        $estimated = $this->estimator->estimate($input);

        if ($this->usageService->isExceeded($ip)) {
            return [
                'allowed' => false,
                'reason' => 'Daily token limit exceeded'
            ];
        }

        // Step 1: Hard reject
        if ($estimated > config('llm.tokens.hard_limit')) {
            return ['allowed' => false, 'reason' => 'Too large input'];
        }

        // Step 2: Safe zone → skip python
        if ($estimated < config('llm.tokens.safe_limit')) {
            return ['allowed' => true, 'tokens' => $estimated];
        }

        # NOTE : If tokens are not handled by the local service, we will call the Python service to validate the accurate token count

        // Step 3: Call Python (accurate)
        $actual = $this->pythonClient->getTokens($input);

        if ($actual !== null) {
            return [
                'allowed' => $actual < config('llm.tokens.hard_limit'),
                'tokens' => $actual
            ];
        }

        // Step 4: fallback (python failed)
        return ['allowed' => true, 'tokens' => $estimated];
    }

    public function record(
        string $ip,
        int $inputTokens,
        int $outputTokens,
        string $provider = 'openai',
        string $model = 'gpt-4o-mini'
    ): void {

        // Get pricing safely
        $pricing = config("llm_pricing.$provider.$model.pricing")
            ?? config("llm_pricing.default.pricing");

        $inputCostPer1k = $pricing['input'] ?? 0;
        $outputCostPer1k = $pricing['output'] ?? 0;

        // Calculate cost
        $inputCost = ($inputTokens / 1000) * $inputCostPer1k;
        $outputCost = ($outputTokens / 1000) * $outputCostPer1k;

        $cost = $inputCost + $outputCost;

        // Round (important for billing)
        $cost = round($cost, 6);

        // Save usage
        $this->usageService->addUsage(
            $ip,
            $inputTokens,
            $outputTokens,
            $cost,
            $provider,
            $model
        );
    }
}