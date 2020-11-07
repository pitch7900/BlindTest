<?php

namespace App\Database;

use Illuminate\Database\Eloquent\Model;
use SimpleXMLElement;
/**
 * Class Track for Illuminate (DB) queries
 */
class User extends Model {
    public $timestamps = true;
    protected $table = 'user';
    protected $primaryKey = 'id';
    protected $fillable = ['id','nickname','email','emailchecklink','emailchecklinktimeout','emailchecked','resetpasswordlink','resetpasswordlinktimeout','password'];
       
}
