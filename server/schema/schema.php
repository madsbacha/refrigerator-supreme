<?php
namespace Api\Schema;

use Api\Exception\InvalidCredentials;
use Api\Exception\NotFound;
use Api\Exception\Unauthorized;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use Api\Exception\AlreadyExist;

$db = require __DIR__.'../util/database.php';
$typeRegistry = new TypeRegistry($db);

$queryType = new ObjectType([
    'name' => 'Query',
    'fields' => [
        'drinks' => [
            'type' => Type::listOf($typeRegistry->Item()),
            'args' => [
                'first' => Type::int(),
                'after' => Type::int()
            ],
            'resolve' => function ($rootValue, $args) use ($db) {
                $where = [];
                if (array_key_exists('first', $args) && array_key_exists('after', $args)) {
                    $where['LIMIT'] = [$args['after'], $args['first']];
                } else if (array_key_exists('first', $args)) {
                    $where['LIMIT'] = $args['first'];
                }
                return $db->select('items', ['id', 'name', 'image'], $where);
            }
        ],
        'ratings' => [
            'type' => Type::listOf($typeRegistry->Rating()),
            'args' => [
                'itemId' => Type::id()
            ],
            'resolve' => function ($rootValue, $args) use ($db) {
                $where = [];
                if (array_key_exists('itemId', $args)) {
                    $where['item_id'] = $args['itemId'];
                }
                return $db->select('ratings', ['id', 'item_id', 'user_id', 'rating'], $where);
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
            'resolve' => function ($rootValue, $args) use ($db) {
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
                return $db->select('comments', ['id', 'item_id', 'user_id', 'text'], $where);
            }
        ],
        'me' => [
            'type' => $typeRegistry->User(),
            'resolve' => function ($rootValue, $args, $context) {
                return $context['user'];
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
            'resolve' => function ($root, $args) use ($db) {
                $email = $args['email'];
                $password = password_hash($args['password'], PASSWORD_DEFAULT);
                if ($db->has('users', compact('email'))) {
                    throw new AlreadyExist("A user with that email already exist");
                }

                $db->insert('users', compact('email', 'password'));
                $user = $db->get('users', ['id', 'email'], compact('email'));

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
            'resolve' => function ($root, $args) use ($db) {
                $email = $args['email'];
                $user = $db->get('users', ['id', 'email', 'password'], compact('email'));
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
            'resolve' => function ($root, $args, $context) use ($db) {
                if (is_null($context['user'])) {
                    throw new Unauthorized();
                }
                $password = password_hash($args['password'], PASSWORD_DEFAULT);
                $db->update('users', compact('password'), ['id'=>$context['user']['id']]);
                return $db->get('user', ['id', 'email'], ['id' => $context['user']['id']]);
            }
        ],
        'CreateItem' => [
            'type' => $typeRegistry->Item(),
            'args' => [
                'name' => Type::nonNull(Type::string()),
                'image' => Type::nonNull(Type::string()),
                'categoryId' => Type::id()
            ],
            'resolve' => function ($root, $args, $context) use ($db) {
                if (is_null($context['user'])) {
                    throw new Unauthorized();
                }
                $data = [
                    'name' => $args['name'],
                    'image' => $args['image'],
                    'category_id' => array_key_exists('category_id', $args) ? $args['category_id'] : null
                ];
                // TODO: Make sure the user has permission to create new items
                $db->insert('items', $data);
                return $db->get('items', ['id', 'name', 'image', 'category_id'], ['id' => $db->id()]);
            }
        ],
        'DeleteItem' => [
            'type' => $typeRegistry->Response(),
            'args' => [
                'id' => Type::nonNull(Type::id())
            ],
            'resolve' => function ($root, $args, $context) use ($db) {
                if (is_null($context['user'])) {
                    throw new Unauthorized();
                }
                $data = $db->delete('items', [ 'id' => $args['id'] ]);
                return [ 'success' => $data->rowCount() > 0 ];
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
            'resolve' => function ($root, $args, $context) use ($db) {
                if (is_null($context['user'])) {
                    throw new Unauthorized();
                }
                $id = $args['id'];
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
                if (count($data) == 0) {
                    return [ 'success' => true ];
                }
                $db->update('items', $data, compact('id'));
                return $db->get('items', ['id', 'name', 'image', 'category_id'], ['id' => $id]);
            }
        ],
        'RateItem' => [
            'type' => $typeRegistry->Rating(),
            'args' => [
                'itemId' => Type::nonNull(Type::id()),
                'rating' => Type::nonNull(Type::float())
            ],
            'resolve' => function ($root, $args, $context) use ($db) {
                if (!$context['user']) {
                    throw new Unauthorized();
                }
                $item_id = $args['itemId'];
                $rating = $args['rating'];
                if (!$db->has('items', ['id' => $item_id])) {
                    throw new NotFound('No item with that id exists');
                }
                if ($db->has('ratings',
                    [
                        'item_id' => $item_id,
                        'user_id' => $context['user']['id']
                    ]
                )) {
                    $db->update('ratings',
                        compact('rating'),
                        [
                            'user_id' => $context['user']['id'],
                            'item_id' => $item_id
                        ]
                    );
                } else {
                    $db->insert('ratings', [
                        'user_id' => $context['user']['id'],
                        'item_id' => $item_id,
                        'rating' => $rating
                    ]);
                }
                return $db->get('ratings',
                    ['id', 'item_id', 'user_id', 'rating'],
                    [
                        'user_id' => $context['user']['id'],
                        'item_id' => $item_id
                    ]
                );
            }
        ],
        'CreateComment' => [
            'type' => $typeRegistry->Comment(),
            'args' => [
                'itemId' => Type::nonNull(Type::id()),
                'text' => Type::nonNull(Type::string())
            ],
            'resolve' => function ($root, $args, $context) use ($db) {
                if (is_null($context['user'])) {
                    throw new Unauthorized();
                }
                $item_id = $args['itemId'];
                $text = $args['text'];
                $user_id = $context['user']['id'];
                if (!$db->has('items', ['id' => $item_id])) {
                    throw new NotFound('No item with that id exists');
                }
                $db->insert('comments', compact('item_id', 'text', 'user_id'));
                return $db->get('comments', ['id', 'item_id', 'text', 'user_id'], ['id' => $db->id()]);
            }
        ],
        'DeleteComment' => [
            'type' => $typeRegistry->Response(),
            'args' => [
                'id' => Type::nonNull(Type::id())
            ],
            'resolve' => function ($root, $args, $context) use ($db) {
                if (is_null($context['user'])) {
                    throw new Unauthorized();
                }
                $id = $args['id'];
                $user_id = $context['user']['id'];
                $where = compact('user_id', 'id');
                $data = $db->delete('comments', $where);
                return [ 'success' => $data->rowCount() > 0 ];
            }
        ],
        'CreateCategory' => [
            'type' => $typeRegistry->Category(),
            'args' => [
                'name' => Type::nonNull(Type::string())
            ],
            'resolve' => function ($root, $args, $context) use ($db) {
                if (is_null($context['user'])) {
                    throw new Unauthorized();
                }
                $db->insert('categories', ['name' => $args['name']]);
                return $db->get('categories', ['id', 'name'], ['id' => $db->id()]);
            }
        ],
        'DeleteCategory' => [
            'type' => $typeRegistry->Response(),
            'args' => [
                'id' => Type::nonNull(Type::id())
            ],
            'resolve' => function ($root, $args, $context) use ($db) {
                if (is_null($context['user'])) {
                    throw new Unauthorized();
                }
                $data = $db->delete('categories', ['id' => $args['id']]);
                return [ 'success' => $data->rowCount() > 0 ];
            }
        ],
        'UpdateCategory' => [
            'type' => $typeRegistry->Category(),
            'args' => [
                'id' => Type::nonNull(Type::id()),
                'name' => Type::nonNull(Type::string())
            ],
            'resolve' => function ($root, $args, $context) use ($db) {
                if (is_null($context['user'])) {
                    throw new Unauthorized();
                }
                $db->update('categories', ['name' => $args['name']], ['id' => $args['id']]);
                return $db->get('categories', ['id', 'name'], ['id'=>$args['id']]);
            }
        ]
    ]
]);

return new Schema([
    'query' => $queryType,
    'mutation' => $mutationType
]);
