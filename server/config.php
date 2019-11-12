<?php
return [
    'database' => [
        'database_type' => 'sqlite',
        'database_file' => 'database.sqlite',
        'database_name' => 'name',
        'server' => 'localhost',
        'username' => '',
        'password' => '',
        'prefix' => ''
    ],
    'jwt' => [
        'key' => '123',
        'issuer' => 'http://localhost',
        'audience' => 'http://localhost',
        'supported_algorithms' => array('HS256')
    ]
];
