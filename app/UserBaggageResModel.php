<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class UserBaggageResModel extends Model
{
	protected $fillable = ['user_br_id','br_id','br_icon','br_rarity','br_type','status','updatedate','createdate'];

	protected $connection = 'mysql';
	protected $table = "User_Baggage_Res";
}