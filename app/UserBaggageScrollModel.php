<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class UserBaggageScrollModel extends Model
{
	protected $fillable = ['user_sc_id','u_id','sc_id','sc_rarity','sc_icon','status','quantity','updated_at', 'created_at'];

	protected $connection = 'mysql';
	protected $table = "User_Baggage_Scroll";
}