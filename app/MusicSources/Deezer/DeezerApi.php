<?php

namespace App\MusicSources\Deezer;

use App\Database\Playlist;
use App\Database\Track;
use App\Database\Artist;
use App\Database\Album;
use App\Database\PlaylistTracks;
use Psr\Log\LoggerInterface;;

use \hamburgscleanest\GuzzleAdvancedThrottle as GuzzleAdvancedThrottle;

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
class DeezerApi implements DeezerApiInterface
{
    /**
     * This is the url to call the API
     *
     * @var string
     */
    private $_sApiUrl = "https://api.deezer.com";

    /**
     * Max queries per $_sApiRequestInterval
     * @var string
     */
    private $_sApiMaxRequest = "50";

    /**
     * Interval for max queries used in _sApiMaxRequest
     * @var string
     */
    private $_sApiRequestInterval = "5";

    /**
     * @var GuzzleAdvancedThrottle\RequestLimitRuleset
     */
    private $ThrottlerRules;

    /**
     * @var \GuzzleHttp\HandlerStack
     */
    private $ThrottlerStack;

    /**
     * Is initialiezd
     * @var boolean
     */
    public $initialized;

    /**
     * Preferences
     * @var LoggerInterface;
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->logger->debug("DeezerApi::__contruct New DeeZerApi Constructor called");
        $this->initiateThrotller();
        $this->initialized = true;
    }


    /**
     * Return a true if this class is correctly initialized
     * @return boolean
     */
    public function isInitialized()
    {
        return $this->initialized;
    }

    /**
     * Initialize Throttler with values set in the class
     */
    private function initiateThrotller()
    {
        $this->ThrottlerRules = new GuzzleAdvancedThrottle\RequestLimitRuleset([
            $this->_sApiUrl => [
                [
                    'max_requests' => $this->_sApiMaxRequest,
                    'request_interval' => $this->_sApiRequestInterval
                ]
            ]
        ]);
    }

    /**
     * This method will be called to send a request
     *
     * @param string $sUrl 
     * @return void
     */
    public function sendRequest($sUrl)
    {
        $this->ThrottlerStack = new \GuzzleHttp\HandlerStack();
        $this->ThrottlerStack->setHandler(new \GuzzleHttp\Handler\CurlHandler());

        $throttle = new GuzzleAdvancedThrottle\Middleware\ThrottleMiddleware($this->ThrottlerRules);

        $this->ThrottlerStack->push($throttle());

        $client = new \GuzzleHttp\Client([
            'base_uri' => $this->_sApiUrl,
            'handler' => $this->ThrottlerStack,
            'verify' => false
        ]);
        $RequestToBeDone = true;
        do {
            try {
                $this->logger->debug("DeezerApi::sendRequest Deezer request recieved : " . $sUrl);
                $response = $client->get($sUrl);
                $output = $response->getBody();
                $RequestToBeDone = false;
            } catch (\Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException $e) {
                $this->logger->debug("DeezerApi::sendRequest Too many requests. Waiting 1 second");
                sleep(1);
            }
        } while ($RequestToBeDone);


        if ($output === false) {
            $this->logger->debug("DeezerApi::sendRequest Error curl : " . curl_error($response), E_USER_WARNING);
            //trigger_error('Erreur curl : ' . curl_error($response), E_USER_WARNING);
        } else {
            //curl_close($response);
            return $output;
        }
    }

    private function search_params($param)
    {
        $url = $this->_sApiUrl . '/search?q=' . $param;
        return json_decode($this->sendRequest($url), true);
    }



    /**
     * Call the api
     *
     * @param string $sUrl 
     * @param array $aParams 
     * @return array
     * @author Mathieu BUONOMO
     */
    private function api($sUrl)
    {
        $sGet = $this->_sApiUrl . $sUrl;
        return json_decode($this->sendRequest($sGet), true);
    }



