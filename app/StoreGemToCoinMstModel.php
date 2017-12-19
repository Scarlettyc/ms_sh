<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class StoreGemToCoinMstModel extends Model
{
	protected $fillable = ['id','coin','gem','start_date','end_date','created_at','updated_at'];
	protected $connection='mysql';
	protected $table = "Store_gem_to_coin_mst";
}