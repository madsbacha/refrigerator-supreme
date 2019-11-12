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
        }
    ]
];
