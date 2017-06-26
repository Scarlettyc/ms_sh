<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class EquipmentMstModel extends Model
{
	protected $fillable = ['u_id','equ_icon','eff_id','equ_chartlet','equ_type','r_id_1','r_id_2','r_id_3','createdate'];

	protected $connection = 'mysql';
	protected $table = "Equipment_mst";
}