<?php 

return [

    ////////////////////// OpenAI //////////////////////
    'openai' => [

        // Cheapest (default)
        'gpt-4o-mini' => [
            'tier' => 'cheap',
            'pricing' => [
                'input' => 0.00015,
                'output' => 0.0006,
            ],
        ],

        // Better reasoning
        'gpt-4.1-mini' => [
            'tier' => 'balanced',
            'pricing' => [
                'input' => 0.0003,
                'output' => 0.0012,
            ],
        ],

        // Strong model
        'gpt-4o' => [
            'tier' => 'premium',
            'pricing' => [
                'input' => 0.005,
                'output' => 0.015,
            ],
        ],

        // Legacy (keep optional)
        'gpt-4' => [
            'tier' => 'premium',
            'pricing' => [
                'input' => 0.01,
                'output' => 0.03,
            ],
        ],

        'gpt-3.5-turbo' => [
            'tier' => 'cheap',
            'pricing' => [
                'input' => 0.0005,
                'output' => 0.0015,
            ],
        ],
    ],


    ////////////////////// Anthropic //////////////////////
    'anthropic' => [

        'claude-3-haiku' => [
            'tier' => 'cheap',
            'pricing' => [
                'input' => 0.00025,
                'output' => 0.00125,
            ],
        ],

        'claude-3-sonnet' => [
            'tier' => 'balanced',
            'pricing' => [
                'input' => 0.003,
                'output' => 0.015,
            ],
        ],

        'claude-3-opus' => [
            'tier' => 'premium',
            'pricing' => [
                'input' => 0.015,
                'output' => 0.075,
            ],
        ],
    ],

    ////////////////////// DeepSeek //////////////////////
    'deepseek' => [
        'deepseek-chat' => [
            'tier' => 'ultra_cheap',
            'pricing' => [
                'input' => 0.00014,
                'output' => 0.00028,
            ],
        ],
    ],

    ////////////////////// Mistral //////////////////////
    'mistral' => [
        'mixtral-8x7b' => [
            'tier' => 'cheap',
            'pricing' => [
                'input' => 0.0002,
                'output' => 0.0006,
            ],
        ],
    ],

    ////////////////////// Default //////////////////////
    'default' => [
        'tier' => 'cheap',
        'pricing' => [
            'input' => 0.001,
            'output' => 0.002,
        ],
    ]

];