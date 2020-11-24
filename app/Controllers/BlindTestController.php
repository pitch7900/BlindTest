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
use App\Database\User;
use App\Authentication\Auth;
use Carbon\Carbon;

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

    private $auth;

    public function __construct(Twig $twig, LoggerInterface $logger, DeezerApiInterface $deezer, Auth $auth)
    {
        parent::__construct($twig);
        $this->logger = $logger;
        $this->deezer = $deezer;
        $this->auth = $auth;
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

        return $response->withHeader('Location', '/blindtest/game/' . $games->id . '/game.html')->withStatus(303);
    }

    public function postGameWriting(Request $request, Response $response, $args)
    {
    }

    public function getGameMessages(Request $request, Response $response, $args)
    {
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
        $arguments['highscores'] = $this->getPlaylistHighScore($playlistid);
        $arguments['playlist_picture'] = Playlist::find($playlistid)->playlist_picture;
        $arguments['playlist_link'] = Playlist::find($playlistid)->playlist_link;
        // $this->logger->debug("BlindTestController::getGameHTML " . print_r($arguments, true));
        $currentuserid = $this->auth->getUserId();

        $this->logger->debug("BlindTestController::getGameHTML User $currentuserid joined game $gamesid");
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
            'Š' => 'S', 'š' => 's', 'Ž' => 'Z', 'ž' => 'z',
            'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'A', 'Ă' => 'A',
            'Ç' => 'C',
            'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E',
            'Ğ' => 'G',
            'İ' => 'I', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
            'Ñ' => 'N',
            'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O',
            'Ş' => 'S', 'Ș' => 'S',
            'Ù' => 'U',
            'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U',
            'Ý' => 'Y', 'Þ' => 'B', 'ß' => 'Ss',
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'a', 'ă' => 'a',
            'ç' => 'c',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
            'ğ' => 'g',
            'ı' => 'i', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
            'ð' => 'o', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ø' => 'o',
            'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u',
            'ý' => 'y', 'þ' => 'b', 'ÿ' => 'y',
            'ş' => 's', 'ș' => 's',
            'ț' => 't', 'Ț' => 'T', 'ć' => 'c',
            '-' => ' ', '/' => '', '\\' => '', '.' => '', '!' => '', '?' => ''
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
        $this->logger->debug("BlindTestController::compareAnswers() Should compare $tofindinit and $guessinit");
        $tofindarray = explode(" ", $this->removeAccents($tofindinit));
        $guessarray = explode(" ", $this->removeAccents($guessinit));

        foreach ($guessarray as $guess) {
            //Don't look for string below 2 chars, or do this if the string to find is =1
            if (strlen($guess) > 1 || strlen($tofindinit) == 1) {
                //if between 2 chars and 4 we need an exact match
                $this->logger->debug("BlindTestController::compareAnswers() Less than 4 chars, we need an exact match");
                if (in_array($guess, $tofindarray)) {

                    return true;
                }
            }
            if (strlen($guess) > 4) {
                $this->logger->debug("BlindTestController::compareAnswers() More than 5 chars, we allow a levenshtein distance of 2");

                foreach ($tofindarray as $tofind) {
                    $this->logger->debug("BlindTestController::compareAnswers()   Comparing $guess and $tofind");
                    if (levenshtein($guess, $tofind) <= 2) {
                        $this->logger->debug("BlindTestController::compareAnswers()   Comparing $guess and $tofind [Match]");
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * getPlaylistHighScore - return the HighScore for this playlist
     *
     * @param  mixed $playlistid
     * @return array
     */
    private function getPlaylistHighScore(int $playlistid): array
    {
        $this->logger->debug("BlindTestController::getPlaylistHighScore() Should search for highscore for playlist $playlistid");
        //$games = Games::where([['games_playlist', '=', $playlistid]]);

        $highscores = [
            'userid' => null,
            'score' => 0,
            'nickname' => null
        ];
        foreach (Games::getGamesIdFromPlaylist($playlistid) as $game) {
            $gameid = $game['id'];
            // $this->logger->debug("BlindTestController::getPlaylistHighScore() Games with this playlist are : " . $gameid);
            // $scores=Game::select("SELECT userid,sum(points) as score FROM blindtest.game
            //         WHERE userid is not null 
            //         AND id = $gameid
            //         GROUP BY userid;"
            //         );
            $scores = Game::getHighScore($gameid);
            // $this->logger->debug("BlindTestController::getPlaylistHighScore() Games with this playlist are : " . var_export($scores, true));

            if ($highscores['score'] <= $scores['score'] && !is_null($scores['userid'])) {
                $highscores['nickname'] = User::getNickName($scores['userid']);
                $highscores['score'] = $scores['score'];
                $highscores['userid'] = $scores['userid'];
            }
        }
        
        return $highscores;
    }

    /**
     * getPlaylistInformations : get JSON on current playlist
     *
     * @param  mixed $request
     * @param  mixed $response
     * @param  mixed $args
     * @return Response
     */
    public function getPlaylistInformations(Request $request, Response $response, $args): Response
    {
        $playlistid = intval($args['playlistid']);
        $informations = [
            'id' => $playlistid,
            'highscore' => $this->getPlaylistHighScore($playlistid),
            'name' => Playlist::find($playlistid)->playlist_title,
            'link' => Playlist::find($playlistid)->playlist_link,
            'picture' => Playlist::find($playlistid)->playlist_picture
        ];

        return $this->withJSON($response, $informations);
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
        $guess = $request->getParam('guess');
        $this->logger->debug("BlindTestController::postGameCheckCurrent Guess passed is  : " . $guess);
        $trackid = $request->getParam('trackid');
        $this->logger->debug("BlindTestController::postGameCheckCurrent TrackID passed is  : " . $trackid);
        $gamesid = intval($args['gamesid']);
        // $games = Games::find($gamesid);
        //$trackid = Game::getCurrentTrack($gamesid);//$games->games_currenttrackindex;

        $currentgame = Game::where([
            ['game_gamesid', '=', $gamesid],
            ['game_track', '=', $trackid]
        ])
            ->first();
        $playlistid = Games::find($gamesid)->games_playlist;
        $this->logger->debug("BlindTestController::postGameCheckCurrent PlaylistID : " . $playlistid);
        //$trackid = $currentgame->game_track;

        //$this->logger->debug("BlindTestController::postGameCheckCurrent Trackid : " . $trackid);

        $checkartist = false;
        $checktitle = false;
        $track = Track::find($trackid);
        $artist = Artist::find($track->track_artist);
        $album = Album::find($track->track_album);
        //$games->games_currenttrackindex = $currentTrackIndex + 1;
        // $games->save();

        if ($guess != null) {
            $guess = $this->removeAccents(utf8_encode($guess));
            $this->logger->debug("BlindTestController::postGameCheckCurrent Guess is now transformed to : " . $guess);
            $checkartist = $this->compareAnswers($artist->artist_name, $guess);
            $checktitle = $this->compareAnswers($track->track_title, $guess);
        } else {
            $this->logger->debug("BlindTestController::postGameCheckCurrent Guess entered was NULL");
        }

        $pointswon = 0;
        if ($checkartist) {
            $pointswon++;
        }
        if ($checktitle) {
            $pointswon++;
        }
        $score = $this->getCurrentUserScore($gamesid);
        //Another user has already answered
        if ($currentgame->points != null) {
            //But less points than the current user, update the poitns attribution to the current user.
            if ($currentgame->points < $pointswon) {
                $currentgame->userid = $this->auth->getUserId();
                $currentgame->points = $pointswon;
                $currentgame->save();
            }
        } else {
            //First to answer, write your score to the DB
            $currentgame->userid = $this->auth->getUserId();
            $currentgame->points = $pointswon;
            $currentgame->save();
        }

        $highscore = $this->getPlaylistHighScore($playlistid);
        return $this->withJson($response, [
            'guess' => $guess,
            'title' => $track->track_title,
            'picture' => $album->album_cover,
            'artist' => $artist->artist_name,
            'track_link' => $track->track_link,
            'checkartist' => $checkartist,
            'checktitle' => $checktitle,
            'points' => intval($pointswon),
            'score' => intval($score),
            'highscore' => $highscore
        ]);
    }

    private function getCurrentUserScore($gamesid)
    {
        return Game::where([
            ['game_gamesid', '=', $gamesid],
            ['userid', '=', $this->auth->getUserId()]
        ])->sum('points');
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
        $playlistid = Games::find($gamesid)->games_playlist;
        $currentTrackIndex = Game::getCurrentTrackIndex($gamesid);
        $numberoftracks = count(Game::where([
            ['game_gamesid', '=', $gamesid]
        ])->get());
        $this->logger->debug("BlindTestController::getCurrentTrackJson() Number of track is : $numberoftracks");
        $this->logger->debug("BlindTestController::getCurrentTrackJson() Current Track index is : $currentTrackIndex");

        //$games->games_playlist;

        //We've reached the end of the track list for this game
        if ($currentTrackIndex > $numberoftracks) {
            //return -1 as code for end of play;
            return $this->withJSON($response, [
                'trackid' => -1,
                'score' => $this->getCurrentUserScore($gamesid)
            ]);
        } else {
            $currentgame = Game::where([
                ['game_gamesid', '=', $gamesid],
                ['game_order', '=', $currentTrackIndex]
            ])
                ->first();
            $trackid = $currentgame->game_track;
            $currentgame->track_playtime = Carbon::createFromTimestamp(time());
            $currentgame->save();

            return $this->withJSON($response, [
                'trackid' => $trackid,
                'playlistid' => $playlistid,
                'score' => $this->getCurrentUserScore($gamesid),
                'highscore' => $this->getPlaylistHighScore($playlistid)
            ]);
        }
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
