<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\UserModel;
use App\MatchRangeModel;
use App\CharacterModel;
use App\SkillMstModel;
use App\DefindMstModel;
use App\BuffEffMstModel;
use App\NormalEffectionMstModel;
use App\MapTrapRelationMst;
use App\Util\DistanceAttackUtil;
use Illuminate\Support\Facades\Redis;
use App\EquipmentMstModel;
use App\EqAttrmstModel;
use App\BattleNormalRewardsMst;
use App\BattleSpRewardsMst;
use App\BattleRewardExpModel;
use App\LevelUPModel;
use App\Util\BaggageUtil;
use App\Util\AttackHitUtil;
use App\Util\CharSkillEffUtil;
use DateTime;
use Exception;
use Math;
use Log;
class BattleController extends Controller
{

	// public function realbattle($data,$clientInfo){
	// 	$now   = new DateTime;;
	// 	$dmy=$now->format( 'Ymd' );
	// 	$x=$data['x'];
	// 	$y=$data['y'];
	// 	$u_id=$data['u_id'];
	// 	$move=$data['move'];//status of user run 2 or stand by 1
	// 	$characterModel=new CharacterModel();
	// 	$skillModel=new SkillMstModel();
	// 	$attackhitutil=new AttackHitUtil();
	// 	$redis_battle=Redis::connection('battle');
		
	// 	$matchKey='battle_status'.$dmy;
	// 	$battle_status=$redis_battle->HGET($matchKey,$u_id);
	// 	$battleData=json_decode($battle_status,TRUE);
	// 	if($battleData){
	// 		$enemy_uid=$battleData['enemy_uid'];
	// 		$match_id=$battleData['match_id'];
	// 		$clientId=$battleData['client'];
	// 		$map_id=$battleData['map_id'];
	// 		$enemy_clientId=$battleData['enmey_client'];
	// 		$battlekey='battle_data'.$match_id.'_'.$u_id;
	// 		$userExist=$redis_battle->LLEN($battlekey);
		
	// 		$charData=[];
	// 		if($userExist<1){
	// 			$charData=$characterModel->select('ch_hp_max','ch_stam','ch_atk','ch_armor','ch_crit','charData['ch_lv']','ch_ranking')->where('u_id',$u_id)->first();
	// 		}
	// 		else{
	// 			$userJson=$redis_battle->LRANGE($battlekey,0,0);
	// 			foreach ($userJson as $key => $each) {
	// 				$userData=json_decode($each,TRUE);
	// 				$charData['ch_ranking']=$userData['ch_ranking'];
	// 				$charData['ch_hp_max']=$userData['ch_hp_max'];
	// 				$charData['ch_stam']=$userData['ch_stam'];
	// 				$charData['ch_atk']=$userData['ch_atk'];
	// 				$charData['ch_crit']=$userData['ch_crit'];
	// 				$charData['ch_armor']=$userData['ch_armor'];
	// 				$charData['charData['ch_lv']']=$userData['charData['ch_lv']'];
	// 				$skill=$each['skill'];

	// 				if(isset($each['constant_eff'])){
	// 					foreach ($each['constant_eff'] as $key => $eff) {
					
	// 							$check=$attackhitutil->haveEffConstant($eff,$skill['occur_time']);
	// 							if($check){
	// 								if(isset($check['self_buff_eff_id'])){
	// 									$buffResult=$attackhitutil->buffStatus($check['self_buff_eff_id']);
	// 								}
	// 								$charData['constant_eff'][]=$check;
	// 								}							}
	// 				$charData['charData['ch_lv']']=$userData['charData['ch_lv']'];
	// 				$charData['ch_ranking']=$userData['ch_ranking'];
	// 			}
		
	// 		}
	// 	}
		
	// 	$charData['x']=$x;		
	// 	$charData['y']=$y;
	// 	$charData['time']=time();
	// 	$charData['address']=$clientInfo['address'];
	// 	$charData['port']=$clientInfo['port'];
	// 	$charData['direction']=1;
	// 	$charData['move']=$move;
	// 	$user_res=1;
	// 	$charData['ch_lv']=$charData['charData['ch_lv']'];
	// 	$charData['ch_ranking']=$charData['ch_ranking'];
	// 	if(isset($data['direction'])){
	// 		$charData['direction']=$data['direction'];
	// 	}
	// 	if(isset($data['skill_id'])){
	// 		$skill=$skillModel->select('skill_id','skill_group','skill_cd')->where('skill_id',$data['skill_id'])->first();

