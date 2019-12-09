<?php

function env($key, $default = null) {
    if ($_ENV[$key] !== null) {
        return $_ENV[$key];
    }
    return $default;
}

return [
    'database' => [
        'database_type' => env('DB_TYPE', 'sqlite'),
        'database_file' => env('DB_FILE', __DIR__.'/database.sqlite'),
        'database_name' => env('DB_NAME', 'main'),
        'server' => env('DB_SERVER', 'localhost'),
        'username' => env('DB_USER'),
        'password' => env('DB_PASSWORD'),
        'prefix' => env('DB_PREFIX', '')
    ],
    'jwt' => [
        'key' => env('JWT_KEY'),
        'issuer' => env('JWT_ISSUER', 'http://localhost'),
        'audience' => env('JWT_AUDIENCE', 'http://localhost'),
        'supported_algorithms' => array('HS256')
    ]
];
