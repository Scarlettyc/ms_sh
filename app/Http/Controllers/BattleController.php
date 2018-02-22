<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\UserModel;
use App\MatchRangeModel;
use App\CharacterModel;
use App\SkillMstModel;
use App\EffectionMstModel;
use App\DefindMstModel;
use App\BuffEffMstModel;
use App\NormalEffectionMstModel;
use App\MapTrapRelationMst;
use App\Util\DistanceAttackUtil;
use Illuminate\Support\Facades\Redis;
use App\EquipmentMstModel;
use App\EqAttrmstModel;
use App\BattleNormalRewardsMst;
use App\BattleSpecialRewardsMst;
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

	public function realbattle($data,$clientInfo){
		$now   = new DateTime;;
		$dmy=$now->format( 'Ymd' );
		$x=$data['x'];
		$y=$data['y'];
		$u_id=$data['u_id'];
		$move=$data['move'];//status of user run 2 or stand by 1
		$characterModel=new CharacterModel();
		$skillModel=new SkillMstModel();
		$attackhitutil=new AttackHitUtil();
		$redis_battle=Redis::connection('battle');
		
		$matchKey='battle_status'.$dmy;
		$battle_status=$redis_battle->HGET($matchKey,$u_id);
		$battleData=json_decode($battle_status,TRUE);
		if($battleData){
			$enemy_uid=$battleData['enemy_uid'];
			$match_id=$battleData['match_id'];
			$clientId=$battleData['client'];
			$map_id=$battleData['map_id'];
			$enemy_clientId=$battleData['enmey_client'];
			$battlekey='battle_data'.$match_id.'_'.$u_id;
			$userExist=$redis_battle->LLEN($battlekey);
		
			$charData=[];
			if($userExist<1){
				$charData=$characterModel->select('ch_hp_max','ch_stam','ch_atk','ch_armor','ch_crit','ch_lv','ch_ranking')->where('u_id',$u_id)
			}
			else{
				$userJson=$redis_battle->LRANGE($battlekey,0,0);
				foreach ($userJson as $key => $each) {
					$userData=json_decode($each,TRUE);
					$charData['ch_hp_max']=$userData['ch_hp_max'];
					$charData['ch_stam']=$userData['ch_stam'];
					$charData['ch_atk']=$userData['ch_atk'];
					$charData['ch_crit']=$userData['ch_crit'];
					$charData['ch_armor']=$userData['ch_armor'];
					$charData['ch_lv']=$userData['ch_lv'];
					if(isset($each['skills'])){
						foreach ($each['skills'] as $key => $skill) {
							if(isset($skill['constant_eff'])){
								$check=$attackhitutil->haveEffConstant($skill['constant_eff'],$skill['occur_time']);
								if($check){
									if(isset($check['self_buff_eff_id'])){
										$buffResult=$attackhitutil->buffStatus($check['self_buff_eff_id']);
									}
									$charData['skills'][]=["skill_id"=>$skill['skill_id'],"skill_group"=>$skill['skill_group'],"occur_time"=>$skill['occur_time'],'constant_eff'=>$check];

									}
								}
							}
					$charData['ch_lv']=$userData['ch_lv'];
					$charData['ch_ranking']=$userData['ch_ranking'];
				}
		
						}
		
					}
				}
		
		$charData['x']=$x;		
		$charData['y']=$y;
		$charData['time']=time();
		$charData['address']=$clientInfo['address'];
		$charData['port']=$clientInfo['port'];
		$charData['direction']=1;
		$charData['move']=$move;
		$user_res=1;
		$ch_lv=$charData['ch_lv'];
		$ch_ranking=$charData['ch_ranking'];
		if(isset($data['direction'])){
			$charData['direction']=$data['direction'];
		}
		if(isset($data['skill_id'])){
			$skill=$skillModel->select('skill_id','skill_group','skill_cd')->where('skill_id',$data['skill_id'])->first();

			$checkCD=$this->checkSkillCD($skill,$match_id,$u_id);
			if($checkCD){
				$charData['skills'][]['skill_id']=$data['skill_id'];
				$charData['skills'][]['skill_group']=$skill['skill_group'];
				$charData['skills'][]['occur_time']=time();
				$skillConstant=$attackhitutil->checkEffConstant($data['skill_id'],$data['x']);
				if($skillConstant){
					$charData['skills'][]=["skill_id"=>$data['skill_id'],"skill_group"=>$data['skill_group'],"occur_time"=>time(),'constant_eff'=>$skillConstant];
				}
			}
		}
		
		$enemykey='battle_data'.$match_id.'_'.$enemy_uid;
		$enemyJson=$redis_battle->LRANGE($enemykey,0,0); 
		// Log::info($data);
		$enemy_charData=$characterModel->select('ch_hp_max','ch_stam','ch_atk','ch_armor','ch_crit')->where('u_id',$enemy_uid)->first();
		if(is_null($enemyJson)){
			$enemy_charData['x']=-1000;
			$enemy_charData['y']=-290;
		}
		else {
				foreach ($enemyJson as $key => $each) 
				{
			  		$enmeyData=json_decode($each,TRUE);
			   		$enemy_charData['x']=$enmeyData['x'];
					$enemy_charData['y']=$enmeyData['y'];
					$enemy_charData['ch_hp_max']=$enmeyData['ch_hp_max'];
					$enemy_charData['ch_stam']=$enmeyData['ch_stam'];
					$enemy_charData['ch_atk']=$enmeyData['ch_atk'];
					$enemy_charData['ch_crit']=$enmeyData['ch_crit'];
					$enemy_charData['direction']=$enmeyData['direction'];
					$enemy_charData['move']=$enmeyData['move'];
				if(isset($enmeyData['skills']))
				{
					foreach ($enmeyData['skills'] as $key => $enemySkill) {
						$enemySkill['skill_id']=$enmeyData['skill_id'];
						$enemySkill['skill_group']=$enmeyData['skill_group'];
						if(isset($enemySkill['constant_eff'])){
							$effs=$attackhitutil->getconstantEff($enemySkill['skill_id'],$enemySkill['occur_time']$charData,$enemy_charData,$clientId,$enemy_clientId,$charData['direction'],$enemy_charData['direction']，$enemySkill['constant_eff']);
							
						}else{
							$effResult=$attackhitutil->getatkEff($enemySkill['skill_id'],$charData,$enemy_charData,$clientId,$enemy_clientId,$charData['direction'],$enemy_charData['direction']);

						}

					if($effResult){ 
						if(isset($effResult['enemy_buff'])){
							if(!isset($buffResult['eff_ch_uncontrollable'])||!isset($buffResult['eff_ch_invincible'])){
								$this->enemyBuffEff($charData['ch_hp_max'],$\$effResult['enemy_buff']);
							}

						}

						if(isset($effResult['atkEff'])){
							$enemy_atk=$enemy_charData['ch_atk'];
							$randCrit=rand(1,100);
							if($randCrit<=$enemy_charData['ch_crit']){
								$critBool=2;
								}else{
									$critBool=1;
							}
							$user_def=($chardata['ch_armor']*1.1)/(15*$charData['ch_lv']+$chardata['ch_armor']+40);

							if(isset($buffResult['eff_ch_res_per']){
								$user_res=$buffResult['eff_ch_res_per'];
							}
							if($enemy_charData['skill_group']==1){
								$enemy_atk=$enemy_charData['ch_atk']*$atkeff['eff_skill_atk_point']*$user_res;
								$enemyDMG=$enemy_atk*$critBool;
								$hpMax=$charData['ch_hp_max']/(1-$user_def);
								$charData['ch_hp_max']=round($hpMax-$enemyDMG);
 	 						}

 	 						else if($enemy_charData['skill_group']==2){
 	 							$enemy_atk=$enemy_charData['ch_atk']*$atkeff['eff_skill_atk_point']+pow($enemy_charData['ch_lv'],2)*2;
 	 							$enemyDMG=$enemy_atk*$critBool;
 	 							$hpMax=$charData['ch_hp_max'];
								$charData['ch_hp_max']=round($hpMax-$enemy_atk);

							}
						}
					}
				}	
			}
		  }
		}
			$enemy_charData['time']=time();
			// $enemyJson=json_decode($enemy_charData,TRUE);
		$result['user_data']=$charData;
		if($clientId>$enemy_clientId){
			$enemy_charData['x']=-($enemy_charData['x']);
			$enemy_charData['direction']=-($enemy_charData['direction']);
		}
		$result['enemy_data']=$enemy_charData;
		 if($enemy_charData['ch_hp_max']<0){
			$result['end']=2;
			//$this->BattleSpeRewards($u_id,$map_id,$match_id,$ch_lv);
		}
		else if($charData['ch_hp_max']<=0){
			$result['end']=1;
			//$this->BattleNormalRewards($u_id,$map_id,$match_id,$ch_lv,$ch_ranking);
		}
		else {
			$result['end']=0;
		}
		if($clientId>$enemy_clientId){
			$charData['x']=-$charData['x'];
			$charData['direction']=-$charData['direction'];
		}
		$charData['end']=$result['end'];
		$charJson=json_encode($charData);
		$redis_battle->LPUSH($battlekey,$charJson);
		$response=json_encode($result,TRUE);
		return  $response;
		}
		else {
			return null;
		}
	}

	private function enemyBuffEff($charData,$){

	}
	private function getMillisecond() {
		list($t1, $t2) = explode(' ', microtime());     
		return (float)sprintf('%.0f', (floatval($t1) + floatval($t2)) * 1000);  
	}

  private function BattleNormalRewards($u_id,$map_id,$match_id,$ch_lv,$ch_ranking){
		  	$baNorReward=new BattleNormalRewardsMst();
		  	$chaEffutil=new CharSkillEffUtil();
		  	$battleRewardExpModel=new BattleRewardExpModel();
		  	$UserModel=new UserModel();
		  	$now   = new DateTime;;
			$dmy=$now->format( 'Ymd' );
		  	$datetime=$now->format('Y-m-d h:m:s');
			$redis_battle=Redis::connection('battle');
		  	$norReward=$baNorReward->where('map_id',$map_id)->where('ranking',$ch_ranking)->where('start_date','<',$datetime)->where('end_date','>',$datetime)->get();
		 //  	$count=count($norReward);
			// shuffle($norReward);
			$baggageUtil=new BaggageUtil();
			$baggageUtil->insertToBaggage($u_id,$norReward);
			$battle_reward=$battleRewardExpModel->select('exp','coin')->where('lv',$ch_lv)->first();
			$UserModel->updateUserValue($u_id,'u_coin',$battle_reward['coin']);
			if($battle_reward['exp']>0){
			$LevelUP=$chaEffutil->levelUP($u_id,$battle_reward['exp']);
			$norReward['exp_reward']=$battle_reward['exp'];
			//$norReward['exp_from']=$charData['ch_exp'];
			$norReward['lv_before']=$ch_lv;
			$norReward['levelUP']=$LevelUP['levelup'];
			$norReward['lv']=$LevelUP['lv'];
			}
			$key="battle_result".$match_id;
			$norReward['coin_reward']=$battle_reward['coin'];
			$reward=json_encode($norReward,TRUE);
			$redis_battle->HSET($key,$u_id,$reward);

  }

  private function BattleSpeRewards($u_id,$map_id,$match_id,$ch_lv){
  	$now   = new DateTime;;
  	$baSpReward=new BattleSpecialRewardsMst();
  	$chaEffutil=new CharSkillEffUtil();
  	$UserModel=new UserModel();
  	$datetime=$now->format('Y-m-d h:m:s');
  	$defindMstModel=new DefindMstModel();
  	$redis_battle=Redis::connection('battle');
  	$defindData=$defindMstModel->where('defind_id',16)->first(); 
  	$random=rand($defindData['value1'],$defindData['value2']);
  	$spReward=$baSpReward->where('map_id',$map_id)->where('start_date','<',$datetime)->where('end_date','>',$datetime)->where('rate_from','<=',$random)->where('rate_to','>',$random)->get();
  	$baggageUtil=new BaggageUtil();
  	$battleRewardExpModel=new BattleRewardExpModel();
  	$battle_reward=$battleRewardExpModel->select('exp','coin')->where('lv',$ch_lv)->first();
	$baggageUtil->insertToBaggage($u_id,$spReward);
	$UserModel->updateUserValue($u_id,'u_coin',$battle_reward['coin']);
	if($battle_reward['exp']>0){
	$LevelUP=$chaEffutil->levelUP($u_id,$battle_reward['exp']);
	$spReward['exp_reward']=$battle_reward['exp'];
	//$spReward['exp_from']=$charData['ch_exp'];
	$spReward['lv_before']=$ch_lv;
	$spReward['levelUP']=$LevelUP['levelup'];
	$spReward['lv']=$LevelUP['lv'];
	}
	$key="battle_result".$match_id;
	$spReward['coin_reward']=$battle_reward['coin'];
	$reward=json_encode($spReward,TRUE);
	$redis_battle->HSET($key,$u_id,$reward);
  }

