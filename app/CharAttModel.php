<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;
class CharAttModel extends Model
{
	protected $fillable = ['ch_lv','base_stamina','la_stamina','eff_armor','stamina_hp_ratio','base_armor','la_armor','base_atk','la_atk','effect_atk_ratio','createdate','updatedate'];
	protected $connection='mysql';
	protected $table = "Character_attribute_mst";
}