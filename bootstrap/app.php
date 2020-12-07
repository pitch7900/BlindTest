<?php

declare(strict_types=1);

use App\ContainerFactory;
use Slim\Factory\AppFactory;
use Dotenv\Exception\InvalidPathException;

use Dotenv\Dotenv;


// Set the absolute path to the root directory.
$rootPath = realpath(__DIR__ . '/..');
// Set the default timezone.
date_default_timezone_set('Europe/Zurich');

/**Need memcached extension to work. See README.md for installation and configuraiton */
ini_set('session.save_handler', 'memcache');
ini_set('memcached.sess_locking','0');
ini_set('session.save_path', 'tcp://127.0.0.1:11211?persistent=1&weight=1&timeout=1&retry_interval=2');
session_cache_limiter('public');
session_start();


require $rootPath . '/vendor/autoload.php';

try {
    // $dotenv = Dotenv::createImmutable($rootPath .'/config/');
    $dotenv = Dotenv::createMutable($rootPath .'/config/');
	$dotenv->load();
} catch (InvalidPathException $e) {
	die("Unable to load configuration file");
}

//Load DB configuration
require_once __DIR__ . '/database.php';

// $sessionhandler = new SessionHandler();

// // Pass DB details to create a new MySQLi connection
// $sessionhandler->setDbDetails($_ENV['SQL_HOST'], $_ENV['SQL_USERNAME'], $_ENV['SQL_PASSWORD'], $_ENV['SQL_DATABASE']);

// session_set_save_handler($sessionhandler, true);
// session_cache_limiter('public');
// session_start([
//     'read_and_close' => true,
// ]);


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

//Call middleware functions
$_SERVER['app'] = &$app;

if (!function_exists('app'))
{
    function app()
    {
        return $_SERVER['app'];
    }
}

//Add Middlewares
(require __DIR__ . '/middleware.php')($app);

// Register routes
$routes = require $rootPath . '/app/routes.php';
$routes($app);



// Add error handling middleware.
$displayErrorDetails = true;
$logErrors = true;
$logErrorDetails = false;
$app->addErrorMiddleware($displayErrorDetails, $logErrors, $logErrorDetails);





