<?php

namespace App;
use DB;
use Carbon\Carbon;
use DateTime;
use CharacterModel;

use Illuminate\Database\Eloquent\Model;

class UserBaggageEqModel extends Model
{
	protected $fillable = ['user_beq_id','u_id','b_equ_id','b_equ_rarity','b_equ_type','b_icon_path','status','updated_at','created_at'];

	protected $connection = 'mysql';
	protected $table = "User_Baggage_Eq";

	public function equipNewEq($u_id,$equ_id,$w_bag_id,$b_equ_id){
		$now=new DateTime;
		$datetime=$now->format( 'Y-m-d h:m:s' );

		$this->where('u_id',$u_id)->where('status',1)->where('user_beq_id',$w_bag_id)->update(['status'=>0,'updated_at'=>$datetime]);
		$this->where('b_equ_id',$b_equ_id)->where('u_id',$u_id)->where('user_beq_id',$user_beq_id)->update(['status'=>1,'updated_at'=>$datetime]);
	}
}