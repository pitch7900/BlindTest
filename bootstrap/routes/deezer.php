<?php

declare(strict_types=1);

use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use App\Controllers\DeezerController;
use App\Middleware\AuthMiddleware; //Used for private pages that requiere authentication

return function (App $app) {

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




};
