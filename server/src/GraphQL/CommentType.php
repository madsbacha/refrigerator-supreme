<?php
namespace Api\GraphQL;

use Api\GraphQL\TypeRegistry;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class CommentType extends ObjectType
{
    public function __construct(TypeRegistry $types)
    {
        parent::__construct([
            'fields' => function () use ($types) {
                return [
                    'id' => Type::id(),
                    'item' => [
                        'type' => $types->Item(),
                        'resolve' => function ($rootValue, $args, $context) {
                            return $context->Db->Items->FindById($rootValue['item_id']);
                        }
                    ],
                    'text' => Type::string(),
                    'author' => [
                        'type' => $types->User(),
                        'resolve' => function ($rootValue, $args, $context) {
                            return $context->Db->Users->FindById($rootValue['user_id']);
                        }
                    ]
                ];
            }
        ]);
    }
}
