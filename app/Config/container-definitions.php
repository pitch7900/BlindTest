<?php

declare(strict_types=1);

use App\Preferences;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Slim\Views\Twig;
use App\MusicSources\Deezer\DeezerApiInterface;
use App\MusicSources\Deezer\DeezerApi;
use App\Config\StaticPlaylists;
use App\Authentication\Authentication;
use App\Authentication\Recaptcha;


return [
    LoggerInterface::class => function (ContainerInterface $container): LoggerInterface {
        // Get the preferences from the container.
        $preferences = $container->get(Preferences::class);

        // Instantiate a new logger and push a handler into the logger.
        $logger = new Logger('blindtest');
        $logger->pushHandler(
            new RotatingFileHandler(
                $preferences->getRootPath() . '/logs/blindtest.log'
            )
        );

        return $logger;
    },
    Twig::class => function (ContainerInterface $container): Twig {
        // Get the preferences from the container.
        $preferences = $container->get(Preferences::class);

        // Instantiate twig.
        $twig = Twig::create(
            $preferences->getRootPath() . '/resources/views',
            [
                'cache' => $preferences->getRootPath() . '/cache',
                'auto_reload' => true,
                'debug' => true,
            ]
        );
        $twig->addExtension(new \Twig\Extension\DebugExtension());
        require_once __DIR__.'/container-twigextentions.php';
        return $twig;
    },
    DeezerApiInterface::class => function (ContainerInterface $container): DeezerApiInterface {
        $deezerapi = new DeezerApi($container->get(LoggerInterface::class));
        return $deezerapi;
    },
    StaticPlaylists::class => function (ContainerInterface $container): StaticPlaylists {
        $playlists = new StaticPlaylists();
        return $playlists;
    },
    Authentication::class => function (ContainerInterface $container): Authentication {
        $auth = new Authentication($container->get(LoggerInterface::class));
        return $auth;
    },
    Recaptcha::class => function (ContainerInterface $container): Recaptcha {
        $recaptcha = new Recaptcha($container->get(LoggerInterface::class));
        return $recaptcha;
    }
    
];
