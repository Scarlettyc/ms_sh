<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class Login_rewardsModel extends Model
{
     protected $fillable = ['lr_id','days', 'item_id', 'item_quantity', 'item_type', 'description','start_date','end_date','createdate'];

     protected $connection='mysql';
     protected $table = "Login_rewards_mst"; 

}
