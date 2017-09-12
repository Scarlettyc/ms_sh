<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class BuffEffModel extends Model
{
	protected $fillable = ['eff_id','eff_ch_hp','eff_ch_hp_per','eff_ch_hp_max','eff_ch_hp_max_per','eff_ch_atk','eff_ch_atk_per','eff_ch_def','eff_ch_def_per','eff_ch_res_per','eff_ch_crit_per','eff_ch_cd','eff_ch_cd_per','eff_ch_spd_per','eff_ch_distance','eff_ch_stun','eff_ch_dur','eff_ch_exp','eff_skill_x','eff_skill_y','eff_bullet_width','eff_skill_cd','eff_skill_spd','eff_description','createdate'];

	protected $connection = 'mysql';
	protected $table = "Buff_effection_mst";
}