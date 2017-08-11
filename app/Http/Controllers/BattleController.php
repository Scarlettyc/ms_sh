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
 				$battlekey='battle_data'.$match_id.'_'.$u_id;
 				$userData=$characterModel->where('u_id',$u_id)->first();
 				$final=$this->initialEffect($data,$userData,$map_id);
 				$redis_battle->LPUSH($battlekey,$final);
 				$response=json_encode($final,TRUE);

 			}
 	    	
		}
	}
	private function isHit($user,$occurTime,$eff,$enemyX1,$enemyX2,$enemyX3,$enemyY,$map_id){
		$mapTrap=new MapTrapUtil();
 		$stoneprotect=$mapTrap->nearStone($map_id,$userX,$userY,$enemyX,$enemyY);
 		if($stoneprotect){
 			return false;
 		}
 		else {
 		$time=time()-$occurTime;
 		$userX1=$user['x1'];
 		$userX2=$user['x2'];
 		$userX3=$user['x3'];
 		$userY=$data['y'];

		$effectX=abs($user['x1'])+$eff['eff_skill_spd']*$time;
		$effectY=abs($userY)+1;
		$bullet=$eff['eff_bullet_width'];
		$effectXfrom=$effectX-$bulle;

		if(abs($enemyX1+1)>=$effectXfrom&&abs($enemyY+1)==$effectY){
			return true;
		}
		return false;
		
	}

 	private function initialEffect($data,$user,$enemy,$map_id){

 			$userX1=$data['x1'];
 			$userX2=$data['x2'];
 			$userX3=$data['x3'];
			$userY=$data['y'];

 			$skillMstModel=new SkillMstModel();
 			$effectMstModel=new EffectionMstModel();
 			$mapTrap=new MapTrapUtil();

 			$trap=$mapTrap->getTrapEff($map_id,$userX1,$userX2,$userX3,$userY);
 			if(isset($trap)){
 				if($trap['trap_id']==1){
 					$trap['visible']=0;
 				}
 				$trap['visible']=1;
 			}
 			
 			if(array_key_exists('self_eff_id',$data)){
 				$user_self_buff=$skillMstModel->where('skill_id',$data['self_eff_id'])->first();
 				$selfEff=$effectMstModel->Where('eff_id',$user_self_buff['eff_id'])->first();
 				$atkEFF['skill_last']=$selfEff['eff_skill_x']/$selfEff['eff_skill_spd'];
 				$user['eff'][time()]['self_eff']=$selfEff;
 			}
 			if(array_key_exists('enemy_eff_id',$data)){
 				$user_ef=$skillMstModel->where('skill_id',$data['enemy_eff_id'])->first();
 				$atkEff=$effectMstModel->Where('eff_id',$user_ef['eff_id'])->first();
 				$atkEFF['skill_last']=$atkEff['eff_skill_x']/$atkEff['eff_skill_spd'];
 				$user['eff'][time()]['atk_eff']=$atkEff;
 			}				
      			$user_hp=$user['ch_hp'];
       			$user_atk=$user['ch_atk'];
       			$user_def=$user['ch_def'];
       			// $user_res=$user['res'];
       			$user_crit=$user['ch_crit'];
       			$user_cd=$user['ch_cd'];
       			$user_speed=$user['ch_spd'];      

     			$defindModel=new DefindMstModel();
     			$defValue=$defindModel->where('defind_id',8);
     			$Usercritical=$this->getCritical();
     			$userDMG=$user_atk*$Usercritical*(1-(1-$enemy_def*$defValue['value1'])/(1+$enemy_def*$defValue['value1']));
     			$userResult['ch_hp']=$user_hp;
    			$userResult['ch_atk']=$user_atk;
    			$userResult['ch_def']=$user_def;
    			$userResult['ch_crit']=$user_crit;
    			$userResult['ch_cd']=$user_cd;
    			$userResult['ch_spd']=$user_speed;
    			$userResult['time']=time();
    			$userResult['location']=['x1'=>$userX1,'x2'=>$userX2,'x3'=>$userX3,'y'=>$userY];
    			$userResult['trap']=$trap;

 				return ['user_result'=>$userResult];
 		}


 	private function calculateEffect($data,$user,$enemy,$map_i){

 			$userX1=$data['x1'];
 			$userX2=$data['x2'];
 			$userX3=$data['x3'];
			$userY=$data['y'];

 			$enemyX1=$enemy['x1'];
 			$enemyX2=$enemy['x2'];
 			$enemyX3=$enemy['x3'];
			$enemyY=$enemy['y'];


			$user_hp=$user['ch_hp'];
       		$user_atk=$user['ch_atk'];
       		$user_def=$user['ch_def'];
       			// $user_res=$user['res'];
       		$user_crit=$user['ch_crit'];
       		$user_cd=$user['ch_cd'];
       		$user_speed=$user['ch_spd'];   
			$defindModel=new DefindMstModel();
			$defValue=$defindModel->where('defind_id',13);

			$final=[];

			if(array_key_exists('eff',$key->$user)){
				foreach($user['eff'] as $eff){
				if(array_key_exists('self_eff',$eff)){
					if($eff['skill_last']<=（$eff['eff_skill_x']/$eff['eff_skill_spd'])){

						$user_hp=($user_hp+$eff['eff_ch_hp'])*(1+$eff['eff_ch_hp_per']);
 						$user_atk=($user_atk+$eff['eff_ch_atk'])*(1+$eff['eff_ch_atk_per']); 
 						$user_def=($user_def+$eff['eff_ch_def'])*(1+$eff['eff_ch_def_per']);
						$user_crit=($user_crit)*(1+$eff['eff_ch_crit_per']);
 						$user_cd=($user_cd+$eff['eff_ch_cd'])*(1+$eff['eff_ch_cd_per']);
 						$user_spd=($user_spd)*(1+$eff['eff_ch_spd_per']);
 						$user_eff_skill_cd=$eff['eff_skill_cd']-(time()-$key);
 						$user_eff_skill_spd=$eff['eff_skill_spd'];
 						$skill_last=（$eff['eff_skill_x']/$eff['eff_skill_spd'])-time()-$key;

					if($eff['skill_last']!=（$eff['eff_skill_x']/$eff['eff_skill_spd']){
						$eff['skill_last']=$eff['eff_skill_x']/$eff['eff_skill_spd']-$eff['skill_last'];
					 	$final['eff'][$key]['self_eff']=$eff;
						}
					}
				}

				if(array_key_exists('atk_eff',$eff)){
					if($this->isHit($user,$key,$eff,$enemyX1,$enemyX2,$enemyX3,$enemyY,$map_id)){
						$enemy_hp=($enemy_hp+$eff['eff_ch_hp'])*(1+$eff['eff_ch_hp_per']);
 						$enemy_atk=($enemy_atk+$eff['eff_ch_atk'])*(1+$eff['eff_ch_atk_per']);
 						$enemy_def=($enemy_def+$eff['eff_ch_def'])*(1+$eff['eff_ch_def_per']);
						$enemy_crit=($enemy_crit)*(1+$eff['eff_ch_crit_per']);
 						$enemy_cd=($enemy_cd+$eff['eff_ch_cd'])*(1+$eff['eff_ch_cd_per']);
 						$enemy_spd=($enemy_spd)*(1+$eff['eff_ch_spd_per']);
 						$enemy_eff_skill_cd=$eff['eff_skill_cd']-(time()-$key);
 						$enemy_eff_skill_spd=$eff['eff_skill_spd'];
 						$skill_last=（$eff['eff_skill_x']/$eff['eff_skill_spd'])-time()-$key;
						}

					}	
				}
			}

			// if(array_key_exists('self_eff_id',$data)){
 		// 		$user_self_buff=$skillMstModel->where('skill_id',$data['self_eff_id'])->first();
 		// 		$selfEff=$effectMstModel->Where('eff_id',$user_self_buff['eff_id'])->first();
 		// 		$atkEFF['skill_last']=$selfEff['eff_skill_x']/$selfEff['eff_skill_spd'];
 		// 		$final['eff'][time()]['self_eff']=$selfEff;
 		// 	}
 		// 	if(array_key_exists('enemy_eff_id',$data)){
 		// 		$user_ef=$skillMstModel->where('skill_id',$data['enemy_eff_id'])->first();
 		// 		$atkEff=$effectMstModel->Where('eff_id',$user_ef['eff_id'])->first();
 		// 		$atkEFF['skill_last']=$atkEff['eff_skill_x']/$atkEff['eff_skill_spd'];
 		// 		$final['eff'][time()]['atk_eff']=$atkEff;
 		// 	}

 		// 	if($user_self_effect){
 		// 		$user_hp=($user_hp+$user_self_effect['eff_ch_hp'])*(1+$user_self_effect['eff_ch_hp_per']);
 		// 		$user_atk=($user_atk+$user_self_effect['eff_ch_atk'])*(1+$user_self_effect['eff_ch_atk_per']);     
 		// 		$user_def=($user_def+$user_self_effect['eff_ch_def'])*(1+$user_self_effect['eff_ch_def_per']);
			// 	$user_crit=($user_crit)*(1+$user_self_effect['eff_ch_crit_per']);
 		// 		$user_cd=($user_cd+$user_self_effect['eff_ch_cd'])*(1+$user_self_effect['eff_ch_cd_per']);
 		// 		$user_spd=($user_spd)*(1+$user_self_effect['eff_ch_spd_per']);
 		// 		$user_eff_id=$user_self_effect['eff_id'];
 		// 		$user_eff_skill_cd=$user_self_effect['eff_skill_cd'];
 		// 		$user_eff_skill_spd=$user_self_effect['eff_skill_spd'];
 		// 		$user_eff_skill_time=time();

 		// 		$this->isHit($user,$enemy_effect,$userX1,$userX2,$userX3,$user_y,$enemyX1,$enemyX2,$enemyX3,$enemy_y)
 		// 	}
 
 		// 	if($enemy_effect){
 				
 		// 		if($this->isHit($user,$enemy_effect,$userX1,$userX2,$userX3,$user_y,$enemyX1,$enemyX2,$enemyX3,$enemy_y))
 		// 		{
			// 		$user_hp=($user_hp-$enemy_effect['eff_ch_hp'])*(1-$enemy_effect['eff_ch_hp_per']);
			// 		$user_atk=($user_atk-$enemy_effect['eff_ch_atk'])*(1-$enemy_effect['eff_ch_atk_per']);
			// 		$user_def=($user_def-$enemy_effect['eff_ch_def'])*(1-$enemy_effect['eff_ch_def_per']);
			// 		$user_crit=($user_crit)*(1-$enemy_effect['eff_ch_crit_per']);
			// 		$user_cd=($user_cd-$enemy_effect['eff_ch_cd'])*(1-$enemy_effect['eff_ch_cd_per']);
			// 		$user_spd=($user_spd)*(1-$enemy_effect['eff_ch_spd_per']);

			// 	}

 		// 	}
 		// 	if($enemy_self_effect){
			// 	$enemy_hp=($enemy_hp+){$enemy_self_effect['eff_ch_hp'])*(1+$enemy_self_effect['eff_ch_hp_per']);
			// 	$enemy_atk=($enemy_atk+$enemy_self_effect['eff_ch_atk'])*(1+$enemy_self_effect['eff_ch_atk_per']);
			// 	$enemy_def=($enemy_def+$enemy_self_effect['eff_ch_def'])*(1+$enemy_self_effect['eff_ch_def_per']);
			// 	$enemy_crit=($enemy_crit)*(1+$enemy_self_effect['eff_ch_crit_per']);
			// 	$enemy_cd=($enemy_cd+$enemy_self_effect['eff_ch_cd'])*(1+$enemy_self_effect['eff_ch_cd_per']);
			// 	$enemy_spd=($enemy_spd)*(1+$enemy_self_effect['eff_ch_spd_per']);
 		// 	}
 		// 	if($user_effect){
 		// 		if($this->isHit($user,$enemy_effect,$userX1,$userX2,$userX3,$user_y,$enemyX1,$enemyX2,$enemyX3,$enemy_y)){
 		// 		$enemy_hp=($enemy_hp-$enemy_self_effect['eff_ch_hp'])*(1-$enemy_self_effect['eff_ch_atk_per']);
 		// 		$enemy_atk=($enemy_atk-$user_effect['eff_ch_atk'])*(1-$user_effect['eff_ch_atk_per']);
 		// 		$enemy_def=($enemy_def-$enemy_self_effect['eff_ch_def'])*(1-$enemy_self_effect['eff_ch_def_per']);
 		// 		$enemy_crit=($enemy_crit)*(1-$enemy_self_effect['eff_ch_crit_per']);
 		// 		$enemy_cd=($enemy_cd-$enemy_self_effect['eff_ch_cd'])*(1-$enemy_self_effect['eff_ch_cd_per']);
 		// 		$enemy_spd=($enemy_spd)*(1-$enemy_self_effect['eff_ch_spd_per']);
 		// 		}
 		// 	}

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

 	private function mapEfft(){

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
