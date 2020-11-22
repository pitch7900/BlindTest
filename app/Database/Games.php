<?php

namespace App\Database;

use Illuminate\Database\Eloquent\Model;

class Games extends Model {
   public $timestamps = true;
   protected $table = 'games';
   protected $primaryKey = 'id';
   protected $fillable = ['games_playlist'];
  
   public static function getGamesIdFromPlaylist($playlistid) :array {
      $games = Games::where([['games_playlist', '=', $playlistid]]);
      $result=array();
      foreach ($games->get() as $game) {
         $gameid = $game->id;
         array_push($result,['id'=>$gameid]);
      }
      return $result;

   }
}
