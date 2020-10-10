<?php

namespace App\Database;


use Psr\Log\LoggerInterface;;
use Illuminate\Database\Eloquent\Model;

class Game  extends Model {
   public $timestamps = true;
   protected $table = 'game';
   protected $primaryKey = 'id';
   protected $fillable = ['gameid','trackid','order'];
   /**
    * @var LoggerInterface $logger
    */
   private $logger;

   

}
