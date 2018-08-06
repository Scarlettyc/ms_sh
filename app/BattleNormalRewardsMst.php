<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class BattleNormalRewardsMst extends Model
{
	protected $fillable = ['bn_id','map_id','lv','item_org_id','item_type','item_quantity','item_rate_from','item_rate_to','start_date','end_date','createdate','updated_at'];

	protected $connection = 'mysql';
	protected $table = "Battle_normal_rewards_mst";
}