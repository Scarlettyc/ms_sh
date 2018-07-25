<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class Lucky_draw_rateModel extends Model
{
     protected $fillable = [ 'lk_rate_id','lk_id', 'draw_type' , 'draw_count', 'rate_from', 'rate_to', 'start_date' , 'end_date' , 'updatedate' , 'createdate' ];

     protected $connection='mysql';
     protected $table = "Lucky_draw_rate_list"; 

}
