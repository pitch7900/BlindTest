<?php

namespace App\Database;

use Illuminate\Database\Eloquent\Model;
use SimpleXMLElement;
use App\Database\PlaylistTracks;
/**
 * Class Track for Illuminate (DB) queries
 */
class Playlist extends AbstractModel {
    public $timestamps = true;
    protected $table = 'playlist';
    protected $primaryKey = 'id';
    protected $fillable = ['id','playlist_title','playlist_link','playlist_picture'];
    
    
    /**
     * getPlaylists - Return an array with all playlists and track count
     *
     * @return array
     */
    public static function getPlaylists():array{
        $playlists = Playlist::orderBy('playlist_title','ASC')
                    ->get()->toArray();
        $results = array();
        foreach ($playlists as $playlist){
            $tracks=PlaylistTracks::where('playlisttracks_playlist','=',$playlist['id']);
            if (is_null($tracks)){
                $playlist['tracks']=0;
            } else {
                $playlist['tracks'] = $tracks->count();
            }
            $playlist['played'] = Games::where('games_playlist','=',$playlist['id'])->count();
            array_push($results,$playlist);       
        }
        return $results;
    }

    /**
     * Return XML formatted data for an entry
     * @return type
     */
    public function toXML() {
        $xml = new SimpleXMLElement('<'.array_pop(explode('\\', get_class($this))).'/>');
        foreach ($this->first()->attributes as $key => $value) {
            if (is_numeric($key)) {
                $key = 'item' . $key; //dealing with <0/>..<n/> issues
            }
            if (is_array($value)) {
                $subnode = $xml->addChild($key);
                $this->array_to_xml($value, $subnode);
            } else {
                $xml->addChild("$key", htmlspecialchars("$value"));
            }
        }
        return $xml->asXML();
    }
}
