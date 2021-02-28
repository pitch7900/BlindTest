<?php

declare(strict_types=1);

use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use App\Controllers\ErrorsController;
use App\Middleware\AuthMiddleware; //Used for private pages that requiere authentication

return function (App $app) {

        $app->group('/errors', function (RouteCollectorProxy $group) {
                $group->post('/player', ErrorsController::class . ':postplayer')
                        ->setName('errors.player.post');
        })->add(new AuthMiddleware($app));

};
