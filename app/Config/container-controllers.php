<?php

declare(strict_types=1);

use App\Authentication\Auth;
use App\Config\StaticPlaylists;

use App\Controllers\DeezerController;
use App\Controllers\HomeController;
use App\Controllers\BlindTestController;
use App\Controllers\AuthController;
use App\Controllers\ErrorsController;
use App\MusicSources\Deezer\DeezerApiInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Slim\Views\Twig;
use App\Authentication\Recaptcha;

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
