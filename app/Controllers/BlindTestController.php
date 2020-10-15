<?php

declare(strict_types=1);

namespace App\Controllers;

use App\MusicSources\Deezer\DeezerApiInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use App\Database\Game;
use App\Database\Games;
use App\Database\Album;
use App\Database\Artist;
use App\Database\Track;
use App\Database\Playlist;

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


    public function __construct(Twig $twig, LoggerInterface $logger, DeezerApiInterface $deezer)
    {
        parent::__construct($twig);
        $this->logger = $logger;
        $this->deezer = $deezer;
    }


    /**
     * Return the page for playing with a given playlits ID
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
    public function getNewPlay(Request $request, Response $response, $args)
    {
        $playlistid = intval($args['playlistid']);

        $tracks = $this->deezer->getPlaylistItems($playlistid);
        shuffle($tracks);


        $games = Games::create(['games_playlist' => $playlistid]);
        $order = 0;
        $gamesid = $games->id;
        foreach ($tracks as $track) {
            $this->logger->debug("BlindTestController::getNewPlay inserting trackid :" . $track['playlisttracks_track']);
            Game::create([
                'game_track' => $track['playlisttracks_track'],
                'game_order' => $order,
                'game_gamesid' => $gamesid
            ]);
            $order++;
        }

        return $response->withHeader('Location', '/blindtest/game/' . $games->id . '/game.html')->withStatus(302);
    }


    /**
     * Return the page for playing with a given playlits ID
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
    public function getGameHTML(Request $request, Response $response, $args)
    {
        $gamesid = $args['gamesid'];

        $playlistid = Games::find($gamesid)->games_playlist;
        $arguments['playlistname'] = Playlist::find($playlistid)->playlist_title;
        
        $arguments['playlistid'] = $playlistid;
        $arguments['gamesid'] = $gamesid;
        $this->logger->debug("BlindTestController::getGameHTML ".print_r($arguments,true));
        return $this->render($response, 'play.twig', $arguments);
    }

    /**
     * Return Game Json for a give GameID
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
    public function getGameJson(Request $request, Response $response, $args)
    {
        $gamesid = $args['gamesid'];
        $game = Game::where('game_gamesid', $gamesid)
            ->join('track', 'game.game_track', '=', 'track.id')
            ->whereNotNull('track_preview')
            ->select('game_track')
            ->get();

        $arguments['tracks'] = $game->toArray();
        $playlistid = Games::find($gamesid)->games_playlist;
        $arguments['playlistname'] = Playlist::find($playlistid)->name;
        $arguments['playlistid'] = $playlistid;
        $arguments['gamesid'] = $gamesid;
        return $this->withJson($response, $arguments);
    }

    /**
     * removeAccents
     * Only remove accents from the passed string.
     * @param  mixed $string
     * @param  mixed $tolower
     * @return string
     */
    private static function removeAccents($string, $tolower = true)
    {
        $unwanted_array = array(
            'Š' => 'S', 'š' => 's', 'Ž' => 'Z', 'ž' => 'z', 'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'A', 'Ç' => 'C', 'È' => 'E', 'É' => 'E',
            'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O', 'Ù' => 'U',
            'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ý' => 'Y', 'Þ' => 'B', 'ß' => 'Ss', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'a', 'ç' => 'c',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ð' => 'o', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o',
            'ö' => 'o', 'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ý' => 'y', 'þ' => 'b', 'ÿ' => 'y', 'Ğ' => 'G', 'İ' => 'I', 'Ş' => 'S', 'ğ' => 'g', 'ı' => 'i', 'ş' => 's', 'ü' => 'u',
            'ă' => 'a', 'Ă' => 'A', 'ș' => 's', 'Ș' => 'S', 'ț' => 't', 'Ț' => 'T', 'ć' => 'c', '-' => '', '\/' => ''
        );
        $newstring = strtr($string, $unwanted_array);


        if ($tolower) {
            $newstring = strtolower($newstring);
        }

        return $newstring;
    }
    /**
     * Check and compare the answer and the guess
     * Exact match or levenshtein distance <= 2  are accepted
     * @param string $tofindinit
     * @param string $guessinit
     * 
     * @return boolean
     */
    private function compareAnswers(string $tofindinit, string $guessinit)
    {
        $tofindarray = explode(" ", $this->removeAccents($tofindinit));
        $guessarray = explode(" ", $this->removeAccents($guessinit));

        foreach ($guessarray as $guess) {
            //Don't look for string below 2 chars
            if (strlen($guess) <= 1) {
                break;
            }
            //if between 2 chars and 4 we need an exact match

            if (in_array($guess, $tofindarray)) {

                return true;
            }
            if (strlen($guess) > 4) {


                foreach ($tofindarray as $tofind) {
                    if (levenshtein($guess, $tofind) <= 2) {

                        return true;
                    }
                }
            }
        }

        return false;
    }


    /**
     * Check current user answer and increment the current playlist item for current game
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
    public function postGameCheckCurrent(Request $request, Response $response, $args)
    {
        $guess=$request->getParam('guess');

        $gamesid = intval($args['gamesid']);
        $games = Games::find($gamesid);
        $currentTrackIndex = $games->games_currenttrackindex;
        $currentgame = Game::where([
            ['game_gamesid', '=', $gamesid],
            ['game_order', '=', $currentTrackIndex]
        ])
            ->first();

        $trackid = $currentgame->game_track;
 
        $this->logger->debug("BlindtestController::postGameCheckCurrent Trackid : " . $trackid);

            $checkartist=false;
            $checktitle=false;
            $track = Track::find($trackid);
        $artist = Artist::find($track->track_artist);
        $album = Album::find($track->track_album);
        $games->games_currenttrackindex = $currentTrackIndex + 1;
        $games->save();

        if ($guess!=null){
            $guess = $this->removeAccents(utf8_encode($guess));
            $this->logger->debug("BlindtestController::postGameCheckCurrent guess is : " . $guess);
            $checkartist=$this->compareAnswers($artist->artist_name,$guess);
            $checktitle=$this->compareAnswers($track->track_title,$guess);

        } else {
            $this->logger->debug("BlindtestController::postGameCheckCurrent Guess entered was NULL");
        }
        

        return $this->withJson($response, [
            'guess' => $guess,
            'title' => $track->track_title,
            'picture' => $album->album_cover,
            'artist' => $artist->artist_name,
            'checkartist' => $checkartist,
            'checktitle' => $checktitle
        ]);
    }


    /**
     * Return the current TrackID for a GameID
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
    public function getCurrentTrackJson(Request $request, Response $response, $args)
    {
        $gamesid = intval($args['gamesid']);
        $games = Games::find($gamesid);
        $currentTrackIndex = $games->games_currenttrackindex;
        $currentgame = Game::where([
            ['game_gamesid', '=', $gamesid],
            ['game_order', '=', $currentTrackIndex]
        ])
            ->first();
        $trackid = $currentgame->game_track;
        return $this->withJSON($response, ['trackid' => $trackid]);
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
        $this->logger->debug("BlindtestController::getStreamMP3 MP3 TrackID : " . $trackid . " should be : " . $trackdata['track_preview']);
        $resp = $this->withMP3($response, $trackdata['track_preview'], 'rb');

        return $resp;
    }
}
