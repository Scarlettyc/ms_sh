<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class BattleSpecialRewardsMst extends Model
{
	protected $fillable = ['bs_id','map_id','ranking','lv','item_org_id','item_quantity','item_type','item_rate_from','item_rate_to','start_date','end_date','createdate','udpated_at'];

	protected $connection = 'mysql';
	protected $table = "Battle_special_rewards_mst";
}