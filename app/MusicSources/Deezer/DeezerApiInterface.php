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
interface DeezerApiInterface
{
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

    /**
     * Retrieve track information
     * @param int $trackid
     */
    public function getTrackInformations($trackid);

    /**
     * Search for a playlist based on string
     * @param string $playliststring
     * @return array
     * "data": [
     *          {
     *              "id": integer,
     *              "title": string,
     *              "public": boolean,
     *              "nb_tracks": integer,
     *              "link": string_url,
     *              "picture": string_url,
     *              "picture_small": string_url,
     *              "picture_medium": string_url,
     *              "picture_big": string_url,
     *              "picture_xl": string_url,
     *              "checksum": string,
     *              "tracklist": string_url,
     *              "creation_date": timestamp,
     *              "md5_image": string,
     *              "user": {
     *                  "id": integer,
     *                  "name": string,
     *                  "tracklist": string_url,
     *                  "type": string
     *              },
     *              "type": string
     *          }
     * ]
     */
    public function searchPlaylist($playliststring);

    /**
     * DBaddPlaylist - Add a playlist to the playlist DB and return an array with deezer informations
     *
     * @param  mixed $playlistID
     * @return array
     */
    public function DBaddPlaylist(int $playlistID);

    /**
     * GetPlaylistInfo
     *
     * @param  mixed $playlistID
     * @return array
     */
    public function GetPlaylistInfo($playlistID);

    /**
     * Return all tracks for a given PlaylistID
     *
     * @param  mixed $playlistID
     * @param  mixed $forceudpate
     * @return array
     */
    public function getPlaylistItems(int $playlistID,bool $forceudpate=false): array;


     /**
     * EmptyPlaylist - Delete all Tracks for a given playlist
     *
     * @param  mixed $playlistID
     * @return void
     */
    public function EmptyPlaylist(int $playlistID):void;

    /**
     * DBremoveTrack Remove a track from database (usualy preview of music is not working)
     *
     * @param  mixed $trackid
     * @return void
     */
    public function DBremoveTrack(int $trackid);
}
