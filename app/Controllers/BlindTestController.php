<?php

declare(strict_types=1);

namespace App\Controllers;

use App\MusicSources\Deezer\DeezerApiInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Http\ServerRequest as Request;
use App\Database\Game;
use App\Database\Games;
use App\Database\Album;
use App\Database\Artist;
use App\Database\Track;
use App\Database\Playlist;
use App\Database\User;
use App\Database\GamePlayers;
use App\Authentication\Auth;
use Carbon\Carbon;
use Psr\Container\ContainerInterface;

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

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->deezer = $container->get(DeezerApiInterface::class);
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
        return $this->withRedirect($response, '/blindtest/game/' . $games->id . '/game.html', 303);
        //return $response->withHeader('Location', '/blindtest/game/' . $games->id . '/game.html')->withStatus(303);
    }

    /**
     * postGameWriting
     *
     * @param  mixed $request
     * @param  mixed $response
     * @param  mixed $args
     * @return void
     */
    public function postGameWriting(Request $request, Response $response, $args)
    {
        $gamesid = $args['gamesid'];
        $user = GamePlayers::where("userid", "=", Auth::getUserId())
            ->where('gameid', '=', $gamesid)
            ->first();
        $user->writing = true;
        $user->save();
        return $response;
    }

    /**
     * postUserIsReady - Set readyness for next track status for current user to true
     *
     * @param  mixed $request
     * @param  mixed $response
     * @param  mixed $args
     * @return void
     */
    public function postUserIsReady(Request $request, Response $response, $args)
    {
        $gamesid = $args['gamesid'];
        $user = GamePlayers::where("userid", "=", Auth::getUserId())
            ->where('gameid', '=', $gamesid)
            ->first();
        $user->isready = true;
        $user->save();
        return $response;
    }

    public function getGameMessages(Request $request, Response $response, $args)
    {
    }

    public function updatePlayers(Request $request, Response $response, $args)
    {
        $gamesid = $args['gamesid'];
        if (!isset($_SESSION['GamePlayersUpdate'])) {
            $_SESSION['GamePlayersUpdate'] = microtime(true);
        }
        if (!isset($GLOBALS['GamePlayersUpdate'])) {
            $GLOBALS['GamePlayersUpdate'] = microtime(true);
        }
        //wait for a change / message need to be pushed
        while (floatval($GLOBALS['GamePlayersUpdate']) > floatval($_SESSION['GamePlayersUpdate'])) {
            sleep(500);
        }
        $payload = GamePlayers::getPlayers($gamesid);
        $payload['userid'] = Auth::getUserId();
        return $this->withJSON($response, $payload);
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


        $GamePlayer = GamePlayers::updateOrCreate([
            'gameid' => $gamesid,
            'userid' => Auth::getUserId()
        ]);
        $GamePlayer->writing = false;
        $GamePlayer->isready = true;
        $GamePlayer->answered = false;
        $GamePlayer->save();
        $arguments['userpoints'] = User::getUserTotalPoints(Auth::getUserId());
        $playlistid = Games::find($gamesid)->games_playlist;
        $arguments['playlistname'] = Playlist::find($playlistid)->playlist_title;

        $arguments['playlistid'] = $playlistid;
        $arguments['gamesid'] = $gamesid;
        $arguments['highscores'] = $this->getPlaylistHighScore($playlistid);
        $arguments['playlist_picture'] = Playlist::find($playlistid)->playlist_picture;
        $arguments['playlist_link'] = Playlist::find($playlistid)->playlist_link;
        $arguments['players'] = GamePlayers::getPlayers($gamesid);
        // $this->logger->debug("BlindTestController::getGameHTML " . print_r($arguments, true));
        $currentuserid = Auth::getUserId();

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
     * getPlaylistHighScore - return the HighScore for this playlist
     *
     * @param  mixed $playlistid
     * @return array
     */
    private function getPlaylistHighScore(int $playlistid): array
    {
        $this->logger->debug("BlindTestController::getPlaylistHighScore() Should search for highscore for playlist $playlistid");

        $highscores = [
            'userid' => null,
            'score' => 0,
            'nickname' => null
        ];
        foreach (Games::getGamesIdFromPlaylist($playlistid) as $game) {
            $gameid = $game['id'];
            $scores = Game::getHighScore($gameid);

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

    public function postSkipSong(Request $request, Response $response, $args)
    {
        $trackid = $request->getParam('trackid');
        $gamesid = intval($args['gamesid']);
        $currentgame = Game::where([
            ['game_gamesid', '=', $gamesid],
            ['game_track', '=', $trackid]
        ])->first();
        $playlistid = Games::find($gamesid)->games_playlist;
        $currentgame->userid = Auth::getUserId();
        $currentgame->points = 0;
        $currentgame->save();
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
        $check = false;
        $pointswon = 0;



        $GamePlayer = GamePlayers::where('userid', '=', Auth::getUserId())
            ->where('gameid', '=', $gamesid)
            ->first();
        $GamePlayer->answered = true;
        $GamePlayer->isready = true;
        $GamePlayer->save();
        $currentgame = Game::where([
            ['game_gamesid', '=', $gamesid],
            ['game_track', '=', $trackid]
        ])->first();

        $playlistid = Games::find($gamesid)->games_playlist;
        $this->logger->debug("BlindTestController::postGameCheckCurrent PlaylistID : " . $playlistid);
        //$trackid = $currentgame->game_track;

        //$this->logger->debug("BlindTestController::postGameCheckCurrent Trackid : " . $trackid);


        $track = Track::find($trackid);
        $artist = Artist::find($track->track_artist);
        $album = Album::find($track->track_album);

        if (!is_null($guess)) {
            if ($guess == $trackid) {
                $pointswon++;
                $check = true;
            }
        } else {
            $this->logger->debug("BlindTestController::postGameCheckCurrent Guess entered was NULL");
        }




        // $score = $this->getCurrentUserScore($gamesid);
        //Another user has already answered
        if ($currentgame->points != null) {
            //But less points than the current user, update the points attribution to the current user.
            if ($currentgame->points < $pointswon) {
                $currentgame->userid = Auth::getUserId();
                $currentgame->points = $pointswon;
                $currentgame->save();
            }
        } else {
            //First to answer, write your score to the DB
            $currentgame->userid = Auth::getUserId();
            $currentgame->points = $pointswon;
            $currentgame->save();
        }

        $highscore = $this->getPlaylistHighScore($playlistid);
        return $this->withJson($response, [
            'guess' => $guess,
            'trackid' => $trackid,
            'check' => $check,
            'points' => intval($pointswon),
            'highscore' => $highscore,
            'totalscore' => User::getUserTotalPoints(Auth::getUserId()),
            'answer' => $artist->artist_name . " - " . $track->track_title,
            'picture' => $album->album_cover,
            'track_link' => $track->track_link           
        ]);
    }

    /**
     * getCurrentUserScore - Return current score for current game
     *
     * @param  mixed $gamesid
     * @return int
     */
    private function getCurrentUserScore($gamesid): int
    {
        return Game::getUserScore($gamesid, Auth::getUserId());
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
        //reset status for this track
        GamePlayers::resetStatus($gamesid);
        // GamePlayers::isReadyStatus($gamesid,false);
        $playlistid = Games::find($gamesid)->games_playlist;
        $currentTrackIndex = Game::getCurrentTrackIndex($gamesid);
        $numberoftracks = count(Game::where([
            ['game_gamesid', '=', $gamesid]
        ])->get());
        $this->logger->debug("BlindTestController::getCurrentTrackJson() Game : " . $gamesid . " Number of track is : $numberoftracks");
        $this->logger->debug("BlindTestController::getCurrentTrackJson() Game : " . $gamesid . " Current Track index is : $currentTrackIndex");

        //$games->games_playlist;

        //We've reached the end of the track list for this game
        if ($currentTrackIndex >= $numberoftracks) {
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
            $offset = 0;
            if (is_null($currentgame->track_playtime)) {
                $currentgame->track_playtime = Carbon::createFromTimestamp(time());
                $currentgame->save();
            } else {
                $offset = Carbon::createFromTimestamp(time())->diffInMilliseconds(Carbon::createFromFormat(Carbon::DEFAULT_TO_STRING_FORMAT, $currentgame->track_playtime));
            }
            $currentgamesuggestion = Game::getPossibleAnswers($gamesid);

            return $this->withJSON($response, [
                'trackid' => $trackid,
                'playlistid' => $playlistid,
                'score' => $this->getCurrentUserScore($gamesid),
                'highscore' => $this->getPlaylistHighScore($playlistid),
                'offset' => $offset,
                'suggestions' => $currentgamesuggestion

            ]);
        }
    }

    public function getGameSuggestions(Request $request, Response $response, $args)
    {
        $gamesid = intval($args['gamesid']);
        $suggestions = Game::getPossibleAnswers($gamesid);
        $arguments['suggestions'] = array();
        foreach ($suggestions as $suggestion) {
            $track = Track::find($suggestion);
            $artist = Artist::find($track->track_artist);
            $album = Album::find($track->track_album);
            array_push($arguments['suggestions'], [
                'id' => $suggestion,
                'artist' => $artist->artist_name,
                'album' => $album->album_title,
                'cover' => $album->album_cover,
                'song' => $track->track_title
            ]);
        }


        return $this->render($response, 'blindtest/gameSuggestions.twig', $arguments);
    }


    /**
     * Return an mp3 stream
     * @param Request $request
     * @param Response $response
     * @param array $args
     */
    public function getGameStreamMP3(Request $request, Response $response, $args)
    {
        $trackid = $args['trackid'];
        $gamesid = $args['gamesid'];
        $trackdata = $this->deezer->getTrackInformations($trackid);
        $this->logger->debug("BlindtestController::getStreamMP3 MP3 TrackID : " . $trackid . " should be : " . $trackdata['track_preview']);
        $resp = $this->withMP3($response, $trackdata['track_preview'], 'rb');

        return $resp;
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
