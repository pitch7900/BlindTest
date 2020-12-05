<?php

namespace App\Database;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Capsule\Manager as DB;
use App\Database\Game;
use App\Database\User;

class GamePlayers extends Model
{
   public $timestamps = true;
   protected $table = 'gameplayers';
   protected $primaryKey = 'id';
   protected $fillable = ['gameid', 'userid', 'writing', 'isready', 'answered'];



   /**
    * getCurrentTrack : Return the current track to play with based on gameid
    *
    * @param  mixed $gameid
    * @return int
    */
   public static function getPlayers($gameid): array
   {
      $players = GamePlayers::where('gameid', '=', $gameid)
         ->orderby('id', 'ASC')
         ->get()
         ->toArray();
      $results = array();
      foreach ($players as $player) {
         $user = User::find($player['userid']);

         array_push($results, [
            'id' => $user->id,
            'nickname' => $user->nickname,
            'status' => boolval($player['isready']),
            'writing' => boolval($player['writing']),
            'answered' => boolval($player['answered']),
            'online' => User::isOnline($user->id),
            'score' => Game::getUserScore($gameid, $user->id)
         ]);
      }
      return $results;
   }

   public static function resetStatus($gameid): void
   {
      GamePlayers::where('gameid', '=', $gameid)->update(array('writing'=>false));
      GamePlayers::where('gameid', '=', $gameid)->update(array('answered'=>false));
      GamePlayers::where('gameid', '=', $gameid)->update(array('isready'=>true));
   }

   public static function isReadyStatus($gameid, bool $status){
      GamePlayers::where('gameid', '=', $gameid)->update(array('isready'=>$status));
   }


  
}
