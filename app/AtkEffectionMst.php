<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;
class AtkEffectionMst extends Model
{
	protected $fillable = ['atk_eff_id','eff_group_id','eff_name','eff_skill_radius','eff_skill_angle','eff_skill_lenght','eff_skill_width','eff_skill_atk_point','eff_skill_damage_point','eff_skill_turn','eff_skill_circle_center','eff_skill_move_distance','eff_skill_move_spd','eff_skill_delay_time','eff_skill_interrupt','eff_skill_follow_character','eff_bullet_radius','eff_bullet_angle','eff_bullet_lenght','eff_bullet_width','eff_bullet_spd','eff_bullet_distance','eff_atk_constant_time','eff_condtion','createdate',];
	protected $connection='mysql';
	protected $table = "Atk_effection_mst";
}