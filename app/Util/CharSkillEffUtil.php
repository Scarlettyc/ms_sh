<?php
namespace App\Util;
use App\Http\Requests;
use App\CharacterModel;
use App\SkillMstModel;
use App\EffectionMstModel;
use App\AtkEffectionMst;
use App\AtkRecEffModel;
use App\BuffEffModel;
use App\CharAttModel;
use App\EqAttrmstModel;
use App\UserBaggageEqModel;
use App\EquipmentMstModel;
use App\DefindMstModel;
use Exception;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\Redis;
use App\RaEffModel;
use DB;

class CharSkillEffUtil
{

 	public function getCharSkill($char_id){
 		$atkEff=new AtkEffectionMst();
		$buffEff=new BuffEffectionMst();
	 	$skill_data=DB::select('select c.* from  User_Character a, Equipment_mst b, Skill_mst c where b.equ_id in (a.w_id, a.m_id, a.core_id) and a.ch_id='.$char_id.' and c.skill_id= b.skill_id;');
	 	$result=[];
	 	foreach ($skill_data as $key => $skill) {
	 		if($skill['self_buff_eff_id']!=0){
	 			$selfbuff=$buffEff->where('buff_eff_id',$skill['self_buff_eff_id'])->first();
	 		}
	 		if($skill['enemy_buff_eff_id']!=0){
	 			$enmeyBuff=$enemyBuffEff->where('buff_eff_id',$skill['enemy_buff_eff_id'])->first();
	 		}
	 		if($skill['atk_eff_id']!=0){
	 				$atkEff=$atkRec->where('atk_re_eff_id',$skill['atk_eff_id'])->first();
	 		}
	 		$eff['self_buff']=$selfbuff;
	 		$eff['enemy_buff']=$enmeyBuff;
	 		$eff['atk_eff']=$atkEff;
	 		$result[]=$eff;
	 	}
	 	return $result;

	 }

	 public function calculateCharAttr($u_id){
	 	$charModel=new CharacterModel();
	 	$charAttr=new CharAttModel();
	 	$eqAttr=new EqAttrmstModel();
	 	$eqModel=new EquipmentMstModel();
	 	$userEqModel=new UserBaggageEqModel();
	 	$defindModel=new DefindMstModel();
	 	$defindData=$defindModel->where('defind_id',20)->first();
	 	$charData=$charModel->where('u_id',$u_id)->first();
	 	$charAttData=$charAttr->where('ch_lv',$charData['ch_lv'])->first();
	 	$userEqData=$userEqModel->where('u_id',$u_id)->where('status',1)->first();
	 	$eqData=$eqModel->where('equ_id',$userEqData['b_equ_id'])->first();
	 	$eqAttData=$eqAttr->where('equ_att_id',$eqData['equ_attribute_id'])->first();
	 	$userStam=($charAttData['base_stamina']+$userEqData['eff_ch_stam']);
	 	$userHp=$userStam*$charAttData['stamina_hp_ratio']+$charData['ch_lv']*$defindData['value1']-$defindData['value2'];
	 	$userArmor=$charAttData['base_armor']+$eqAttData['eff_ch_armor'];
	 	$userAtk=$charAttData['base_atk']+$userEqData['eff_ch_armor'];
	 	$crit=$userEqData['eff_ch_crit_per'];
	 	$now   = new DateTime;
		$datetime=$now->format( 'Y-m-d h:m:s' );
	 	$charModel->update(['ch_hp_max'=>$userHp,'ch_stam'=>$userStam,'ch_atk'=>$userAtk,'ch_armor'=>$userArmor,'ch_crit'=>$crit,'update_at'=>$datetime]);


	 }




}