// 	public function battle($u_id,$enmey_uid,$data)
// {	    //$req=$request->getContent();
// 		//$json=base64_decode($request);
// 		$data=json_decode($request,TRUE);
// 		$redis_battle=Redis::connection('battle');
// 		$characterModel=new CharacterModel();
// 		$u_id=$data['u_id'];		
// 		$now   = new DateTime;;
// 		$dmy=$now->format( 'Ymd' );
// 		$data=json_decode($json,TRUE);
// 		$skillMstModel=new SkillMstModel();
// 		$battlekey='battle_data'.$match_id.'_'.$u_id;
//  	    $userExist=$redis_battle->LLEN($battlekey);
//  	    $attackhitutil=new AttackHitUtil();
//  	    $defindModel=new DefindMstModel();
//  	    $eqAttr=new EqAttrmstModel();
//  	    $eqModel=new EquipmentMstModel();
//      	$defValue=$defindModel->where('defind_id',8);
//      	list($t1, $t2) = explode(' ', microtime());
// 		$mileTime=(float)sprintf('%.0f',(floatval($t1)+floatval($t2))*1000);
// 		$effResult=[];
// 		$userData=$characterModel->where('u_id',$u_id)->first();
// 		$enemy=$characterModel->where('u_id',$enmey_uid)->first();
// 		$user_hp=$userData['ch_hp_max;'];
// 		$enmey_hp=$enemy['ch_hp_max'];
// 		$enemyKey='battle_data'.$match_id.'_'.$enmey_uid;
//      	if($userExist>0){
//      		$userBattleData=$redis_battle->LRANGE($battlekey,0,0);
// 			$enemyJson=$redis_battle->LRANGE($enemyKey,0,0);
// 			if($userBattleData&&$enemyJson){
// 				$enemy=json_decode($enemyJson);
// 				$userData=json_decode($userBattleData);
// 				$user_hp=$userData['ch_hp'];
// 				$enmey_hp=$enemy['ch_hp'];
// 			}
// 		}
// 		$effects=[];
// 		$enemy_effects=[];
// 		if($data['skill']){
// 			foreach ($userData['skill'] as $key => $skill) {

