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
use App\Util\MapTrapUtil;
use Illuminate\Support\Facades\Redis;
use DateTime;
use Exception;
use Math;
class BattleController extends Controller
{

	public function test($data){
		return json_encode($data,TRUE);
	}
    public function battle(Request $request)
    {
    	$req=$request->getContent();
		$json=base64_decode($req);
		$redis_battle=Redis::connection('battle');
		$characterModel=new CharacterModel();
	 	//dd($json);
		$data=json_decode($json,TRUE);
		$u_id=$data['u_id'];		
		$now   = new DateTime;;
		$dmy=$now->format( 'Ymd' );
		$data=json_decode($json,TRUE);
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
 	    	if($userExist>0){	
 	    		$enenmyKey='battle_data'.$match_id.'_'.$enmey_uid;
				$existEnemy=$redis_battle->LLEN($enenmyKey);
				if($existEnemy){
					$userBattleData=$redis_battle->LRANGE($battlekey,1,1);
					$enemyJson=$redis_battle->LRANGE($battlekey,1,1);
					$enemy=json_decode($enemyJson);
					$userData=json_decode($userBattleData);
					$final=$this->calculateEffect($data,$userData,$enemy,$map_id);
					$userResult=$final['user_result'];
					$enemyResult=$final['enemy_result'];
					$userJson=json_encode($userResult,TRUE);
					$enemyJson=json_encode($enemyResult,TRUE);
					$redis_battle->LPUSH($battlekey,$userJson);
					$redis_battle->LPUSH($enenmyKey,$enemyJson);
					$response=json_encode($final,TRUE);
					return  base64_encode($response);
					}
    			else{
 	    			throw new Exception("there have some error");
 	    			}
 				}
 			else{
 				$userData=$characterModel->where('u_id',$u_id)->first();
 				$enemyData=$characterModel->where('u_id',$enmey_uid)->first();
 				$this->calculateEffect($data,$userData,$enemyData,$map_id);
 			}
 	    	
		}
	}
	private function isHit($data,$enemy,$enemyEffect,$userX,$userY,$enemyX,$enemyY){
		$mapTrap=new MapTrapUtil();
 		$stoneprotect=$mapTrap->nearStone($map_id,$userX,$userY,$enemyX,$enemyY);
 		if($stoneprotect){
 			return false;
 		}
 		else {
		$effectX=$enemyEffect['eff_ch_x'];
		$effectY=$enemyEffect['eff_ch_y'];
		$effectXtill=abs($userX)+abs($effectX);
		$effectYtill=abs($userY)+abs($effectY);
		if(abs($enemyX)<$effectXtill&&abs($enemyY)<$effectYtill){
			return true;
		}
		else {
			return false;
		}
	}
	}

 	private function calculateEffect($data,$user,$enemy,$map_id){

 			$userX=$data['x'];
			$userY=$data['y'];
 			$skillMstModel=new SkillMstModel();
 			$effectMstModel=new EffectionMstModel();
 			$mapTrap=new MapTrapUtil();
 			$trap=$mapTrap->getTrapEff($map_id,$userX,$userY);
 			$enemyX=$enemy['x'];
			$enemyy=$enemy['y'];
 			if($trap){
 				$visible=0;
 				if($trap['trap_id']==1){
 					$visible=1;
 				}
 				$trapEffHp=$trap['eff_ch_hp'];
 				$trapEffHp_per=$trap['eff_ch_hp_per'];
 				$trapEffspd=$trap['eff_ch_spd'];
 				$trapEffspd_per=$trap['eff_ch_spd_per'];
 			}

 			$user_skill=$skillMstModel->where('skill_id',$data['skill_id'])->first();
 			$enemy_skill=$skillMstModel->where('skill_id',$enemy['skill_id'])->first();
 			if($user_skill['self_eff_id']){
 			$user_self_effect=$effectMstModel->where('eff_id',$user_skill['self_eff_id'])->first();
 			$user_effect=$effectMstModel->where('eff_id',$user_skill['enemy_eff_id'])->first();
 			$enemy_self_effect=$effectMstModel->where('eff_id',$enemy_skill['self_eff_id'])->first();
 			$enemy_effect=$effectMstModel->where('eff_id',$enemy_skill['enemy_eff_id'])->first();
 			}

			$user_hp=$user['ch_hp'];
 			$user_atk=$user['ch_atk'];
 			$user_def=$user['ch_def'];
 			// $user_res=$user['res'];
 			$user_crit=$user['ch_crit'];
 			$user_cd=$user['ch_cd'];
 			$user_speed=$user['ch_spd'];

 			$enemy_hp=$enemy['ch_hp'];
 			$enemy_atk=$enemy['ch_atk'];
 			$enemy_def=$enemy['ch_def'];
 			// $enemy_res=$enemy['res'];
 			$enemy_crit=$enemy['ch_crit'];
 			$enemy_cd=$enemy['ch_cd'];
 			$enemy_speed=$enemy['ch_spd'];
 			

 			if($user_self_effect){
 				$user_hp=($user_hp+$user_self_effect['eff_ch_hp'])*(1+$user_self_effect['eff_ch_hp_per']);
 				$user_atk=($user_atk+$user_self_effect['eff_ch_atk'])*(1+$user_self_effect['eff_ch_atk_per']);     
 				$user_def=($user_def+$user_self_effect['eff_ch_def'])*(1+$user_self_effect['eff_ch_def_per']);
				$user_crit=($user_crit)*(1+$user_self_effect['eff_ch_crit_per']);
 				$user_cd=($user_cd+$user_self_effect['eff_ch_cd'])*(1+$user_self_effect['eff_ch_cd_per']);
 				$user_spd=($user_spd)*(1+$user_self_effect['eff_ch_spd_per']);
 				$user_eff_id=$user_self_effect['eff_id'];
 				$user_eff_skill_x=$user_self_effect['eff_skill_x'];
 				$user_eff_skill_y=$user_self_effect['eff_skill_y'];
 				$user_eff_skill_cd=$user_self_effect['eff_skill_cd'];
 				$user_eff_skill_spd=$user_self_effect['eff_skill_spd'];
 				$user_eff_skill_time=time();
 				$user_x=$data['userx'];
 			}
 
 			if($enemy_effect){
 				
 				if($this->isHit($user,$enemy_effect,$user_x,$user_y,$enemy_x,$enemy_y)){

				$user_hp=($user_hp-$enemy_effect['eff_ch_hp'])*(1-$enemy_effect['eff_ch_hp_per']);
				$user_atk=($user_atk-$enemy_effect['eff_ch_atk'])*(1-$enemy_effect['eff_ch_atk_per']);
				$user_def=($user_def-$enemy_effect['eff_ch_def'])*(1-$enemy_effect['eff_ch_def_per']);
				$user_crit=($user_crit)*(1-$enemy_effect['eff_ch_crit_per']);
				$user_cd=($user_cd-$enemy_effect['eff_ch_cd'])*(1-$enemy_effect['eff_ch_cd_per']);
				$user_spd=($user_spd)*(1-$enemy_effect['eff_ch_spd_per']);

				}

 			}
 			if($enemy_self_effect){
				$enemy_hp=($enemy_hp+){$enemy_self_effect['eff_ch_hp'])*(1+$enemy_self_effect['eff_ch_hp_per']);
				$enemy_atk=($enemy_atk+$enemy_self_effect['eff_ch_atk'])*(1+$enemy_self_effect['eff_ch_atk_per']);
				$enemy_def=($enemy_def+$enemy_self_effect['eff_ch_def'])*(1+$enemy_self_effect['eff_ch_def_per']);
				$enemy_crit=($enemy_crit)*(1+$enemy_self_effect['eff_ch_crit_per']);
				$enemy_cd=($enemy_cd+$enemy_self_effect['eff_ch_cd'])*(1+$enemy_self_effect['eff_ch_cd_per']);
				$enemy_spd=($enemy_spd)*(1+$enemy_self_effect['eff_ch_spd_per']);
 			}
 			if($user_effect){
 				if($this->isHit($enemy,$user_effect,$enemy_x,$enemy_y,$user_x,$user_y)){
 				$enemy_hp=($enemy_hp-$enemy_self_effect['eff_ch_hp'])*(1-$enemy_self_effect['eff_ch_atk_per']);
 				$enemy_atk=($enemy_atk-$user_effect['eff_ch_atk'])*(1-$user_effect['eff_ch_atk_per']);
 				$enemy_def=($enemy_def-$enemy_self_effect['eff_ch_def'])*(1-$enemy_self_effect['eff_ch_def_per']);
 				$enemy_crit=($enemy_crit)*(1-$enemy_self_effect['eff_ch_crit_per']);
 				$enemy_cd=($enemy_cd-$enemy_self_effect['eff_ch_cd'])*(1-$enemy_self_effect['eff_ch_cd_per']);
 				$enemy_spd=($enemy_spd)*(1-$enemy_self_effect['eff_ch_spd_per']);
 			}


 			$defindModel=new DefindMstModel();
 			$defValue=$defindModel->where('defind_id',8);
 			$Usercritical=$this->getCritical();
 			$userDMG=$user_atk*$Usercritical*(1-(1-$enemy_def*$defValue['value1'])/(1+$enemy_def*$defValue['value1']));
 			$Enemycritical=$this->getCritical();
 			$enemyDMG=$enemy_atk*$Enemycritical*(1-(1-$enemy_def*$defValue['value1'])/(1+$enemy_def*$defValue['value1']));
 			$userFinalHp=$user_hp-$enemyDMG;
 			$enemyFinalHp=$enemy_hp-$userDMG;

 			$enemyStun=$enemy_effect['eff_ch_stun'];

 			$userResult['ch_hp']=$user_hp;
			$userResult['ch_atk']=$user_atk;
			$userResult['ch_def']=$user_def;
			$userResult['ch_crit']=$user_crit;
			$userResult['ch_cd']=$user_cd;
			$userResult['ch_spd']=$user_speed;
			$userResult['ch_stun']=$enemyResult['eff_ch_stun'];

			$enemyResult['ch_hp']=$enemy_hp;
			$enemyResult['ch_atk']=$enemy_atk;
			$enemyResult['ch_def']=$enemy_def;
			$enemyResult['ch_crit']=$enemy_crit;
			$enemyResult['ch_cd']=$enemy_cd;
			$enemyResult['ch_spd']=$enemy_speed;
			$enemyResult['ch_stun']=$user_effect['eff_ch_stun'];

 		return ['user_result'=>$userResult,'enemy_result'=>$enemyResult];
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
