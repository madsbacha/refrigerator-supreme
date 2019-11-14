<?php
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;

$db = require __DIR__.'/../util/database.php';
$jwt = require __DIR__.'/../util/jwt.php';
require_once __DIR__.'/Types.php';
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
            'resolve' => function ($root, $args) use ($db, $jwt) {
                $email = $args['email'];
                $password = password_hash($args['password'], PASSWORD_DEFAULT);
                if ($db->has('users', compact('email'))) {
                    return [ 'success' => false ];
                }

                $db->insert('users', compact('email', 'password'));
                $user = $db->get('users', ['id', 'email'], compact('email'));

                $token = $jwt::encode($user);
                return [
                    'success' => true,
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
            'resolve' => function ($root, $args) use ($db, $jwt) {
                $email = $args['email'];
                $user = $db->get('users', ['id', 'email', 'password'], compact('email'));
                if (!$user) {
                    return [ 'success' => false ];
                }
                $password_match = password_verify($args['password'], $user['password']);
                if (!$password_match) {
                    return [ 'success' => false ];
                }
                unset($user['password']);
                $token = $jwt::encode($user);
                return [
                    'success' => true,
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
                if (!$context['user']) {
                    return null;
                }
                $password = password_hash($args['password'], PASSWORD_DEFAULT);
                $db->update('users', compact('password'), ['id'=>$context['user']['id']]);
                return $context['user'];
            }
        ],
        'CreateItem' => [
            'type' => $typeRegistry->Item(),
            'args' => [
                'name' => Type::nonNull(Type::string()),
                'image' => Type::nonNull(Type::string())
            ],
            'resolve' => function ($root, $args, $context) use ($db) {
                if ($context['user'] == null) {
                    return null;
                }
                $name = $args['name'];
                $image = $args['image'];
                // TODO: Make sure the user has permission to create new items
                $db->insert('items', compact('name', 'image'));
                return $db->get('items', ['id', 'name', 'image'], ['id' => $db->id()]);
            }
        ],
        'DeleteItem' => [
            'type' => $typeRegistry->Response(),
            'args' => [
                'itemId' => Type::nonNull(Type::id())
            ],
            'resolve' => function ($root, $args, $context) use ($db) {
                if (!$context['user']) {
                    return [ 'success' => false ];
                }
                $id = $args['itemId'];
                $data = $db->delete('items', compact('id'));
                return [ 'success' => $data->rowCount() > 0 ];
            }
        ],
        'UpdateItem' => [
            'type' => $typeRegistry->Item(),
            'args' => [
                'itemId' => Type::nonNull(Type::id()),
                'name' => Type::string(),
                'image' => Type::string()
            ],
            'resolve' => function ($root, $args, $context) use ($db) {
                if (!$context['user']) {
                    return null;
                }
                $id = $args['itemId'];
                $data = [];
                if ($args['image']) {
                    $data['image'] = $args['image'];
                }
                if ($args['name']) {
                    $data['name'] = $args['name'];
                }
                if (count($data) == 0) {
                    return [ 'success' => true ];
                }
                $data = $db->update('items', $data, compact('id'));
                return [ 'success' => $data->rowCount() > 0 ];
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
                    return null;
                }
                $item_id = $args['itemId'];
                $rating = $args['rating'];
                if (!$db->has('items', ['id' => $item_id])) {
                    return null;
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
                if (!$context['user']) {
                    return null;
                }
                $item_id = $args['itemId'];
                $text = $args['text'];
                $user_id = $context['user']['id'];
                if (!$db->has('items', ['id' => $item_id])) {
                    return null;
                }
                $db->insert('comments', compact('item_id', 'text', 'user_id'));
                return $db->get('comments', ['id', 'item_id', 'text', 'user_id'], ['id' => $db->id()]);
            }
        ],
        'DeleteComment' => [
            'type' => $typeRegistry->Response(),
            'args' => [
                'commentId' => Type::nonNull(Type::id())
            ],
            'resolve' => function ($root, $args, $context) use ($db) {
                if (!$context['user']) {
                    return [ 'success' => false ];
                }
                $id = $args['commentId'];
                $user_id = $context['user']['id'];
                $where = compact('user_id', 'id');
                $data = $db->delete('comments', $where);
                return [ 'success' => $data->rowCount() > 0 ];
            }
        ]
    ]
]);

return new Schema([
    'query' => $queryType,
    'mutation' => $mutationType
]);
