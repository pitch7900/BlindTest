<?php

namespace App\MusicSources\Deezer;


use App\Database\Playlist;
use App\Database\Track;
use App\Database\Artist;
use App\Database\Album;
use App\Database\PlaylistTracks;
use Psr\Log\LoggerInterface;;
use App\Database\Game;
use \hamburgscleanest\GuzzleAdvancedThrottle as GuzzleAdvancedThrottle;
use Carbon\Carbon;
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

    /**
     * __construct
     *
     * @param  mixed $logger
     * @return void
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->logger->debug("DeezerApi::__contruct New DeeZerApi Constructor called");
        $this->initiateThrotller();
        $this->initialized = true;
    }


    /**
     * isInitialized : Return a true if this class is correctly initialized
     * 
     * @return boolean
     */
    public function isInitialized()
    {
        return $this->initialized;
    }

    /**
     * initiateThrotller : Initialize Throttler with values set in the class
     *
     * @return void
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
     * sendRequest : This method will be called to send a request
     *
     * @param  mixed $sUrl
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

    /**
     * search_params : Search based on string
     *
     * @param  string $param
     * @return void
     */
    private function search_params(string $param)
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
            $this->DBaddPlaylistTracks($playlistID);
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
            $this->DBaddPlaylistTracks($playlistID);
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

        return Track::find($trackid)->toArray();
    }

    /**
     * Search for a playlist based on string
     * @param string $playliststring
     * @return array
     */
    public function searchPlaylist($playliststring)
    {
        $url = $this->_sApiUrl . '/search/playlist?q=' . $playliststring;
        return json_decode($this->sendRequest($url), true);
    }

    /**
     * PlaylistInfoFormat
     *
     * @param  mixed $playlistID
     * @return array
     */
    private function PlaylistInfoFormat($playlistID): array
    {
        $playlist = Playlist::find($playlistID);
        if (empty($playlist)) {
            $this->logger->debug("DeezerApi::PlaylistInfoFormat add playlist to DB");
            $this->DBaddPlaylistTracks($playlistID);
        }
        $playlist = Playlist::find($playlistID);


        $output['name'] = $playlist->playlistname;
        $output['id'] = $playlist->id;
        $output['description'] = $playlist->playlistname;
        $output['picture'] = $playlist->picture;

        $playlisttracks = PlaylistTracks::where('playlisttracks_playlist', $playlistID);
        $output['nb_tracks'] = $playlisttracks->count();

        $output['tracks'] = array();
        foreach ($playlisttracks->get()->playlisttracks_track as $trackid) {
            $track = Track::find($trackid);

            $trackdata = array();
            $trackdata['id'] = $track->id;
            $trackdata['title'] = $track->track_title;
            $artist = Artist::find($track->track_artist);
            $trackdata['artist'] = $artist->artist_name;
            $album = Album::find($track->track_album);
            $trackdata['coverurl'] = $album->album_cover;
            array_push($output['tracks'], $trackdata);
        }

        return $output;
    }

    /**
     * GetPlaylistInfo
     *
     * @param  mixed $playlistID
     * @return array
     */
    public function GetPlaylistInfo($playlistID): array
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
            "readable" => $track['readable']
        ];
        return $array;
    }

    /**
     * getLargePlaylistTracks : For playlists with more than 400 tracks
     * Do sequential search
     *
     * @param  mixed $playlistID
     * @return array
     */
    private function getLargePlaylistTracks(int $playlistID): array
    {
        $url = "/playlist/" . $playlistID . "/tracks";
        $tracklist = array();
        do {
            $tracks = $this->api($url);
            if (array_key_exists('next', $tracks)) {
                $url = str_replace($this->_sApiUrl, '', $tracks['next']);
            }
            foreach ($tracks['data'] as $track) {
                $this->logger->debug("DeezerApi::getLargePlaylistTracks \n" . json_encode($track, JSON_PRETTY_PRINT));

                array_push($tracklist, $this->getTrackArray($track));
            }
        } while (array_key_exists('next', $tracks));
        return $tracklist;
    }

    /**
     *  DBaddArtist : Add Artist to database
     *
     * @param  array $artist
     * @return int
     */
    private function DBaddArtist(array $artist): int
    {
        $artistdb = Artist::find($artist['id']);
        
        if (is_null($artistdb)) {
            $this->logger->debug("DeezerApi::DBaddArtist Adding Artist : ".$this->forceLatinChars($artist['name']));
            Artist::updateOrCreate([
                'id' => $artist['id'],
                'artist_name' => $this->forceLatinChars($artist['name']),
                'artist_link' => $artist['link'],
                'artist_tracklist' => $artist['tracklist']
            ]);
        }
        return intval($artist['id']);
    }

    
    /**
     * forceLatinChars : Replace special chars with an equivalent in latin charset
     * Written to solve the "KoЯn" bug !
     * 
     * @param  mixed $str
     * @return void
     */
    private function forceLatinChars(string $text,$strict = false){
        $unwanted_array = array(
            'Š' => 'S', 'š' => 's', 'Ž' => 'Z', 'ž' => 'z', 'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'A', 'Ç' => 'C', 'È' => 'E', 'É' => 'E',
            'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O', 'Ù' => 'U',
            'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ý' => 'Y', 'Þ' => 'B', 'ß' => 'Ss', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'a', 'ç' => 'c',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ð' => 'o', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o',
            'ö' => 'o', 'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ý' => 'y', 'þ' => 'b', 'ÿ' => 'y', 'Ğ' => 'G', 'İ' => 'I', 'Ş' => 'S', 'ğ' => 'g', 'ı' => 'i', 'ş' => 's', 'ü' => 'u',
            'ă' => 'a', 'Ă' => 'A', 'ș' => 's', 'Ș' => 'S', 'ț' => 't', 'Ț' => 'T', 'ć' => 'c', 'Я' => 'R', 'œ' => 'oe', '™' => 'TM'
        );
        return strtr($text, $unwanted_array);
        
    }

    /**
     * DBaddAlbum : Add album to database
     *
     * @param  mixed $album
     * @return int
     */
    private function DBaddAlbum(array $album): int
    {
        
        $albumdb = Album::find($album['id']);
        
        if (is_null($albumdb)) {
            $this->logger->debug("DeezerApi::DBaddAlbum Adding album : ".$this->forceLatinChars($album['title']));
            Album::updateOrCreate([
                'id' => $album['id'],
                'album_title' => $this->forceLatinChars($album['title']),
                'album_tracklist' => $album['tracklist'],
                'album_cover' => $album['cover_xl']
            ]);
        }
        return intval($album['id']);
    }
    /**
     * DBaddTrack : Add a track to database
     *
     * @param  mixed $track
     * @return int
     */
    private function DBaddTrack(array $track): int
    {
        
        $this->DBaddAlbum($track['album']);
        $this->DBaddArtist($track['artist']);
        $trackdb = Track::find($track['id']);
        if (is_null($trackdb)) {
            $this->logger->debug("DeezerApi::DBaddTrack Add track : ". $this->forceLatinChars($track['title']));
            Track::updateOrCreate([
                'id' => $track['id'],
                'track_title' => $this->forceLatinChars($track['title']),
                'track_link' => $track['link'],
                'track_preview' => $track['preview'],
                'track_artist' => $track['artist']['id'],
                'track_album' => $track['album']['id'],
                'track_duration' => $track['duration']
            ]);
        }
        return intval($track['id']);
    }
    
    /**
     * DBremoveTrack Remove a track from database (usualy preview of music is not working)
     *
     * @param  mixed $trackid
     * @return void
     */
    public function DBremoveTrack(int $trackid) {
        $trackdb = Track::find($trackid);
        $this->logger->debug("DeezerApi::DBremoveTrack Should remove track : ". $trackid);
        if (!is_null($trackdb)) {
            $this->logger->debug("DeezerApi::DBremoveTrack remove track ". $trackid ." from games");
            $game = Game::where("game_track","=",$trackid);
            $game->forceDelete();
            $this->logger->debug("DeezerApi::DBremoveTrack remove track ". $trackid ." from all playlist");
            $playlisttracks=  PlaylistTracks::where("playlisttracks_track","=",$trackid);
            $playlisttracks->forceDelete();
            $this->logger->debug("DeezerApi::DBremoveTrack remove track : ". $trackid);
            $trackdb->forceDelete();


        }
    }
    
    /**
     * DBaddPlaylist - Add a playlist to the playlist DB and return an array with deezer informations
     *
     * @param  mixed $playlistID
     * @return array
     */
    public function DBaddPlaylist(int $playlistID):bool {
        $exist=false;
        $playlist = Playlist::find($playlistID);
        if (is_null($playlist)) {

            $playlist_array = $this->api("/playlist/" . $playlistID);
            Playlist::updateOrCreate([
                'id' => $playlist_array['id'],
                'playlist_title' => $this->forceLatinChars($playlist_array['title']),
                'playlist_link' => $playlist_array['link'],
                'playlist_picture' => $playlist_array['picture_xl']
            ]);
            
        } else {
            $exist=true;
        }
        return $exist;
    }

    /**
     * DBaddPlaylistTracks : Add Playlist to database
     *
     * @param  mixed $playlistID
     * @return int
     */
    private function DBaddPlaylistTracks(int $playlistID): int
    {
        $tracks = array();
        //$playlist_json = $this->api("/playlist/" . $playlistID);
        $playlist_exist = $this->DBaddPlaylist($playlistID);
        $playlist_array = $this->api("/playlist/" . $playlistID);
        if ($playlist_array['nb_tracks'] <= 400) {
            $this->logger->debug("DeezerApi::DBaddPlaylistTracks Playlist has " . $playlist_array['nb_tracks'] . " tracks. Do normal search");

            foreach ($playlist_array['tracks']['data'] as $track) {
               // $this->logger->debug("DeezerApi::DBaddPlaylistTracks \n" . json_encode($track, JSON_PRETTY_PRINT));

                array_push($tracks, $this->getTrackArray($track));
            }
        } else {
            $this->logger->debug("DeezerApi::DBaddPlaylistTracks Playlist has " . $playlist_array['nb_tracks'] . " tracks. Do Extended search");
            $tracks = $this->getLargePlaylistTracks($playlistID);
        }
                
        //Add each Track to database
        foreach ($tracks as $track) {
            //Only add a track with a preview and that can be readed
            if (strlen($track['preview'])>0 && $track['readable']) {
                $this->DBaddTrack($track);
                PlaylistTracks::updateOrCreate([
                    'playlisttracks_track' => $track['id'],
                    'playlisttracks_playlist' => $playlistID
                ]);
            }
        }
        return intval($playlistID);
    }

    /**
     * Return all tracks for a given PlaylistID
     * @param int $playlistID
     * @return array
     */
    public function getPlaylistItems(int $playlistID): array
    {
        $playlist = Playlist::find($playlistID);
        $playlisttracks = PlaylistTracks::where('playlisttracks_playlist', $playlistID);
        
       
        //playlist is not in the DB yet, or there is no track, or hte lateset update is one week all. Add or update it.
        if (empty($playlist) || $playlisttracks->count()==0) {
            $this->logger->debug("DeeezerApi::getPlaylistItems Playlist $playlistID not in DB cache adding it");
            $this->DBaddPlaylistTracks($playlistID);
        } else {

            $oneweekbefore =  Carbon::createFromTimestamp(time()-10080);
            $latestupdate = Carbon::createFromFormat(Carbon::DEFAULT_TO_STRING_FORMAT,$playlisttracks->orderBy('updated_at','DESC')->first()->updated_at);
            
            if ($latestupdate->lte($oneweekbefore)){
                $this->logger->debug("DeeezerApi::getPlaylistItems Playlist $playlistID Lastest update is ".$playlisttracks->orderBy('updated_at','DESC')->first()->updated_at);
                $this->logger->debug("DeeezerApi::getPlaylistItems Forcing udpate");
                $this->DBaddPlaylistTracks($playlistID);
            }

            $this->logger->debug("DeeezerApi::getPlaylistItems Playlist already in DB cache");
        }
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