	// 		$checkCD=$this->checkSkillCD($skill,$match_id,$u_id);
	// 		if($checkCD){
	// 			$charData['skill']['skill_id']=$data['skill_id'];
	// 			$charData['skill']['skill_group']=$skill['skill_group'];
	// 			$charData['skill']['occur_time']=time();
	// 			$skillConstant=$attackhitutil->checkEffConstant($data['skill_id'],$data['x']);
	// 			if($skillConstant){
	// 				$charData['constant_eff'][]=$skillConstant;
	// 			}
	// 		}
	// 	}
		
	// 	$enemykey='battle_data'.$match_id.'_'.$enemy_uid;
	// 	$enemyJson=$redis_battle->LRANGE($enemykey,0,0); 
	// 	// Log::info($data);
	// 	$enemy_charData=$characterModel->select('ch_hp_max','ch_stam','ch_atk','ch_armor','ch_crit')->where('u_id',$enemy_uid)->first();
	// 	if(is_null($enemyJson)){
	// 		$enemy_charData['x']=-1000;
	// 		$enemy_charData['y']=-290;
	// 	}
	// 	else {
	// 			foreach ($enemyJson as $key => $each) 
	// 			{
	// 		  		$enmeyData=json_decode($each,TRUE);
	// 		   		$enemy_charData['x']=$enmeyData['x'];
	// 				$enemy_charData['y']=$enmeyData['y'];
	// 				$enemy_charData['ch_hp_max']=$enmeyData['ch_hp_max'];
	// 				$enemy_charData['ch_stam']=$enmeyData['ch_stam'];
	// 				$enemy_charData['ch_atk']=$enmeyData['ch_atk'];
	// 				$enemy_charData['ch_crit']=$enmeyData['ch_crit'];
	// 				$enemy_charData['direction']=$enmeyData['direction'];
	// 				$enemy_charData['move']=$enmeyData['move'];
	// 			if(isset($enmeyData['skill']))
	// 			{		$enemySkill['skill_id']=$enmeyData['skill_id'];
	// 					$enemySkill['skill_group']=$enmeyData['skill_group'];
	// 					if(isset($enemySkill['constant_eff'])){
	// 						$effResult=$attackhitutil->getconstantEff($enemySkill['skill_id'],$enemySkill['occur_time'],$charData,$enemy_charData,$clientId,$enemy_clientId,$charData['direction'],$enemy_charData['direction'],$enemySkill['constant_eff']);
							
	// 					}else{
	// 						$effResult=$attackhitutil->getatkEff($enemySkill['skill_id'],$charData,$enemy_charData,$clientId,$enemy_clientId,$charData['direction'],$enemy_charData['direction']);

	// 					}

	// 				if($effResult){ 
	// 					if(isset($effResult['enemy_buff'])){
	// 						if(!isset($buffResult['eff_ch_uncontrollable'])||!isset($buffResult['eff_ch_invincible'])){
	// 							$this->enemyBuffEff($charData['ch_hp_max'],$effResult['enemy_buff']);
	// 						}

	// 					}

	// 					if(isset($effResult['atkEff'])){
	// 						$enemy_atk=$enemy_charData['ch_atk'];
	// 						$randCrit=rand(1,100);
	// 						if($randCrit<=$enemy_charData['ch_crit']){
	// 							$critBool=2;
	// 							}else{
	// 								$critBool=1;
	// 						}
	// 						$user_def=($chardata['ch_armor']*1.1)/(15*$charData['charData['ch_lv']']+$chardata['ch_armor']+40);

	// 						if(isset($buffResult['eff_ch_res_per'])){
	// 							$user_res=$buffResult['eff_ch_res_per'];
	// 						}
	// 						if($enemy_charData['skill_group']==1){
	// 							$enemy_atk=$enemy_charData['ch_atk']*$atkeff['eff_skill_atk_point']*$user_res;
	// 							$enemyDMG=$enemy_atk*$critBool;
	// 							$hpMax=$charData['ch_hp_max']/(1-$user_def);
	// 							$charData['ch_hp_max']=round($hpMax-$enemyDMG);
 // 	 						}

