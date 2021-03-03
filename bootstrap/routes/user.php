<?php

declare(strict_types=1);

use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use App\Controllers\AuthController;
use App\Middleware\AuthMiddleware; //Used for private pages that requiere authentication

return function (App $app) {
        /**
         * Starting here only private pages that requieres authentication et Capatcha
         */

        $app->group('/user', function (RouteCollectorProxy $group) {
                $group->get('/signout', AuthController::class . ':signout')
                        ->setName('auth.signout');
                $group->get('/changepassword', AuthController::class . ':changepassword')
                        ->setName('user.changepassword');
                $group->get('/preferences', AuthController::class . ':preferences')
                        ->setName('user.preferences');
                $group->post('/changepassword', AuthController::class . ':postchangepassword')
                        ->setName('user.changepassword.post');
                $group->post('/preferences', AuthController::class . ':postpreferences')
                        ->setName('user.preferences.post');
        })->add(new AuthMiddleware($app));

};
