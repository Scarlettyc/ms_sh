<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class BuffEffModel extends Model
{
	protected $fillable = ['buff_eff_id','eff_group','eff_name','eff_skill_radius','eff_skill_angle','eff_ch_hp_max_shield','eff_skill_atk_point','eff_skill_base_point','eff_ch_hp','eff_ch_atk_per_self','eff_ch_atk_per_enemy','eff_ch_def_per','eff_ch_res_per','eff_ch_crit_per','eff_ch_stuck_self','eff_ch_stuck_enemy','eff_ch_spd_per_self','eff_ch_spd_per_enemy','eff_ch_clear_eff_self','eff_ch_clear_eff_enemy','eff_ch_hp_interval','eff_skill_dur_self','eff_skill_dur_enemy','eff_ch_uncontrollable','eff_ch_invincible','eff_skill_ch','eff_skill_move_distance','eff_skill_spd','eff_skill_x','eff_skill_y','eff_description','createdate'];

	protected $connection = 'mysql';
	protected $table = "Buff_effection_mst";
}