// 				$checkCD=$this->checkSkillCD($skill['skill_id'],$match_id,$u_id);
// 				if($checkCD){
// 					$effResult=$attackhitutil->getSelfEff($skill['skill_id'],$user,$enemy,$skills['direction'],$skills['occur_time']);	

// 				$effects[]=$effResult;
// 				}
// 			}
			
// 		}

// 		if($userData['skill']){
// 				foreach ($userData['skill'] as $key => $skills) {
// 					$effResult=$attackhitutil->getSelfEff($skills['skill_id'],$user,$enemy,$skills['direction'],$skills['occur_time']);
// 					$effects[]=$effResult;
// 				}
// 		}

// 		if($enemy['skill']){
// 				foreach ($enemy['skill'] as $key => $skills) {
// 					$enemyEffResult=$attackhitutil->getEnmeyEff($skills['skill_id'],$user,$enemy,$skills['direction'],$skills['occur_time']);

// 					$enemy_effects[]=$enemyEffResult;
// 				}
// 		}
			
//  			//$user_shield=$userData['ch_shield'];
// 			$eqAtt=$eqModel->select('equ_attribute_id')->wherein('equ_id',[$userData['w_id'],$userData['m_id'],$userData['core_id']]);
// 			$eqAtk= $eqAttr->sum('eff_ch_atk')->wherein('equ_att_id',$eqAtt);
// 			$enemyEqAtt=$eqModel->select('equ_attribute_id')->wherein('equ_id',[$userData['w_id'],$enemy['m_id'],$enemy['core_id']]);
// 			$enemyEqAtk=$eqAtt->sum('eff_ch_atk')->wherein('equ_att_id',$enemyEqAtt);

