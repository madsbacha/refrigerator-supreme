<?php
namespace Api;

require __DIR__.'/../vendor/autoload.php';

use Api\Database\DatabaseRepository;
use Api\GraphQL\MutationType;
use Api\GraphQL\QueryType;
use Api\GraphQL\TypeRegistry;
use Api\Util\JWTHelper;
use Dotenv\Dotenv;
use GraphQL\Type\Schema;
use Siler\GraphQL;
use Siler\Http\Request;
use Siler\Http\Response;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();
$dotenv->required('JWT_KEY');

// Enable CORS
Response\header('Access-Control-Allow-Origin', '*');
Response\header('Access-Control-Allow-Headers', 'content-type,x-apollo-tracing,Authorization');

// Respond only for POST requests
if (Request\method_is('post')) {
    // Retrieve the Schema
    $typeRegistry = new TypeRegistry();
    $schema = new Schema([
        'query' => new QueryType($typeRegistry),
        'mutation' => new MutationType($typeRegistry)
    ]);

    // Give it to siler
    GraphQL\init($schema, null, getContext());
}

function startsWith($str, $needle) {
    return substr($str, 0, strlen($needle)) === $needle;
}

function getContext() {
    $context = new \stdClass();
    $context->User = null;
    $context->Db = new DatabaseRepository();
    $context->IsLoggedIn = false;

    $auth_header = Request\header('Authorization');
    if (startsWith($auth_header, 'JWT ')) {
        $token = substr($auth_header, strlen('JWT '));
        try {
            $decoded = JWTHelper::decode($token);
            if ($decoded) {
                $context->User = $decoded->user;
                $context->IsLoggedIn = true;
            }
        } catch (\Exception $exception) {
            // pass
        }
    }
    return $context;
}
