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
        if ($estimated > config('tokens.hard_limit')) {
            return ['allowed' => false, 'reason' => 'Too large input'];
        }

        // Step 2: Safe zone → skip python
        if ($estimated < config('tokens.safe_limit')) {
            return ['allowed' => true, 'tokens' => $estimated];
        }

        # NOTE : If tokens are not handled by the local service, we will call the Python service to validate the accurate token count

        // Step 3: Call Python (accurate)
        $actual = $this->pythonClient->getTokens($input);

        if ($actual !== null) {
            return [
                'allowed' => $actual < config('tokens.hard_limit'),
                'tokens' => $actual
            ];
        }

        // Step 4: fallback (python failed)
        return ['allowed' => true, 'tokens' => $estimated];
    }

    public function record(string $ip, int $tokens): void
    {
        $this->usageService->addUsage($ip, $tokens);
    }
}