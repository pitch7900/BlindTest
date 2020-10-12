<?php

namespace App\Database;

use Illuminate\Database\Eloquent\Model;

class Games extends Model {
   public $timestamps = true;
   protected $table = 'games';
   protected $primaryKey = 'id';
   protected $fillable = ['games_playlist', 'games_currenttrackindex', 'games_currenttrack_starttime'];
  
   
}
