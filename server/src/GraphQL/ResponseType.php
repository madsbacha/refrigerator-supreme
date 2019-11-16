<?php
namespace Api\GraphQL;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class ResponseType extends ObjectType
{
    public function __construct()
    {
        parent::__construct([
            'fields' => [
                'success' => Type::boolean()
            ]
        ]);
    }
}
