<?php

declare(strict_types=1);

use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use App\Controllers\BlindTestController;
use App\Middleware\AuthMiddleware; //Used for private pages that requiere authentication

return function (App $app) {

    $app->group('/blindtest', function (RouteCollectorProxy $group) {
        $group->get('/info/playlist/{playlistid}.json', BlindTestController::class . ':getPlaylistInformations')
            ->setName('blindtest.info.playlist');
        $group->get('/game/{gamesid}/game.html', BlindTestController::class . ':getGameHTML')
            ->setName('blindtest.play');
        $group->post('/game/{gamesid}/writing', BlindTestController::class . ':postGameWriting')
            ->setName('blindtest.writing');
        $group->post('/game/{gamesid}/ready', BlindTestController::class . ':postUserIsReady')
            ->setName('blindtest.user.isready');
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
        $group->get('/game/{gamesid}/suggestions', BlindTestController::class . ':getGameSuggestions')
            ->setName('blindtest.getGameSuggestions');
        $group->get('/game/{gamesid}/updateplayers.json', BlindTestController::class . ':updatePlayers')
            ->setName('blindtest.updateplayers');
        $group->get('/game/{gamesid}/{trackid}.mp3', BlindTestController::class . ':getGameStreamMP3')
            ->setName('blindtest.stream.game.mp3');
        $group->get('/play/{playlistid}.html', BlindTestController::class . ':getNewPlay')
            ->setName('blindtest.newplay');
        $group->get('/play/{trackid}.mp3', BlindTestController::class . ':getStreamMP3')
            ->setName('blindtest.streammp3');
    })->add(new AuthMiddleware($app));
};