    /**
     * Return the name of a playlist for a given PlaylistID
     * @param int $playlistID
     * @return string
     */
    public function getPlaylistName($playlistID)
    {
        $playlist = Playlist::find($playlistID);
        //Track is not in the DB yet. Add it.
        if (empty($playlist)) {
            $this->DBaddPlaylist($playlistID);
        }
        return Playlist::find($playlistID)->playlist_name;
    }

    /**
     * Return the link to the picture of a playlist for a given PlaylistID
     * @param int $playlistID
     * @return string
     */
    public function getPlaylistPicture($playlistID)
    {
        $playlist = Playlist::find($playlistID);
        //Track is not in the DB yet. Add it.
        if (empty($playlist)) {
            $this->DBaddPlaylist($playlistID);
        }
        return Playlist::find($playlistID)->playlist_picture;
    }

    /**
     * Retrieve track information
     * @param int $trackid
     */
    public function getTrackInformations($trackid)
    {
        $track = Track::find($trackid);
        //Track is not in the DB yet. Add it.
        if (empty($track)) {
            $rawdata = $this->api("/track/" . $trackid);
            $this->DBaddTrack($rawdata);
            $this->logger->debug("DeezerApi::getTrackInformations " . var_export($rawdata, true));
        }

        return Track::find($trackid)->first()->toArray();
    }
    /**
     * Search for a playlist based on string
     * @param string $playliststring
     * @return json
     */
    public function searchPlaylist($playliststring)
    {
        $url = $this->_sApiUrl . '/search/playlist?q=' . $playliststring;
        return json_decode($this->sendRequest($url), true);
    }

    private function PlaylistInfoFormat($playlistID)
    {
        $playlist = Playlist::find($playlistID);
        if (empty($playlist)) {
            $this->logger->debug("DeezerApi::PlaylistInfoFormat add playlist to DB");
            $this->DBaddPlaylist($playlistID);
        }
        $playlist = Playlist::find($playlistID)->first();


        $output['name'] = $playlist->playlistname;
        $output['id'] = $playlist->id;



        $output['description'] = $playlist->playlistname;
        $output['picture'] = $playlist->picture;

        $playlisttracks = PlaylistTracks::where('playlisttracks_playlist', $playlistID);
        $output['nb_tracks'] = $playlisttracks->count();

        $output['tracks'] = array();
        foreach ($playlisttracks->get()->playlisttracks_track as $trackid) {
            $track = Track::find($trackid)->first();

            $trackdata = array();
            $trackdata['id'] = $track->id;
            $trackdata['title'] = $track->track_title;
            $artist = Artist::find($track->track_artist)->first();
            $trackdata['artist'] = $artist->artist_name;
            $album = Album::find($track->track_album)->first();
            $trackdata['coverurl'] = $album->album_cover;
            array_push($output['tracks'], $trackdata);
        }

        return $output;
    }

    public function GetPlaylistInfo($playlistID)
    {
        return $this->PlaylistInfoFormat($playlistID);
    }

