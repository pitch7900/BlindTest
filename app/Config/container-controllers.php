<?php

declare(strict_types=1);

use App\Config\StaticPlaylists;
use App\Controllers\ExceptionDemoController;
use App\Controllers\DeezerController;
use App\Controllers\HomeController;
use App\Controllers\BlindTestController;
use App\MusicSources\Deezer\DeezerApiInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Slim\Views\Twig;
use Hoa\Eventsource\Server;

return [
    ExceptionDemoController::class => function (ContainerInterface $container): ExceptionDemoController {
        return new ExceptionDemoController();
    },
    DeezerController::class => function (ContainerInterface $container): DeezerController {
        return new DeezerController($container->get(Twig::class),
            $container->get(LoggerInterface::class),
            $container->get(DeezerApiInterface::class)
        );
    },
    BlindtestController::class => function (ContainerInterface $container): BlindtestController {
        return new BlindtestController($container->get(Twig::class),
            $container->get(LoggerInterface::class),
            $container->get(DeezerApiInterface::class)
        );
    },
    HomeController::class => function (ContainerInterface $container): HomeController {
        return new HomeController($container->get(Twig::class), 
            $container->get(LoggerInterface::class),
            $container->get(DeezerApiInterface::class),
            $container->get(StaticPlaylists::class)
        );
    },

];
