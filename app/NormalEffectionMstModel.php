<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;
class NormalEffectionMstModel extends Model
{
	protected $fillable = ['normal_eff_id','eff_group_id','eff_name','eff_ch_spd_per','eff_ch_def_per','eff_ch_invisible','eff_skill_atk','eff_ch_stun','eff_skill_dur','eff_skill_x','eff_skill_y','eff_skill_turn','eff_bullet_width','eff_skill_stuck','eff_skill_cd','eff_skill_spd','eff_description','createdate'];
	protected $connection='mysql';
	protected $table = "Normal_effection_mst";
}