<?php
namespace Api\GraphQL;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class QueryType extends ObjectType
{
    public function __construct(TypeRegistry $typeRegistry)
    {
        $config = [
            'name' => 'Query',
            'fields' => [
                'drinks' => [
                    'type' => Type::listOf($typeRegistry->Item()),
                    'args' => [
                        'first' => Type::int(),
                        'after' => Type::int()
                    ],
                    'resolve' => function ($rootValue, $args, $context) {
                        $where = [];
                        if (array_key_exists('first', $args) && array_key_exists('after', $args)) {
                            $where['LIMIT'] = [$args['after'], $args['first']];
                        } else if (array_key_exists('first', $args)) {
                            $where['LIMIT'] = $args['first'];
                        }
                        return $context->Db->Items->Where($where);
                    }
                ],
                'ratings' => [
                    'type' => Type::listOf($typeRegistry->Rating()),
                    'args' => [
                        'itemId' => Type::id()
                    ],
                    'resolve' => function ($rootValue, $args, $context) {
                        $where = [];
                        if (array_key_exists('itemId', $args)) {
                            $where['item_id'] = $args['itemId'];
                        }
                        return $context->Db->Ratings->Where($where);
                    }
                ],
                'comments' => [
                    'type' => Type::listOf($typeRegistry->Comment()),
                    'args' => [
                        'itemId' => Type::id(),
                        'userId' => Type::id(),
                        'first' => Type::int(),
                        'after' => Type::int()
                    ],
                    'resolve' => function ($rootValue, $args, $context) {
                        $where = [];
                        if (array_key_exists('itemId', $args)) {
                            $where['item_id'] = $args['itemId'];
                        }
                        if (array_key_exists('userId', $args)) {
                            $where['user_id'] = $args['userId'];
                        }
                        if (array_key_exists('first', $args) && array_key_exists('after', $args)) {
                            $where['LIMIT'] = [$args['after'], $args['first']];
                        } else if (array_key_exists('first', $args)) {
                            $where['LIMIT'] = $args['first'];
                        }
                        return $context->Db->Comments->Where($where);
                    }
                ],
                'me' => [
                    'type' => $typeRegistry->User(),
                    'resolve' => function ($rootValue, $args, $context) {
                        if (is_null($context->User)) return null;
                        return $context->Db->Users->FindById($context->User->id);
                    }
                ]
            ]
        ];
        parent::__construct($config);
    }
}
