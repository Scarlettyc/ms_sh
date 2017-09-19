<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;
class AtkRecEffModel extends Model
{
	protected $fillable = ['atk_re_eff_id','eff_group_id','eff_name','eff_skill_spd_per','eff_skill_def_per','eff_ch_invisible','eff_skill_atk_point','eff_skill_base_point','eff_skill_stun','eff_skill_follow_character','eff_skill_dur','eff_skill_lenght','eff_skill_width','eff_skill_turn','eff_skill_interrupt','eff_skill_move_distance','eff_skill_cd','eff_skill_spd','eff_condtion','eff_description','createdate'];
	protected $connection='mysql';
	protected $table = "Atk_rectabngle_effection_mst";
}