 // 	 						else if($enemy_charData['skill_group']==2){
 // 	 							$enemy_atk=$enemy_charData['ch_atk']*$atkeff['eff_skill_atk_point']+pow($enemy_charData['charData['ch_lv']'],2)*2;
 // 	 							$enemyDMG=$enemy_atk*$critBool;
 // 	 							$hpMax=$charData['ch_hp_max'];
	// 							$charData['ch_hp_max']=round($hpMax-$enemy_atk);

	// 						}
	// 					}
	// 				}
	// 		}
	// 	  }
	// 	}
	// 		$enemy_charData['time']=time();
	// 		// $enemyJson=json_decode($enemy_charData,TRUE);
	// 	$result['user_data']=$charData;
	// 	if($clientId>$enemy_clientId){
	// 		$enemy_charData['x']=-($enemy_charData['x']);
	// 		$enemy_charData['direction']=-($enemy_charData['direction']);
	// 	}
	// 	$result['enemy_data']=$enemy_charData;
	// 	 if($enemy_charData['ch_hp_max']<0){
	// 		$result['end']=2;
	// 		$win=1;
	// 		$this->BattleRewards($u_id,$map_id,$win,$match_id,$charData['ch_lv']);
	// 	}
	// 	else if($charData['ch_hp_max']<=0){
	// 		$result['end']=1;
	// 		$win=0;
	// 		$this->BattleRewards($u_id,$map_id,$match_id,$win,$charData['ch_lv'],$charData['ch_ranking']);
	// 	}
	// 	else {
	// 		$result['end']=0;
	// 	}
	// 	if($clientId>$enemy_clientId){
	// 		$charData['x']=-$charData['x'];
	// 		$charData['direction']=-$charData['direction'];
	// 	}
	// 	$charData['end']=$result['end'];
	// 	$charJson=json_encode($charData);
	// 	$redis_battle->LPUSH($battlekey,$charJson);
	// 	$response=json_encode($result,TRUE);
	// 	return  $response;
	// 	}
	// 	else {
	// 		return null;
	// 	}
	// }
	/* 2018.04.09 edition*/
	public function battleTest($data,$clientInfo){
		$now   = new DateTime;
		$dmy=$now->format( 'Ymd' );
		$x=$data['x'];
		$y=$data['y'];
		$x2=$data['x2'];
		$y2=$data['y2'];
		$u_id=$data['u_id'];
		$move=$data['move'];//status of user run 2 or stand by 1

		$characterModel=new CharacterModel();
		$skillModel=new SkillMstModel();
		$attackhitutil=new AttackHitUtil();
		$redis_battle=Redis::connection('battle');
		$matchKey='battle_status'.$dmy;
		$battle_status=$redis_battle->HGET($matchKey,$u_id);
		$battleData=json_decode($battle_status,TRUE);
		$enemy_uid=$battleData['enemy_uid'];
		$match_id=$battleData['match_id'];
		$clientId=$battleData['client'];
		$map_id=$battleData['map_id'];
		$battlekey='battle_data'.$match_id.'_'.$u_id;
		$enemy_clientId=$battleData['enmey_client'];
		$charData=$this->mapingData($match_id,$u_id,1,$x,$y);
		$charData['x']=$x;		
		$charData['y']=$y;
		$charData['time']=time();
		$charData['address']=$clientInfo['address'];
		$charData['port']=$clientInfo['port'];
		$charData['direction']=1;
		$charData['move']=$move;
		$user_res=1;

		if(isset($data['direction'])){
			$charData['direction']=$data['direction'];
		}
		if(isset($data['skill_id'])){
			$skill=$skillModel->select('skill_id','skill_group','skill_cd')->where('skill_id',$data['skill_id'])->first();

			$checkCD=$this->checkSkillCD($skill,$match_id,$u_id);
			if($checkCD){
				$charData['skill']['skill_id']=$data['skill_id'];
				$charData['skill']['skill_group']=$skill['skill_group'];
				$charData['skill']['occur_time']=time();
				$charData['skill']['start_x']=$x;
				$skillConstant=$attackhitutil->checkEffConstant($data['skill_id'],$data['x']);
				if($skillConstant){
					$charData['eff_list'][]=$skillConstant;
				}
			}
		}
		$enemyData=$this->mapingData($match_id,$enemy_uid,2,$x,$y);

		if($clientId<$enemy_clientId){
			$enemyData['x']=-($enemyData['x']);
			$enemyData['direction']=-($enemyData['direction']);
		}else{
			$enemyData['x']=-($enemyData['x']);
			$enemyData['direction']=-($enemyData['direction']);
			$charData['x']=-($charData['x']);
			$charData['direction']=-($charData['direction']);
		}


		if(isset($enemyData['skill'])){
		$hit=$attackhitutil->checkSkillHit($enemyData['skill'],$x,$y,$enemyData['x'],$enemyData['y']);
		if($hit){
			$skillatkEff=$attackhitutil->getEffValue($hit);
			$charData=$attackhitutil->calculateCharValue($charData,$enemyData,$skillatkEff);
			}
		}
		$result['user_data']=$charData;
		$result['enemy_data']=$enemyData;
		 if($enemyData['ch_hp_max']<0){
			$result['end']=2;
			$win=1;
			$this->BattleRewards($u_id,$map_id,$win,$match_id,$charData['ch_lv']);
		}
		else if($charData['ch_hp_max']<=0){
			$result['end']=1;
			$win=0;
			$this->BattleRewards($u_id,$map_id,$match_id,$win,$charData['ch_lv'],$charData['ch_ranking']);
		}
		else {
			$result['end']=0;
		}
		$charData['end']=$result['end'];
		if($clientId>$enemy_clientId){
			$charData['x']=-($charData['x']);
			$charData['direction']=-($charData['direction']);
		}
		$charJson=json_encode($charData);
		$redis_battle->LPUSH($battlekey,$charJson);
		$response=json_encode($result,TRUE);
		return  $response;
	}

/*2018.04.19 edition*/
public function battleNew($data,$clientInfo){
		$now   = new DateTime;
		$dmy=$now->format( 'Ymd' );
		$x=$data['x'];
		$y=$data['y'];
		$x2=$data['x2'];
		$y2=$data['y2'];
		$u_id=$data['u_id'];
		$status=$data['status'];//status of user in battle
		$characterModel=new CharacterModel();
		$skillModel=new SkillMstModel();
		$attackhitutil=new AttackHitUtil();
		$redis_battle=Redis::connection('battle');
		$matchKey='battle_status'.$dmy;
		$battle_status=$redis_battle->HGET($matchKey,$u_id);
		$battleData=json_decode($battle_status,TRUE);
		$enemy_uid=$battleData['enemy_uid'];
		$match_id=$battleData['match_id'];
		$clientId=$battleData['client'];
		$map_id=$battleData['map_id'];
		$battlekey='battle_data'.$match_id.'_'.$u_id;
		$enemy_clientId=$battleData['enmey_client'];
		$charData=$this->mapingData($match_id,$u_id,1,$x,$y);
		$charData['x']=$x;		
		$charData['y']=$y;
		$charData['time']=time();
		$charData['address']=$clientInfo['address'];
		$charData['port']=$clientInfo['port'];
		$charData['direction']=1;
		$charData['status']=$status;
		$charData['x2']=$data['x2'];
		$charData['y2']=$data['y2'];
		$user_res=1;
		if(isset($data['direction'])){
			$charData['direction']=$data['direction'];
		}
		if(isset($data['skill_id'])){
			$skill=$skillModel->select('skill_id','skill_group','skill_cd','skill_damage','skill_name','skill_prepare_time','skill_atk_time')->where('skill_id',$data['skill_id'])->first();
			$checkCD=$this->checkSkillCD($skill,$match_id,$u_id);
			if($checkCD>0){
				$possbileSkill=$this->checkNormalSkill($skill['skill_group'],$skill['skill_name'],$skill['skill_prepare_time'],$skill['skill_atk_time']);
				if($possbileSkill){
					$charData['skill']['skill_id']=$data['skill_id'];
					$charData['skill']['skill_group']=$skill['skill_group'];
					$charData['skill']['occur_time']=time();
					$charData['skill']['start_x']=$x;
					$charData['skill']['skill_damage']=$skill['skill_damage'];
					$charData['skill']['skill_prepare_time']=$skill['skill_prepare_time'];
					$charData['skill']['skill_atk_time']=$skill['skill_atk_time'];
				}
			}
		}
		$enemyData=$this->mapingData($match_id,$enemy_uid,2,$x,$y);

		if($clientId<$enemy_clientId){
			$enemyData['x']=-($enemyData['x']);
			$enemyData['direction']=-($enemyData['direction']);
		}else{
			$enemyData['x']=-($enemyData['x']);
			$enemyData['direction']=-($enemyData['direction']);
			$charData['x']=-($charData['x']);
			$charData['direction']=-($charData['direction']);
		}

		if(isset($enemyData['skill'])){
		$hit=$attackhitutil->checkSkillHit($enemyData['skill'],$x,$y,$enemyData['x'],$enemyData['y']);
		if($hit){
			$skillatkEff=$attackhitutil->getEffValue($enemyData['skill']['skill_id']);
			$charData=$attackhitutil->calculateCharValue($charData,$enemyData,$skillatkEff);
			}
		}
		$result['user_data']=$charData;
		$result['enemy_data']=$enemyData;
		 if(isset($enemyData['ch_hp_max'])&&$enemyData['ch_hp_max']<0){
			$result['end']=2;
			$win=1;
			$this->BattleRewards($u_id,$map_id,$win,$match_id,$charData['ch_lv']);
		}
		else if($charData['ch_hp_max']<=0){
			$result['end']=1;
			$win=0;
			$this->BattleRewards($u_id,$map_id,$match_id,$win,$charData['ch_lv'],$charData['ch_ranking']);
		}
		else {
			$result['end']=0;
		}
		$charData['end']=$result['end'];
		if($clientId>$enemy_clientId){
			$charData['x']=-($charData['x']);
			$charData['direction']=-($charData['direction']);
		}
		$charJson=json_encode($charData);
		$redis_battle->LPUSH($battlekey,$charJson);
		$response=json_encode($result,TRUE);
		return  $response;

}
	private function checkNormalSkill($skill_group,$skill_name,$skill_prepare_time,$skill_atk_time){
		$skillModel=new SkillMstModel();
		if($skill_group==1&&strpos($skill_name,'b')){
			$skill_before=$skillModel->select('skill_id','skill_group','skill_cd','skill_name','skill_prepare_time','skill_atk_time')->where('skill_group',$skill_group)->where('skill_name','like','%-a%')->first();
			$skill_key='skill_'.$match_id.'_'.$u_id;
			$skillTime=$redis_battle->HGET($skill_key,$skill_id);
			$current=$this->getMillisecond();
			if($skillTime-$current<=$skill_before['skill_prepare_time']+$skill_before['skill_atk_time']){
				return TRUE;
			}
			else{
				return false;
			}
		}
		else if($skill_group==1&&strpos($skill_name,'c')){
			$skill_before=$skillModel->select('skill_id','skill_group','skill_cd','skill_name','skill_prepare_time','skill_atk_time')->where('skill_group',$skill_group)->where('skill_name','like','%-b%')->first();
			$skill_key='skill_'.$match_id.'_'.$u_id;
			$skillTime=$redis_battle->HGET($skill_key,$skill_id);
			$current=$this->getMillisecond();
			if($skillTime-$current<=$skill_before['skill_prepare_time']+$skill_before['skill_atk_time']){
				return TRUE;
			}
			else{
				return false;
			}
		}
		else {
			return TRUE;
		}
	}

