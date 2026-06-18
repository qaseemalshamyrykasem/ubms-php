<?php

return [
    'enabled' => true,
    'max_request_weight' => 60,
    'throttle' => true,
    'paths' => [
        'api/*' => [
            'max_attempts' => 60,
            'decay_minutes' => 1,
        ],
        'auth/login' => [
            'max_attempts' => 5,
            'decay_minutes' => 1,
        ],
        'auth/forgot-password' => [
            'max_attempts' => 3,
            'decay_minutes' => 5,
        ],
    ],
];
