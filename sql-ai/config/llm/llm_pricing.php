<?php 

return [

    'openai' => [
        'gpt-4' => [
            'input' => 0.01,
            'output' => 0.03,
        ],
        'gpt-3.5' => [
            'input' => 0.001,
            'output' => 0.002,
        ],
    ],

    'anthropic' => [
        'claude-3-sonnet' => [
            'input' => 0.003,
            'output' => 0.015,
        ],
    ],

    'default' => [
        'input' => 0.001,
        'output' => 0.002,
    ]

];