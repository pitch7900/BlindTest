<?php

declare(strict_types=1);

use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use App\Controllers\DeezerController;
use App\Controllers\HomeController;
use App\Controllers\BlindTestController;
use App\Controllers\ErrorsController;
use App\Controllers\AuthController;
use App\Middleware\AuthMiddleware; //Used for private pages that requiere authentication

return function (App $app) {

        //Public pages for authentication
        $app->group('/auth', function (RouteCollectorProxy $group) {
                $group->get('/signin', AuthController::class . ':signin')
                        ->setName('auth.signin');
                $group->get('/signinconfirmation.html', AuthController::class . ':signinconfirmation')
                ->setName('auth.signinconfirmation');
                $group->get('/login', AuthController::class . ':login')
                        ->setName('auth.login');
                $group->get('/forgotpassword', AuthController::class . ':forgotpassword')
                        ->setName('auth.forgotpassword');
                $group->get('/checkmail/{uuid}', AuthController::class . ':checkmail')
                        ->setName('auth.checkmail');
                $group->get('/validate/{uuid}', AuthController::class . ':validateemail')
                        ->setName('auth.validatemail');
                $group->get('/resetpassword/{uuid}', AuthController::class . ':resetpassword')
                        ->setName('auth.resetpassword');
                $group->post('/resetpassword/{uuid}', AuthController::class . ':postresetpassword')
                        ->setName('auth.resetpassword.post');
                $group->post('/login', AuthController::class . ':postlogin')
                        ->setName('auth.login');
                $group->post('/forgotpassword', AuthController::class . ':postforgotpassword')
                        ->setName('auth.forgotpassword.post');
                $group->post('/signin', AuthController::class . ':postsignin')
                        ->setName('auth.signin.post');
        });

        /**
         * Starting here only private pages that requieres authentication et Capatcha
         */

        $app->group('/user', function (RouteCollectorProxy $group) {
                $group->get('/signout', AuthController::class . ':signout')
                        ->setName('auth.signout');
                $group->get('/changepassword', AuthController::class . ':changepassword')
                        ->setName('auth.changepassword');
                $group->get('/preferences', AuthController::class . ':preferences')
                        ->setName('auth.preferences');
                $group->post('/changepassword', AuthController::class . ':postchangepassword')
                        ->setName('auth.changepassword.post');
                $group->post('/preferences', AuthController::class . ':postpreferences')
                        ->setName('auth.preferences.post');
        })->add(new AuthMiddleware($app));

        $app->get('/spinner.html', HomeController::class . ':getWaitingIcons')
                ->setName('getWaitingIcons')
                ->add(new AuthMiddleware($app));

        $app->group('/deezer', function (RouteCollectorProxy $group) {
                $group->post('/search.json', DeezerController::class . ':postSearch')
                        ->setName('deezer.search');
                $group->post('/searchlist.json', DeezerController::class . ':postSearchList')
                        ->setName('deezer.searchlist');
                $group->post('/playlist/{playlistid}/updatetracks', DeezerController::class . ':postPlaylistUpdateTracks')
                        ->setName('deezer.playlist.updatetracks');
                $group->post('/playlist/add', DeezerController::class . ':postPlaylistAdd')
                        ->setName('deezer.playlist.add');
                $group->get('/playlist/{playlistid}/cover.html', DeezerController::class . ':getPlaylistCover')
                        ->setName('deezer.getplaylist');
                $group->get('/playlist/{playlistid}/info.json', DeezerController::class . ':getPlaylistInfo')
                        ->setName('deezer.playlist.informations');
        })->add(new AuthMiddleware($app));



        $app->group('/blindtest', function (RouteCollectorProxy $group) {
                $group->get('/info/playlist/{playlistid}.json', BlindTestController::class . ':getPlaylistInformations')
                        ->setName('blindtest.info.playlist');
                $group->get('/game/{gamesid}/game.html', BlindTestController::class . ':getGameHTML')
                        ->setName('blindtest.play');
                $group->post('/game/{gamesid}/writing', BlindTestController::class . ':postGameWriting')
                        ->setName('blindtest.writing');
                $group->get('/game/{gamesid}/messages.json', BlindTestController::class . ':getGameMessages')
                        ->setName('blindtest.messages');
                $group->get('/game/{gamesid}.json', BlindTestController::class . ':getGameJson')
                        ->setName('blindtest.playjsondata');
                $group->post('/game/{gamesid}/check.json', BlindTestController::class . ':postGameCheckCurrent')
                        ->setName('blindtest.playjsondata');
                $group->post('/game/{gamesid}/skipsong.json', BlindTestController::class . ':postSkipSong')
                        ->setName('blindtest.playjsondata');
                $group->get('/game/{gamesid}/currenttrack.json', BlindTestController::class . ':getCurrentTrackJson')
                        ->setName('blindtest.getcurrenttrackjson');
                $group->get('/play/{playlistid}.html', BlindTestController::class . ':getNewPlay')
                        ->setName('blindtest.newplay');
                $group->get('/play/{trackid}.mp3', BlindTestController::class . ':getStreamMP3')
                        ->setName('blindtest.streammp3');
        })->add(new AuthMiddleware($app));



        $app->group('/errors', function (RouteCollectorProxy $group) {
                $group->post('/player', ErrorsController::class . ':postplayer')
                        ->setName('errors.player.post');
        })->add(new AuthMiddleware($app));



        $app->get('/', HomeController::class . ':home')
                ->setName('home')
                ->add(new AuthMiddleware($app));
};
