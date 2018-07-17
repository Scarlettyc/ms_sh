<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class MapTrapRelationMst extends Model
{
	protected $fillable = ['map_trap_id','map_id','trap_id','x','y','created_at','updated_at'];

	protected $connection = 'mysql';
	protected $table = "Map_Trap_Relation_mst";
}