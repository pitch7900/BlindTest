<?php
use Slim\Routing\RouteCollectorProxy;
use App\Controllers\DeezerController;
use App\Controllers\HomeController;

$app->group('/', function (RouteCollectorProxy $group) {
        $group->get('', HomeController::class)
                ->setName('home');
        $group->get('/spinner.html', HomeController::class. 'getWaitingIcons')
                ->setName('getWaitingIcons');
});


$app->group('/deezer', function (RouteCollectorProxy $group) {
        $group->post('/search.json', DeezerController::class. 'postSearch')
                ->setName('deezer.search');
        $group->post('/searchlist.json', DeezerController::class. 'postSearchList')
                ->setName('deezer.searchlist');
        $group->get('/searchlist.json', DeezerController::class. 'getSearchList')
                ->setName('deezer.searchlist');
        $group->get('/playlist/{playlistid}/items.html', DeezerController::class. 'getPlaylistItems')
                ->setName('deezer.getplaylistitems');
        $group->get('/playlist/{playlistid}/cover.html', DeezerController::class. 'getPlaylistCover')
                ->setName('deezer.getplaylist');
        $group->get('/playlist/{playlistid}/info.json', DeezerController::class. 'getPlaylistInfo')
                ->setName('deezer.playlist.informations');
       
});


$app->group('/blindtest', function (RouteCollectorProxy $group) {
        $group->get('/playlists.json', BlindTestController::class. 'getBlindtestPlaylists')
                ->setName('blindtest.playlist');
        $group->get('/play/{playlistid}.html', BlindTestController::class. 'getBlindtestPlay')
                ->setName('blindtest.play');
        $group->get('/play/{trackid}.mp3', BlindTestController::class. 'getBlindtestPlayMP3')
                ->setName('blindtest.playmp3');
});