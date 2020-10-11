<?php

namespace App\Database;

use Psr\Log\LoggerInterface;;
use App\Database\Game;
use Illuminate\Database\Eloquent\Model;
use SimpleXMLElement;

class Games extends Model {
   public $timestamps = true;
   protected $table = 'games';
   protected $primaryKey = 'id';
   protected $fillable = ['games_playlist,games_currenttrackindex,games_currenttrack_starttime'];
  

   /**
    * @var array $gamelist
    */
   private $gamelist;

    

   
}
