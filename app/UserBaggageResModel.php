<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class UserBaggageResModel extends Model
{
	protected $fillable = ['user_r_id','u_id','r_id','br_icon','r_rarity','r_type','r_quantity','status','updated_at','created_at'];

	protected $connection = 'mysql';
	protected $table = "User_Baggage_Res";
}