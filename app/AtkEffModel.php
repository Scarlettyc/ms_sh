<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;
class AtkEffModel extends Model
{
	protected $fillable = ['atk_eff_id','eff_group_id','eff_name','eff_ch_spd_per','eff_ch_def_per','eff_ch_invisible','eff_skill_atk','eff_skill_stun','eff_skill_dur','eff_skill_radius','eff_skill_angle','eff_skill_turn','eff_bullet_width','eff_skill_stuck','eff_skill_move_distance','eff_skill_cd','eff_skill_spd','eff_invulnerability','eff_disable_stone_block','eff_condtion','eff_description','createdate'];
	protected $connection='mysql';
	protected $table = "atk_effection_mst";
}