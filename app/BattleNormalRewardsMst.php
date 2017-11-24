<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class BattleNormalRewardsMst extends Model
{
	protected $fillable = ['bn_id','map_id','ranking','item_org_id','item_quantity','item_type','start_date','end_date','createdate'];

	protected $connection = 'mysql';
	protected $table = "Battle_normal_rewards_mst";
}