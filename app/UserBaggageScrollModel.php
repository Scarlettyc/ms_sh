<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class UserBaggageScrollModel extends Model
{
	protected $fillable = ['user_bsc_id','u_id','bsc_id','bsc_rarity','bsc_icon','status','updated_at','created_at'];

	protected $connection = 'mysql';
	protected $table = "User_Baggage_Scroll";
}