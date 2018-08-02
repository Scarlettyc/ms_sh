<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;
class EquipLVLimitMstModel extends Model
{
	protected $fillable = ['ch_lv','equ_rarity','comment','udpatedate','createdate'];

	protected $connection='mysql';
	protected $table = "Equipment_level_limit_mst";
}