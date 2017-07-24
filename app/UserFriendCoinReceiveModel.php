<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class UserFriendCoinReceiveModel extends Model
{
	protected $fillable = ['user_f_coin_receive_id','u_id','friend_u_id','rcoin_quanitty','rcoin_status','sent_dmy','received_dmy','updated_at','created_at'];

	protected $connection = 'mysql';
	protected $table = "User_Friend_Coin_Receive_History";
}