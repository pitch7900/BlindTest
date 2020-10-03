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
        $this->get('/playlist/{playlistid}.html', 'DeezerController:getPlaylistItems')
                ->setName('deezer.getplaylistitems');
        $this->get('/playlist/{playlistid}/info.json', 'DeezerController:getPlaylistInfo')
                ->setName('deezer.playlist.informations');
        $this->get('/blindtest/playlists.json', 'DeezerController:getBlindtestPLaylists')
                ->setName('deezer.blindtest.playlist');
});
