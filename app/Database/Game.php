<?php

namespace App\Database;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Capsule\Manager as DB;

class Game  extends AbstractModel
{
   public $timestamps = true;
   protected $table = 'game';
   protected $primaryKey = 'id';
   protected $fillable = ['game_gamesid', 'game_track', 'game_order', 'userid', 'points', 'track_playtime'];



   /**
    * getCurrentTrack : Return the current track to play with based on gameid
    *
    * @param  mixed $gameid
    * @return int
    */
   public static function getCurrentTrack($gameid): int
   {
      $track = Game::where('game_gamesid', '=', $gameid)
         ->whereNull('userid')
         ->orderBy('game_order', 'asc')
         ->first();
      return intval($track->game_track);
   }


   public static  function getCurrentTrackIndex($gameid): int
   {
      $track = Game::where('game_gamesid', '=', $gameid)
         ->whereNull('userid')
         ->orderBy('game_order', 'asc')
         ->first();
      if (is_null($track)) {
         return Game::where('game_gamesid', '=', $gameid)->count();
      } else {
         return intval($track->game_order);
      }
   }
   
   /**
    * getPossibleAnswers - return an array of possible answer for a given game
    * in the answers there will be the correct answer.
    *
    * @param  int $gamesid
    * @return array
    */
   public static function getPossibleAnswers(int $gamesid) :array
   {
      $currentTrackIndex = Game::getCurrentTrackIndex($gamesid);
      $currentgamesuggestion = Game::where([
         ['game_gamesid', '=', $gamesid],
         ['game_order', '!=', $currentTrackIndex]
      ])->get()->toArray();
      shuffle($currentgamesuggestion);
      $returnarray = array();
      array_push($returnarray,$currentgamesuggestion[0]['game_track']);
      array_push($returnarray,$currentgamesuggestion[1]['game_track']);
      array_push($returnarray,$currentgamesuggestion[2]['game_track']);
      array_push($returnarray,$currentgamesuggestion[3]['game_track']);
      array_push($returnarray,Game::where([
         ['game_gamesid', '=', $gamesid],
         ['game_order', '=', $currentTrackIndex]
      ])->first()->game_track);
      shuffle($returnarray);
      return $returnarray;
   }

   /**
    * getHighScore for a given gameid
    * Equivalent to SQL query below : 
    * "SELECT userid,sum(points) as score FROM blindtest.game
    *              WHERE userid is not null 
    *              AND game_gamesid = $gameid
    *              GROUP BY userid
    *              ORDER BY score DESC;"
    *
    * @param  mixed $gameid
    * @return array
    */
   public static function getHighScore($gameid): array
   {

      $scores = Game::select('userid', DB::raw('sum(points) as score'))
         ->where('game_gamesid', '=', $gameid)
         ->whereNotNull('userid')
         ->groupBy('userid')
         ->orderBy('score', 'desc')
         ->first();

      $userid = null;
      $userscore = 0;

      if (!is_null($scores)) {
         $userid = $scores->userid;
         $userscore = intval($scores->score);
      }

      return ['userid' => $userid, 'score' => $userscore];
   }


   /**
    * getUserScore - return specific userid score for a given gameId
    *
    * @param  mixed $gamesid
    * @param  mixed $userid
    * @return int
    */
   public static function getUserScore($gamesid, $userid): int
   {
      return intval(Game::where([
         ['game_gamesid', '=', $gamesid],
         ['userid', '=', $userid]
      ])->sum('points'));
   }
}
