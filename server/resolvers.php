<?php

$db = include __DIR__.'/database.php';

return [
  'Query' => [
      'me' => null,
  ],
  'Mutation' => [
    'CreateUser' => function ($root, $args) use ($db) {
      try {
        $db['insertUser']($args['email'], $args['password']);
      } catch (Error $e) {
        return [
          "success" => false
        ];
      }
      return [ "success" => true ];
    }
  ]
];
