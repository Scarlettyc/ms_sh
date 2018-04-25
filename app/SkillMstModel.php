<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;
class SkillMstModel extends Model
{
	protected $fillable = [   'skill_id',
  'skill_group',
  'skill_code',
  'skill_name',
  'skill_icon',
  'skill_hurt_times',
  'skill_code',
  'skill_prepare_time',
  'skill_atk_time',
  'skill_info',
  'udpatedate',
  'createdate'];

	protected $connection='mysql';
	protected $table = "Skill_master_mst";
}