<?php
use Medoo\Medoo;

$config = require __DIR__.'/../config.php';
return new Medoo($config['database']);
