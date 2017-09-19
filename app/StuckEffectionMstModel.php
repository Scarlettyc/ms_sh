<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class StuckEffectionMstModel extends Model
{
	protected $fillable = ['stuck_eff_id','eff_name','eff_group','eff_ch_spd_per','eff_ch_distance','eff_ch_stun','eff_ch_stuck','eff_skill_dur','eff_skill_atk','eff_skill_x','eff_skill_y','eff_skill_loading','eff_skill_turn','eff_bullet_width','eff_invulnerability','eff_skill_cd','eff_skill_spd','eff_description','createdate'];
	protected $connection = 'mysql';
	protected $table = "Stuck_effection_mst";
}