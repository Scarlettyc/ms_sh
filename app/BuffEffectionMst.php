<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;
class BuffEffectionMst extends Model
{
	// protected $fillable = ['eff_id','eff_buff_type','eff_ch_hp' ,'eff_ch_shield' ,'eff_ch_hp_interval','eff_ch_hp_per','eff_ch_atk_per','eff_ch_def_per','eff_ch_res_per','eff_ch_crit_per','eff_ch_cd','eff_ch_spd_per','eff_ch_stun','eff_ch_stuck','eff_ch_uncontrollable','eff_ch_invincible','eff_ch_knockup','eff_ch_clear_buff','eff_ch_rebound','eff_ch_interrupt','eff_ch_condition','eff_duration','createdate'];
		protected $fillable = ['eff_id','eff_buff_type','eff_value1','eff_value2','eff_value3','eff_duration','createdate','udpated_at'];
	protected $connection='mysql';
	protected $table = "Buff_effection_mst";
}