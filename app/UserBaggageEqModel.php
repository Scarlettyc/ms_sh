<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class UserBaggageEqModel extends Model
{
	protected $fillable = ['user_beq_id','b_equ_id','b_equ_rarity','b_equ_type','b_icon_path','status','updatedate','createdate'];

	protected $connection = 'mysql';
	protected $table = "User_Baggage_Eq";
}