	private function mapingData($match_id,$u_id,$identity,$x,$y){
		$characterModel=new CharacterModel();
		$redis_battle=Redis::connection('battle');
		$battlekey='battle_data'.$match_id.'_'.$u_id;
		$userExist=$redis_battle->LLEN($battlekey);
		$charData=[];
		if($userExist<1){
			$charData=$characterModel->select('ch_hp_max','ch_stam','ch_atk','ch_armor','ch_crit','ch_lv','ch_ranking')->where('u_id',$u_id)->first();
			if($identity==2){
				$charData['x']=-1000;
				$charData['y']=-290;
				$charData['direction']=1;
			}
		}
		else{
			$userJson=$redis_battle->LRANGE($battlekey,0,0);
				foreach ($userJson as $key => $each) {
					$userData=json_decode($each,TRUE);
					$charData['ch_ranking']=$userData['ch_ranking'];
					$charData['ch_hp_max']=$userData['ch_hp_max'];
					$charData['ch_stam']=$userData['ch_stam'];
					$charData['ch_atk']=$userData['ch_atk'];
					$charData['ch_crit']=$userData['ch_crit'];
					$charData['ch_armor']=$userData['ch_armor'];
					$charData['ch_lv']=$userData['ch_lv'];
					if($identity==1){
					$charData['x']=$x;
					$charData['y']=$y;
					}else{
						$charData['x']=$userData['x'];
						$charData['y']=$userData['y'];
					}
					if(isset($userData['eff_list'])){
						$charData['eff_list']=$userData['eff_list'];
					}
					if(isset($userData['status'])){
						$charData['status']=$userData['status'];
					}
					if(isset($userData['direction'])){
						$charData['direction']=$userData['direction'];
					}				
				}
		}
		return $charData;
	}

