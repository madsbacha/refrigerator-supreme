<?php
namespace Api\Schema;

use Api\Exception\InvalidCredentials;
use Api\Exception\NotFound;
use Api\Exception\Unauthorized;
use Api\Exception\AlreadyExist;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;

$typeRegistry = new TypeRegistry();

$queryType = new ObjectType([
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
                return $context->db->Items->Where($where);
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
                return $context->db->Ratings->Where($where);
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
                return $context->db->Comments->Where($where);
            }
        ],
        'me' => [
            'type' => $typeRegistry->User(),
            'resolve' => function ($rootValue, $args, $context) {
                if (is_null($context->User)) return null;
                return $context->db->Users->FindById($context->User->id);
            }
        ]
    ]
]);

$mutationType = new ObjectType([
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
                $password = password_hash($args['password'], PASSWORD_DEFAULT);
                if ($context->db->Users->HasByEmail($email)) {
                    throw new AlreadyExist("A user with that email already exist");
                }

                $id = $context->db->Users->Create(compact('email', 'password'));
                $user = $context->db->Users->FindById($id);

                $token = \JWTHelper::encode($user);
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
                $user = $context->db->Users->GetByEmailWithPassword($args['email']);
                if (is_null($user)) {
                    throw new InvalidCredentials();
                }
                $password_match = password_verify($args['password'], $user['password']);
                if (!$password_match) {
                    throw new InvalidCredentials();
                }
                unset($user['password']);
                $token = \JWTHelper::encode($user);
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
                if (!$context->db->Users->Update($context->User->id, compact('password'))) {
                    throw new \UnknownError('Something went wrong, try again later.');
                }
                return $context->db->Users->FindById($context->User->id);
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
                $id = $context->db->Items->Create($data);
                return $context->db->Items->FindById($id);
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
                return [ 'success' => $context->db->Items->Delete($args['id']) ];
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
                    if (!$context->db->Items->Update($args['id'], $data)) {
                        throw new \UnknownError('Something went wrong, try again later.');
                    }
                }
                return $context->db->Items->FindById($args['id']);
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
                if (!$context->db->Items->HasById($item_id)) {
                    throw new NotFound('No item with that id exists');
                }
                $rating = null;
                if ($context->db->Ratings->Has([
                    'item_id' => $item_id,
                    'user_id' => $context['user']['id']
                ])) {
                    $where = [
                        'user_id' => $context['user']['id'],
                        'item_id' => $item_id
                    ];
                    $context->db->Ratings->Update(['rating' => $args['rating']], $where);
                    $rating = $context->db->Ratings->Get($where);
                } else {
                    $id = $context->db->Ratings->Create([
                        'user_id' => $context['user']['id'],
                        'item_id' => $item_id,
                        'rating' => $args['rating']
                    ]);
                    $rating = $context->db->Ratings->FindById($id);
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
                if (!$context->db->Items->HasById($args['ItemId'])) {
                    throw new NotFound('No item with that id exists');
                }
                $id = $context->db->Comments->Create([
                    'item_id' => $args['ItemId'],
                    'text' => $args['text'],
                    'user_id' => $context->User->id
                ]);
                return $context->db->Comments->FindById($id);
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
                $success = $context->db->Comments->Delete([
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
                $id = $context->db->Categories->Create(['name' => $args['name']]);
                return $context->db->Categories->FindById($id);
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
                return [ 'success' => $context->db->Categories->DeleteById($args['id']) ];
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
                $context->db->Categories->UpdateById(['name' => $args['name']], $args['id']);
                return $context->db->Categories->FindById($args['id']);
            }
        ]
    ]
]);

return new Schema([
    'query' => $queryType,
    'mutation' => $mutationType
]);
