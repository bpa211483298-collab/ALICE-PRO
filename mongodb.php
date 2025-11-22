<?php

return [
    'driver' => 'mongodb',
    'host' => env('MONGODB_HOST', 'localhost'),
    'port' => env('MONGODB_PORT', 27017),
    'database' => env('MONGODB_DATABASE', 'alice_pro'),
    'username' => env('MONGODB_USERNAME', ''),
    'password' => env('MONGODB_PASSWORD', ''),
    'options' => [
        'database' => 'admin',
    ],
];