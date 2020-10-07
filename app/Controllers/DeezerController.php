<?php

declare(strict_types=1);

namespace App\Controllers;

use App\MusicSources\Deezer\DeezerApiInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;


/**
 * Description of DeezerController
 *
 * @author pierre
 */
class DeezerController extends AbstractTwigController
{

   private $deezer;


    public function __construct(Twig $twig, DeezerApiInterface $deezer) {
        parent::__construct($twig);
        $this->deezer = $deezer;  
    }


 
    /**
     * Return a playlist information in JSON format
     * @param Request $request
     * @param Response $response
     */
    public function getPlaylistInfo(Request $request, Response $response, $args)
    {
        $playlistid = $args['playlistid'];
        return $response->withJson($this->deezer->GetPlaylistInfo($playlistid));
    }

 
    /**
     * Redirect to the songs.twig page. Display all songs for a given PlaylistID
     * @param Request $request
     * @param Response $response
     * @param type $args
     * @return type
     */
    public function getPlaylistItems(Request $request, Response $response, $args)
    {
        $playlistid = $args['playlistid'];
        $arguments['playlist'] = $this->deezer->getPlaylistItems($playlistid);
        $arguments['playlistname'] = $this->deezer->getPlaylistName($playlistid);

        $arguments['destination'] = $_SESSION['destinations'];


        return $this->render($response, 'songs.twig', $arguments);
    }

    

    /**
     * Return the html for a playlist cover
     */
    public function getPlaylistCover(Request $request, Response $response, $args)
    {
        $playlistid = $args['playlistid'];

        $arguments['playlistname'] = $this->deezer->GetPlaylistInfo($playlistid)['name'];
        $arguments['nb_tracks'] = $this->deezer->GetPlaylistInfo($playlistid)['nb_tracks'];
        $arguments['picture'] = $this->deezer->GetPlaylistInfo($playlistid)['picture'];
        $arguments['id'] = $this->deezer->GetPlaylistInfo($playlistid)['id'];
        return $this->render($response, 'elements/playlist.twig', $arguments);
    }
}
