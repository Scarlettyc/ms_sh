<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class NationBuildingsModel extends Model
{
     protected $fillable = ['u_id', 'b_id', 'b_x', 'b_y','createdate',];

     protected $connection='mysql';
     protected $table = "nation_buildings"; 

}
