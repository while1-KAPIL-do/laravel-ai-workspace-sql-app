<?php

/**
 * LLM Provider Pricing Configuration
 *
 * All pricing values are in USD per 1,000 tokens (per-1K).
 * To convert: price_here = official_price_per_MTok / 1000
 *
 * Example: $1.00 / MTok = 0.001 here
 *          $0.15 / MTok = 0.00015 here
 *
 * Models within each provider are sorted cheapest → most expensive (by input price).
 * Sources verified April 2026.
 * Always double-check at each provider's official pricing page before production use.
 */

return [

    ////////////////////// OpenAI //////////////////////
    // Docs: https://platform.openai.com/docs/models
    // Pricing: https://openai.com/api/pricing/
    'openai' => [

        // Cheapest — $0.15 / $0.60 per MTok
        'gpt-4o-mini' => [
            'tier'    => 'cheap',
            'pricing' => [
                'input'  => 0.00015,
                'output' => 0.0006,
            ],
        ],

        // Legacy cheap — $0.50 / $1.50 per MTok (deprecated, avoid for new projects)
        'gpt-3.5-turbo' => [
            'tier'    => 'cheap',
            'pricing' => [
                'input'  => 0.0005,
                'output' => 0.0015,
            ],
        ],

        // Budget — $0.40 / $1.60 per MTok (1M token context window)
        'gpt-4.1-mini' => [
            'tier'    => 'cheap',
            'pricing' => [
                'input'  => 0.0004,
                'output' => 0.0016,
            ],
        ],

        // Balanced — $2.00 / $8.00 per MTok (1M token context window)
        'gpt-4.1' => [
            'tier'    => 'balanced',
            'pricing' => [
                'input'  => 0.002,
                'output' => 0.008,
            ],
        ],

        // Premium — $2.50 / $10.00 per MTok
        'gpt-4o' => [
            'tier'    => 'premium',
            'pricing' => [
                'input'  => 0.0025,
                'output' => 0.01,
            ],
        ],

        // Legacy premium — $10.00 / $30.00 per MTok (deprecated, avoid for new projects)
        'gpt-4' => [
            'tier'    => 'premium',
            'pricing' => [
                'input'  => 0.01,
                'output' => 0.03,
            ],
        ],
    ],

    ////////////////////// Anthropic //////////////////////
    // Docs: https://docs.anthropic.com/en/docs/about-claude/models/overview
    // Pricing: https://docs.anthropic.com/en/about-claude/pricing
    'anthropic' => [

        // Legacy cheap — $0.25 / $1.25 per MTok (deprecated, avoid for new projects)
        // 'claude-3-haiku-20240307' => [
        //     'tier'    => 'cheap',
        //     'pricing' => [
        //         'input'  => 0.00025,
        //         'output' => 0.00125,
        //     ],
        // ],

        // Budget — $1.00 / $5.00 per MTok
        'claude-haiku-4-5-20251001' => [
            'tier'    => 'cheap',
            'pricing' => [
                'input'  => 0.001,
                'output' => 0.005,
            ],
        ],

        // Legacy balanced — $3.00 / $15.00 per MTok (deprecated, avoid for new projects)
        'claude-3-sonnet-20240229' => [
            'tier'    => 'balanced',
            'pricing' => [
                'input'  => 0.003,
                'output' => 0.015,
            ],
        ],

        // Balanced — $3.00 / $15.00 per MTok
        'claude-sonnet-4-6' => [
            'tier'    => 'balanced',
            'pricing' => [
                'input'  => 0.003,
                'output' => 0.015,
            ],
        ],

        // Premium — $5.00 / $25.00 per MTok
        'claude-opus-4-6' => [
            'tier'    => 'premium',
            'pricing' => [
                'input'  => 0.005,
                'output' => 0.025,
            ],
        ],

        // Legacy premium — $15.00 / $75.00 per MTok (deprecated, avoid for new projects)
        'claude-3-opus-20240229' => [
            'tier'    => 'premium',
            'pricing' => [
                'input'  => 0.015,
                'output' => 0.075,
            ],
        ],
    ],

    ////////////////////// Google Gemini //////////////////////
    // Docs: https://ai.google.dev/gemini-api/docs/models
    // Pricing: https://ai.google.dev/gemini-api/docs/pricing
    // Free tier: Available via Google AI Studio (rate-limited, no credit card required)
    'gemini' => [

        // Ultra-budget — $0.10 / $0.40 per MTok | FREE tier available
        'gemini-2.5-flash-lite' => [
            'tier'    => 'ultra_cheap',
            'pricing' => [
                'input'  => 0.0001,
                'output' => 0.0004,
            ],
        ],

        // Budget — $0.30 / $2.50 per MTok | FREE tier available (rate-limited)
        'gemini-2.5-flash' => [
            'tier'    => 'cheap',
            'pricing' => [
                'input'  => 0.0003,
                'output' => 0.0025,
            ],
        ],

        // Premium — $1.25 / $10.00 per MTok (<=200K ctx)
        'gemini-2.5-pro' => [
            'tier'    => 'premium',
            'pricing' => [
                'input'  => 0.00125,
                'output' => 0.01,
            ],
        ],
    ],

    ////////////////////// DeepSeek //////////////////////
    // Docs: https://api-docs.deepseek.com/quick_start/pricing
    // Note: deepseek-chat / deepseek-reasoner are deprecated aliases
    //       for deepseek-v4-flash and will be removed 2026-07-24.
    // 'deepseek' => [

    //     // Ultra-budget — $0.14 / $0.28 per MTok (cache miss)
    //     'deepseek-v4-flash' => [
    //         'tier'    => 'ultra_cheap',
    //         'pricing' => [
    //             'input'  => 0.00014,
    //             'output' => 0.00028,
    //         ],
    //     ],

    //     // Balanced — $1.74 / $3.48 per MTok (cache miss; 75% discount active until 2026-05-05)
    //     'deepseek-v4-pro' => [
    //         'tier'    => 'balanced',
    //         'pricing' => [
    //             'input'  => 0.00174,
    //             'output' => 0.00348,
    //         ],
    //     ],
    // ],

    ////////////////////// Mistral //////////////////////
    // Docs: https://docs.mistral.ai/getting-started/models/overview/
    // Pricing: https://mistral.ai/pricing
    // Free tier: 1B tokens/month free across all models via la Plateforme
    // 'mistral' => [

    //     // Budget — $0.10 / $0.30 per MTok | FREE tier available
    //     'mistral-small-latest' => [
    //         'tier'    => 'cheap',
    //         'pricing' => [
    //             'input'  => 0.0001,
    //             'output' => 0.0003,
    //         ],
    //     ],

    //     // Legacy cheap — $0.20 / $0.60 per MTok (kept for compatibility)
    //     'open-mixtral-8x7b' => [
    //         'tier'    => 'cheap',
    //         'pricing' => [
    //             'input'  => 0.0002,
    //             'output' => 0.0006,
    //         ],
    //     ],

    //     // Balanced — $0.40 / $2.00 per MTok
    //     'mistral-medium-latest' => [
    //         'tier'    => 'balanced',
    //         'pricing' => [
    //             'input'  => 0.0004,
    //             'output' => 0.002,
    //         ],
    //     ],

    //     // Premium — $2.00 / $6.00 per MTok
    //     'mistral-large-latest' => [
    //         'tier'    => 'premium',
    //         'pricing' => [
    //             'input'  => 0.002,
    //             'output' => 0.006,
    //         ],
    //     ],
    // ],

    ////////////////////// Groq (Free tier available) //////////////////////
    // Docs: https://console.groq.com/docs/models
    // Pricing: https://wow.groq.com/pricing/
    // Free tier: Rate-limited free access (no credit card required)
    //            ~30 RPM / 14,400 RPD on most models
    // Note: Groq runs open-source models (Meta Llama, etc.) at ultra-low latency.
    'groq' => [

        // Ultra-cheap & ultra-fast — $0.05 / $0.08 per MTok | FREE tier available
        'llama-3.1-8b-instant' => [
            'tier'    => 'ultra_cheap',
            'pricing' => [
                'input'  => 0.00005,
                'output' => 0.00008,
            ],
        ],

        // Cheap — $0.59 / $0.79 per MTok | FREE tier available
        'llama-3.3-70b-versatile' => [
            'tier'    => 'cheap',
            'pricing' => [
                'input'  => 0.00059,
                'output' => 0.00079,
            ],
        ],
    ],

    ////////////////////// Default Fallback //////////////////////
    'default' => [
        'tier'    => 'cheap',
        'pricing' => [
            'input'  => 0.001,
            'output' => 0.002,
        ],
    ],

];