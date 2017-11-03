<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;
class EqAttrmstModel extends Model
{
	protected $fillable = ['equ_eff_id', 'eff_ch_stam', 'eff_ch_armor', 'eff_ch_def', 'eff_ch_crit_per', 'eff_ch_res_per', 'eff_ch_atk_spd', 'createdate'];
	protected $connection='mysql';
	protected $table = "Equipment_attribute_mst";
}