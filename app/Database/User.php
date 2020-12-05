<?php

namespace App\Database;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * Class Track for Illuminate (DB) queries
 */
class User extends Model {
    public $timestamps = true;
    protected $table = 'user';
    protected $primaryKey = 'id';
    protected $fillable = ['id','nickname','email','emailchecklink','emailchecklinktimeout','emailchecked','resetpasswordlink','resetpasswordlinktimeout','password','approvaleuuid','adminapproved','lastaction'];
        
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
     * isOnline - Return true if user seems to be online
     *
     * @param  mixed $userid
     * @return bool
     */
    public static function isOnline($userid):bool{
        $lastaction = Carbon::createFromFormat(Carbon::DEFAULT_TO_STRING_FORMAT, USER::find($userid)->lastaction);
        $timetocompare =  Carbon::createFromTimestamp(time()-15);
        if ($lastaction->gte($timetocompare)) {
            return true;
        }
        return false;
    }
    /**
     * getUserTotalPoints
     *
     * @param  mixed $userid
     * @return int
     */
    public static function getUserTotalPoints(int $userid):int{
        return Game::where("userid","=",$userid)->sum("points");
    }
    
}
