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
   protected $fillable = ['playlist'];
   /**
    * @var LoggerInterface $logger
    */
   private $logger;

   /**
    * @var array $gamelist
    */
   private $gamelist;

    

   
}
