<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;
class MissionListMstModel extends Model
{
	protected $fillable = ['mission_reward_id', 'mission_id', 'item_org_id', 'item_quantity', 'item_rarilty', 'item_type', 'description', 'updated_at'];

	protected $connection = 'mysql';
	protected $table = "Mission_List_mst";
	
}