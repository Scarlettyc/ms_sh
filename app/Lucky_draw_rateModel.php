<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class Lucky_draw_rateModel extends Model
{
     protected $fillable = ['lk_rate_id','lk_id' ,'draw_count','draw_type' ,'rate_from' ,'rate_to' ,'updated_at','created_at' ];

     protected $connection='mysql';
     protected $table = "Lucky_draw_rate_list"; 

}
