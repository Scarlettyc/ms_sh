<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class UserFriendModel extends Model
{
     protected $fillable = ['friend_list_id', 'u_id', 'friend_u_id', 'friend_status','updatedate','createdate'];

     protected $connection='mysql';
     protected $table = "User_friend_list"; 
     
}
