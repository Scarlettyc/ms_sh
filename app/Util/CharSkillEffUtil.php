<?php
namespace App\Util;
use App\Http\Requests;
use App\CharacterModel;
use App\SkillMstModel;
use App\EffectionMstModel;
use App\AtkCircleEffModel;
use App\AtkRecEffModel;
use App\BuffEffModel;
use Exception;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\Redis;
use App\RaEffModel;

use DB;

class CharSkillEffUtil()
{

 	public function getCharSkill($char_id){
 		$atkCircle=new AtkCircleEffModel();
		$atkRec=new AtkRecEffModel();
		$buffEff=new BuffEffModel();
	 	$skill_data=DB::select('select c.* from  User_Character a, Equipment_mst b, Skill_mst c where b.equ_id in (a.w_id, a.m_id, a.core_id) and a.ch_id='.$char_id.' and c.skill_id= b.skill_id;')->get();
	 	$result=[];
	 	foreach ($skill_data as $key => $skill) {
	 		if($skill['self_buff_eff_id']!=0){
	 			$selfbuff=$buffEff->where('buff_eff_id',$skill['self_buff_eff_id'])->first();
	 		}
	 		if($skill['enemy_buff_eff_id']!=0){
	 			$enmeyBuff=$enemyBuffEff->where('buff_eff_id',$skill['enemy_buff_eff_id'])->first();
	 		}
	 		if($skill['atk_eff_id']!=0){
	 			if($eff_group_id==2){
	 				$atkEff=$atkRec->where('atk_re_eff_id',$skill['atk_eff_id'])->first();
	 			}
	 			else {
	 				$atkEff=$atkCircle->where('atk_circ_eff_id',$skill['atk_eff_id'])->first();
	 			}

	 		}
	 		$eff['self_buff']=$selfbuff;
	 		$eff['enemy_buff']=$enmeyBuff;
	 		$eff['atk_eff']=$atkEff;
	 		$result[]=$eff;
	 	}
	 	return $result;

	 }




}
