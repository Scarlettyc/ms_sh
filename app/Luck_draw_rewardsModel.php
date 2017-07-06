<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class Luck_draw_rewardsModel extends Model
{
     protected $fillable = ['lk_id','user_lv_from', 'user_lv_to', 'star', 'star_from','star_to', 'star_lv_from','star_lv_to','item_org_id','item_quantity','item_type','draw_coin','draw_gem','rate_from','rate_to','start_date','end_date','createdate'];

     protected $connection='mysql';
     protected $table = "Luck_draw_rewards_mst"; 

}
