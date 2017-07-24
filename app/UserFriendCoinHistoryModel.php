<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class UserFriendCoinHistoryModel extends Model
{
	protected $fillable = ['user_friend_coin_id','u_id','friend_u_id','fcoin_quanitty','fcoin_status','sent_dmy','received_dmy','updated_at','created_at'];

	protected $connection = 'mysql';
	protected $table = "User_Friend_Coin_History";
}