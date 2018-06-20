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
/*2018.04.19 edition*/
	public function battleNew($data,$clientInfo){
		$now   = new DateTime;
		$dmy=$now->format( 'Ymd' );
		if($data['u_id']){
 			$x=$data['x'];
 			$y=$data['y'];
 			$x2=$data['x2'];
 			$y2=$data['y2'];
 			$u_id=$data['u_id'];
 			$direction=$data['direction'];
 			$status=$data['status'];//status of user in battle
 			$current=$this->getMillisecond();
 			$characterModel=new CharacterModel();
 			$skillModel=new SkillMstModel();
 			$attackhitutil=new AttackHitUtil();
 			$redis_battle_history=Redis::connection('battle');
 			$redis_user=Redis::connection('battle_user');
 			$matchKey='battle_status'.$dmy;
 			$battle_status=$redis_battle_history->HGET($matchKey,$u_id);
 			$battleData=json_decode($battle_status,TRUE);
 			$enemy_uid=$battleData['enemy_uid'];
 			$match_id=$battleData['match_id'];
 			$clientId=$battleData['client'];
 			$map_id=$battleData['map_id'];
 			$battlekey='battle_data'.$match_id.'_'.$u_id;
 			$battle_status_key='battle'.$u_id;
 			$end=0;
 			$enemy_clientId=$battleData['enmey_client'];
 			// $this->removeUsedSkill($u_id);
 			$redis_user->HSET($battle_status_key,'x',$x);
			$redis_user->HSET($battle_status_key,'x2',$x2);
			$redis_user->HSET($battle_status_key,'y',$y);
			$redis_user->HSET($battle_status_key,'y2',$y2);
			$redis_user->HSET($battle_status_key,'status',$status);
			$redis_user->HSET($battle_status_key,'end',$end);
			$redis_user->HSET($battle_status_key,'direction',$direction);
 			$charData=$this->mapingData($match_id,$u_id,1,$x,$y,$x2,$y2,$status,1);
 			$charData['time']=$current;
 			$charData['address']=$clientInfo['address'];
 			$charData['port']=$clientInfo['port'];
 			


 			// $charData['direction']=1;
 			// $charData['status']=$status;
 			//$user_res=1;
 			
 			$fly_tools_key='battle_flytools'.$match_id.$u_id;
			if(isset($data['direction'])){
				$charData['direction']=$data['direction'];
			}
			$enemy_fly_tools_key='battle_flytools'.$match_id.$enemy_uid;
			$displacement_key='displacement'.$match_id.$u_id;
			$multi_key='multi'.$match_id.$u_id;
			$multi_interval_key='multi_interval'.$match_id.$u_id;
			if(isset($data['skill_id'])){
				$skill=$skillModel->select('skill_id','skill_group','skill_cd','skill_damage','skill_name','skill_prepare_time','skill_atk_time')->where('skill_id',$data['skill_id'])->first();
				$checkCD=$this->checkSkillCD($skill,$match_id,$u_id);
				if($checkCD>0){
					$possbileSkill=$this->checkNormalSkill($skill['skill_group'],$skill['skill_name'],$skill['skill_prepare_time'],$skill['skill_atk_time']);
					if($possbileSkill){
						$charData['skill_id']=$data['skill_id'];
						$charData['skill_group']=$skill['skill_group'];
						$charData['skill_damage']=$skill['skill_damage'];
						$charData['skill_prepare_time']=$skill['skill_prepare_time'];
						$charData['skill_atk_time']=$skill['skill_atk_time'];
						$charData['occur_time']=$current;
						$charData['start_x']=$x;
						$charData['start_y']=$y;
						$charData['start_direction']=$data['direction'];
						$redis_user->HSET($battle_status_key,'skill_id',$data['skill_id']);
						$redis_user->HSET($battle_status_key,'skill_group',$skill['skill_group']);
						$redis_user->HSET($battle_status_key,'skill_damage',$skill['skill_damage']);
						$redis_user->HSET($battle_status_key,'skill_prepare_time',$skill['skill_prepare_time']);
						$redis_user->HSET($battle_status_key,'skill_atk_time',$skill['skill_atk_time']);
						$redis_user->HSET($battle_status_key,'occur_time',$current);
						$redis_user->HSET($battle_status_key,'start_x',$x);
						$redis_user->HSET($battle_status_key,'start_y',$y);
						$redis_user->HSET($battle_status_key,'start_direction',$data['direction']);
						if($skill['skill_damage']==0||$skill['skill_damage']==5){
							$skillatkEff=$attackhitutil->getEffValue($data['skill_id']);
							// $attackhitutil->addBuff($data['skill_id'],$u_id,$match_id,$enemy_uid);
						}
						if($skill['skill_damage']==2){
							Log::info('damage 2');
							$flytools['skill_id']=$skill['skill_id'];
							$flytools['skill_damage']=$skill['skill_damage'];
							$flytools['skill_group']=$skill['skill_group'];
							$flytools['occur_time']=$current;
							$flytools['x']=$x;
							$flytools['y']=$y;
							$flytools['direction']=$data['direction'];
							$flySkillJson=json_encode($flytools);
							$redis_battle_history->HSET($fly_tools_key,$skill['skill_id'],$flySkillJson);
						}
						if($skill['skill_damage']==6){
							$displacement['skill_id']=$skill['skill_id'];
							$displacement['occur_time']=$current;
							$displacement['x']=$x;
							$displacement['y']=$y;
							$displacement['direction']=$data['direction'];
							$displacement['skill_group']=$skill['skill_group'];
							$displacement['skill_damage']=$skill['skill_damage'];
							$displacementJson=json_encode($displacement);
							$redis_battle_history->HSET($displacement_key,$data['skill_id'],$displacementJson);
						}
					    if($skill['skill_damage']==3||$skill['skill_damage']==4){
							// $multi['skill_id']=$skill['skill_id'];
							// $multi['occur_time']=$current;
							// $multi['start_x']=$x;
							// $multi['start_y']=$y;
							// $multi['skill_group']=$skill['skill_group'];
							// $multi['start_direction']=$data['direction'];
							// $multi['skill_damage']=$skill['skill_damage'];
							// $multiJson=json_encode($multi);
							// $redis_battle_history->HSET($multi_key,$data['skill_id'],$multiJson);
							//op[n $redis_battle_history->HSET($multi_interval_key.'_'.$data['skill_id'],1,$current);
						}
					}
				}
			}
			$enemyData=$this->mapingData($match_id,$enemy_uid,2);	
			if(isset($enemyData['x'])){
					if($clientId<$enemy_clientId){
				    	$enemyData['x']=-($enemyData['x']);
				    	$enemyData['x2']=-($enemyData['x2']);
				    	$enemyData['direction']=-($enemyData['direction']);
				    }else{
				    	$enemyData['x']=-($enemyData['x']);
				    	$enemyData['x2']=-($enemyData['x2']);
				    	$enemyData['direction']=-($enemyData['direction']);
				    	$charData['x']=-($charData['x']);
				    	$charData['x2']=-($charData['x2']);
				    	$charData['direction']=-($charData['direction']);
				    }
			}
		    $flytools=$attackhitutil->checkSkillRecord($match_id,$enemy_uid,'battle_flytools');	   
		    $displacement=$attackhitutil->checkSkillRecord($match_id,$enemy_uid,'displacement');
		    $multi=$attackhitutil->checkSkillRecord($match_id,$enemy_uid,'multi');

			if(isset($enemyData['skill_id'])){
				$hit=$attackhitutil->checkSkillHit($enemyData,$x,$y,$charData['direction'],$match_id,$enemy_uid,$u_id);
				Log::info("check skill enmeyData".$enemyData['skill_id']);
				if($hit&&$hit!=null&&$hit!=''){
					$skillatkEff=$attackhitutil->getEffValue($enemyData['skill_id']);
					$effValues=$attackhitutil->findEffFunciton($skillatkEff);
					$charData=$attackhitutil->calculateCharValue($charData,$enemyData,$effValues,$enemyData['skill_group'],$u_id,$u_id,$enemy_uid,$match_id);
					$this->removeUsedSkill($enemy_uid);
					// Log::info($charData);
				}
			}
			if(isset($flytools)){
					//Log::info($flytools);
				 	foreach ($flytools as $key => $eachskill) 
				 	{	$eachskillData=json_decode($eachskill,TRUE);
				 		$hit=$attackhitutil->checkSkillHit($eachskillData,$x,$y,$direction,$match_id,$enemy_uid,$u_id);
				 	if($hit&&$hit!=null&&$hit!=''){
				 		$skillatkEff=$attackhitutil->getEffValue($eachskillData['skill_id']);
						$effValues=$attackhitutil->findEffFunciton($skillatkEff);
						$charData=$attackhitutil->calculateCharValue($charData,$enemyData,$effValues,$eachskillData['skill_group'],$u_id,$enemy_uid,$match_id);
				 	}
				  }
			}
			if(isset($displacement)){
				foreach ($displacement as $key => $eachskill) {
					$eachskillData=json_decode($eachskill,TRUE);
					$hit=$attackhitutil->checkSkillHit($eachskillData,$x,$y,$enemyData['x'],$enemyData['y'],$charData['direction'],$enemyData['direction'],$match_id,$enemy_uid,$u_id,$key);
					if($hit&&$hit!=null&&$hit!=''){
				 		$skillatkEff=$attackhitutil->getEffValue($eachskillData['skill_id']);
						$effValues=$attackhitutil->findEffFunciton($skillatkEff);
						$charData=$attackhitutil->calculateCharValue($charData,$enemyData,$effValues,$eachskillData['skill_group'],$u_id,$enemy_uid,$match_id);
					// Log::info($charData);
				 	}
				}
			}
			if(isset($multi)){	
					foreach ($multi as $key => $eachskill) {
					$eachskillData=json_decode($eachskill,TRUE);
					$hit=$attackhitutil->checkSkillHit($eachskillData,$x,$y,$enemyData['x'],$enemyData['y'],$charData['direction'],$enemyData['direction'],$match_id,$enemy_uid,$u_id,$key);
					if($hit&&$hit!=null&&$hit!=''){
						Log::info("test hit damge 3,4");
				 		$skillatkEff=$attackhitutil->getEffValue($eachskillData['skill_id']);
						$effValues=$attackhitutil->findEffFunciton($skillatkEff);
						$charData=$attackhitutil->calculateCharValue($charData,$enemyData,$effValues,$eachskillData['skill_group'],$u_id,$enemy_uid,$match_id);
					Log::info($charData);
				 	}
				}
			}
			$charData['request_time']=$data['request_time'];
			// $charData['buffs']=$attackhitutil->mapingBuffs($u_id,$match_id,1);
			// $charData['debuffs']=$attackhitutil->mapingBuffs($u_id,$match_id,2);
			// $enemyData['buffs']=$attackhitutil->mapingBuffs($enemy_uid,$match_id,1);
			// $enemyData['debuffs']=$attackhitutil->mapingBuffs($enemy_uid,$match_id,2);
			$result['user_data']=$charData;
			$result['enemy_data']=$enemyData;

			 if(isset($enemyData['ch_hp_max'])&&$enemyData['ch_hp_max']<=0){
				$result['end']=2;
				$win=1;
				// $this->BattleRewards($u_id,$map_id,$win,$match_id,$charData['ch_lv']);
			}
			else if(isset($charData['ch_hp_max'])&&$charData['ch_hp_max']<=0){
				$result['end']=1;
				$win=0;
				//$this->BattleRewards($u_id,$map_id,$match_id,$win,$charData['ch_lv'],$charData['ch_ranking']);
			}
			else {
				$result['end']=0;
			}

			$charData['end']=$result['end'];
			$end=$result['end'];
			if($clientId>$enemy_clientId){
				$charData['x']=-($charData['x']);
				$charData['x2']=-($charData['x2']);
				$charData['direction']=-($charData['direction']);
			}	

			$charJson=json_encode($charData);
			
			// $this->removeUsedSkill($u_id);
			
			//$count=$redis_battle_history->HLEN($battlekey);
			//$redis_battle_history->LPUSH($battlekey,$charJson);

			$response=json_encode($result,TRUE);
			Log::info('test remove skill'.$response);
			return  $response;
		}

}
	private function removeUsedSkill($u_id){
		$redis_user=Redis::connection('battle_user');
		$battle_status_key='battle'.$u_id;
		$redis_user->HDEL($battle_status_key,'skill_id');
		$redis_user->HDEL($battle_status_key,'skill_group');
		$redis_user->HDEL($battle_status_key,'skill_damage');
		$redis_user->HDEL($battle_status_key,'skill_prepare_time');
		$redis_user->HDEL($battle_status_key,'skill_atk_time');
		$redis_user->HDEL($battle_status_key,'occur_time');
		$redis_user->HDEL($battle_status_key,'start_x');
		$redis_user->HDEL($battle_status_key,'start_y');
		$redis_user->HDEL($battle_status_key,'start_direction');
	}
	private function checkNormalSkill($skill_group,$skill_name,$skill_prepare_time,$skill_atk_time){
		$skillModel=new SkillMstModel();
		if($skill_group==1&&strpos($skill_name,'b')){
			$skill_before=$skillModel->select('skill_id','skill_group','skill_cd','skill_name','skill_prepare_time','skill_atk_time')->where('skill_group',$skill_group)->where('skill_name','like','%-a%')->first();
			$skill_key='skill_'.$match_id.'_'.$u_id;
			$skillTime=$redis_battle_history->HGET($skill_key,$skill_id);
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
			$skillTime=$redis_battle_history->HGET($skill_key,$skill_id);
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

	private function mapingData($match_id,$u_id,$identity,$x=null,$y=null,$x2=null,$y2=null,$status=null,$direction=null){
		$redis_user=Redis::connection('battle_user');
		$characterModel=new CharacterModel();
		//$redis_battle_history=Redis::connection('battle');
		$user_key='battle'.$u_id;
		$user_data=$redis_user->HGETALL($user_key);
		$existX=$redis_user->HEXISTS($user_key,'x');
		// $battlekey='battle_data'.$match_id.'_'.$u_id;
		// $userExist=$redis_battle_history->HLEN($battlekey);
		$charData=[];
		if($existX<1){
			if($identity==2){
				$user_data['x']=-1000;
				$user_data['y']=-290;
				$user_data['x2']=-1000;
				$user_data['y2']=-290;
				$user_data['direction']=-1;
				$user_data['status']=0;
			}
		}
		else{ 	
			if($identity==1){			
				$user_data['x']=$x;
				$user_data['y']=$y;
				$user_data['x2']=$x2;
				$user_data['y2']=$y2;
				$user_data['direction']=$direction;
				$user_data['status']=$status;
			}
		}
		return $user_data;
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
			$redis_battle_history=Redis::connection('battle');
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
			$redis_battle_history->HSET($key,$u_id,$reward);
  }

  	public function battleResult(Request $request){
  		$req=$request->getContent();
		$json=base64_decode($req);
		$data=json_decode($json,TRUE);
		$match_id=$data['match_id'];
		$u_id=$data['u_id'];
		$redis_battle_history=Redis::connection('battle');
		$key="battle_result".$match_id;
		$battle_reward=$redis_battle_history->HGET($key,$u_id);
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
		$redis_battle_history=Redis::connection('battle');
		$skill_id=$skill['skill_id'];
		$skill_cd=$skill['skill_cd'];
		if($skill_cd>0){
			$skill_key='skill_'.$match_id.'_'.$u_id;
			$skillTime=$redis_battle_history->HGET($skill_key,$skill_id);
			$current=$this->getMillisecond();
			if($skillTime){
				if($current-$skillTime>=$skill_cd){
				$redis_battle_history->HSET($skill_key,$skill_id,$current);
				return $skillTime;
				}
			else {
				return 0;
				}
			}
			else {
			$redis_battle_history->HSET($skill_key,$skill_id,$current);
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
 	 	$redis_battle_history=Redis::connection('battle');
		$match_id=$data['match_id'];
		$matchList=$redis_battle_history->HGET('match_list',$match_id);
		$u_id=$data['u_id'];
		
		$matchArr=json_decode($matchList,TRUE);
		// Log::info($matchArr);

		if(isset($matchArr)){
			if($matchArr['u_id']==$u_id){
				$enemy_uid=$matchArr['enemy_uid'];
			}
			else{
				$enemy_uid=$matchArr['u_id'];
			}
			$map_id=$matchArr['map_id'];
			$key='match_history'.$match_id.'_'.$u_id;
			$count=$redis_battle_history->LLEN($key);
			$data['time']=$this->getMillisecond();
			$dataJson=json_encode($data,TRUE);
			if($count==0){
			$redis_battle_history->LPUSH($key,$dataJson);
			return $enemy_uid;
			}
			else{
				$redis_battle_history->LPUSH($key,$dataJson);
				return null;
			}

 	 	}
 	 }

 	 public function testBattle(Request $request){
 	 	$defindMst=new DefindMstModel();
 	 	$attackhitutil=new AttackHitUtil();
 	 	$req=$request->getContent();
		$json=base64_decode($req);
		$data=json_decode($json,TRUE);
		$redis_battle_history=Redis::connection('battle');
		$current=$this->getMillisecond();
		$characterModel=new CharacterModel();
		$match_id=$data['match_id'];
		$u_id=$data['u_id'];
		$battlekey='battle_data'.$match_id.'_'.$u_id;
		 $code='B04_a';
		$interval=$defindMst->select('value2')->where('comment', 'like',$code)->where('value1',45)->first();
		var_dump($interval['value2']);
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
