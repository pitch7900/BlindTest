<?php

namespace App\Database;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Track for Illuminate (DB) queries
 */
class User extends Model {
    public $timestamps = true;
    protected $table = 'user';
    protected $primaryKey = 'id';
    protected $fillable = ['id','nickname','email','emailchecklink','emailchecklinktimeout','emailchecked','resetpasswordlink','resetpasswordlinktimeout','password','approvaleuuid','adminapproved'];
        
    /**
     * getNickName
     *
     * @param  mixed $userid
     * @return void
     */
    public static function getNickName($userid){
        return User::find($userid)->nickname;
    }

      
    /**
     * getCurrentUserTotalPoints
     *
     * @param  mixed $userid
     * @return int
     */
    public static function getCurrentUserTotalPoints(int $userid):int{
        return Game::where("userid","=",$userid)->sum("points");
    }
    
}
