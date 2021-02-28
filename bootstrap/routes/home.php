<?php

declare(strict_types=1);

use Slim\App;
use App\Controllers\HomeController;
use App\Middleware\AuthMiddleware; //Used for private pages that requiere authentication

return function (App $app) {

        $app->get('/spinner.html', HomeController::class . ':getWaitingIcons')
                ->setName('getWaitingIcons')
                ->add(new AuthMiddleware($app));



        $app->get('/', HomeController::class . ':home')
                ->setName('home')
                ->add(new AuthMiddleware($app));
};
