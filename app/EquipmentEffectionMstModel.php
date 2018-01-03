<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class EquipmentEffectionMstModel extends Model
{
	protected $fillable = ['equ_eff_id','eff_ch_hp_max','eff_ch_atk','eff_ch_def','eff_ch_crit_per','eff_ch_cd_per','eff_ch_spd_per','createdate'];
	protected $connection = 'mysql';
	protected $table = "Equipment_effection_mst";
}