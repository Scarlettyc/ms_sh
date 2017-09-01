<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class LevelUPModel extends Model
{
	protected $fillable = ['level','exp','updatedate','createdate'];
	protected $connection = 'mysql';
	protected $table = "Level_up_mst";
}