	private function getMillisecond() {
		list($t1, $t2) = explode(' ', microtime());     
		return (float)sprintf('%.0f', (floatval($t1) + floatval($t2)) * 1000);  
	}

  private function BattleRewards($u_id,$map_id,$match_id,$win,$ch_lv,$ch_ranking){
		  	$baNorReward=new BattleNormalRewardsMst();
		  	$baSpReward=new BattleSpRewardsMst();
		  	$battleRewardExpModel=new BattleRewardExpModel();
		  	$chaEffutil=new CharSkillEffUtil();
		  	$UserModel=new UserModel();
		  	$now   = new DateTime;
		  	$defindModel=new DefindMstModel();
			$dmy=$now->format( 'Ymd' );
			$defindData=$defindModel->where('defind_id',27)->first();
		  	$datetime=$now->format('Y-m-d h:m:s');
			$redis_battle=Redis::connection('battle');
			$battle_reward=$battleRewardExpModel->select('exp','coin','loots_normal','loots_special')->where('lv',$ch_lv)->where('win',$win)->where('ranking',$ch_ranking)->where('start_date','<',$datetime)->where('end_date','>',$datetime)->first();
			$loots_normal=$battle_reward['loots_normal'];
			$loots_special=$battle_reward['loots_special'];
			$rewards=[];
			$result=[];
			for($i=0;$i<$loots_normal;$i++){
				$rate=rand($defindData['value1'], $defindData['value2']);
		  		$norReward=$baNorReward->select('item_org_id','item_type','item_quantity')->where('map_id',$map_id)->where('ranking',$ch_ranking)->where('lv',$ch_lv)->where('start_date','<',$datetime)->where('end_date','>',$datetime)->where('item_rate_from','<=',$rate)->where('item_rate_to','>=',$rate)->first();
		  		$rewards['normarl'][]=$norReward;
			}
			for($j=0;$j<$loots_special;$j++){
				$rate=rand($defindData['value1'], $defindData['value2']);
		  		$spReward=$baSpReward->select('item_org_id','item_type','item_quantity')->where('map_id',$map_id)->where('ranking',$ch_ranking)->where('lv',$ch_lv)->where('start_date','<',$datetime)->where('end_date','>',$datetime)->where('item_rate_from','<=',$rate)->where('item_rate_to','>=',$rate)->first();
		  		$rewards['special'][]=$spReward;
			}

		 //  	$count=count($norReward);
			// shuffle($norReward);
			$baggageUtil=new BaggageUtil();
			$result['normal']=$baggageUtil->insertToBaggage($u_id,$rewards['normarl']);
			$result['special']=$baggageUtil->insertToBaggage($u_id,$rewards['special']);
			
			$UserModel->updateUserValue($u_id,'u_coin',$battle_reward['coin']);
			if($battle_reward['exp']>0){
				$LevelUP=$chaEffutil->levelUP($u_id,$battle_reward['exp']);
				$result['exp_reward']=$battle_reward['exp'];
				$result['lv_before']=$charData['ch_lv'];
				$result['levelUP']=$LevelUP['levelup'];
				$result['lv']=$LevelUP['lv'];
			}else{
				$result['exp']=0;
			}
			$key="battle_result".$match_id;
			$result['coin_reward']=$battle_reward['coin'];
			$reward=json_encode($result,TRUE);
			$redis_battle->HSET($key,$u_id,$reward);


  }

