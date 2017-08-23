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
		if(isset($userBattleData['skill_id'])){

			$occuredTime=time()-$userBattleData['time'];
			$skill=$skillMstModel->where('skill_id',$userBattleData['skill_id'])->first();
			$eff=$normalEff->where('normal_eff_id',$skill['enemy_eff_id'])->first();
			$effspd=$eff['eff_skill_spd'];
			$direction=$eff['direction'];
			$range=$occuredTime*$effspd*$direction;
			$occurX=$eff['x']+$range;
			$occurY=$eff['y'];
			$bulletfrom=$occurX-$eff['eff_bullet_width'];
			$trap=$mapTrap->where('map_trap_id',1)->first();
			if($trap['trap_x_from']-$occurX<=1&&$trap_y_from<=$occurY&&$trap_y_to>=$occurY){
				$hit=1;
			}
			if($hit!=1){
				if(abs($range)<$eff_skill_x){
					$result['skill_id']=$userBattleData['skill_id'];
				}
			}
		}
			$result['hit']=$hit;

			if(isset($data['skill_id'])){
				$skill_id=$data['skill_id'];
				$skill=$skillMstModel->where('skill_id',$skill_id)->first();
				$eff=$normalEff->where('normal_eff_id',$skill['enemy_eff_id'])->first();
				$result['skill_id']=$skill_id;	
			}
				if($x1>$x2){
					$result['x']=$x1;
				}
				else{
					$result['x']=$x2;
				}
				$result['direction']=$direction;
				$result['y']=$y+1;
				$result['hp']=$userHP;
				$result['spd']=$userSpd;
				$result['time']=time();
				$userJson=json_encode($result,TRUE);
				$redis_battle->LPUSH($key,$userJson);

		return $userJson;
		

	}
    // public function battle($request)
    // {
  //   	//$req=$request->getContent();
		// $json=base64_decode($request);
		// $redis_battle=Redis::connection('battle');
		// $characterModel=new CharacterModel();
	 // 	//dd($json);
		// $data=json_decode($json,TRUE);
		// $u_id=$data['u_id'];		
		// $now   = new DateTime;;
		// $dmy=$now->format( 'Ymd' );
		// $data=json_decode($json,TRUE);
		// if(isset($data)){
		// 	$u_id=$data['u_id'];
		// 	$match_id=$data['match_id'];
		// 	$matchList=$redis_battle->HGET('match_list',$match_id);
		// 	$matchArr=json_decode($matchList);
		// 	if(isset($matchArr)){
		// 	 $enmey_uid=$matchArr['enmey_uid'];
		// 	 $map_id=$matchArr['map_id'];
		// 	}
		// 	else {
		// 		throw new Exception("there is no exist match");
		// 	}
 	    	
 	//     	$battlekey='battle_data'.$match_id.'_'.$u_id;
 	//     	$userExist=$redis_battle->LLEN($battlekey);
 	//     	if($userExist>0){	
 	//     		$enenmyKey='battle_data'.$match_id.'_'.$enmey_uid;
		// 		$existEnemy=$redis_battle->LLEN($enenmyKey);
		// 		if($existEnemy){
		// 			$userBattleData=$redis_battle->LRANGE($battlekey,1,1);
		// 			$enemyJson=$redis_battle->LRANGE($battlekey,1,1);
		// 			$enemy=json_decode($enemyJson);
		// 			$userData=json_decode($userBattleData);
		// 			$final=$this->calculateEffect($data,$userData,$enemy,$map_id);
		// 			$userResult=$final['user_result'];
		// 			$enemyResult=$final['enemy_result'];
		// 			$userJson=json_encode($userResult,TRUE);
		// 			$enemyJson=json_encode($enemyResult,TRUE);
		// 			$redis_battle->LPUSH($battlekey,$userJson);
		// 			$redis_battle->LPUSH($enenmyKey,$enemyJson);
		// 			$response=json_encode($final,TRUE);
		// 			return  base64_encode($response);
		// 			}
  //   			else{
 	//     			throw new Exception("there have some error");
 	//     			}
 	// 			}
 	// 		else{
 	// 			$battlekey='battle_data'.$match_id.'_'.$u_id;
 	// 			$userData=$characterModel->where('u_id',$u_id)->first();
 	// 			$final=$this->initialEffect($data,$userData,$map_id);
 	// 			$redis_battle->LPUSH($battlekey,$final);
 	// 			$response=json_encode($final,TRUE);

 	// 		}
 	    	
		// }
	// }
	// private function isHit($user,$occurTime,$eff,$enemy,$map_id){

 // 		$time=time()-$occurTime;
 // 		$userX1=$user['x1'];
 // 		$userX2=$user['x2'];
 // 		$userX3=$user['x3'];
 // 		$userY=$userX3['y'];

 // 		$enemyX1=$enemy['x1'];
 // 		$enemyX2=$enemy['x2'];
 // 		$enemyX3=$enemy['x3'];
 // 		$enemyY=$enemy['y'];


	// 	$effectX=abs($user['x1'])+$eff['eff_skill_spd']*$time;
	// 	$effectY=abs($userY)+1;
	// 	$bullet=$eff['eff_bullet_width'];
	// 	$effectXfrom=$effectX-$bullet;

	// 	$mapTrap=new MapTrapUtil();
 // 		$stoneprotect=$mapTrap->nearStone($map_id,$userX1,$userX2,$userX3,$effectXfrom,$effectX,$effectY);

 // 		if($stoneprotect){
 // 			return false;
 // 		}
 // 		else  if(abs($enemyX1+1)<=$effectXfrom&&abs($enemyX3+1)>=$effectX&&abs($enemyY+1)==$effectY){
	// 		return true;
	// 	}
	// 	return false;
		
	// }

 // 	private function initialEffect($data,$user,$enemy,$map_id){

 // 			$userX1=$data['x1'];
 // 			$userX2=$data['x2'];
 // 			$userX3=$data['x3'];
	// 		$userY=$data['y'];

 // 			$skillMstModel=new SkillMstModel();
 // 			$effectMstModel=new EffectionMstModel();
 // 			$buffEff=new BuffEffMstModel();
 // 			$mapTrap=new MapTrapUtil();
 // 			$normalEff=new NormalEffectionMstModel();
 // 			$jumpEff= new JumpEffModel();

 // 			$trap=$mapTrap->getTrapEff($map_id,$userX1,$userX2,$userX3,$userY);
 // 			if(isset($trap)){
 // 				if($trap['trap_id']==1){
 // 					$trap['visible']=0;
 // 				}
 // 				$trap['visible']=1;
 // 			}
 			
 // 			if(array_key_exists('self_eff_id',$data)){
 // 				$user_self_buff=$skillMstModel->where('skill_id',$data['self_eff_id'])->first();
 // 				$selfEff=$buffEff->Where('eff_id',$user_self_buff['eff_id'])->first();
 // 				$selfEff['self_skill_last']=$selfEff['eff_skill_x']/$selfEff['eff_skill_spd'];
 // 				$user['eff'][time()]['self_eff']=$selfEff;
 // 			}
 // 			if(array_key_exists('atk_eff_id',$data)){
 // 				$user_ef=$skillMstModel->where('skill_id',$data['enemy_eff_id'])->first();
 // 				if($user_ef['eff_group_id']==1||$user_ef['eff_group_id']==2){
 // 					$atkEff=$normalEff->where('normal_eff_id',$user_ef['eff_id'])->first();
 // 				}
 // 				else if($user_ef['eff_group_id']==3){
 // 					$atkEff=$jumpEff->where('jump_eff_id',$user_ef['eff_id'])->first();
 // 				}
 // 				else if($user_ef['eff_group_id']==4){
 // 					$atkEff=$RaEffModel->where('radiation_eff_id',$user_ef['eff_id'])->first();
 // 				}
 // 				$atkEff['atk_skill_last']=$atkEff['eff_skill_x']/$atkEff['eff_skill_spd'];
 // 				$user['eff'][time()]['atk_eff']=$atkEff;
 // 			}				
 //      			$user_hp=$user['ch_hp'];
 //       			$user_atk=$user['ch_atk'];
 //       			$user_def=$user['ch_def'];
 //       			// $user_res=$user['res'];
 //       			$user_crit=$user['ch_crit'];
 //       			$user_cd=$user['ch_cd'];
 //       			$user_speed=$user['ch_spd'];      

 //     			$defindModel=new DefindMstModel();
 //     			$defValue=$defindModel->where('defind_id',8);
 //     			$Usercritical=$this->getCritical();
 //     			$userDMG=$user_atk*$Usercritical*(1-(1-$enemy_def*$defValue['value1'])/(1+$enemy_def*$defValue['value1']));
 //     			$userResult['ch_hp']=$user_hp;
 //    			$userResult['ch_atk']=$user_atk;
 //    			$userResult['ch_def']=$user_def;
 //    			$userResult['ch_crit']=$user_crit;
 //    			$userResult['ch_cd']=$user_cd;
 //    			$userResult['ch_spd']=$user_speed;
 //    			$userResult['time']=time();
 //    			$userResult['location']=['x1'=>$userX1,'x2'=>$userX2,'x3'=>$userX3,'y'=>$userY];
 //    			$userResult['trap']=$trap;

 // 				return ['user_result'=>$userResult];
 // 		}


 // 	private function calculateEffect($data,$user,$enemy,$map_id){

 // 			$userX1=$data['x1'];
 // 			$userX2=$data['x2'];
 // 			$userX3=$data['x3'];
	// 		$userY=$data['y'];

 // 			$enemyX1=$enemy['x1'];
 // 			$enemyX2=$enemy['x2'];
 // 			$enemyX3=$enemy['x3'];
	// 		$enemyY=$enemy['y'];


	// 		$user_hp=$user['ch_hp'];
 //       		$user_atk=$user['ch_atk'];
 //       		$user_def=$user['ch_def'];
 //       			// $user_res=$user['res'];
 //       		$user_crit=$user['ch_crit'];
 //       		$user_cd=$user['ch_cd'];
 //       		$user_speed=$user['ch_spd'];   

 //       		$self_skill_last=0;
 //       		$atk_skill_last=0;
 //       		$user_stun=0;
 //       		$enemy_stun=0;

	// 		$defindModel=DefindMstModel();
	// 		$defValue=$defindModel->where('defind_id',13);


	// 		$final=[];

	// 		if(array_key_exists('eff',$key->$user)){
	// 			$result=$this->getEFf($effects,$user_hp,$user_atk,$user_def,$user_crit,$user_cd,$user_spd,$user_eff_skill_cd,$user_eff_skill_spd,$self_skill_last,$enemy_hp,$enemy_atk,$enemy_def,$enemy_crit,$enemy_cd,$enemy_spd,$enemy_eff_skill_cd,$enemy_eff_skill_spd,$atk_skill_last,$enemy_stun);
			
	// 				$user_hp=$result["user_hp"];
 // 					$user_atk=$result["user_atk"];
 // 					$user_def=$result["user_def"];
 // 					$user_crit=$result["user_crit"];
 // 					$user_cd=$result["user_cd"];
 // 					$user_spd=$result["user_spd"];
 // 					$user_eff_skill_cd=$result["user_eff_skill_cd"];
 // 					$user_eff_skill_spd=$result["user_eff_skill_spd"];
 // 					$self_skill_last=$result["self_skill_last"];
	// 				$enemy_hp=$result["enemy_hp"];
 // 					$enemy_atk=$result["enemy_atk"];
 // 					$enemy_def=$result["enemy_def"];
 // 					$enemy_crit=$result["enemy_crit"];
 // 					$enemy_cd=$result["enemy_cd"];
 // 					$enemy_spd=$result["enemy_spd"];
 // 					$enemy_eff_skill_cd=$result["enemy_eff_skill_cd"];
 // 					$enemy_eff_skill_spd=$result["enemy_eff_skill_spd"];
 // 					$atk_skill_last=$result["atk_skill_last"];
 // 					$enemy_stun=$result['enemy_stun'];
 // 					if(isset($result['eff'])){
 // 						$userResult['eff']=$result['eff'];
 // 					}

	// 		}

	// 		if(array_key_exists('eff',$key->$enemy)){
				
	// 			$result=$this->getEFf($effects,$user_hp,$user_atk,$user_def,$user_crit,$user_cd,$user_spd,$user_eff_skill_cd,$user_eff_skill_spd,$self_skill_last,$enemy_hp,$enemy_atk,$enemy_def,$enemy_crit,$enemy_cd,$enemy_spd,$enemy_eff_skill_cd,$enemy_eff_skill_spd,$atk_skill_last,$user_stun);
			
	// 				$user_hp=$result["user_hp"];
 // 					$user_atk=$result["user_atk"];
 // 					$user_def=$result["user_def"];
 // 					$user_crit=$result["user_crit"];
 // 					$user_cd=$result["user_cd"];
 // 					$user_spd=$result["user_spd"];
 // 					$user_eff_skill_cd=$result["user_eff_skill_cd"];
 // 					$user_eff_skill_spd=$result["user_eff_skill_spd"];
 // 					$self_skill_last=$result["self_skill_last"];
	// 				$enemy_hp=$result["enemy_hp"];
 // 					$enemy_atk=$result["enemy_atk"];
 // 					$enemy_def=$result["enemy_def"];
 // 					$enemy_crit=$result["enemy_crit"];
 // 					$enemy_cd=$result["enemy_cd"];
 // 					$enemy_spd=$result["enemy_spd"];
 // 					$enemy_eff_skill_cd=$result["enemy_eff_skill_cd"];
 // 					$enemy_eff_skill_spd=$result["enemy_eff_skill_spd"];
 // 					$atk_skill_last=$result["atk_skill_last"];
 // 					$user_stun=$result['enemy_stun'];
	// 				if(isset($result['eff'])){
 // 						$userResult['eff']=$result['eff'];
 // 					}

	// 			}

 // 			$defValue=$defindModel->where('defind_id',8);
 // 			$usercritical=$this->getCritical();
 // 			$userDMG=$user_atk*$usercritical*(1-(1-$enemy_def*$defValue['value1'])/(1+$enemy_def*$defValue['value1']));
 // 			$Enemycritical=$this->getCritical();
 // 			$enemyDMG=$enemy_atk*$Enemycritical*(1-(1-$enemy_def*$defValue['value1'])/(1+$enemy_def*$defValue['value1']));
 // 			$userFinalHp=$user_hp-$enemyDMG;
 // 			$enemyFinalHp=$enemy_hp-$userDMG;

 // 			$enemyStun=$enemy_effect['eff_ch_stun'];

 // 			$userResult['ch_hp']=$user_hp;
	// 		$userResult['ch_atk']=$user_atk;
	// 		$userResult['ch_def']=$user_def;
	// 		$userResult['ch_crit']=$user_crit;
	// 		$userResult['ch_cd']=$user_cd;
	// 		$userResult['ch_spd']=$user_speed;
	// 		$userResult['ch_stun']=$user_stun;


	// 		$enemyResult['ch_hp']=$enemy_hp;
	// 		$enemyResult['ch_atk']=$enemy_atk;
	// 		$enemyResult['ch_def']=$enemy_def;
	// 		$enemyResult['ch_crit']=$enemy_crit;
	// 		$enemyResult['ch_cd']=$enemy_cd;
	// 		$enemyResult['ch_spd']=$enemy_speed;
	// 		$enemyResult['ch_stun']=$enemy_stun;

 // 		return ['user_result'=>$userResult,'enemy_result'=>$enemyResult];

 // 	}


 // 	private function getEFf($effects,$user_hp,$user_atk,$user_def,$user_crit,$user_cd,$user_spd,$user_eff_skill_cd,$user_eff_skill_spd,$self_skill_last,$enemy_hp,$enemy_atk,$enemy_def,$enemy_crit,$enemy_cd,$enemy_spd,$enemy_eff_skill_cd,$enemy_eff_skill_spd,$atk_skill_last,$enemy_stun){

 // 		foreach($effects as $eff){
	// 			if(array_key_exists('self_eff',$eff)){
	// 				if($eff['skill_last']<=（$eff['eff_skill_x']/$eff['eff_skill_spd'])){

	// 					$user_hp=($user_hp+$eff['eff_ch_hp'])*(1+$eff['eff_ch_hp_per']);
 // 						$user_atk=($user_atk+$eff['eff_ch_atk'])*(1+$eff['eff_ch_atk_per']); 
 // 						$user_def=($user_def+$eff['eff_ch_def'])*(1+$eff['eff_ch_def_per']);
	// 					$user_crit=($user_crit)*(1+$eff['eff_ch_crit_per']);
 // 						$user_cd=($user_cd+$eff['eff_ch_cd'])*(1+$eff['eff_ch_cd_per']);
 // 						$user_spd=($user_spd)*(1+$eff['eff_ch_spd_per']);
 // 						$user_eff_skill_cd=$eff['eff_skill_cd']-(time()-$key);
 // 						$user_eff_skill_spd=$eff['eff_skill_spd'];
 // 						$self_skill_last=（$eff['eff_skill_x']/$eff['eff_skill_spd'])-time()-$key;
	// 				if($eff['self_skill_last']!=（$eff['eff_skill_x']/$eff['eff_skill_spd']){
	// 					$eff['self_skill_last']=$eff['eff_skill_x']/$eff['eff_skill_spd']-$eff['self_skill_last'];
	// 				 	// $final['eff'][$key]['self_eff']=$eff;
	// 				 	$result['eff']['self_eff'][]=$eff;
	// 						}
			
	// 				}
	// 			}

	// 			if(array_key_exists('atk_eff',$eff)){
	// 				if($this->isHit($user,$occurTime,$eff,$enemy,$map_id)){
	// 					$enemy_hp=($enemy_hp+$eff['eff_ch_hp'])*(1+$eff['eff_ch_hp_per']);
 // 						$enemy_atk=($enemy_atk+$eff['eff_ch_atk'])*(1+$eff['eff_ch_atk_per']);
 // 						$enemy_def=($enemy_def+$eff['eff_ch_def'])*(1+$eff['eff_ch_def_per']);
	// 					$enemy_crit=($enemy_crit)*(1+$eff['eff_ch_crit_per']);
 // 						$enemy_cd=($enemy_cd+$eff['eff_ch_cd'])*(1+$eff['eff_ch_cd_per']);
 // 						$enemy_spd=($enemy_spd)*(1+$eff['eff_ch_spd_per']);
 // 						$enemy_eff_skill_cd=$eff['eff_skill_cd']-(time()-$key);
 // 						$enemy_eff_skill_spd=$eff['eff_skill_spd'];
 // 						$atk_skill_last=（$eff['eff_skill_x']/$eff['eff_skill_spd'])-time()-$key;
	// 					$enemy_stun=$eff['eff_ch_stun'];
	// 					if($eff['atk_skill_last']!=（$eff['eff_skill_x']/$eff['eff_skill_spd']){
	// 					$eff['atk_skill_last']=$eff['eff_skill_x']/$eff['eff_skill_spd']-$eff['atk_skill_last'];
	// 				 	// $final['eff'][$key]['self_eff']=$eff;
	// 				 	$result['eff']['atk_eff'][]=$eff;
	// 							}
	// 						}
	// 					}
	// 				}	
	// 			}

	// 	$result["user_hp"]=$user_hp;
 // 		$result["user_atk"]=$user_atk;
 // 		$result["user_def"]=$user_def;
 // 		$result["user_crit"]=$user_crit;
 // 		$result["user_cd"]=$user_cd;
 // 		$result["user_spd"]=$user_spd;
 // 		$result["user_eff_skill_cd"]=$user_eff_skill_cd;
 // 		$result["user_eff_skill_spd"]=$user_eff_skill_spd;
 // 		$result["self_skill_last"]=$self_skill_last;
	// 	$result["enemy_hp"]=$enemy_hp;
 // 		$result["enemy_atk"]=$enemy_atk;
 // 		$result["enemy_def"]=$enemy_def;
 // 		$result["enemy_crit"]=$enemy_crit;
 // 		$result["enemy_cd"]=$enemy_cd;
 // 		$result["enemy_spd"]=$enemy_spd;
 // 		$result["enemy_eff_skill_cd"]=$enemy_eff_skill_cd;
 // 		$result["enemy_eff_skill_spd"]=$enemy_eff_skill_spd;
 // 		$result["atk_skill_last"]=$atk_skill_last;
 // 		$result['enemy_stun']=$enemy_stun;
 // 		return $result;

 // 	}

	// private function getCritical(){
 // 			$defindModel=new DefindMstModel();
 // 			$critcalRound=$defindModel->where('defind_id',6)->first();
 // 			$critalNumer=random($critcalRound['value1'],$critcalRound['value2']);
 // 			$critcalTimes=$defindModel->where('defind_id',7)->first();
 // 			if($critalNumer<$user_crit){
 // 				$critical=$critcalTimes['value2'];
 // 			}
 // 			else {
 // 				$critical=$critcalTimes['value2'];
 // 			}
 // 			return $critical;
 // 		}
 // 	}

 // 	public function longDistanceAttack(Request $request)
 // 	{
 // 		$req=$request->getContent();
 // 		$data=json_decode($req,TRUE);

 // 		$DistanceAttackUtil=new DistanceAttackUtil;
 // 		$NormalEffectionMstModel=new NormalEffectionMstModel;
 // 		$result=[];

 		
 // 	}
}
