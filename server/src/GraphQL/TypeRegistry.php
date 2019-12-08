<?php
namespace Api\GraphQL;

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
