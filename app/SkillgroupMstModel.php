<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class SkillgroupMstModel extends Model
{
	protected $fillable = ['skill_group_id','skillection_name','updated_at','created_at'];
	protected $connection = 'mysql';
	protected $table = "Skill_group_mst"
}