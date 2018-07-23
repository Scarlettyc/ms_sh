<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class StoreReRewardModel extends Model
{
	protected $fillable = ['store_reward_id','max_times' ,'item_id' ,'item_type' ,'item_quantity' ,'first_gem_spend' ,'rate_from' ,'rate_to' ,'gem_increament' ,'start_date','end_date','updated_at','created_at'];
	protected $connection='mysql';
	protected $table = "Store_refresh_reward_mst";
}