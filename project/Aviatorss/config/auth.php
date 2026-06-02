<?php

return [

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'ldap',
            'model' => App\Models\User::class,
            'rules' => [],
            'database' => [
                'model' => App\Models\User::class,
                'sync_passwords' => false,
                'sync_attributes' => [
                    'login' => 'samaccountname',
                    'firstname' => 'givenname',
                    'lastname' => 'sn',
                    'email' => 'mail',
                    'name' => 'cn',
                    // Добавьте другие поля по необходимости
                ],
            ],
        ],
    ],

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];
