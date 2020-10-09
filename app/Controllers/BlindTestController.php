<?php

declare(strict_types=1);

namespace App\Controllers;

use App\MusicSources\Deezer\DeezerApiInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use App\Games\Game;
use App\Games\Games;

/**
 * Description of BlindTestController
 *
 * @author pierre
 */
class BlindTestController extends AbstractTwigController
{
    /**
     * @var DeezerApiInterface $deezer
     */
    private $deezer;

    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    /**
     * @var Games $games
     */
    private $games;

    public function __construct(Twig $twig, LoggerInterface $logger, DeezerApiInterface $deezer, Games $games) {
        parent::__construct($twig);
        $this->logger = $logger;
        $this->deezer = $deezer;  
        $this->games = $games;
    }


    /**
     * Return the page for playing with a given playlits ID
     */
    public function getNewPlay(Request $request, Response $response, $args)
    {
        $playlistid = $args['playlistid'];

        $arguments['tracks'] = $this->deezer->getPlaylistItems($playlistid);

        $arguments['playlistname'] = $this->deezer->getPlaylistName($playlistid);
        $arguments['playlistid']=$playlistid;


        $game=New Game($this->logger,$args['playlistid'],
            $this->deezer->getPlaylistName($playlistid),
            $this->deezer->getPlaylistItems($playlistid),
            $this->deezer->getPlaylistPicture($playlistid));
        $this->games->add($game);
        return $response->withHeader('Location', '/blindtest/game/'.$game->getGameID().'.html')->withStatus(302);
    }


    /**
     * Return the page for playing with a given playlits ID
     */
    public function getGame(Request $request, Response $response, $args)
    {
        $gameid = $args['gameid'];
        $game=$this->games->get($gameid);

        $arguments['tracks'] = $game->getTrackList();

        $arguments['playlistname'] = $game->getName();
        $arguments['playlistid']=$game->getID();

        return $this->render($response, 'play.twig', $arguments);
    }
 
   
    /**
     * Return an mp3 stream
     * @param Request $request
     * @param Response $response
     * @param array $args
     */
    public function getStreamMP3(Request $request, Response $response, $args)
    {
        $trackid = $args['trackid'];
        $trackdata = $this->deezer->getTrackInformations($trackid);
        //$stream=(new  StreamFactory())->createStreamFromFile($trackdata['preview'],'rb');
        $this->logger->debug("BlindtestController::getStreamMP3 MP3 TrackID : ".$trackid." should be : ".$trackdata['preview']);
        return $this->withMP3($response,$trackdata['preview'],'rb');

    }

}
