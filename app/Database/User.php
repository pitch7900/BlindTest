<?php

namespace App\Database;

use Carbon\Carbon;
use App\Authentication\Auth;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Track for Illuminate (DB) queries
 */
class User extends AbstractModel {
    public $timestamps = true;
    protected $table = 'user';
    protected $primaryKey = 'id';
    protected $fillable = ['id','nickname','email','emailchecklink','emailchecklinktimeout','emailchecked','resetpasswordlink','resetpasswordlinktimeout','password','approvaleuuid','adminapproved','lastaction','darktheme'];
        
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
    
    /**
     * isDarkTheme - Tell if dark Theme is enabled for current user. Default is false
     *
     * @return bool
     */
    public static function isDarkTheme():bool
    {
        if (Auth::IsAuthentified()) {
            return boolval(User::find(Auth::CurrentUserID())->darktheme);
        } else {
            return false;
        }
    }
        
    /**
     * getUserName - reutrn user's id name properly formated for display
     *
     * @param  mixed $id
     * @return void
     */
    public static function getUserName($id)
    {
        if (!(is_null($id) || $id == -1)) {
            $user = User::find($id);
            return $user->firstname . " " . $user->lastname;
        } else {
            return "";
        }
    }

}
