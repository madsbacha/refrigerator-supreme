<?php
namespace Api\Schema;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Medoo\Medoo;


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
                    'rating' => [
                        'type' => Type::float(),
                        'resolve' => function ($rootValue, $args, $context) {
                            return $context->db->Items->RatingOf($rootValue['id']);
                        }
                    ],
                    'ratings' => [
                        'type' => Type::listOf($types->Rating()),
                        'resolve' => function ($rootValue, $args, $context) {
                            return $context->db->Ratings->On($rootValue['id']);
                        }
                    ],
                    'comments' => [
                        'type' => Type::listOf($types->Comment()),
                        'resolve' => function ($rootValue, $args, $context) {
                            return $context->db->Comments->Where(['item_id' => $rootValue['id']]);
                        }
                    ],
                    'category' => [
                        'type' => $types->Category(),
                        'resolve' => function ($rootValue, $args, $context) {
                            if (is_null($rootValue['category_id'])) {
                                return null;
                            }
                            return $context->db->Categories->FindById($rootValue['category_id']);
                        }
                    ]
                ];
            }
        ]);
    }
}

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
                            return $context->db->Items->Where(['category_id' => $rootValue['id']]);
                        }
                    ]
                ];
            }
        ]);
    }
}

class RatingType extends ObjectType
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
                            return $context->db->Items->FindById($rootValue['item_id']);
                        }
                    ],
                    'user' => [
                        'type' => $types->User(),
                        'resolve' => function ($rootValue, $args, $context) {
                            return $context->db->Users->FindById($rootValue['user_id']);
                        }
                    ],
                    'rating' => Type::float()
                ];
            }
        ]);
    }
}

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
                            return $context->db->Ratings->ByUserId($rootValue['id']);
                        }
                    ],
                    'comments' => [
                        'type' => Type::listOf($types->Comment()),
                        'resolve' => function ($rootValue, $args, $context) {
                            return $context->db->Comments->ByUserId($rootValue['id']);
                        }
                    ]
                ];
            }
        ]);
    }
}

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
                            return $context->db->Items->FindById($rootValue['item_id']);
                        }
                    ],
                    'text' => Type::string(),
                    'author' => [
                        'type' => $types->User(),
                        'resolve' => function ($rootValue, $args, $context) {
                            return $context->db->Users->FindById($rootValue['user_id']);
                        }
                    ]
                ];
            }
        ]);
    }
}

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

class TypeRegistry
{
    private $user;
    private $item;
    private $rating;
    private $comment;
    private $response;
    private $loginResponse;
    private $category;

    public function User()
    {
        return $this->user ?: ($this->user = new UserType($this));
    }
    public function Item()
    {
        return $this->item ?: ($this->item = new ItemType($this));
    }
    public function Rating()
    {
        return $this->rating ?: ($this->rating = new RatingType($this));
    }
    public function Comment()
    {
        return $this->comment ?: ($this->comment = new CommentType($this));
    }
    public function Response()
    {
        return $this->response ?: ($this->response = new ResponseType());
    }
    public function LoginResponse()
    {
        return $this->loginResponse ?: ($this->loginResponse = new LoginResponseType($this));
    }

    public function Category()
    {
        return $this->category ?: ($this->category = new CategoryType($this));
    }
}
