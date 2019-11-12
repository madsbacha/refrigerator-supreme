<?php

use Medoo\Medoo;

$config = include __DIR__.'/config.php';

return new Medoo($config['database']);
