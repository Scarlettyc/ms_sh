<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class BattleSpRewardsMst extends Model
{
	protected $fillable = ['br_id','map_id','ranking','lv','item_org_id','item_type','item_quantity','item_rate_from','item_rate_to','start_date','end_date','createdate','updated_at'];

	protected $connection = 'mysql';
	protected $table = "Battle_speical_rewards_mst";
}