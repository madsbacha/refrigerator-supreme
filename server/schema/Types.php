<?php

use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class SortOrderEnum extends EnumType
{
    public function __construct()
    {
        parent::__construct([
            'description' => 'The ordering of the sort',
            'values' => [
                'ASC' => [
                    'value' => 0,
                    'description' => 'Ascending order'
                ],
                'DESC' => [
                    'value' => 1,
                    'description' => 'Descending order'
                ]
            ]
        ]);
    }
}

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
                    'rating' => Type::float(),
                    'comments' => Type::listOf($types->Comment())
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
                    'item' => $types->Item(),
                    'user' => $types->User(),
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
                    'ratings' => Type::listOf($types->Rating()),
                    'comments' => Type::listOf($types->Comment())
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
                    'item' => $types->Item(),
                    'text' => Type::string(),
                    'author' => $types->User()
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
    private $sortOrder;

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
    public function SortOrderEnum()
    {
        return $this->sortOrder ?: ($this->sortOrder = new SortOrderEnum());
    }
}
