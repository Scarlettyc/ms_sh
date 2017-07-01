<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;
class CharacterModel extends Model
{
	protected $fillable = ['ch_id','w_id_l','w_id_r','m_id','equ_id_1','equ_id_2','equ_id_3','b_id','ch_lv','ch_hp_max','ch_atk_max','ch_atk_min','ch_def','ch_res','ch_crit','ch_cd','ch_spd','ch-tp','createdate'];

	protected $connection = 'mysql';
	protected $table = "Character";
	public function isExist($key,$chid){
        return $this->where($key,'=',$chid)->count();
     }
}