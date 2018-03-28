<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class Lucky_draw_rateModel extends Model
{
     protected $fillable = [ 'lk_id', 'draw_type' , 'item_id', 'item_quantity', 'item_type', 'item_rarity', 'item_name', 'start_date' , 'end_date' , 'updatedate' , 'createdate' , ];

     protected $connection='mysql';
     protected $table = "Lucky_draw_rate_list"; 

}
