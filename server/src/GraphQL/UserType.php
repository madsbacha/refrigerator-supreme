<?php
namespace Api\GraphQL;

use Api\GraphQL\TypeRegistry;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class UserType extends ObjectType
{
    public function __construct(TypeRegistry $types)
    {
        parent::__construct([
            'fields' => function () use ($types) {
                return [
                    'id' => Type::id(),
                    'email' => Type::string(),
                    'ratings' => [
                        'type' => Type::listOf($types->Rating()),
                        'resolve' => function ($rootValue, $args, $context) {
                            return $context->Db->Ratings->ByUserId($rootValue['id']);
                        }
                    ]
                ];
            }
        ]);
    }
}
