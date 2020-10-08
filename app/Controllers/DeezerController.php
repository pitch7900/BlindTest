<?php

declare(strict_types=1);

namespace App\Controllers;

use App\MusicSources\Deezer\DeezerApiInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;

/**
 * Description of DeezerController
 *
 * @author pierre
 */
class DeezerController extends AbstractTwigController
{

    private $deezer;

    private $logger;

    public function __construct(Twig $twig,LoggerInterface $logger, DeezerApiInterface $deezer) {
        parent::__construct($twig);
        $this->deezer = $deezer;  
        $this->logger = $logger;
        $this->logger->debug("Construct of DeezerController called");
    }


 
    /**
     * Return a playlist information in JSON format
     * @param Request $request
     * @param Response $response
     */
    public function getPlaylistInfo(Request $request, Response $response, $args) {
        $playlistid = $args['playlistid'];
        $this->logger->debug("DeezerController::getPlaylistInfo called with playlist id : ".$playlistid);
        $payload=$this->deezer->GetPlaylistInfo($playlistid);
        $this->logger->debug("DeezerController::getPlaylistInfo should return : ".print_r($payload,true));
        return $this->withJSON($response,$payload);
    }

    /**
     * Return the html for a playlist cover
     */
    public function getPlaylistCover(Request $request, Response $response, $args) {
        $playlistid = $args['playlistid'];

        $arguments['playlistname'] = $this->deezer->GetPlaylistInfo($playlistid)['name'];
        $arguments['nb_tracks'] = $this->deezer->GetPlaylistInfo($playlistid)['nb_tracks'];
        $arguments['picture'] = $this->deezer->GetPlaylistInfo($playlistid)['picture'];
        $arguments['id'] = $this->deezer->GetPlaylistInfo($playlistid)['id'];
        return $this->render($response, 'elements/playlist.twig', $arguments);
    }

    
}
