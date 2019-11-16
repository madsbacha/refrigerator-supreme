<?php
namespace Api\GraphQL;

use Api\GraphQL\TypeRegistry;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class LoginResponseType extends ObjectType
{
    public function __construct(TypeRegistry $types)
    {
        parent::__construct([
            'fields' => function () use ($types) {
                return [
                    'token' => Type::string(),
                    'user' => $types->User()
                ];
            }
        ]);
    }
}
