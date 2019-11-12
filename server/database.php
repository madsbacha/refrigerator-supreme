<?php

use Medoo\Medoo;

return new Medoo([
  'database_type' => 'sqlite',
  'database_file' => 'database.sqlite',
  'database_name' => 'name',
  'server' => 'localhost',
  'username' => '',
  'password' => '',
  'prefix' => ''
]);
