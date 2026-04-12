<?php

return [
    'safe_limit' => 100,
    'hard_limit' => 400,
    'daily_limit_per_ip' => 1000,
    'python_service_url' => env(
        'TOKENIZER_URL',
        'http://llm-service:8000/count-tokens'
    ),
];