<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;
class AtkCircleEffModel extends Model
{
	protected $fillable = ['atk_circ_eff_id','eff_group_id','eff_name','eff_skill_radius','eff_skill_atk_angle','eff_skill_cd','eff_skill_atk_point','eff_skill_base_point','eff_skill_spd_per','eff_enemy_spd_per','eff_skill_def_per','eff_ch_invisible','eff_skill_stun','eff_skill_follow_character','eff_skill_dur','eff_skill_turn','eff_atk_constant_time','eff_bullet_width','eff_skill_stuck','eff_skill_circle_center','eff_skill_move_distance','eff_skill_delay_time','eff_bullet_spd','eff_bullet_distance','eff_skill_spd','eff_skill_interrupt','eff_invulnerability','eff_skill_jump_spd','eff_condtion','eff_description','createdate'];
	protected $connection='mysql';
	protected $table = "Atk_circle_effection_mst";
}