<?php

use Medoo\Medoo;

$database = new Medoo([
  'database_type' => 'sqlite',
  'database_file' => 'database.sqlite',
  'database_name' => 'name',
  'server' => 'localhost',
  'username' => '',
  'password' => '',
  'prefix' => 'refrigerator_'
]);

$userById = function ($id) use ($database) {
  return $database->get('users', ['id', 'email'], ['id' => $id]);
};

$commentById = function ($id) use ($database) {
  return $database->get('comments', ['id', 'item_id', 'user_id', 'text'], ['id' => $id]);
};

$commentsByItemId = function ($id) use ($database) {
  return $database->select('comments', ['id', 'item_id', 'user_id', 'text'], ['item_id' => $id]);
};

$commentsByUserId = function ($id) use ($database) {
  return $database->select('comments', ['id', 'item_id', 'user_id', 'text'], ['user_id' => $id]);
};

$ratingsByItemId = function ($id) use ($database) {
  return $database->select('ratings', ['id', 'item_id', 'user_id', 'rating'], ['item_id' => $id]);
};

$ratingsByUserId = function ($id) use ($database) {
  return $database->select('ratings', ['id', 'item_id', 'user_id', 'rating'], ['user_id' => $id]);
};

$rating = function ($user_id, $item_id) use ($database) {
  return $database->get('ratings', ['id', 'item_id', 'user_id', 'rating'], ['user_id' => $user_id, 'item_id' => $item_id]);
};

$item = function ($id) use ($database) {
  return $database->get('items', ['id', 'name', 'image'], ['id' => $id]);
};

$allItems = function () use ($database) {
  return $database->select('items', ['id', 'name', 'image']);
};

$confirmCredentials = function ($email, $password) use ($database) {
  return $database->has('users', [
    'email' => $email,
    'password' => $password
  ]);
};

$insertUser = function ($email, $password) use ($database) {
  $database->insert('users', [
    'email' => $email,
    'password' => $password
  ]);
};

$insertRating = function ($rating, $item_id, $user_id) use ($database) {
  $database->insert('ratings', [
    'rating' => $rating,
    'item_id' => $item_id,
    'user_ud' => $user_id
  ]);
};

$insertComment = function ($text, $item_id, $user_id) use ($database) {
  $database->insert('comments', [
    'text' => $text,
    'item_id' => $item_id,
    'user_id' => $user_id
  ]);
};

$insertItem = function ($name, $image) use ($database) {
  $database->insert('items', [
    'name' => $name,
    'image' => $image
  ]);
};

$deleteUser = function ($id) use ($database) {
  $database->delete('users', [ 'id' => $id ]);
};

$deleteComment = function ($id) use ($database) {
  $database->delete('comments', [ 'id' => $id ]);
};

$deleteItem = function ($id) use ($database) {
  $database->delete('items', [ 'id' => $id ]);
};

$updateUser = function ($id, $new_data) use ($database) {
  $database->update('users', $new_data, ['id' => $id]);
};

$updateItem = function ($id, $new_data) use ($database) {
  $database->update('items', $new_data, ['id' => $id]);
};

return [
  'commentById'         => $commentById,
  'commentsByItemId'    => $commentsByItemId,
  'commentsByUserId'    => $commentsByUserId,
  'ratingsByItemId'     => $ratingsByItemId,
  'ratingsByUserId'     => $ratingsByUserId,
  'rating'              => $rating,
  'item'                => $item,
  'allItems'            => $allItems,
  'confirmCredentials'  => $confirmCredentials,
  'insertUser'          => $insertUser,
  'deleteUser'          => $deleteUser,
  'insertRating'        => $insertRating,
  'insertComment'       => $insertComment,
  'insertItem'          => $insertItem,
  'deleteComment'       => $deleteComment,
  'deleteItem'          => $deleteItem,
  'updateUser'          => $updateUser,
  'updateItem'          => $updateItem
];
