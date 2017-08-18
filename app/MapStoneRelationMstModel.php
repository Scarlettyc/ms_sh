<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class MapStoneRelationMst extends Model
{
	protected $fillable = ['map_stone_id','map_id','stone_x','stone_y','createdate'];

	protected $connection = 'mysql';
	protected $table = "Map_Stone_Relation_mst";
}