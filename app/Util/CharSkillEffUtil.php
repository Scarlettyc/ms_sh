<?php
namespace App\Util;
use App\Http\Requests;
use App\CharacterModel;
use App\SkillMstModel;
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
use App\LevelUPModel;
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

	 public function validateToken($access_token,$u_id){
	 	    $redisLoad= Redis::connection('default');
        	$loginToday=$redisLoad->HGET('login_data',$dmy.$u_id);
       		$loginTodayArr=json_decode($loginToday,TRUE);
       		$access_token2=$loginTodayArr->access_token;
       		if($access_token==$access_token2){
       			return true;
       		}else{
       			throw new Exception("there is something wrong with token");
       		}

	 }

	 public function calculatCharEq($u_id){
	 	$charModel=new CharacterModel();
	 	$charAttr=new CharAttModel();
	 	$eqAttr=new EqAttrmstModel();
	 	$eqModel=new EquipmentMstModel();
	 	$userEqModel=new UserBaggageEqModel();
	 	$defindModel=new DefindMstModel();
	 	$defindData=$defindModel->where('defind_id',20)->first();
	 	$charData=$charModel->where('u_id',$u_id)->first();
	 	$charAttData=$charAttr->where('ch_lv',$charData['ch_lv'])->first();
	 	$asData=$eqModel->where('equ_id',$charData['core_id'])->where('equ_part',3)->first();
	 	$legData=$eqModel->where('equ_id',$charData['m_id'])->where('equ_part',2)->first();
	 	$eqData=$eqModel->where('equ_id',$charData['w_id'])->where('equ_part',1)->first();
	 	$eqAttData=$eqAttr->where('equ_att_id',$eqData['equ_attribute_id'])->first();
	 	$asAttData=$eqAttr->where('equ_att_id',$asData['equ_attribute_id'])->first();
	 	$legAttData=$eqAttr->where('equ_att_id',$legData['equ_attribute_id'])->first();

	 	$userStam=($charAttData['base_stamina']+$eqAttData['eff_ch_stam']+$asAttData['eff_ch_stam']+$legAttData['eff_ch_stam']);
	 	$userHp=$userStam*$charAttData['stamina_hp_ratio']+$charData['ch_lv']*$defindData['value1']-$defindData['value2'];
	 	$userArmor=$charAttData['base_armor']+$eqAttData['eff_ch_armor']+$legAttData['eff_ch_armor']+$asAttData['eff_ch_armor'];
	 	$userAtk=$charAttData['base_atk']+$eqAttData['eff_ch_atk']+$legAttData['eff_ch_atk']+$asAttData['eff_ch_atk'];
	 	$crit=$eqAttData['eff_ch_crit_per']+$asAttData['eff_ch_crit_per']+$legAttData['legAttData'];

	 	$now   = new DateTime;
		$datetime=$now->format( 'Y-m-d h:m:s' );
	 	$charModel->where('u_id',$u_id)->update(['ch_hp_max'=>$userHp,'ch_stam'=>$userStam,'ch_atk'=>$userAtk,'ch_armor'=>$userArmor,'ch_crit'=>$crit,'updated_at'=>$datetime]);
	 	$result['ch_hp_max']=$userHp;
	 	$result['ch_stam']=$userStam;
	 	$result['ch_atk']=$userAtk;
	 	$result['ch_armor']=$userArmor;
	 	$result['ch_crit']=$crit;
	 	return $result;
	 }
	 public function sendEq($u_id){
	 	$now   = new DateTime;
	 	$datetime=$now->format('Y-m-d h:m:s');
	 	$charModel=new CharacterModel();
	 	$userEqModel=new UserBaggageEqModel();
	 	$eqModel=new EquipmentMstModel();
	 	$core=$this->mapEQ($u_id,'CORE1',1);
	 	$coreID=$userEqModel->insertGetId($core);
	 	$leg=$this->mapEQ($u_id,'LEG1',1);
	 	$legID=$userEqModel->insertGetId($leg);
	 	$wepaon=$this->mapEQ($u_id,'A01',1);
	 	$weaponID=$userEqModel->insertGetId($wepaon);
	 	$backWeapon1=$this->mapEQ($u_id,'C01,',0);
	 	$backWeapon2=$this->mapEQ($u_id,'E01,',0);
	 	$backWeapon3=$this->mapEQ($u_id,'G01,',0);
	 	$userEqModel->insert($backWeapon1,$backWeapon2,$backWeapon3);
	 	$charModel->where('u_id',$u_id)->update(['w_id'=>$wepaon['equ_id'],'w_bag_id'=>$weaponID,'m_id'=>$leg['equ_id'],'m_bag_id'=>$legID,'core_id'=>$core,'core_bag_id'=>$coreID,'updated_at'=>$datetime]);

	 }
	 private function mapEQ($u_id,$code,$status){
	 	$eqData=$eqModel->where('equ_code',$code)->where('equ_lv',1)->first();
	 	$result['u_id']=$u_id;
	 	$result['b_equ_id']=$coreData['equ_id'];
	 	$result['b_equ_rarity']=$coreData['equ_rarity'];
	 	$result['b_icon_path']=$coreData['icon_path'];
	 	$result['status']=$status;
	 	$result['updated_at']=$datetime;
	 	$result['created_at']=$datetime;
	    return $result;	
	 }

	 public function  equipLaChar($u_id,$equ_id,$equ_part){ 
	 	$charModel=new CharacterModel();
	 	$charAttr=new CharAttModel();
	 	$eqAttr=new EqAttrmstModel();
	 	$eqModel=new EquipmentMstModel();
	 	$userEqModel=new UserBaggageEqModel();
	 	$defindModel=new DefindMstModel();
	 	$defindData=$defindModel->where('defind_id',20)->first();
	 	$charData=$charModel->where('u_id',$u_id)->first();

	 	$charAttData=$charAttr->where('ch_lv',$charData['ch_lv'])->first();
		if($equ_part=2){
		$asData=$eqModel->where('equ_id',$charData['core_id'])->where('equ_part',3)->where('status',1)->first();
	 		
	 	}
	 	$asData=$eqModel->where('equ_id',$charData['core_id'])->where('equ_part',3)->where('status',1)->first();
	 	$eqData=$eqModel->where('equ_id',$equ_id)->where('equ_part',1)->first();
	 	$eqAttData=$eqAttr->where('equ_att_id',$eqData['equ_attribute_id'])->first();
	 	$asAttData=$eqAttr->where('equ_att_id',$asData['equ_attribute_id'])->first();
	 	$legAttData=$eqAttr->where('equ_att_id',$asData['equ_attribute_id'])->first();


	 	$userStam=($charAttData['base_stamina']+$eqAttData['eff_ch_stam']);
	 	$userHp=$userStam*$charAttData['stamina_hp_ratio']+$charData['ch_lv']*$defindData['value1']-$defindData['value2']+$legAttData['eff_ch_hp']+$asData['eff_ch_hp'];
	 	$userArmor=$charAttData['base_armor']+$eqAttData['eff_ch_armor']+$legAttData['eff_ch_armor']+$asData['eff_ch_armor'];
	 	$userAtk=$charAttData['base_atk']+$eqAttData['eff_ch_atk']+$legAttData['eff_ch_atk']+$asData['eff_ch_atk'];
	 	$crit=round($eqAttData['eff_ch_crit_per']/100);
	 	$now   = new DateTime;
		$datetime=$now->format( 'Y-m-d h:m:s' );
	 	$charModel->update(['ch_hp_max'=>$userHp,'ch_stam'=>$userStam,'ch_atk'=>$userAtk,'ch_armor'=>$userArmor,'ch_crit'=>$crit,'update_at'=>$datetime])->where('u_id',$u_id);
	 	$result['ch_hp_max']=$userHp;
	 	$result['ch_stam']=$userStam;
	 	$result['ch_atk']=$userAtk;
	 	$result['ch_armor']=$userArmor;
	 	$result['ch_crit']=$crit;
	 	return $result;
	 }

  	public  function levelUP($u_id,$exp){
  		$now   = new DateTime;
	 	$datetime=$now->format('Y-m-d h:m:s');
  		$levelupMst=new LevelUPModel();
  		$baggageUtil=new BaggageUtil();
		$characterModel=new CharacterModel();
		$chaEffutil=new CharSkillEffUtil();
		$charData=$characterModel->select('ch_lv','ch_exp')->where('u_id',$u_id)->first();
		$lv=$charData['ch_lv'];
		$u_exp=$charData['ch_exp']+$exp;

  		$levels=$levelupMst->where('level','>',$lv)->where('exp_total','<=',$u_exp)->orderBy('level','DESC')->first();
  		if(isset($levels)){
  		 	$charData->where('u_id',$u_id)->update(['ch_lv'=>$levels['level'],'ch_exp'=>$u_exp,'update_at'=>$datetime]);
  		 	return ['levelup'=>1,'lv'=>$levels['level']];
  		}
  		else {
  			return ['levelup'=>0,'lv'=>$levels['level']];
  		}
  }
  	public function validateEq($ch_lv,$equ_rarity){
  		$defindMstModel=new DefindMstModel();
  		$standardData=$defindMstModel->select('value1','value2')->wherein('defind_id',[29,30,31])->get();
  		foreach ($standardData as $key => $rule) {
  			if($ch_lv>=$rule['value2']&&$equ_rarity==$rule['value1']){
  				return TRUE;
  				break;
  			}else{
  				throw new Exception("You cannot equip this Equipment", 1);
  				
  			}
  		}
  	}





}
