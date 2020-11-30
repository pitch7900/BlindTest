<?php

$capsule = new \Illuminate\Database\Capsule\Manager;

$capsule->addConnection([
    'driver' => 'mysql',
    'host' => $_ENV['SQL_HOST'],
    'database' => $_ENV['SQL_DATABASE'],
    'username' => $_ENV['SQL_USERNAME'],
    'password' => $_ENV['SQL_PASSWORD'],
    'charset' => $_ENV['SQL_CHARSET'],
    'port' => $_ENV['SQL_PORT'],
    'collation' => $_ENV['SQL_COLLATION'],
    'prefix' => ''
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();