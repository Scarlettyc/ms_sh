<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class TrapMstModel extends Model
{
	protected $fillable = ['trap_id','trap_name','trap_icon','trap_charlet','eff_id','createdate'];

	protected $connection = 'mysql';
	protected $table = "Trap_mst";
}