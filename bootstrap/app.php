<?php

session_cache_limiter('public');
session_start();


require __DIR__ . '/../vendor/autoload.php';




$app = new \Slim\App([
    'settings' => [
        'displayErrorDetails' => true,
        'debug' => true
    ]
]);
$app->add(new \Slim\HttpCache\Cache('public', 0));



$container = $app->getContainer();


require_once __DIR__ . '/container_view.php';

$container['ErrorController'] = function ($container) {
    return new \App\Controllers\ErrorController($container);
};

try {
    $dotenv = (Dotenv\Dotenv::createImmutable(__DIR__ . '/../config/'))->load();
} catch (\Dotenv\Exception\InvalidPathException $e) {
}


$container['HomeController'] = function ($container) {
    return new \App\Controllers\HomeController($container);
};


$container['DeezerController'] = function ($container) {
    return new \App\Controllers\DeezerController($container);
};


$_SESSION['deezerapi'] = serialize(new \App\MusicSources\DeezerApi());


$_SESSION['sources'] = "deezer";

require __DIR__ . '/../app/routes.php';
