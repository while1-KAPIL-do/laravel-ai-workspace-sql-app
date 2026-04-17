<?php

namespace App\Support\Utils;

use Illuminate\Support\Facades\Log;

class LlmConfig
{
    protected static string $configKey = 'llm.llm_pricing';

    // Get all providers
    public static function providers(): array
    {

        $config = config(self::$configKey);

        if (!$config) {
            Log::error('LlmConfig: Missing llm_pricing config');
            return [];
        }

        $providers = array_keys($config);

        Log::debug('LlmConfig: providers()', ['providers' => $providers]);

        return $providers;
    }

    // Get models for provider
    public static function models(string $provider): array
    {
        $models = config(self::$configKey . '.' . $provider);

        if (!$models) {
            Log::warning('LlmConfig: No models found for provider', [
                'provider' => $provider
            ]);
            return [];
        }

        $modelKeys = array_keys($models);

        Log::debug('LlmConfig: models()', [
            'provider' => $provider,
            'models' => $modelKeys
        ]);

        return $modelKeys;
    }

    // Check valid provider
    public static function isValidProvider(string $provider): bool
    {
        $isValid = in_array($provider, self::providers());

        Log::debug('LlmConfig: isValidProvider()', [
            'provider' => $provider,
            'is_valid' => $isValid
        ]);

        return $isValid;
    }

    // Check valid model for provider
    public static function isValidModel(string $provider, string $model): bool
    {
        $models = self::models($provider);
        $isValid = in_array($model, $models);

        Log::debug('LlmConfig: isValidModel()', [
            'provider' => $provider,
            'model' => $model,
            'is_valid' => $isValid
        ]);

        return $isValid;
    }

    // Get pricing
    public static function pricing(string $provider, string $model): array
    {
        $pricing = config(self::$configKey . ".$provider.$model.pricing");

        if (!$pricing) {
            Log::warning('LlmConfig: Pricing not found, using default', [
                'provider' => $provider,
                'model' => $model
            ]);

            $pricing = config(self::$configKey . '.default.pricing');
        }

        Log::debug('LlmConfig: pricing()', [
            'provider' => $provider,
            'model' => $model,
            'pricing' => $pricing
        ]);

        return $pricing ?? [];
    }

    // Get tier
    public static function tier(string $provider, string $model): ?string
    {
        $tier = config(self::$configKey . ".$provider.$model.tier");

        Log::debug('LlmConfig: tier()', [
            'provider' => $provider,
            'model' => $model,
            'tier' => $tier
        ]);

        return $tier;
    }

    // Safe provider (fallback)
    public static function resolveProvider(?string $provider): string
    {
        $resolved = self::isValidProvider($provider ?? '') ? $provider : 'openai';

        Log::info('LlmConfig: resolveProvider()', [
            'input' => $provider,
            'resolved' => $resolved
        ]);

        return $resolved;
    }

    // Safe model (fallback)
    public static function resolveModel(string $provider, ?string $model): string
    {
        $models = self::models($provider);

        if (in_array($model, $models)) {
            Log::info('LlmConfig: resolveModel() valid', [
                'provider' => $provider,
                'model' => $model
            ]);

            return $model;
        }

        $fallback = $models[0] ?? 'gpt-4o-mini';

        Log::warning('LlmConfig: resolveModel() fallback used', [
            'provider' => $provider,
            'input_model' => $model,
            'fallback' => $fallback
        ]);

        return $fallback;
    }

    // Restriction check
    public static function isRestricted(string $provider, string $model): bool
    {
        $tier = self::tier($provider, $model);

        $restricted = in_array($tier, ['premium']);

        Log::info('LlmConfig: isRestricted()', [
            'provider' => $provider,
            'model' => $model,
            'tier' => $tier,
            'restricted' => $restricted
        ]);

        return $restricted;
    }
}