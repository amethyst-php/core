<?php

return [
    'http' => [
        'admin' => [
            'router' => [
                'as'     => 'admin.',
                'prefix' => '/api/admin',
            ],
        ],
        'app' => [
            'router' => [
                'as'     => 'app.',
                'prefix' => '/api',
            ],
        ],
        'user' => [
            'router' => [
                'as'     => 'user.',
                'prefix' => '/api',
            ],
        ],
    ],
];
