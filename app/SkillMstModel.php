<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;
class SkillMstModel extends Model
{
	protected $fillable = [  'skill_id',
  'skill_group',
  'skill_code',
  'skill_name',
  'skill_icon',
  'skill_chartlet',
  'self_buff_eff_id',
  'buff_constant_time',
  'enemy_buff_eff_id',
  'enemy_buff_constant_time',
  'atk_eff_id',
  'atk_constant_time',
  'skill_cd',
  'skill_info',
  'createdate'];

	protected $connection='mysql';
	protected $table = "Skill_mst";
}