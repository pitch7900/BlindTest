<?php

namespace App\Games;

use Psr\Log\LoggerInterface;;

class Game
{  
   /**
    * @var LoggerInterface $logger
    */
   private $logger;

   /**
    * @var integer $gameid
    */
   private $gameid;

    /**
     * @var array $tracklist
     */
   private $tracklist;

   /**
   * @var integer nb_tracks;
   */
   private $nb_tracks;

   /**
    * @var string coverurl
    */
   private $coverurl;

   /**
    * @var integer $id
    */
    private $id;

   /**
    * @var string $name
    */
   private $name;

   /**
    * @param LoggerInterface $logger
    * @param integer $id
    * @param string $name
    * @param array $tracklist
    * @param string $coverurl
    */
   public function __construct(LoggerInterface $logger, $id, $name, $tracklist,$coverurl){
      //generated the gameid with the epoch
      $this->gameid=time();
      $this->tracklist=shuffle($tracklist);
      $this->logger=$logger;
      $this->id=$id;
      $this->name=$name;
      $this->coverurl=$coverurl;
      $this->nb_tracks=count($tracklist);
      $this->logger->debug("Game::___construct constructor called");
      $this->logger->debug("Game::___construct New Game ".$this->gameid." created");
      $this->logger->debug("Game::___construct New Game playlist id ".$this->id);
      $this->logger->debug("Game::___construct New Game playlist name ".$this->name);
      $this->logger->debug("Game::___construct New Game coverurl ".$this->coverurl);
   }

   public function getName(){
      return $this->name;
   }

   public function getID(){
      return $this->id;
   }

   public function getTrackList(){
      return $this->tracklist;
   }

   public function getGameID(){
      return $this->gameid;
   }
}
