<?php

namespace App\Controllers;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use App\MusicSources\DeezerApi as DeezerApi;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

/**
 * Description of DeezerController
 *
 * @author pierre
 */
class DeezerController extends Controller {

    private $log;

    public function __construct($container) {

        parent::__construct($container);
        $this->log = new Logger('DeezerController.php');
        $this->log->pushHandler(new StreamHandler(__DIR__.'/../../logs/debug.log', Logger::DEBUG));
    }



    /**
     * Return a json array with all blindtest playlist ID in Deezer
     * @param Request $request
     * @param Response $response
     * @return type
     */
    public function getBlindtestPLaylists(Request $request, Response $response) {
            return $response->withJson(unserialize($_SESSION['deezerapi'])->getBlindtestPLaylists());
        
    }

    
    /**
     * Search for a full list of track in Deezer.
     * Return a Json with track informations found
     * @param Request $request
     * @param Response $response
     * @return type
     */
    public function postSearchList(Request $request, Response $response) {

        $tracklist = json_decode($request->getParsedBody()['tracklist']);
        if (!isset($_SESSION['deezerapi'])) {
            $this->log->debug("(postSearchList) Creating a new Deezer API class instance");
            $_SESSION['deezerapi'] = serialize(new \App\MusicSources\DeezerApi());
        }

        return $response->withJson(unserialize($_SESSION['deezerapi'])->SearchList($tracklist));
    }

    /**
     * Return the List of track to find on Deezer
     * This list is created by the function postSearchList
     * @param Request $request
     * @param Response $response
     * @return type
     */
    public function getSearchList(Request $request, Response $response) {
        if (!isset($_SESSION['deezerapi'])) {
            return $this->response
                            ->withStatus(412)
                            ->withHeader('Error', 'Session not initialized');
        } else {
            return $response->withJson(unserialize($_SESSION['deezersearchlist']));
        }
    }

    /**
     * Return a playlist information in JSON format
     * @param Request $request
     * @param Response $response
     */
    public function getPlaylistInfo(Request $request, Response $response,$args) {
        $playlistid = $args['playlistid'];
        return $response->withJson(unserialize($_SESSION['deezerapi'])->GetPlaylistInfo($playlistid));
    }
    
  
    /**
     * Redirect to the songs.twig page. Display all songs for a given PlaylistID
     * @param Request $request
     * @param Response $response
     * @param type $args
     * @return type
     */
    public function getPlaylistItems(Request $request, Response $response, $args) {
        $playlistid = $args['playlistid'];
        $arguments['playlist'] = unserialize($_SESSION["deezerapi"])->getPlaylistItems($playlistid);
        $arguments['playlistname'] = unserialize($_SESSION["deezerapi"])->getPlaylistName($playlistid);
        $arguments['destination'] = $_SESSION['destinations'];

        switch ($_SESSION['destinations']) {
            case "deezer":
                $arguments['destinationauthenticated'] = true;
                $arguments['destinationplaylists'] = unserialize($_SESSION['deezerapi'])->getUserPlaylists();
                break;
            case "spotify":
                $arguments['destinationauthenticated'] = true;
                $arguments['destinationplaylists'] = unserialize($_SESSION['spotifyapi'])->getUserPlaylists();
                break;
            default:
                $arguments['destinationauthenticated'] = false;
                break;
        }

        return $this->view->render($response, 'songs.twig', $arguments);
        
    }
}
