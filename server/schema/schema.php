<?php
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;

$db = require __DIR__.'/../util/database.php';
$jwt = require __DIR__.'/../util/jwt.php';
require_once __DIR__.'/Types.php';
$typeRegistry = new TypeRegistry();

$queryType = new ObjectType([
    'name' => 'Query',
    'fields' => [
        'drinks' => Type::listOf($typeRegistry->Item()),
        'ratings' => Type::listOf($typeRegistry->Rating()),
        'comments' => Type::listOf($typeRegistry->Comment()),
        'me' => $typeRegistry->User()
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
                    throw new TypeError('A user with that email already exists');
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
                    throw new Error('Unauthorized');
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
                if (!$context['user']) {
                    throw new Error('Unauthorized');
                }
                $name = $args['name'];
                $image = $args['image'];
                // TODO: Make sure the user has permission to create new items
                $db->insert('items', compact('name', 'image'));
                return $db->get('items', ['name', 'image'], ['id' => $db->lastInsertId()]);
            }
        ],
        'DeleteItem' => [
            'type' => $typeRegistry->Response(),
            'args' => [
                'itemId' => Type::nonNull(Type::string())
            ],
            'resolve' => function ($root, $args, $context) use ($db) {
                if (!$context['user']) {
                    throw new Error('Unauthorized');
                }
                $id = $args['itemId'];
                $data = $db->delete('items', compact('id'));
                return [ 'success' => $data->rowCount() > 0 ];
            }
        ],
        'UpdateItem' => [
            'type' => $typeRegistry->Item(),
            'args' => [
                'itemId' => Type::nonNull(Type::string()),
                'name' => Type::string(),
                'image' => Type::string()
            ],
            'resolve' => function ($root, $args, $context) use ($db) {
                if (!$context['user']) {
                    throw new Error('Unauthorized');
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
                'itemId' => Type::nonNull(Type::string()),
                'rating' => Type::nonNull(Type::float())
            ],
            'resolve' => function ($root, $args, $context) use ($db) {
                if (!$context['user']) {
                    throw new Error('Unauthorized');
                }
                $item_id = $args['itemId'];
                $rating = $args['rating'];
                if (!$db->has('items', ['id' => $item_id])) {
                    throw new Error('Invalid item id');
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
                'itemId' => Type::nonNull(Type::int()),
                'comment' => Type::nonNull(Type::string())
            ],
            'resolve' => function ($root, $args, $context) use ($db) {
                if (!$context['user']) {
                    throw new Error('Unauthorized');
                }
                $item_id = $args['itemId'];
                $text = $args['comment'];
                $user_id = $context['user']['id'];
                if (!$db->has('items', ['id' => $item_id])) {
                    throw new Error('No item with that id exist');
                }
                $db->insert('comments', compact('item_id', 'text', 'user_id'));
                return $db->get('comments', ['id', 'item_id', 'text', 'user_id'], ['id' => $db->lastInsertId()]);
            }
        ],
        'DeleteComment' => [
            'type' => $typeRegistry->Response(),
            'args' => [
                'commentId' => Type::nonNull(Type::int())
            ],
            'resolve' => function ($root, $args, $context) use ($db) {
                if (!$context['user']) {
                    throw new Error('Unauthorized');
                }
                $id = $args['commentId'];
                $user_id = $context['user']['id'];
                $where = compact('user_id', 'id');
                if (!$db->has('comments', $where)) {
                    throw new Error('Cannot find or delete comment');
                }
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
