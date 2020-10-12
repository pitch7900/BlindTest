<?php

namespace App\Database;

use Illuminate\Database\Eloquent\Model;

class Game  extends Model {
   public $timestamps = true;
   protected $table = 'game';
   protected $primaryKey = 'id';
   protected $fillable = ['game_gamesid','game_track','game_order'];
}
