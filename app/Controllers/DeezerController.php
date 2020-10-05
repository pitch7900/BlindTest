<?php

namespace App\Controllers;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use App\MusicSources\DeezerApi as DeezerApi;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Slim\Http\Stream;

/**
 * Description of DeezerController
 *
 * @author pierre
 */
class DeezerController extends Controller
{

    private $log;

    public function __construct($container)
    {

        parent::__construct($container);
        $this->log = new Logger('DeezerController.php');
        $this->log->pushHandler(new StreamHandler(__DIR__ . '/../../logs/debug.log', Logger::DEBUG));
    }



    /**
     * Return a json array with all blindtest playlist ID in Deezer
     * @param Request $request
     * @param Response $response
     * @return type
     */
    public function getBlindtestPlaylists(Request $request, Response $response)
    {
        return $response->withJson(unserialize($_SESSION['deezerapi'])->getBlindtestPlaylists());
    }

    /**
     * Return the page for playing with a given playlits ID
     */
    public function getBlindtestPlay(Request $request, Response $response, $args)
    {
        $playlistid = $args['playlistid'];

        $arguments['tracks'] = (unserialize($_SESSION['deezerapi'])->getPlaylistItems($playlistid));

        $arguments['playlistname'] = unserialize($_SESSION["deezerapi"])->getPlaylistName($playlistid);
        $arguments['playlistid']=$playlistid;

        return $this->view->render($response, 'play.twig', $arguments);
    }
    /**
     * Search for a full list of track in Deezer.
     * Return a Json with track informations found
     * @param Request $request
     * @param Response $response
     * @return type
     */
    public function postSearchList(Request $request, Response $response)
    {

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
    public function getSearchList(Request $request, Response $response)
    {
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
    public function getPlaylistInfo(Request $request, Response $response, $args)
    {
        $playlistid = $args['playlistid'];
        return $response->withJson(unserialize($_SESSION['deezerapi'])->GetPlaylistInfo($playlistid));
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
        $trackdata = unserialize($_SESSION['deezerapi'])->getTrackInformations($trackid);
        $fh = fopen($trackdata['preview'], 'rb');
        $stream = new Stream($fh);
        return $response
            ->withBody($stream)
            ->withHeader('Content-Type', 'audio/mp3');
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
        $arguments['playlist'] = unserialize($_SESSION["deezerapi"])->getPlaylistItems($playlistid);
        $arguments['playlistname'] = unserialize($_SESSION["deezerapi"])->getPlaylistName($playlistid);

        $arguments['destination'] = $_SESSION['destinations'];


        return $this->view->render($response, 'songs.twig', $arguments);
    }

    /**
     * Return the html for a playlist cover
     */
    public function getPlaylistCover(Request $request, Response $response, $args)
    {
        $playlistid = $args['playlistid'];

        $arguments['playlistname'] = unserialize($_SESSION["deezerapi"])->GetPlaylistInfo($playlistid)['name'];
        $arguments['nb_tracks'] = unserialize($_SESSION["deezerapi"])->GetPlaylistInfo($playlistid)['nb_tracks'];
        $arguments['picture'] = unserialize($_SESSION["deezerapi"])->GetPlaylistInfo($playlistid)['picture'];
        $arguments['id'] = unserialize($_SESSION["deezerapi"])->GetPlaylistInfo($playlistid)['id'];
        return $this->view->render($response, 'elements/playlist.twig', $arguments);
    }
}
