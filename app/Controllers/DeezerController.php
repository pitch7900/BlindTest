<?php

declare(strict_types=1);

namespace App\Controllers;

use App\MusicSources\Deezer\DeezerApiInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Psr\Container\ContainerInterface;
/**
 * Description of DeezerController
 *
 * @author pierre
 */
class DeezerController extends AbstractTwigController
{

    private $deezer;

    public function __construct(ContainerInterface $container) {
        parent::__construct($container);
        $this->deezer = $container->get(DeezerApiInterface::class);  
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
     * postPlaylistAdd
     *
     * @param  mixed $request
     * @param  mixed $response
     * @param  mixed $args
     * @return void
     */
    public function postPlaylistAdd(Request $request, Response $response, $args) {
        $url = $request->getParam('url');
        $this->logger->debug("DeezerController::postPlaylistAdd Should add playlist from URL :  ".$url);
        $path = explode('/',parse_url($url,PHP_URL_PATH));
        $host = parse_url($url,PHP_URL_HOST);
        $this->logger->debug("DeezerController::postPlaylistAdd ".var_export($path,true));
        $this->logger->debug("DeezerController::postPlaylistAdd ".parse_url($url,PHP_URL_PATH));
        if (strcmp($host,"www.deezer.com")==0 && strcmp($path[2],"playlist")==0){
            $this->logger->debug("DeezerController::postPlaylistAdd Should add playlist ID :  ".intval($path[3]));
            $this->deezer->DBaddPlaylist(intval($path[3]));
            
            return $this->withJSON($response,['playlist'=>$this->deezer->getPlaylistItems(intval($path[3]))]);
        } else {
            return $this->withJSON($response,['playlist'=>false]);
        }
        
    }
    /**
     * postPlaylistUpdateTracks
     *
     * @param  mixed $request
     * @param  mixed $response
     * @param  mixed $args
     * @return void
     */
    public function postPlaylistUpdateTracks(Request $request, Response $response, $args) {
        $playlistid = intval($args['playlistid']);
        $this->deezer->EmptyPlaylist($playlistid);
        $tracks=$this->deezer->getPlaylistItems($playlistid,true);
        return $this->withJSON($response,['tracks'=>count($tracks)]);
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
