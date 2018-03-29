<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;
class MissionListMstModel extends Model
{
	protected $fillable = ['mission_id' ,'mission_type' ,'user_lv_from','user_lv_to','ranking_from','ranking_to','times','description','start_date','end_date','createdate','updated_at'];

	protected $connection = 'mysql';
	protected $table = "Mission_List_mst";
	
}