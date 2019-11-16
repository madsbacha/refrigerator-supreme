<?php

namespace Api;

use Siler\GraphQL;
use Siler\Http\Request;
use Siler\Http\Response;
use Exception;

require 'vendor/autoload.php';

// Enable CORS
Response\header('Access-Control-Allow-Origin', '*');
Response\header('Access-Control-Allow-Headers', 'content-type,x-apollo-tracing');

$context = [
    'user' => null
];

$auth_header = Request\header('Authorization');
if (startsWith($auth_header, 'JWT ')) {
    $token = substr($auth_header, strlen('JWT '));
    $jwt = include __DIR__ . '/util/jwt.php';
    try {
        $decoded = $jwt::decode($token);
        if ($decoded) {
            $context['user'] = (array) $decoded->user;
        }
    } catch (Exception $exception) {
        // pass
    }
}

// Respond only for POST requests
if (Request\method_is('post')) {
    // Retrive the Schema
    $schema = include __DIR__ . '/schema/schema.php';

    // Give it to siler
    GraphQL\init($schema, null, $context);
}

function startsWith($str, $needle) {
    return substr($str, 0, strlen($needle)) === $needle;
}
