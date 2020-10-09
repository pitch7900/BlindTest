<?php

namespace App\MusicSources\Deezer;
use Psr\Log\LoggerInterface;;

/**
 * This class will help you to interact with the Deezer API
 *
 * This is a really simple implementation and it will just help to bootstrap a project using the Deezer API.
 *
 * For more informations about the api please visit http://www.deezer.com/fr/developers/simpleapi
 *
 * @author Mathieu BUONOMO <mbuonomo@gmail.com>,Pierre Christensen <pchristensen@gmail.com>
 * @version 0.2
 * 
 */
interface DeezerApiInterface {
    

    public function __construct(LoggerInterface $logger);
    
    
    /**
     * Return a true if this class is correctly initialized
     * @return boolean
     */
    public function isInitialized();

    

    /**
     * This method will be called to send a request
     *
     * @param string $sUrl 
     * @return void
     */
    public function sendRequest($sUrl);

   


    
    /**
     * Return the name of a playlist for a given PlaylistID
     * @param int $playlistID
     * @return string
     */
    public function getPlaylistName($playlistID);
    
    /**
     * Return the link to the picture of a playlist for a given PlaylistID
     * @param int $playlistID
     * @return string
     */
    public function getPlaylistPicture($playlistID);


    public function getTrackInformations($trackid);
    /**
     * Search for a playlist based on string
     * @param string $playliststring
     * @return json
     */
    public function searchPlaylist($playliststring);

    
    
    public function GetPlaylistInfo($playlistID);

    /**
     * Return all tracks for a given PlaylistID
     * @param type $playlistID
     * @return array
     */
    public function getPlaylistItems($playlistID);


}
