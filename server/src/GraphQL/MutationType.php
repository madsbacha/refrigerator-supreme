<?php
namespace Api\GraphQL;

use Api\Exception\AlreadyExist;
use Api\Exception\InvalidCredentials;
use Api\Exception\InvalidEmail;
use Api\Exception\NotFound;
use Api\Exception\Unauthorized;
use Api\Util\JWTHelper;
use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\RFCValidation;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class MutationType extends ObjectType
{
    public function __construct(TypeRegistry $typeRegistry)
    {
        $config = [
            'name' => 'Mutation',
            'fields' => [
                'CreateUser' => [
                    'type' => $typeRegistry->LoginResponse(),
                    'args' => [
                        'email' => Type::nonNull(Type::string()),
                        'password' => Type::nonNull(Type::string())
                    ],
                    'resolve' => function ($root, $args, $context) {
                        $email = $args['email'];
                        $validator = new EmailValidator();
                        if (!$validator->isValid($email, new RFCValidation())) {
                            throw new InvalidEmail('Invalid email');
                        }
                        $password = password_hash($args['password'], PASSWORD_DEFAULT);
                        if ($context->Db->Users->HasByEmail($email)) {
                            throw new AlreadyExist("A user with that email already exist");
                        }

                        $id = $context->Db->Users->Create(compact('email', 'password'));
                        $user = $context->Db->Users->FindById($id);

                        $token = JWTHelper::encode($user);
                        return [
                            'user' => $user,
                            'token' => $token
                        ];
                    }
                ],
                'Login' => [
                    'type' => $typeRegistry->LoginResponse(),
                    'args' => [
                        'email' => Type::nonNull(Type::string()),
                        'password' => Type::nonNull(Type::string())
                    ],
                    'resolve' => function ($root, $args, $context) {
                        $user = $context->Db->Users->GetByEmailWithPassword($args['email']);
                        if (is_null($user)) {
                            throw new InvalidCredentials();
                        }
                        $password_match = password_verify($args['password'], $user['password']);
                        if (!$password_match) {
                            throw new InvalidCredentials();
                        }
                        unset($user['password']);
                        $token = JWTHelper::encode($user);
                        return [
                            'user' => $user,
                            'token' => $token
                        ];
                    }
                ],
                'UpdateUser' => [
                    'type' => $typeRegistry->User(),
                    'args' => [
                        'password' => Type::nonNull(Type::string())
                    ],
                    'resolve' => function ($root, $args, $context) {
                        if (!$context->IsLoggedIn) {
                            throw new Unauthorized();
                        }
                        $password = password_hash($args['password'], PASSWORD_DEFAULT);
                        if (!$context->Db->Users->UpdateById($context->User->id, compact('password'))) {
                            throw new \UnknownError('Something went wrong, try again later.');
                        }
                        return $context->Db->Users->FindById($context->User->id);
                    }
                ],
                'CreateItem' => [
                    'type' => $typeRegistry->Item(),
                    'args' => [
                        'name' => Type::nonNull(Type::string()),
                        'image' => Type::nonNull(Type::string()),
                        'categoryId' => Type::id()
                    ],
                    'resolve' => function ($root, $args, $context) {
                        if (!$context->IsLoggedIn) {
                            throw new Unauthorized();
                        }
                        $data = [
                            'name' => $args['name'],
                            'image' => $args['image'],
                            'category_id' => array_key_exists('category_id', $args) ? $args['category_id'] : null
                        ];
                        // TODO: Make sure the user has permission to create new items
                        $id = $context->Db->Items->Create($data);
                        return $context->Db->Items->FindById($id);
                    }
                ],
                'DeleteItem' => [
                    'type' => $typeRegistry->Response(),
                    'args' => [
                        'id' => Type::nonNull(Type::id())
                    ],
                    'resolve' => function ($root, $args, $context) {
                        if (!$context->IsLoggedIn) {
                            throw new Unauthorized();
                        }
                        return [ 'success' => $context->Db->Items->DeleteById($args['id']) ];
                    }
                ],
                'UpdateItem' => [
                    'type' => $typeRegistry->Item(),
                    'args' => [
                        'id' => Type::nonNull(Type::id()),
                        'name' => Type::string(),
                        'image' => Type::string(),
                        'categoryId' => Type::id()
                    ],
                    'resolve' => function ($root, $args, $context) {
                        if (!$context->IsLoggedIn) {
                            throw new Unauthorized();
                        }
                        $data = [];
                        if (array_key_exists('image', $args)) {
                            $data['image'] = $args['image'];
                        }
                        if (array_key_exists('name', $args)) {
                            $data['name'] = $args['name'];
                        }
                        if (array_key_exists('categoryId', $args)) {
                            $data['category_id'] = $args['categoryId'];
                        }
                        if (count($data) != 0) {
                            if (!$context->Db->Items->UpdateById($args['id'], $data)) {
                                throw new \UnknownError('Something went wrong, try again later.');
                            }
                        }
                        return $context->Db->Items->FindById($args['id']);
                    }
                ],
                'RateItem' => [
                    'type' => $typeRegistry->Rating(),
                    'args' => [
                        'itemId' => Type::nonNull(Type::id()),
                        'rating' => Type::nonNull(Type::float())
                    ],
                    'resolve' => function ($root, $args, $context) {
                        if (!$context->IsLoggedIn) {
                            throw new Unauthorized();
                        }
                        $item_id = $args['itemId'];
                        if (!$context->Db->Items->HasById($item_id)) {
                            throw new NotFound('No item with that id exists');
                        }
                        $rating = null;
                        if ($context->Db->Ratings->Has([
                            'item_id' => $item_id,
                            'user_id' => $context['user']['id']
                        ])) {
                            $where = [
                                'user_id' => $context['user']['id'],
                                'item_id' => $item_id
                            ];
                            $context->Db->Ratings->Update(['rating' => $args['rating']], $where);
                            $rating = $context->Db->Ratings->Get($where);
                        } else {
                            $id = $context->Db->Ratings->Create([
                                'user_id' => $context['user']['id'],
                                'item_id' => $item_id,
                                'rating' => $args['rating']
                            ]);
                            $rating = $context->Db->Ratings->FindById($id);
                        }
                        return $rating;
                    }
                ],
                'CreateComment' => [
                    'type' => $typeRegistry->Comment(),
                    'args' => [
                        'itemId' => Type::nonNull(Type::id()),
                        'text' => Type::nonNull(Type::string())
                    ],
                    'resolve' => function ($root, $args, $context) {
                        if (!$context->IsLoggedIn) {
                            throw new Unauthorized();
                        }
                        if (!$context->Db->Items->HasById($args['ItemId'])) {
                            throw new NotFound('No item with that id exists');
                        }
                        $id = $context->Db->Comments->Create([
                            'item_id' => $args['ItemId'],
                            'text' => $args['text'],
                            'user_id' => $context->User->id
                        ]);
                        return $context->Db->Comments->FindById($id);
                    }
                ],
                'DeleteComment' => [
                    'type' => $typeRegistry->Response(),
                    'args' => [
                        'id' => Type::nonNull(Type::id())
                    ],
                    'resolve' => function ($root, $args, $context) {
                        if (!$context->IsLoggedIn) {
                            throw new Unauthorized();
                        }
                        $success = $context->Db->Comments->Delete([
                            'user_id' => $context->User->id,
                            'id' => $args['id']
                        ]);
                        return compact('success');
                    }
                ],
                'CreateCategory' => [
                    'type' => $typeRegistry->Category(),
                    'args' => [
                        'name' => Type::nonNull(Type::string())
                    ],
                    'resolve' => function ($root, $args, $context) {
                        if (!$context->IsLoggedIn) {
                            throw new Unauthorized();
                        }
                        $id = $context->Db->Categories->Create(['name' => $args['name']]);
                        return $context->Db->Categories->FindById($id);
                    }
                ],
                'DeleteCategory' => [
                    'type' => $typeRegistry->Response(),
                    'args' => [
                        'id' => Type::nonNull(Type::id())
                    ],
                    'resolve' => function ($root, $args, $context) {
                        if (!$context->IsLoggedIn) {
                            throw new Unauthorized();
                        }
                        return [ 'success' => $context->Db->Categories->DeleteById($args['id']) ];
                    }
                ],
                'UpdateCategory' => [
                    'type' => $typeRegistry->Category(),
                    'args' => [
                        'id' => Type::nonNull(Type::id()),
                        'name' => Type::nonNull(Type::string())
                    ],
                    'resolve' => function ($root, $args, $context) {
                        if (!$context->IsLoggedIn) {
                            throw new Unauthorized();
                        }
                        $context->Db->Categories->UpdateById(['name' => $args['name']], $args['id']);
                        return $context->Db->Categories->FindById($args['id']);
                    }
                ]
            ]
        ];
        parent::__construct($config);
    }
}
