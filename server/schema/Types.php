<?php

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Medoo\Medoo;


class ItemType extends ObjectType
{
    public function __construct(TypeRegistry $types, Medoo $db)
    {
        parent::__construct([
            'fields' => function () use ($types, $db) {
                return [
                    'id' => Type::id(),
                    'name' => Type::string(),
                    'image' => Type::string(),
                    'rating' => [
                        'type' => Type::float(),
                        'resolve' => function ($rootValue) use ($db) {
                            return $db->avg('ratings', 'rating', ['item_id' => $rootValue['id']]);
                        }
                    ],
                    'ratings' => [
                        'type' => Type::listOf($types->Rating()),
                        'resolve' => function ($rootValue) use ($db) {
                            return $db->select('ratings', ['id', 'item_id', 'user_id', 'rating'], ['item_id' => $rootValue['id']]);
                        }
                    ],
                    'comments' => [
                        'type' => Type::listOf($types->Comment()),
                        'resolve' => function ($rootValue) use ($db) {
                            return $db->select(
                                'comments',
                                ['id', 'user_id', 'item_id', 'text'],
                                ['item_id' => $rootValue['id']]);
                        }
                    ]
                ];
            }
        ]);
    }
}

class RatingType extends ObjectType
{
    public function __construct(TypeRegistry $types, Medoo $db)
    {
        parent::__construct([
            'fields' => function () use ($types, $db) {
                return [
                    'id' => Type::id(),
                    'item' => [
                        'type' => $types->Item(),
                        'resolve' => function ($rootValue) use ($db) {
                            return $db->get('items', ['id', 'name', 'image'], ['id' => $rootValue['item_id']]);
                        }
                    ],
                    'user' => [
                        'type' => $types->User(),
                        'resolve' => function ($rootValue) use ($db) {
                            return $db->get('users', ['id', 'email'], ['id' => $rootValue['user_id']]);
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
    public function __construct(TypeRegistry $types, Medoo $db)
    {
        parent::__construct([
            'fields' => function () use ($types, $db) {
                return [
                    'id' => Type::id(),
                    'email' => Type::string(),
                    'ratings' => [
                        'type' => Type::listOf($types->Rating()),
                        'resolve' => function ($rootValue) use ($db) {
                            return $db->select('ratings', ['id', 'user_id', 'item_id', 'rating'], ['user_id' => $rootValue['id']]);
                        }
                    ],
                    'comments' => [
                        'type' => Type::listOf($types->Comment()),
                        'resolve' => function ($rootValue) use ($db) {
                            return $db->select('comments', ['id', 'user_id', 'item_id', 'text'], ['user_id' => $rootValue['id']]);
                        }
                    ]
                ];
            }
        ]);
    }
}

class CommentType extends ObjectType
{
    public function __construct(TypeRegistry $types, Medoo $db)
    {
        parent::__construct([
            'fields' => function () use ($types, $db) {
                return [
                    'id' => Type::id(),
                    'item' => [
                        'type' => $types->Item(),
                        'resolve' => function ($rootValue) use ($db) {
                            return $db->get('items', ['id', 'name', 'image'], ['id' => $rootValue['item_id']]);
                        }
                    ],
                    'text' => [
                        'type' => Type::string(),
                        'resolve' => function ($rootValue) {
                            return $rootValue['text'];
                        }
                    ],
                    'author' => [
                        'type' => $types->User(),
                        'resolve' => function ($rootValue) use ($db) {
                            return $db->get('users', ['id', 'email'], ['id' => $rootValue['user_id']]);
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
                    'success' => Type::boolean(),
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
    private $db;

    public function __construct(Medoo $db)
    {
        $this->db = $db;
    }

    public function User()
    {
        return $this->user ?: ($this->user = new UserType($this, $this->db));
    }
    public function Item()
    {
        return $this->item ?: ($this->item = new ItemType($this, $this->db));
    }
    public function Rating()
    {
        return $this->rating ?: ($this->rating = new RatingType($this, $this->db));
    }
    public function Comment()
    {
        return $this->comment ?: ($this->comment = new CommentType($this, $this->db));
    }
    public function Response()
    {
        return $this->response ?: ($this->response = new ResponseType());
    }
    public function LoginResponse()
    {
        return $this->loginResponse ?: ($this->loginResponse = new LoginResponseType($this));
    }
}
