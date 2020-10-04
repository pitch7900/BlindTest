<?php

$app->get('/', 'HomeController:home')
        ->setName('home');

$app->get('/spinner.html', 'HomeController:getWaitingIcons')
        ->setName('getWaitingIcons');



$app->group('/deezer', function () {
        $this->post('/search.json', 'DeezerController:postSearch')
                ->setName('deezer.search');
        $this->post('/searchlist.json', 'DeezerController:postSearchList')
                ->setName('deezer.searchlist');
        $this->get('/searchlist.json', 'DeezerController:getSearchList')
                ->setName('deezer.searchlist');
        $this->get('/playlist/{playlistid}/items.html', 'DeezerController:getPlaylistItems')
                ->setName('deezer.getplaylistitems');
        $this->get('/playlist/{playlistid}/cover.html', 'DeezerController:getPlaylistCover')
                ->setName('deezer.getplaylist');
        $this->get('/playlist/{playlistid}/info.json', 'DeezerController:getPlaylistInfo')
                ->setName('deezer.playlist.informations');
        $this->get('/blindtest/playlists.json', 'DeezerController:getBlindtestPlaylists')
                ->setName('deezer.blindtest.playlist');
        $this->get('/blindtest/playlists.json', 'DeezerController:getBlindtestPlay')
                ->setName('deezer.blindtest.play');
        $this->get('/blindtest/play/{trackid}.mp3', 'DeezerController:getBlindtestPlayMP3')
                ->setName('deezer.blindtest.playmp3');
});