  	public function battleResult(Request $request){
  		$req=$request->getContent();
		$json=base64_decode($req);
		$data=json_decode($json,TRUE);
		$match_id=$data['match_id'];
		$u_id=$data['u_id'];
		$redis_battle=Redis::connection('battle');
		$key="battle_result".$match_id;
		$battle_reward=$redis_battle->HGET($key,$u_id);
		$rewards=json_decode($battle_reward,TRUE);
		$normarl=[];
		$special=[];
		foreach ($rewards['normal'] as $key => $value) {
			$normarl[]=['item_org_id'=>$key,'item_quantity'=>$value];
		}
		foreach ($rewards['special'] as $key => $value) {
			$special[]=['item_org_id'=>$key,'item_quantity'=>$value];
		}
		unset($rewards['normal']);
		unset($rewards['special']);
		$result=$rewards;
		$result['normal']=$normarl;
		$result['special']=$special;
		$response=json_encode($result,TRUE);
		return   base64_encode($response);

  	} 
	private function checkSkillCD($skill,$match_id,$u_id){
		$attackhitutil=new AttackHitUtil();
		$redis_battle=Redis::connection('battle');
		$skill_id=$skill['skill_id'];
		$skill_cd=$skill['skill_cd'];
		if($skill_cd>0){
			$skill_key='skill_'.$match_id.'_'.$u_id;
			$skillTime=$redis_battle->HGET($skill_key,$skill_id);
			$current=$this->getMillisecond();
			if($skillTime){
				if($current-$skillTime>=$skill_cd){
				$redis_battle->HSET($skill_key,$skill_id,$current);
				return $skillTime;
				}
			else {
				return 0;
				}
			}
			else {
			$redis_battle->HSET($skill_key,$skill_id,$current);
			return 1;
			}
		}
		else {
			return 1;
		}
	}
	private function getCritical(){
 			$defindModel=new DefindMstModel();
 			$critcalRound=$defindModel->where('defind_id',6)->first();
 			$critalNumer=random($critcalRound['value1'],$critcalRound['value2']);
 			$critcalTimes=$defindModel->where('defind_id',7)->first();
 			if($critalNumer<$user_crit){
 				$critical=$critcalTimes['value2'];
 			}
 			else {
 				$critical=$critcalTimes['value2'];
 			}
 			return $critical;
 	}



