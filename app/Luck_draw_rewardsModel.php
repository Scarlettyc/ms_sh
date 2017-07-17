<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class Luck_draw_rewardsModel extends Model
{
     protected $fillable = ['lk_id','user_lv_from', 'user_lv_to', 'draw_type','star_from','star_to', 'item_org_id','item_quantity','item_type','free_drwa_duration','draw_spend','rate_from','rate_to','start_date','end_date','updatedate','createdate'];

     protected $connection='mysql';
     protected $table = "Luck_draw_rewards_mst"; 

}
