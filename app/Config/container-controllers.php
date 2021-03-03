<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\HomeController;
use App\Controllers\DeezerController;
use App\Controllers\ErrorsController;
use Psr\Container\ContainerInterface;
use App\Controllers\BlindTestController;

return [
    DeezerController::class => function (ContainerInterface $container): DeezerController {
        return new DeezerController($container);
    },
    BlindtestController::class => function (ContainerInterface $container): BlindtestController {
        return new BlindtestController($container);
    },
    HomeController::class => function (ContainerInterface $container): HomeController {
        return new HomeController($container);
    },
    AuthController::class => function (ContainerInterface $container): AuthController {
        return new AuthController($container);
    },
    ErrorsController::class => function (ContainerInterface $container): ErrorsController {
        return new ErrorsController($container);
    }
    
];
