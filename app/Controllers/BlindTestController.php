<?php

declare(strict_types=1);

namespace App\Controllers;

use App\MusicSources\Deezer\DeezerApiInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;


/**
 * Description of BlindTestController
 *
 * @author pierre
 */
class BlindTestController extends AbstractTwigController
{

   private $deezer;


    public function __construct(Twig $twig, DeezerApiInterface $deezer) {
        parent::__construct($twig);
        $this->deezer = $deezer;  
    }



    /**
     * Return a json array with all blindtest playlist ID in Deezer
     * @param Request $request
     * @param Response $response
     * @return type
     */
    public function getBlindtestPlaylists(Request $request, Response $response)
    {
        return $response->withJson($this->deezer->getBlindtestPlaylists());
    }

    /**
     * Return the page for playing with a given playlits ID
     */
    public function getBlindtestPlay(Request $request, Response $response, $args)
    {
        $playlistid = $args['playlistid'];

        $arguments['tracks'] = $this->deezer->getPlaylistItems($playlistid);

        $arguments['playlistname'] = $this->deezer->getPlaylistName($playlistid);
        $arguments['playlistid']=$playlistid;

        return $this->render($response, 'play.twig', $arguments);
    }
 
   
    /**
     * Return a mp3 stream
     * @param Request $request
     * @param Response $response
     * @param array $args
     */
    public function getBlindtestPlayMP3(Request $request, Response $response, $args)
    {
        $trackid = $args['trackid'];
        $trackdata = $this->deezer->getTrackInformations($trackid);
        $fh = fopen($trackdata['preview'], 'rb');
        $stream = new Stream($fh);
        return $response
            ->withBody($stream)
            ->withHeader('Content-Type', 'audio/mp3');
    }

}