//   			$user_atk=$userData['ch_atk']+$eqAtk;
//   			$user_def=$userData['ch_def'];
//   			$user_crit=$userData['ch_crit '];
//   			$user_cd=$userData['ch_cd'];
//   			$user_spd=$userData['ch_spd'];
//   			$enemy_atk=$enemy['ch_atk']+$enemyEqAtk;

//   		foreach($effects as $effResult){
// 	 		if(isset($effResult['selfbuff'])){
// 	 			$selfBuff=$effResult['selfbuff'];
//        			$user_hp=($user_hp+$selfBuff['eff_ch_hp'])*(1+$selfBuff['eff_ch_hp_per']);
//  				$user_atk=$user_atk*(1+$selfBuff['eff_ch_hp_per']);
//  				$user_def=$user_def*(1+$selfBuff['eff_ch_def_per']);
//  				$user_crit=$user_crit*(1+$selfBuff['eff_ch_crit_per']);
//  				$user_cd=$user_cd*(1-$selfBuff['eff_ch_cd']);
//  				$user_spd=$user_spd*(1-$selfBuff['eff_ch_spd_per']);
//  				$user_stuck=$selfBuff['eff_ch_stuck'];
//  				$user_clear_buff=$selfBuff['eff_ch_clear_buff'];
// 	 		}
// 		}
// 		foreach($enemy_effects as $enemyEffResult){

