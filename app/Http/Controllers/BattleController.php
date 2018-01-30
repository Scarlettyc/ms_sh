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

	public function test($data,$clientInfo){
		$now   = new DateTime;;
		$dmy=$now->format( 'Ymd' );
		$x=$data['x'];
		$y=$data['y'];
		$u_id=$data['u_id'];
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
			$enemy_clientId=$battleData['enmey_client'];
			$battlekey='battle_data'.$match_id.'_'.$u_id;
			$userExist=$redis_battle->LLEN($battlekey);
		
			$charData=[];
			if($userExist<=1){
				$charData=$characterModel->select('ch_hp_max','ch_stam','ch_atk','ch_armor','ch_crit')->where('u_id',$u_id)->first();
			}
			else{
				$userJson=$redis_battle->LRANGE($battlekey,0,0);
				foreach ($userJson as $key => $each) {
					$userData=json_decode($each,TRUE);
					$charData['ch_hp_max']=$userData['ch_hp_max'];
					$charData['ch_stam']=$userData['ch_stam'];
					$charData['ch_atk']=$userData['ch_atk'];
					$charData['ch_crit']=$userData['ch_crit'];
				}
		
			}
		
		$charData['x']=$x;		
		$charData['y']=$y;
		$charData['time']=time();
		$charData['address']=$clientInfo['address'];
		$charData['port']=$clientInfo['port'];
		$result['end']=0;
		if(isset($data['skill_id'])){
			$charData['skill_id']=$data['skill_id'];
			$skill_group=$skillModel->select('skill_group')->where('skill_id',$charData['skill_id'])->first();
			$charData['skill_group']=$skill_group['skill_group'];
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
			foreach ($enemyJson as $key => $each) {
			   $enmeyData=json_decode($each,TRUE);
			   		$enemy_charData['x']=$enmeyData['x'];
					$enemy_charData['y']=$enmeyData['y'];
					$enemy_charData['ch_hp_max']=$enmeyData['ch_hp_max'];
					$enemy_charData['ch_stam']=$enmeyData['ch_stam'];
					$enemy_charData['ch_atk']=$enmeyData['ch_atk'];
					$enemy_charData['ch_crit']=$enmeyData['ch_crit'];
				if(isset($enmeyData['skill_id'])){
					$enemy_charData['skill_id']=$enmeyData['skill_id'];
					$enemy_charData['skill_group']=$enmeyData['skill_group'];
					$atkeff=$attackhitutil->getatkEff($enemy_charData['skill_id'],$charData,$enemy_charData,$clientId,$enemy_clientId);
					if($atkeff){ 
						$enemy_atk=$enemy_charData['ch_atk'];
						$randCrit=rand(1,100);
						if($randCrit<=$enemy_charData['ch_crit']){
							$critBool=2;
						}else{
							$critBool=1;
						}

						if($enemy_charData['skill_group']==0){
							$enemy_atk=$enemy_charData['ch_atk']*$atkeff['eff_skill_atk_point'];

							$enemyDMG=$enemy_atk*$critBool;
 	 					}

 	 					else if($enemy_charData['skill_group']==1){
 	 					$enemy_atk=$enemy_charData['ch_atk']*$atkeff['eff_skill_atk_point']*$atkeff['eff_skill_damage_point']+pow($enemy_charData['ch_lv'],2)*2;
 	 					$enemyDMG=($atkeff['eff_skill_atk_point']*$enemy_atk+$enemy_charData['ch_crit']*(1-(1-$charData['ch_stam'])/(1+$charData['ch_stam'])));
 	 					
 	 					$hpMax=$charData['ch_hp_max'];
						$charData['ch_hp_max']=round($hpMax-$enemyDMG);
				}
				}
				if(isset($enmeyData['end'])&&$enmeyData['end']==0){
					$result['end']=2;
				}
			}
		}
			$enemy_charData['time']=time();
			// $enemyJson=json_decode($enemy_charData,TRUE);
		$result['user_data']=$charData;
		if($clientId>$enemy_clientId){
			$enemy_charData['x']=-($enemy_charData['x']);
		}
		$result['enemy_data']=$enemy_charData;
		if($charData['ch_hp_max']<=0){
			$result['end']=1;
		}
		if($clientId>$enemy_clientId){
			$charData['x']=-$charData['x'];
		}
		$charData['end']=$result['end'];
		$charJson=json_encode($charData);
		$redis_battle->LPUSH($battlekey,$charJson);
		$response=json_encode($result,TRUE);
		return  $response;
			}
		}
		else {
			return null;
		}
	}

	private function getMillisecond() {
		list($t1, $t2) = explode(' ', microtime());     
		return (float)sprintf('%.0f', (floatval($t1) + floatval($t2)) * 1000);  
}


  private function BattleNormalRewards($u_id,$map_id){
  	$characterModel=new CharacterModel();
  	$baNorReward=new BattleNormalRewardsMst();
  	$chaEffutil=new CharSkillEffUtil();
  	$datetime=$now->format('Y-m-d h:m:s');
	$charData=$characterModel->where('u_id',$u_id)->first();
	$cha_ranking=$charData['ch_ranking'];
  	$norReward=$baNorReward->where('map_id',$map_id)->where('ranking',$cha_ranking)->where('start_date','<',$datetime)->where('end_date','>',$datetime)->get();
  	$count=count($norReward);
  	$isLevelUP=0;
	shuffle($norReward);
	$baggageUtil=new BaggageUtil();
	$baggageUtil->insertToBaggage($u_id,$norReward);
	if($norReward['exp']>0){
	$chaEffutil->levelUP($u_id,$norReward['exp']);
	$isLevelUP=1;
		}
	return $isLevelUP;
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
	$chaEffutil=new CharSkillEffUtil();

  	$levels=$levelupMst->where('level','<',$lv)->where('exp','<',$exp)->orderBy('level','DESC')->get();
  	if(isset($levels)){
  		foreach ($levels as $key => $level) {
  			$baggageUtil->levelMissionReward($u_id,$level['level']);
  		}
  		 $characterModel->update(array('ch_lv'=>$levels[0]['level'],'update_at'=>$datetime))->where('u_id',$u_id);
  		 $chaEffutil->calculatCharEq($u_id);
  		 return 1;
  	}
  		return 0;
  }

	public function battle($u_id,$enmey_uid,$data)
{	    //$req=$request->getContent();
		//$json=base64_decode($request);
		$data=json_decode($request,TRUE);
		$redis_battle=Redis::connection('battle');
		$characterModel=new CharacterModel();
		$u_id=$data['u_id'];		
		$now   = new DateTime;;
		$dmy=$now->format( 'Ymd' );
		$data=json_decode($json,TRUE);
		$skillMstModel=new SkillMstModel();
		$battlekey='battle_data'.$match_id.'_'.$u_id;
 	    $userExist=$redis_battle->LLEN($battlekey);
 	    $attackhitutil=new AttackHitUtil();
 	    $defindModel=new DefindMstModel();
 	    $eqAttr=new EqAttrmstModel();
 	    $eqModel=new EquipmentMstModel();
     	$defValue=$defindModel->where('defind_id',8);
     	list($t1, $t2) = explode(' ', microtime());
		$mileTime=(float)sprintf('%.0f',(floatval($t1)+floatval($t2))*1000);
		$effResult=[];
		$userData=$characterModel->where('u_id',$$u_id)->first();
		$enemy=$characterModel->where('u_id',$enmey_uid)->first();
		$user_hp=$userData['ch_hp_max;'];
		$enmey_hp=$enemy['ch_hp_max'];
		$enemyKey='battle_data'.$match_id.'_'.$enmey_uid;
     	if($userExist>0){
     		$userBattleData=$redis_battle->LRANGE($battlekey,0,0);
			$enemyJson=$redis_battle->LRANGE($enemyKey,0,0);
			if($userBattleData&&$enemyJson){
				$enemy=json_decode($enemyJson);
				$userData=json_decode($userBattleData);
				$user_hp=$userData['ch_hp'];
				$enmey_hp=$enemy['ch_hp'];
			}
		}
		$effects=[];
		$enemy_effects=[];
		if($data['skill']){
			foreach ($userData['skill'] as $key => $skill) {

				$checkCD=$this->checkSkillCD($skill['skill_id'],$match_id,$u_id);
				if($checkCD){
					$effResult=$attackhitutil->getSelfEff($skill['skill_id'],$user,$enemy,$skills['direction'],$skills['occur_time']);	

				$effects[]=$effResult;
				}
			}
			
		}

		if($userData['skill']){
				foreach ($userData['skill'] as $key => $skills) {
					$effResult=$attackhitutil->getSelfEff($skills['skill_id'],$user,$enemy,$skills['direction'],$skills['occur_time']);
					$effects[]=$effResult;
				}
		}

		if($enemy['skill']){
				foreach ($enemy['skill'] as $key => $skills) {
					$enemyEffResult=$attackhitutil->getEnmeyEff($skills['skill_id'],$user,$enemy,$skills['direction'],$skills['occur_time']);

					$enemy_effects[]=$enemyEffResult;
				}
		}
			
 			//$user_shield=$userData['ch_shield'];
			$eqAtt=$eqModel->select('equ_attribute_id')->wherein('equ_id',[$userData['w_id'],$userData['m_id'],$userData['core_id']]);
			$eqAtk= $eqAttr->sum('eff_ch_atk')->wherein('equ_att_id',$eqAtt);
			$enemyEqAtt=$eqModel->select('equ_attribute_id')->wherein('equ_id',[$userData['w_id'],$enemy['m_id'],$enemy['core_id']]);
			$enemyEqAtk=$eqAtt->sum('eff_ch_atk')->wherein('equ_att_id',$enemyEqAtt);

			
  			$user_atk=$userData['ch_atk']+$eqAtk;
  			$user_def=$userData['ch_def'];
  			$user_crit=$userData['ch_crit '];
  			$user_cd=$userData['ch_cd'];
  			$user_spd=$userData['ch_spd'];
  			$enemy_atk=$enemy['ch_atk']+$enemyEqAtk;

  		foreach($effects as $effResult){
	 		if(isset($effResult['selfbuff'])){
	 			$selfBuff=$effResult['selfbuff'];
       			$user_hp=($user_hp+$selfBuff['eff_ch_hp'])*(1+$selfBuff['eff_ch_hp_per']);
 				$user_atk=$user_atk*(1+$selfBuff['eff_ch_hp_per']);
 				$user_def=$user_def*(1+$selfBuff['eff_ch_def_per']);
 				$user_crit=$user_crit*(1+$selfBuff['eff_ch_crit_per']);
 				$user_cd=$user_cd*(1-$selfBuff['eff_ch_cd']);
 				$user_spd=$user_spd*(1-$selfBuff['eff_ch_spd_per']);
 				$user_stuck=$selfBuff['eff_ch_stuck'];
 				$user_clear_buff=$selfBuff['eff_ch_clear_buff'];
	 		}
		}
		foreach($enemy_effects as $enemyEffResult){

		if(isset($enemyEffResult['enemyBuff'])){

			$userBuff=$enemyEffResult['userBuff'];
     		$user_hp=($user_hp+$userBuff['eff_ch_hp'])*(1-$userBuff['eff_ch_hp_per']);
 			$user_atk=$user_atk*(1-$userBuff['eff_ch_hp_per']);
 			$user_def=$user_def*(1-$userBuff['eff_ch_def_per']);
 			$user_crit=$user_crit*(1-$userBuff['eff_ch_crit_per']);
 			$user_cd=$user_cd*(1+$userBuff['eff_ch_cd']);
 			$user_spd=$user_spd*(1+$userBuff['eff_ch_spd_per']);
 			$user_stun=$userBuff['eff_ch_stun'];
 			$user_stuck=$userBuff['eff_ch_stuck'];
	 	}

 	   		if(isset($enemyEffResult['atk_eff']['eff'])&&$enemyEffResult['atk_eff']['hit']==1){
 	 				$atkeff=$enemyEffResult['atkeff']['eff'];
 	 				if($enemyEffResult['skill_group']==0){
					$enemy_atk=$enemy_atk*$atkeff['eff_skill_atk_point'];
 	 			
 	 				}

 	 				else if($enemyEffResult['skill_group']==1){
 	 				$enemy_atk=$enemy_atk*$atkeff['eff_skill_atk_point']*$atkeff['eff_skill_damage_point']+pow($enemy['ch_lv'],2)*2;
 	 				}
 	 				$enemyDMG=($atkeff['eff_skill_atk_point']*$enemy_atk+$enemy['eff_skill_base'])*$enemyCritical*(1-(1-$user_def)/(1+$user_def));

 					$user_hp=$user_hp-$enemyDMG;
 					if($enemyEffResult['atkeff']['end']==0){
 						$userBattleData=$redis_battle->LRANGE($battlekey,0,0);
						$enemyJson=$redis_battle->LRANGE($enemyKey,0,0);
 			}

		}
	}
			$battleCheck=$this->winCheck($user_hp,$enmey_hp,$u_id,$map_id);
			$result['win']=$battleCheck['win'];
			$result['battle_end']=$battleCheck['end'];
			$result['level_uP']=$battleCheck['leveluP'];
			$result['ch_hp']=$user_hp;
			$result['ch_atk']=$user_atk;
			$result['ch_def']=$user_def;
			$result['ch_crit']=$ch_crit;
			$result['ch_cd']=$ch_crit;
  			$result['ch_spd']=$user_spd; 		
  			$response=json_encode($result,TRUE);
  			$redis_battle->LPUSH($battlekey,$response);
  			return 	$response;
	}


	private function winCheck($userHp,$enemyHP,$u_id,$map_id){
		$win=0;
		$end=0;
		if($userHp>0&&$enemyHP<=0){
		 	$win=1;
		 	$end=1;
		 	$isLevelUP=$this->BattleNormalRewards($u_id,$map_id);
		 	$this->BattleSpeRewards($u_id,$map_id);

		}
		else if($userHp<=0&&$enemyHP>0||$userHp<=0&&$enemyHP<=0){
			$win=0;
		 	$end=1;
		 	$isLevelUP=$this->BattleNormalRewards($u_id,$map_id);
		}
		return ['win'=>$win,'end'=>$end,'leveluP'=>$isLevelUP];
	}

	private function checkSkillCD($skill,$match_id,$u_id){
		$attackhitutil=new AttackHitUtil();
		$redis_battle=Redis::connection('battle');
		$skill_id=$skill['skill_id'];
		$skill_cd=$skill['skill_cd'];
		$skill_key='skill_'.$match_id.'_'.$u_id;
		$skillTime=$redis_battle->HGET($skill_key,$$skill_id);
		$current=$this->getMillisecond();
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



 	 public function getData($data){
 	 	$now   = new DateTime;
 	 	$dmy=$now->format( 'Ymd' );
 	 	$match_id=$data['match_id'];
 	 	$redis_battle=Redis::connection('battle');
		$match_id=$data['match_id'];
		$matchList=$redis_battle->HGET('match_list',$match_id);
		$u_id=$data['u_id'];
		
		$matchArr=json_decode($matchList,TRUE);
		Log::info($matchArr);

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
 	 	$header=$request->header('Content-Type');
 		$req=$request->getContent();
		$json=base64_decode($req);
	 	$now   = new DateTime;
		$dmy=$now->format( 'Ymd' );
		$data=json_decode($json,TRUE);
		$charData['address']='1111';
		$charData['port']=2;
		$result=$this->test($data,$charData);
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
