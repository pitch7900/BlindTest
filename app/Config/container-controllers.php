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
use Slim\Interfaces\RouteParserInterface;

return [
    DeezerController::class => function (ContainerInterface $container): DeezerController {
        return new DeezerController($container->get(Twig::class),
            $container->get(LoggerInterface::class),
            $container->get(DeezerApiInterface::class)
        );
    },
    BlindtestController::class => function (ContainerInterface $container): BlindtestController {
        return new BlindtestController($container->get(Twig::class),
            $container->get(LoggerInterface::class),
            $container->get(DeezerApiInterface::class),
            $container->get(Auth::class)
        );
    },
    HomeController::class => function (ContainerInterface $container): HomeController {
        return new HomeController($container->get(Twig::class), 
            $container->get(LoggerInterface::class),
            $container->get(DeezerApiInterface::class),
            $container->get(StaticPlaylists::class),
            $container->get(Auth::class)
        );
    },
    AuthController::class => function (ContainerInterface $container): AuthController {
        return new AuthController($container->get(Twig::class), 
            $container->get(LoggerInterface::class),
            $container->get(Auth::class),
            $container->get(Recaptcha::class)
        );
    },

    ErrorsController::class => function (ContainerInterface $container): ErrorsController {
        return new ErrorsController($container->get(Twig::class), 
            $container->get(LoggerInterface::class),
            $container->get(DeezerApiInterface::class)
        );
    }
];
