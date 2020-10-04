<?php

namespace App\MusicSources;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use \hamburgscleanest\GuzzleAdvancedThrottle as GuzzleAdvancedThrottle;

/**
 * This class will help you to interact with the Deezer API
 *
 * This is a really simple implementation and it will just help to bootstrap a project using the Deezer API.
 *
 * For more informations about the api please visit http://www.deezer.com/fr/developers/simpleapi
 *
 * @author Mathieu BUONOMO <mbuonomo@gmail.com>
 * @version 0.1
 */
class DeezerApi {

    private $log;

    
    /**
     * This is the url to call the API
     *
     * @var string
     */
    private $_sApiUrl = "https://api.deezer.com";
    private $_sApiMaxRequest = "50";
    private $_sApiRequestInterval = "5";
    private $ThrottlerRules;
    private $ThrottlerStack;
    public $initialized;

    public function __construct() {
        
        $this->log = new Logger('DeezerApi.php');
        $this->log->pushHandler(new StreamHandler(__DIR__.'/../../logs/debug.log', Logger::DEBUG));
        $this->log->debug("(__contruct) New DeeZerApi Constructor called");
        $this->initiateThrotller();
        $this->initialized = true;
    }
    
    
    /**
     * Return a true if this class is correctly initialized
     * @return boolean
     */
    public function isInitialized() {
        return $this->initialized;
    }

    /**
     * Initialize Throttler with values set in the class
     */
    private function initiateThrotller() {
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
    public function sendRequest($sUrl) {
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
                $this->log->debug("(sendRequest) Deezer request recieved : " . $sUrl);
                $response = $client->get($sUrl);
                $output = $response->getBody();
                $RequestToBeDone = false;
            } catch (\Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException $e) {
                $this->log->debug("(sendRequest) Too many requests. Waiting 1 second");
                sleep(1);
            }
        } while ($RequestToBeDone);


        if ($output === false) {
            $this->log->debug("(sendRequest) Error curl : " . curl_error($c), E_USER_WARNING);
            //trigger_error('Erreur curl : ' . curl_error($response), E_USER_WARNING);
        } else {
            //curl_close($response);
            return $output;
        }
    }

    private function search_params($param) {
        $url = $this->_sApiUrl . '/search?q=' . $param;
        return json_decode($this->sendRequest($url), true);
    }

    public function getBlindtestPLaylists(){
        $ids=getenv('playlistsids');

        $ids=preg_replace("/[^0-9,]/", "", $ids );
        $ids=explode(",",$ids);
        $this->log->debug("(getBlindtestPLaylists) Blindtest playlists IDs : " . print_r($ids,true));
        return $ids;
    }

    /**
     * Call the api
     *
     * @param string $sUrl 
     * @param array $aParams 
     * @return array
     * @author Mathieu BUONOMO
     */
    private function api($sUrl) {
        $sGet = $this->_sApiUrl . $sUrl ;
        return json_decode($this->sendRequest($sGet), true);
    }


    
    /**
     * Return the name of a playlist for a given PlaylistID
     * @param int $playlistID
     * @return string
     */
    public function getPlaylistName($playlistID) {
        return $this->api("/playlist/".$playlistID)['title'];        
    }
    
    /**
     * Return the link to the picture of a playlist for a given PlaylistID
     * @param int $playlistID
     * @return string
     */
    public function getPlaylistPicture($playlistID) {
        return $this->api("/playlist/".$playlistID)['picture_big'];        
    }


    public function getTrackInformations($trackid) {
        $rawdata=$this->api("/track/".$trackid);
        $this->log->debug("(getTrackInformations) ".var_export($rawdata,true));
        return $rawdata ;  
    }

    private function PlaylistInfoFormat($rawdata){
        $this->log->debug("(PlaylistInfoFormat) ".var_export($rawdata,true));
        $output['name']=$rawdata['title'];
        $output['id']=$rawdata['id'];
        $output['description']=$rawdata['description'];
        $output['tracks']=$rawdata['nb_tracks'];
        $output['picture']=$rawdata['picture_big'];
        $output['nb_tracks']=$rawdata['nb_tracks'];
        $output['tracks']=array();
        foreach ($rawdata['tracks']['data'] as $track){
            if ($track['preview']!=null) {
                $trackdata=array();
                $trackdata['id']=$track['id'];
                $trackdata['title']=$track['title'];
                $trackdata['artist']=$track['artist']['name'];
                array_push($output['tracks'],$trackdata);
            }
        }
        shuffle($output['tracks']);
        return $output;
    }
    
    public function GetPlaylistInfo($playlistID) {
        return $this->PlaylistInfoFormat($this->api("/playlist/".$playlistID));      
    }
    

    /**
     * Return all tracks for a given PlaylistID
     * @param type $playlistID
     * @return array
     */
    public function getPlaylistItems($playlistID) {

        $playlist = $this->api("/playlist/" . $playlistID);
//        $this->log->debug("(getPlaylistItems)" . var_export($playlist, true));
        $list = array();
        foreach ($playlist['tracks']['data'] as $track) {
            $this->log->debug("(getPlaylistItems)" . var_export($track, true));
            
            array_push($list, ["ID" => $track["id"],
                "Artist" => $track["artist"]["name"],
                "Album" => $track["album"]["title"],
                "Song" => $track["title"],
                "Time" => intval($track["duration"])*1000,
                "Track" => null,
                "TotalTracks" => null,
                "Preview" => $track['preview'],
                "Picture" => $track['album']['cover']
            ]);
        }
        return $list;
    }


}
