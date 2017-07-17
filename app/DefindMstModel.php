<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;
class DefindMstModel extends Model
{
	protected $fillable = ['defind_id','value1','value2','comment','udpatedate','createdate'];

	protected $connection='mysql';
	protected $table = "Defind_mst";
}