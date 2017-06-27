<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class ResourceMstModel extends Model
{
     protected $fillable = ['r_id', 'r_name', 'r_rarity','createdate',];

     protected $connection='mysql';
     protected $table = "Rescource_mst"; 

}
