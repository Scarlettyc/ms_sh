<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class EffectionMstModel extends Model
{
	protected $fillable = ['eff_id','eff_ch_hp','eff_ch_hp_per','eff_ch_hp_max','eff_ch_hp_max_per','eff_ch_atk_max','eff_ch_atk_max_per','eff_ch_atk_min','eff_ch_atk_min_per','eff_ch_def','eff_ch_def_per','eff_ch_res_per','eff_ch_crit_per','eff_ch_cd','eff_ch_cd_per','eff_ch_spd_per','eff_ch_distance','eff_ch_stun','eff_ch_dur','eff_ch_exp','createdate'];

	protected $connection = 'mysql';
	protected $table = "Effection_mst";
}