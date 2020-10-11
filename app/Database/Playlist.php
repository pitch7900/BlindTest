<?php

namespace App\Database;

use Illuminate\Database\Eloquent\Model;
use SimpleXMLElement;
/**
 * Class Track for Illuminate (DB) queries
 */
class Playlist extends Model {
    public $timestamps = true;
    protected $table = 'playlist';
    protected $primaryKey = 'id';
    protected $fillable = ['id','playlist_title','playlist_link','playlist_picture'];
    
    
  

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
