<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;
class MapModel extends Model
{
	protected $fillable = ['map_id','map_type','map_img_name','map_img_path','createdate'];

	protected $connection = 'mysql';
	protected $table = "Map_mst";
	
}