 	 public function getData($data){
 	 	$now   = new DateTime;
 	 	$dmy=$now->format( 'Ymd' );
 	 	$match_id=$data['match_id'];
 	 	$redis_battle=Redis::connection('battle');
		$match_id=$data['match_id'];
		$matchList=$redis_battle->HGET('match_list',$match_id);
		$u_id=$data['u_id'];
		
		$matchArr=json_decode($matchList,TRUE);
		// Log::info($matchArr);

		if(isset($matchArr)){
			if($matchArr['u_id']==$u_id){
				$enmey_uid=$matchArr['enemy_uid'];
			}
			else{
				$enmey_uid=$matchArr['u_id'];
			}
			$map_id=$matchArr['map_id'];
			$key='match_history'.$match_id.'_'.$u_id;
			$count=$redis_battle->LLEN($key);
			$data['time']=$this->getMillisecond();
			$dataJson=json_encode($data,TRUE);
			if($count==0){
			$redis_battle->LPUSH($key,$dataJson);
			return $enmey_uid;
			}
			else{
				$redis_battle->LPUSH($key,$dataJson);
				return null;
			}

 	 	}
 	 }

 	 public function testBattle(Request $request){
 	 	$characterModel=new CharacterModel();
 	 	$header=$request->header('Content-Type');
 		$req=$request->getContent();
		$json=base64_decode($req);
	 	$now   = new DateTime;
		$dmy=$now->format( 'Ymd' );
		$data=json_decode($json,TRUE);
		$clientInfo['address']='1111';
		$clientInfo['port']=2;
		$u_id=$data['u_id'];

		$charData=$characterModel->where('u_id',$u_id)->first();
		$result=$this->battleNew($data,$clientInfo);
		var_dump($result);
 	 }

	 public function finalMatchResult ($data){
	 	$match_id=$data['match_id'];
	 	$u_id=$data['u_id'];

	 	$usermodel=new UserModel();
     	$matchrange=new MatchRangeModel();
     	$characterModel=new CharacterModel();
     	$charSkillUtil=new CharSkillEffUtil();
     	$chardata=$characterModel->where('u_id',$u_id)->first();
	 	$effect=$charSkillUtil->getCharSkill($chardata['ch_id']);
	 	$enmeydata=$usermodel->where('u_id',$enemy_uid)->first();
	 	
	 	$result['match_id']=$match_id;
		$result['userData']['eff']=$effect;
		$result['userData']['char']=$chardata;
		$result['mapData']=$mapData;
		$result['enemyData']=$enmeydata;
		$response=json_encode($result,TRUE);
		return $response;

	 }

}
