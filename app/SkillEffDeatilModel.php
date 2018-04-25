<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;
class SkillEffDeatilModel extends Model
{
	protected $fillable = ['skill_eff_id', 'skill_id','eff_element_id', 'eff_value', 'eff_type','created_at', 'updated_at'];
	protected $connection='mysql';
	protected $table = "Skill_effects_details_mst";
}