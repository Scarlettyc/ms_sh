<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class EquipmentMstModel extends Model
{
	protected $fillable = ['equ_id','equ_group' ,'equ_type','equ_code','equ_name','equ_rarity','equ_lv' ,'equ_att_id' ,'equ_part','upgrade_id' ,'upgrade_coin' ,'equ_price','icon_path','equ_description','createdate','updatedate'];

	protected $connection = 'mysql';
	protected $table = "Equipment_mst";
}