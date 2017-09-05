<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class StoreGemRefreashMstModel extends Model
{
	protected $fillable = ['id_ref','gem'];
	protected $connection='mysql';
	protected $table = "Store_gem_refreash_mst";
}