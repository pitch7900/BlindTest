<?php

declare(strict_types=1);

use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use App\Controllers\DeezerController;
use App\Controllers\HomeController;
use App\Controllers\BlindTestController;
use App\Controllers\AuthController;
use App\Middleware\GuestMiddleware;
use App\Middleware\AuthMiddleware;
use App\Middleware\RedirectIfNotAuthenticatedMiddleware;

return function (App $app) {

        $app->group('/auth', function (RouteCollectorProxy $group) {
                $group->get('/signin', AuthController::class . ':signin')
                        ->setName('auth.signin');
                $group->get('/signout', AuthController::class . ':signout')
                        ->setName('auth.signout');
                $group->get('/login', AuthController::class . ':login')
                        ->setName('auth.login');
                $group->get('/forgotpassword', AuthController::class . ':forgotpassword')
                        ->setName('auth.forgotpassword');
                $group->post('/checklogin', AuthController::class . ':checklogin')
                        ->setName('auth.checklogin');
        })->add(new GuestMiddleware($app));

        $app->get('/', HomeController::class . ':home')
                ->setName('home')
                ->add(new AuthMiddleware($app));

        $app->get('/spinner.html', HomeController::class . ':getWaitingIcons')
                ->setName('getWaitingIcons');



        $app->group('/deezer', function (RouteCollectorProxy $group) {
                $group->post('/search.json', DeezerController::class . ':postSearch')
                        ->setName('deezer.search');
                $group->post('/searchlist.json', DeezerController::class . ':postSearchList')
                        ->setName('deezer.searchlist');
                $group->get('/playlist/{playlistid}/cover.html', DeezerController::class . ':getPlaylistCover')
                        ->setName('deezer.getplaylist');
                $group->get('/playlist/{playlistid}/info.json', DeezerController::class . ':getPlaylistInfo')
                        ->setName('deezer.playlist.informations');
        });



        $app->group('/blindtest', function (RouteCollectorProxy $group) {
                $group->get('/game/{gamesid}/game.html', BlindTestController::class . ':getGameHTML')
                        ->setName('blindtest.play');
                $group->post('/game/{gamesid}/writing', BlindTestController::class . ':postGameWriting')
                        ->setName('blindtest.writing');
                $group->get('/game/{gamesid}.json', BlindTestController::class . ':getGameJson')
                        ->setName('blindtest.playjsondata');
                $group->post('/game/{gamesid}/check.json', BlindTestController::class . ':postGameCheckCurrent')
                        ->setName('blindtest.playjsondata');

                $group->get('/game/{gamesid}/currenttrack.json', BlindTestController::class . ':getCurrentTrackJson')
                        ->setName('blindtest.getcurrenttrackjson');
                $group->get('/play/{playlistid}.html', BlindTestController::class . ':getNewPlay')
                        ->setName('blindtest.newplay');
                $group->get('/play/{trackid}.mp3', BlindTestController::class . ':getStreamMP3')
                        ->setName('blindtest.streammp3');
        });
};
