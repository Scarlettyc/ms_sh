<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class NationModel extends Model
{
     protected $fillable = ['n_id', 'u_id', 'nr_id', 'n_lv', 'n_output','n_defend','n_rarerate','createdate'];

     protected $connection='mysql';
     protected $table = "nation"; 
}
