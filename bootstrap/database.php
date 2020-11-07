<?php

$capsule = new \Illuminate\Database\Capsule\Manager;

$capsule->addConnection([
    'driver' => 'mysql',
    'host' => $_ENV['SQL_HOST'],
    'database' => $_ENV['SQL_DATABASE'],
    'username' => $_ENV['SQL_USERNAME'],
    'password' => $_ENV['SQL_PASSWORD'],
    'charset' => 'utf8',
    'port' => $_ENV['SQL_PORT'],
    'collation' => 'utf8_unicode_ci',
    'prefix' => ''
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();
