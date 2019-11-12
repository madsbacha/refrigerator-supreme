<?php

$db = include __DIR__ . '/database.php';
$jwt = include __DIR__ . '/jwt.php';

return [
    'Query' => [
        'me' => function ($root, $args, $context) use ($db) {
            return $context['user'];
        }
    ],
    'Mutation' => [
        'CreateUser' => function ($root, $args) use ($db, $jwt) {
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
        },
        'Login' => function ($root, $args) use ($db, $jwt) {
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
        },
        'UpdateUser' => function ($root, $args, $context) use ($db) {
            if (!$context['user']) {
                throw new Error('Unauthorized');
            }
            $password = password_hash($args['password'], PASSWORD_DEFAULT);
            $db->update('users', compact('password'), ['id'=>$context['user']['id']]);
            return $context['user'];
        },
        'CreateItem' => function ($root, $args, $context) use ($db) {
            if (!$context['user']) {
                throw new Error('Unauthorized');
            }
            $name = $args['name'];
            $image = $args['image'];
            // TODO: Make sure the user has permission to create new items
            $db->insert('items', compact('name', 'image'));
            return $db->get('items', ['name', 'image'], ['id' => $db->lastInsertId()]);
        },
        'DeleteItem' => function ($root, $args, $context) use ($db) {
            if (!$context['user']) {
                throw new Error('Unauthorized');
            }
            $id = $args['itemId'];
            $data = $db->delete('items', compact('id'));
            return [ 'success' => $data->rowCount() > 0 ];
        },
        'UpdateItem' => function ($root, $args, $context) use ($db) {
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
    ]
];