    private function getTrackArray($track)
    {
        $array = [
            "id" => $track["id"],
            // "Artist" => $track["artist"]["name"],
            "artist" => $track["artist"],
            // "Album" => $track["album"]["title"],
            "album" => $track["album"],
            "title" => $track["title"],
            "link" => $track["link"],
            "duration" => $track["duration"],
            // "Time" => intval($track["duration"]) * 1000,
            // "Track" => null,
            // "TotalTracks" => null,
            "preview" => $track['preview'],
            // "Picture" => $track['album']['cover']
        ];
        return $array;
    }
    /**
     * For playlists with more than 400 tracks
     */
    private function getLargePlaylistTracks($playlistID)
    {
        $url = "/playlist/" . $playlistID . "/tracks";
        $tracklist = array();
        do {
            $tracks = $this->api($url);
            if (array_key_exists('next', $tracks)) {
                $url = str_replace($this->_sApiUrl, '', $tracks['next']);
            }
            foreach ($tracks['data'] as $track) {
                // $this->logger->debug("DeezerApi::getLargePlaylistTracks \n" . json_encode($track, JSON_PRETTY_PRINT));

                array_push($tracklist, $this->getTrackArray($track));
            }
        } while (array_key_exists('next', $tracks));
        return $tracklist;
    }
    /**
     * ['id','name','link','tracklist']
     */
    private function DBaddArtist($artist)
    {
        Artist::updateOrCreate([
            'id' => $artist['id'],
            'artist_name' => $artist['name'],
            'artist_link' => $artist['link'],
            'artist_tracklist' => $artist['tracklist']
        ]);

        return $artist['id'];
    }
    private function DBaddAlbum($album)
    {
        Album::updateOrCreate([
            'id' => $album['id'],
            'album_title' => $album['title'],
            'album_tracklist' => $album['tracklist'],
            'album_cover' => $album['cover']
        ]);
        return $album['id'];
    }
    private function DBaddTrack($track)
    {
        $this->logger->debug("DeezerApi::DBaddTrack Add track : \n" . print_r($track, true));
        $this->DBaddAlbum($track['album']);
        $this->DBaddArtist($track['artist']);
        Track::updateOrCreate([
            'id' => $track['id'],
            'track_title' => $track['title'],
            'track_link' => $track['link'],
            'track_preview' => $track['preview'],
            'track_artist' => $track['artist']['id'],
            'track_album' => $track['album']['id'],
            'track_duration' => $track['duration']
        ]);
        return $track['id'];
    }

    private function DBaddPlaylist($playlistID)
    {
        $tracks = array();
        $playlist = $this->api("/playlist/" . $playlistID);

        if ($playlist['nb_tracks'] <= 400) {
            $this->logger->debug("DeezerApi::DBaddPlaylist Playlist has " . $playlist['nb_tracks'] . " tracks. Do normal search");

            foreach ($playlist['tracks']['data'] as $track) {
                // $this->logger->debug("DeezerApi::DBaddPlaylist \n" . json_encode($track, JSON_PRETTY_PRINT));

                array_push($tracks, $this->getTrackArray($track));
            }
        } else {
            $this->logger->debug("DeezerApi::DBaddPlaylist Playlist has " . $playlist['nb_tracks'] . " tracks. Do Extended search");
            $tracks = $this->getLargePlaylistTracks($playlistID);
        }
        Playlist::updateOrCreate([
            'id' => $playlist['id'],
            'playlist_title' => $playlist['title'],
            'playlist_link' => $playlist['link'],
            'playlist_picture' => $playlist['picture_xl']
        ]);
        //add to database
        foreach ($tracks as $track) {
            $this->DBaddTrack($track);
            PlaylistTracks::updateOrCreate([
                'playlisttracks_track' => $track['id'],
                'playlisttracks_playlist' => $playlist['id']
            ]);
        }
    }

    /**
     * Return all tracks for a given PlaylistID
     * @param type $playlistID
     * @return array
     */
    public function getPlaylistItems($playlistID)
    {
        $playlist = Playlist::find($playlistID);
        //playlist is not in the DB yet. Add it.
        if (empty($playlist)) {
            $this->DBaddPlaylist($playlistID);
        }
        $this->logger->debug("DeeezerApi::getPlaylistItems Playlist already in DB cache");
        $tracklist = PlaylistTracks::where('playlisttracks_playlist', $playlistID)
            ->join('playlist', 'playlisttracks.playlisttracks_playlist', '=', 'playlist.id')
            ->join('track', 'playlisttracks.playlisttracks_track', '=', 'track.id')
            ->join('album', 'track.track_album', '=', 'album.id')
            ->join('artist', 'track.track_artist', '=', 'artist.id')
            ->get();
        // die(print_r($tracklist->toJson(), true));

        return $tracklist->toArray();
    }
}
