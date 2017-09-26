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
use App\BattleNormalRewardsMst;
use App\BattleSpecialRewardsMst;
use App\LevelUPModel;
use App\Util\BaggageUtil;
use App\Util\AttackHitUtil;
use DateTime;
use Exception;
use Math;
class BattleController extends Controller
{

	public function test($json){
		$data=json_decode($json,TRUE);
		$x1=$data['x1'];
		$x2=$data['x2'];
		$y=$data['y'];
		$direction=$data['direction'];
		$u_id=$data['u_id'];
		$normalEff=new NormalEffectionMstModel();
		$skillMstModel=new SkillMstModel();
		$characterModel=new CharacterModel();
		$redis_battle=Redis::connection('battle');
		$mapTrap=new MapTrapRelationMst();
		$key='battle_test_'.$u_id;
		$hit=0;
		$userBattleData=$redis_battle->LRANGE($key,1,1);
		$userData=$characterModel->where('u_id',$u_id)->first();
		$userHP=$userData['ch_hp_max'];
		$userSpd=$userData['ch_spd'];
		if(isset($data['skill_id'])){
				$skill_id=$data['skill_id'];
				$skill=$skillMstModel->where('skill_id',$skill_id)->first();
				$eff=$normalEff->where('normal_eff_id',$skill['enemy_eff_id'])->first();
				while($time<$eff['eff_skill_dur']){
					$timekey=$this->getMillisecond();
					$tmp['time']=$timekey;
					$tmp['skill_id']=$skill_id;
					$result['skill'][]=$tmp;
					$finalX=$eff['eff_skill_spd']*10;
					$finalY=$y+1;
					$trap=$mapTrap->where('map_trap_id',1)->first();
					if($finalX+abs($x2)-$trap['trap_x_from']<1&&$trap['trap_y_from']&&$trap['trap_y_from']<=$finalY&&$trap['trap_y_to']>=$finalY){
						$hit=1;
					}

					if($hit!=1){
						if(abs($finalX)<$eff['eff_skill_x']){
							$tmp['time']=$this->getMillisecond();
							$tmp['skill_id']=$skill_id;
							$result['skill'][]=$tmp;
						}
					}
					$time=$time+5;
				}
				if($x1>$x2){
					$result['x']=$x1;
			}
			else{
					$result['x']=$x2;
			}

				$result['hit']=$hit;
				$result['direction']=$direction;
				$result['y']=$y+1;
				$result['hp']=$userHP;
				$result['spd']=$userSpd;
				$result['time']=$this->getMillisecond();
				$userJson=json_encode($result,TRUE);
				$redis_battle->LPUSH($key,$userJson);

			return $userJson;
			}

	}

	private function getMillisecond() {
		list($t1, $t2) = explode(' ', microtime());     
		return (float)sprintf('%.0f', (floatval($t1) + floatval($t2)) * 1000);  
}


  private function BattleNormalRewards($u_id,$map_id){
  	$baNorReward=new BattleNormalRewardsMst();
  	$datetime=$now->format('Y-m-d h:m:s');
  	$norReward=$baNorReward->where('map_id',$map_id)->where('start_date','<',$datetime)->where('end_date','>',$datetime)->get();
  	$count=count($norReward);
	shuffle($norReward);
	$baggageUtil=new BaggageUtil();
	$baggageUtil->insertToBaggage($u_id,$norReward);
  }

  private function BattleSpeRewards($u_id,$map_id){
  	$baSpReward=new BattleSpecialRewardsMst();
  	$datetime=$now->format('Y-m-d h:m:s');
  	$defindData=$defindMstModel->where('defind_id',16)->first(); 
  	$random=rand($defindData['value1'],$defindData['value2']);
  	$spReward=$baSpReward->where('map_id',$map_id)->where('start_date','<',$datetime)->where('end_date','>',$datetime)->where('rate_from','<=',$random)->where('rate_to','>',$random)->get();
  	$baggageUtil=new BaggageUtil();
	$baggageUtil->insertToBaggage($u_id,$norReward);
  }

  private function levelUP($u_id,$exp,$lv){
  	$levelupMst=new LevelUPModel();
  	$baggageUtil=new BaggageUtil();
	$characterModel=new CharacterModel();
  	$levels=$levelupMst->where('level','<',$lv)->where('exp','<',$exp)->orderBy('level','DESC')->get();
  	if(isset($levels)){
  		foreach ($levels as $key => $level) {
  			$baggageUtil->levelMissionReward($u_id,$level['level']);
  		}
  		 $characterModel->update(array('ch_lv'=>$levels[0]['level']))->where('u_id',$u_id);
  	}
  }

