<?php

declare(strict_types=1);

use App\ContainerFactory;
use Slim\Factory\AppFactory;

use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;


session_cache_limiter('public');
session_start();

// Set the absolute path to the root directory.
$rootPath = realpath(__DIR__ . '/..');
// Set the default timezone.
date_default_timezone_set('Europe/Zurich');


require $rootPath . '/vendor/autoload.php';

// Create the container for dependency injection.
try {
    $container = ContainerFactory::create($rootPath);

} catch (Exception $e) {
    die($e->getMessage());
}


// Set the container to create the App with AppFactory.
AppFactory::setContainer($container);
$app = AppFactory::create();

// Set the cache file for the routes. Note that you have to delete this file
// whenever you change the routes.
// $app->getRouteCollector()->setCacheFile(
//     $rootPath . '/cache/routes.cache'
// );

// Add the routing middleware.
$app->addRoutingMiddleware();

// Add the twig middleware.
$app->addMiddleware(
    TwigMiddleware::create($app, $container->get(Twig::class))
);

// Add error handling middleware.
$displayErrorDetails = true;
$logErrors = true;
$logErrorDetails = false;
$app->addErrorMiddleware($displayErrorDetails, $logErrors, $logErrorDetails);


// Register routes
$routes = require $rootPath . '/app/routes.php';
$routes($app);

