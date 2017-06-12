<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class NationResourceModel extends Model
{
     protected $fillable = ['nr_id', 'n_id', 'r_id', 'r_quantity', 'createdate'];

     protected $connection='mysql';
     protected $table = "nation_resource"; 
     
}
