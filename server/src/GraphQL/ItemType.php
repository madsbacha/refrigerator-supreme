<?php
namespace Api\GraphQL;

use Api\GraphQL\TypeRegistry;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class ItemType extends ObjectType
{
    public function __construct(TypeRegistry $types)
    {
        parent::__construct([
            'fields' => function () use ($types) {
                return [
                    'id' => Type::id(),
                    'name' => Type::string(),
                    'image' => Type::string(),
                    'price' => Type::float(),
                    'energy' => Type::float(),
                    'size' => Type::float(),
                    'slug' => Type::string(),
                    'rating' => [
                        'type' => Type::float(),
                        'resolve' => function ($rootValue, $args, $context) {
                            return $context->Db->Ratings->RatingOf($rootValue['id']);
                        }
                    ],
                    'ratings' => [
                        'type' => Type::listOf($types->Rating()),
                        'resolve' => function ($rootValue, $args, $context) {
                            return $context->Db->Ratings->On($rootValue['id']);
                        }
                    ],
                    'myRating' => [
                        'type' => $types->Rating(),
                        'resolve' => function ($rootValue, $args, $context) {
                            if (!$context->IsLoggedIn) return null;
                            return $context->Db->Ratings->OnBy($rootValue['id'], $context->User->id);
                        }
                    ],
                    'category' => [
                        'type' => $types->Category(),
                        'resolve' => function ($rootValue, $args, $context) {
                            if (is_null($rootValue['category_id'])) {
                                return null;
                            }
                            return $context->Db->Categories->FindById($rootValue['category_id']);
                        }
                    ]
                ];
            }
        ]);
    }
}