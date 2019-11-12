<?php

$db = include __DIR__.'/database.php';

return [
  'Query' => [
      'me' => null,
  ],
  'Mutation' => [
    'CreateUser' => function ($root, $args) use ($db) {
      $email = $args['email'];
      $password = $args['password'];
      if ($db->has('users', compact('email'))) {
        throw new TypeError('A user with that email already exists');
      }

      // TODO: Encrypt password

      $db->insert('users', compact(['email', 'password']));
      $user = $db->get('users', ['id', 'email'], compact('email'));

      return [ 'success' => true, 'user' => $user ];
    }
  ]
];
