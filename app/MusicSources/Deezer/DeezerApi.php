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
        return $this->api("/playlist/" . $playlistID)['title'];
    }

    /**
     * Return the link to the picture of a playlist for a given PlaylistID
     * @param int $playlistID
     * @return string
     */
    public function getPlaylistPicture($playlistID)
    {
        return $this->api("/playlist/" . $playlistID)['picture_big'];
    }

    /**
     * Retrieve track information
     * @param int $trackid
     */
    public function getTrackInformations($trackid)
    {
        $rawdata = $this->api("/track/" . $trackid);
        $this->logger->debug("DeezerApi::getTrackInformations " . var_export($rawdata, true));
        return $rawdata;
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

    private function PlaylistInfoFormat($rawdata)
    {
        $this->logger->debug("DeezerApi::PlaylistInfoFormat " . var_export($rawdata, true));
        $output['name'] = $rawdata['title'];
        $output['id'] = $rawdata['id'];
        $output['description'] = $rawdata['description'];
        $output['tracks'] = $rawdata['nb_tracks'];
        $output['picture'] = $rawdata['picture_big'];
        $output['nb_tracks'] = $rawdata['nb_tracks'];
        $output['tracks'] = array();
        foreach ($rawdata['tracks']['data'] as $track) {
            if ($track['preview'] != null) {
                $trackdata = array();
                $trackdata['id'] = $track['id'];
                $trackdata['title'] = $track['title'];
                $trackdata['artist'] = $track['artist']['name'];
                $trackdata['coverurl'] = $track['album']['cover_xl'];
                array_push($output['tracks'], $trackdata);
            }
        }
        shuffle($output['tracks']);
        return $output;
    }

    public function GetPlaylistInfo($playlistID)
    {

        return $this->PlaylistInfoFormat($this->api("/playlist/" . $playlistID));
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
            'name' => $artist['name'],
            'link' => $artist['link'],
            'tracklist' => $artist['tracklist']
        ]);

        return $artist['id'];
    }
    private function DBaddAlbum($album)
    {
        Album::updateOrCreate([
            'id' => $album['id'],
            'title' => $album['title'],
            'tracklist' => $album['tracklist'],
            'cover' => $album['cover']
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
            'title' => $track['title'],
            'link' => $track['link'],
            'preview' => $track['preview'],
            'artist' => $track['artist']['id'],
            'album' => $track['album']['id'],
            'duration' => $track['duration']
        ]);
        return $track['id'];
    }
    /**
     * Return all tracks for a given PlaylistID
     * @param type $playlistID
     * @return array
     */
    public function getPlaylistItems($playlistID)
    {
        $playlist = Playlist::find($playlistID);
        $tracks = array();
        if (empty($playlist)){
            $playlist = $this->api("/playlist/" . $playlistID);
            // $this->logger->debug("DeeezerApi::getPlaylistItems \n" . var_export($playlist, true));
            
            if ($playlist['nb_tracks'] <= 400) {
                $this->logger->debug("DeezerApi::getPlaylistItems Playlist has " . $playlist['nb_tracks'] . " tracks. Do normal search");

                foreach ($playlist['tracks']['data'] as $track) {
                    // $this->logger->debug("DeezerApi::getPlaylistItems \n" . json_encode($track, JSON_PRETTY_PRINT));

                    array_push($tracks, $this->getTrackArray($track));
                }
            } else {
                $this->logger->debug("DeezerApi::getPlaylistItems Playlist has " . $playlist['nb_tracks'] . " tracks. Do Extended search");
                $tracks = $this->getLargePlaylistTracks($playlistID);
            }
            Playlist::updateOrCreate([
                'id' => $playlist['id'],
                'title' => $playlist['title'],
                'link' => $playlist['link']
            ]);
            //add to database
            foreach ($tracks as $track) {
                $this->DBaddTrack($track);
                PlaylistTracks::updateOrCreate([
                    'track' => $track['id'],
                    'playlist' => $playlist['id']
                ]);
            }
        } else {
            $this->logger->debug("DeeezerApi::getPlaylistItems Playlist already in DB cache");
            // $this->logger->debug("DeeezerApi::getPlaylistItems ".var_dump($playlist->get()->first(),true));
            foreach (PlaylistTracks::where('playlist',$playlistID)->get() as $playlisttrack ) {
                array_push($tracks,Track::find($playlisttrack->track)->get()->toArray());
            }
        }
        return $tracks;
    }
}
