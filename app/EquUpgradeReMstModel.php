<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class EquUpgradeReMstModel extends Model
{
	protected $fillable = ['equ_re_id','equ_code','upgrade_id','lv','r_id','r_quantity','updated_at','created_at'];

	protected $connection = 'mysql';
	protected $table = "Equ_Upgrade_Resource_mst";
}