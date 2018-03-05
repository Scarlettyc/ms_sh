<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;
class SkillEffModel extends Model
{
	protected $fillable = ['skill_eff_id', 'eff_id', 'eff_element_id', 'eff_name', 'eff_value', 'created_at', 'updated_at'];
	protected $connection='mysql';
	protected $table = "Skill_effects_mst";
}