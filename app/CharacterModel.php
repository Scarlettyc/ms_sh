<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;
class CharacterModel extends Model
{
	protected $fillable = ['ch_id','ch_title','u_id','w_id','m_id','ad_id','core_id','ch_lv','ch_star','ch_hp_max','ch_atk_max','ch_atk_min','ch_def','ch_res','ch_crit','ch_cd','ch_spd','ch_img','createdate'];

	protected $connection = 'mysql';
	protected $table = "Character";
	public function isExist($key,$chid){
        return $this->where($key,'=',$chid)->count();
     }
}