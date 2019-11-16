<?php
namespace Api\GraphQL;

use Api\GraphQL\TypeRegistry;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class CategoryType extends ObjectType
{
    public function __construct(TypeRegistry $types)
    {
        parent::__construct([
            'fields' => function () use ($types) {
                return [
                    'id' => Type::id(),
                    'name' => Type::string(),
                    'drinks' => [
                        'type' => Type::listOf($types->Item()),
                        'resolve' => function ($rootValue, $args, $context) {
                            return $context->Db->Items->Where(['category_id' => $rootValue['id']]);
                        }
                    ]
                ];
            }
        ]);
    }
}