	public function battle($request)
{	    //$req=$request->getContent();
		//$json=base64_decode($request);
		$data=json_decode($request,TRUE);
		$redis_battle=Redis::connection('battle');
		$characterModel=new CharacterModel();
		$u_id=$data['u_id'];		
		$now   = new DateTime;;
		$dmy=$now->format( 'Ymd' );
		$data=json_decode($json,TRUE);
		$characterModel=new CharacterModel();
		$skillMstModel=new SkillMstModel();
		if(isset($data)){
			$u_id=$data['u_id'];
			$match_id=$data['match_id'];
			$matchList=$redis_battle->HGET('match_list',$match_id);
			$matchArr=json_decode($matchList);
			if(isset($matchArr)){
			 $enmey_uid=$matchArr['enmey_uid'];
			 $map_id=$matchArr['map_id'];
			}
			else {
				throw new Exception("there is no exist match");
			}
		$battlekey='battle_data'.$match_id.'_'.$u_id;
 	    $userExist=$redis_battle->LLEN($battlekey);
 	    $attackhitutil=new AttackHitUtil();
 	    $defindModel=new DefindMstModel();
     	$defValue=$defindModel->where('defind_id',8);
     	list($t1, $t2) = explode(' ', microtime());
		$mileTime=(float)sprintf('%.0f',(floatval($t1)+floatval($t2))*1000);
     	if($userExist>0){
     		$enemyKey='battle_data'.$match_id.'_'.$enmey_uid;
     		$userBattleData=$redis_battle->LRANGE($battlekey,0,0);
			$enemyJson=$redis_battle->LRANGE($enemyKey,0,0);
			$enemy=json_decode($enemyJson);
			$userData=json_decode($userBattleData);

		}else{
			$userData=$characterModel->where('u_id',$$u_id)->first();
			$enemy=$characterModel->where('u_id',$enmey_ui)->first();

		}
		if($data['skill_id']){
			$skill=$skillMstModel->where('skill_id',$skill_id)->first();
			$checkCD=$this->checkSkillCD($skill,$match_id,$u_id);
			if($checkCD){
					$effResult=$attackhitutil->getEff($skill_id,$user,$enemy,$data['direction'],$mileTime);
				}
			else {
					throw new Exception("there skill still in cd time");
				}
		}
		if($userData['skill']){
				foreach ($userData['skill'] as $key => $skills) {
					$effResult=$attackhitutil->getEff($skills['skill_id'],$user,$enemy,$skills['direction'],$skills['occur_time']);
				}
		}
 	   	if(isset($effhit['atkeff'])){
 	    		$userBattleData=$redis_battle->LRANGE($battlekey,0,0);
				$enemyJson=$redis_battle->LRANGE($enemyKey,0,0);
				$atkeff=$effhit['atkeff'];
				if(isset($userBattleData)&&isset($enemyJson)){
					$enemy=json_decode($enemyJson);
					$userData=json_decode($userBattleData);
					$user_hp=$userData['ch_hp'];
       				$user_atk=$userData['ch_atk'];
       				$user_def=$userData['ch_def'];
       			// $user_res=$user['res'];
       				$user_crit=$userData['ch_crit'];
       				$user_cd=$userData['ch_cd'];
       				$user_speed=$userData['ch_spd']; 

       				$enemy_hp=$enemy["enemy_hp"];
 					$enemy_atk=$enemy["enemy_atk"];
 					$enemy_def=$enemy["enemy_def"];
 					$enemy_crit=$enemy["enemy_crit"];
 					$enemy_cd=$enemy["enemy_cd"];
 					$enemy_spd=$enemy["enemy_spd"];

					$usercritical=1;
 					if($atkeff['eff_group_id']==1||$atkeff['eff_group_id']==2){
 						$usercritical=$this->getCritical();
 					}

 					$userDMG=($atkeff['eff_skill_atk_point']*$user_atk+$atkeff['eff_skill_base'])*$usercritical*(1-(1-$enemy_def)/(1+$enemy_def));
				}

			}

			$user_atk=$atkeff['eff_skill_atk'];

			$user_hp=$userData['ch_hp'];
       		$user_atk=$userData['ch_atk'];
       		$user_def=$userData['ch_def'];
       			// $user_res=$user['res'];
       		$user_crit=$userData['ch_crit'];
       		$user_cd=$userData['ch_cd'];
       		$user_speed=$userData['ch_spd']; 

       		$enemy_hp=$enemy["enemy_hp"];
 			$enemy_atk=$enemy["enemy_atk"];
 			$enemy_def=$enemy["enemy_def"];
 			$enemy_crit=$enemy["enemy_crit"];
 			$enemy_cd=$enemy["enemy_cd"];
 			$enemy_spd=$enemy["enemy_spd"];
			$userDMG=$user_atk*$usercritical*(1-(1-$enemy_def*$defValue['value1'])/(1+$enemy_def*$defValue['value1']));

 	    }
	}
}

	private function checkSkillCD($skill,$match_id,$u_id){
		$attackhitutil=new AttackHitUtil();
		$redis_battle=Redis::connection('battle');
		$skill_id=$skill['skill_id'];
		$skill_cd=$skill['skill_cd'];
		$skill_key='skill_'.$match_id.'_'.$u_id;
		$skillTime=$redis_battle->HGET($skill_key,$$skill_id);
		$current=$attackhitutil->getMillisecond();
		if($skillTime){
			if($current-$skillTime>=$skill_cd){
				$redis_battle->HSET($skill_key,$$skill_id,$current);
				return true;
			}
			else {
				return false;
			}
		}
		else {
			$redis_battle->HSET($skill_key,$$skill_id,$current);
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

}