// 		if(isset($enemyEffResult['enemyBuff'])){

// 			$userBuff=$enemyEffResult['userBuff'];
//      		$user_hp=($user_hp+$userBuff['eff_ch_hp'])*(1-$userBuff['eff_ch_hp_per']);
//  			$user_atk=$user_atk*(1-$userBuff['eff_ch_hp_per']);
//  			$user_def=$user_def*(1-$userBuff['eff_ch_def_per']);
//  			$user_crit=$user_crit*(1-$userBuff['eff_ch_crit_per']);
//  			$user_cd=$user_cd*(1+$userBuff['eff_ch_cd']);
//  			$user_spd=$user_spd*(1+$userBuff['eff_ch_spd_per']);
//  			$user_stun=$userBuff['eff_ch_stun'];
//  			$user_stuck=$userBuff['eff_ch_stuck'];
// 	 	}

//  	   		if(isset($enemyEffResult['atk_eff']['eff'])&&$enemyEffResult['atk_eff']['hit']==1){
//  	 				$atkeff=$enemyEffResult['atkeff']['eff'];
//  	 				if($enemyEffResult['skill_group']==0){
// 					$enemy_atk=$enemy_atk*$atkeff['eff_skill_atk_point'];
 	 			
//  	 				}

//  	 				else if($enemyEffResult['skill_group']==1){
//  	 				$enemy_atk=$enemy_atk*$atkeff['eff_skill_atk_point']*$atkeff['eff_skill_damage_point']+pow($enemy['ch_lv'],2)*2;
//  	 				}
//  	 				$enemyDMG=($atkeff['eff_skill_atk_point']*$enemy_atk+$enemy['eff_skill_base'])*$enemyCritical*(1-(1-$user_def)/(1+$user_def));

//  					$user_hp=$user_hp-$enemyDMG;
//  					if($enemyEffResult['atkeff']['end']==0){
//  						$userBattleData=$redis_battle->LRANGE($battlekey,0,0);
// 						$enemyJson=$redis_battle->LRANGE($enemyKey,0,0);
//  			}

// 		}
// 	}
// 			$battleCheck=$this->winCheck($user_hp,$enmey_hp,$u_id,$map_id);
// 			$result['win']=$battleCheck['win'];
// 			$result['battle_end']=$battleCheck['end'];
// 			$result['level_uP']=$battleCheck['leveluP'];
// 			$result['ch_hp']=$user_hp;
// 			$result['ch_atk']=$user_atk;
// 			$result['ch_def']=$user_def;
// 			$result['ch_crit']=$ch_crit;
// 			$result['ch_cd']=$ch_crit;
//   			$result['ch_spd']=$user_spd; 		
//   			$response=json_encode($result,TRUE);
//   			$redis_battle->LPUSH($battlekey,$response);
//   			return 	$response;
// 	}



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
				return true;
				}
			else {
				return false;
				}
			}
			else {
			$redis_battle->HSET($skill_key,$skill_id,$current);
			return true;
			}
		}
		else {
			return true;
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
		$charData['address']='1111';
		$charData['port']=2;
		$u_id=$data['u_id'];
		$charData=$characterModel->where('u_id',$u_id)->first();
		$result=$this->BattleSpeRewards($u_id,1,1222,$charData['ch_lv']);
		var_dump($result);
		$result2=$this->BattleNormalRewards($u_id,1,1222,$charData['ch_lv'],$charData['ch_ranking']);
		var_dump($result2);
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
