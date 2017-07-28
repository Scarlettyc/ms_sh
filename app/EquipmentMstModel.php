<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class EquipmentMstModel extends Model
{
	protected $fillable = ['equ_id','equ_name','eff_id','equ_rarity','skill_id','equ_part','equ_type','equ_lv','upgrade_id','equ_price','icon_path','equ_description','createdate','updatedate'];

	protected $connection = 'mysql';
	protected $table = "Equipment_mst";
}