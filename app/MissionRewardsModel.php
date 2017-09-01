<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;
class MissionRewardsModel extends Model
{
	protected $fillable = ['misson_id','mission_type','user_lv_from','user_lv_to','star_from','star_to','item_org_id','item_quantity','item_type','start_date','end_date','createdate'];

	protected $connection = 'mysql';
	protected $table = "Mission_rewards_mst";
	
}