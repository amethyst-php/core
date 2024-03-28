<?php

return [
    'http' => [
        'app' => [
            'router' => [
                'as'         => 'app.',
                'middleware' => [
                    'api',
                ],
                'prefix' => '/api',
            ],
        ],
        'data' => [
            'router' => [
                'as'         => 'data.',
                'middleware' => [
                    'api',
                ],
                'prefix' => '/api/data',
            ],
        ],
    ],
];
