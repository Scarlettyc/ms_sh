<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;
class RaEffModel extends Model
{
	protected $fillable = ['radiation_eff_id','eff_group_id','eff_skill_name','eff_skill_atk','eff_ch_crit_per','eff_ch_spd_per','eff_ch_distance','eff_ch_stun','eff_ch_stuck','eff_skill_dur','eff_shake_frequency','eff_shake_round','eff_skill_x_left','eff_skill_x_right','eff_skill_y_top','eff_skill_y_down','eff_skill_combo','eff_buff_time','eff_bullet_width','eff_invulnerability','eff_skill_cd','eff_skill_spd','eff_description','createdate'];

	protected $connection='mysql';
	protected $table = "Radiation_effection_mst";
}