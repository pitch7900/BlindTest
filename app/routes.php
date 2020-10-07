<?php

declare(strict_types=1);

use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use App\Controllers\DeezerController;
use App\Controllers\HomeController;
use App\Controllers\BlindTestController;

return function (App $app) {
  
        $app->get('/', HomeController::class. ':home')
        ->setName('home');

        $app->get('/spinner.html', HomeController::class .':getWaitingIcons') 
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
                $group->get('/playlists.json', BlindTestController::class . ':getPlaylists')
                        ->setName('blindtest.playlist');
                $group->get('/play/{playlistid}.html', BlindTestController::class . ':getPlay')
                        ->setName('blindtest.play');
                $group->get('/play/{trackid}.mp3', BlindTestController::class . ':getStreamMP3')
                        ->setName('blindtest.streammp3');
        });
};
