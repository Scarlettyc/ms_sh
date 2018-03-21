<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;
class MissionRewardsModel extends Model
{
	protected $fillable = ['mission_reward_id', 'mission_id', 'item_id','item_equ_type', 'item_quantity', 'item_rarilty', 'item_type', 'description', 'updated_at'];

	protected $connection = 'mysql';
	protected $table = "Mission_rewards_mst";
	
}