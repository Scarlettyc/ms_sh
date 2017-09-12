<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class EffectionGroupMstModel extends Model
{
	protected $fillable = ['eff_group_id','effection_name','updated_at','created_at'];
	protected $connection = 'mysql';
	protected $table = "Effection_group_mst"
}