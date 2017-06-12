<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class BuildingsMstModel extends Model
{
     protected $fillable = ['b_id', 'map_ud', 'e_id','b_szie','btype_id','b_timecost','b_lv','b_exp','b_n_lv','b_img','createdate'];

     protected $connection='mysql';
     protected $table = "buildings_mst"; 

}
