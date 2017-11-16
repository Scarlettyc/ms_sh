<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;
class CharacterModel extends Model
{
	protected $fillable = ['ch_id','ch_title','u_id','w_id','m_id','core_id','ch_lv','ch_exp','ch_star','ch_ranking','ch_hp_max','ch_shield','ch_stam','ch_atk','ch_armor','ch_res','ch_crit','ch_spd','ch_img','update_at','createdate'];

	protected $connection = 'mysql';
	protected $table = "User_Character";
	public function isExist($key,$chid){
        return $this->where($key,'=',$chid)->count();
     }
     public function updateValue($key,$data,$u_id){
        $now   = new DateTime;
     	$datetime=$now->format( 'Y-m-d h:m:s' );
     	return $this->where('u_id',$u_id)->update([$key=>$data,'update_at'=>$datetime]);

     }
}