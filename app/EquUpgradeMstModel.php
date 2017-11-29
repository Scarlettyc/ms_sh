<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class EquUpgradeMstModel extends Model
{
	protected $fillable = ['upgrade_id','equ_id','equ_upgrade_id','r_id_1','rd1_quantity','r_id_2','rd2_quantity','r_id_3','rd3_quantity','r_id_4','rd4_quantity','r_id_5','rd5_quantity','r_id_6','rd6_quantity','r_id_7','rd7_quantity','equ_coin','updated_at' ,'created_at'];

	protected $connection='mysql';
	protected $table = "Equ_Upgrade_mst";

}