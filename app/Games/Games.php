<?php

namespace App\Games;

use Psr\Log\LoggerInterface;;
use App\Games\Game;
class Games
{  
   /**
    * @var LoggerInterface $logger
    */
   private $logger;

   /**
    * @var array $gamelist
    */
   private $gamelist;

    

   public function __construct(LoggerInterface $logger ){
      $this->gamelist=array();
      $this->logger=$logger;
      $this->logger->debug("Games::___construct constructor called");
   }

   public function add(Game $game){
      array_push($this->gamelist,$game);
      $this->logger->debug("Games::add game ".$game->getID()." added");
      $this->logger->debug("Games::add ".print_r($this->gamelist,true));
   }

   /**
    * get specific game for a given gameID
    * @param integer $gameid
    * @return App\Games\Game || null
    */
   public function get($gameid){
      $this->logger->debug("Games::get gameID ".$gameid);
      $this->logger->debug("Games::get ".print_r($this->gamelist,true));
      foreach($this->gamelist as $game){
         $this->logger->debug("Games::get Parsing " .$game['id']);
         if ($game['id']==$gameid) {
            return $game;
         }
      }
      return null;
   }
}
