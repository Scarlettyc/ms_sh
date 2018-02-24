<?php
namespace App\Util;
use App\Http\Requests;
use App\EffectionMstModel;
use App\SkillMstModel;
use App\MapStoneRelationMst;
use App\AtkEffectionMst;
use App\BuffEffectionMst;
use App\Util\MapTrapUtil;
use App\DefindMstModel;
use Exception;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\Redis;
use Log;

class AttackHitUtil
{
	public function getSelfEff($skill_id,$user,$enemy,$occurtime){
		$skillModel=new SkillMstModel();
 		$buffEffModel= new BuffEffectionMst();
 		$atkEffModel=new AtkEffectionMst();
		$skillData=$skillModel->where('skill_id',$skill_id)->first();
		$result=[];
		if($skillData['self_buff_eff_id']!=0){
			$mileSecond=$this->getMillisecond();
			$buffEff=$buffEffModel->where('buff_eff_id',$skillData['self_eff_id'])->first();
			if($mileSecond-$occurtime<=$buffEff['eff_skill_dur']){
				$result['selfbuff']=$buffEff;
			}
		}
		return $result;
	}

	public function getatkEff($skill_id,$user,$enemy,$clientID,$enemy_clientId,$user_direction,$enemy_direction){
		$atkEffModel=new AtkEffectionMst();
		$skillModel=new SkillMstModel();
		$tmp=1;
		if($clientID>$enemy_clientId){
			$user['x']=-$user['x'];
			$tmp=-1;
			$user_direction=-$user_direction;
		}

		$skillData=$skillModel->select('atk_eff_id')->where('skill_id',$skill_id)->first();
		$atkEff=$atkEffModel->where('atk_eff_id',$skillData['atk_eff_id'])->first();
		$effXfrom=$enemy['x'];
		$effYfrom=$enemy['y'];

		Log::info(abs($user['x']-$enemy['x'])<$atkEff['eff_skill_hit_width']&&($user['x']-$enemy['x'])*$enemy_direction>0);

			if(abs($user['x']-$enemy['x'])<$atkEff['eff_skill_hit_width']&&($user['x']-$enemy['x'])*$enemy_direction>0){
				return $atkEff;
			}
 			// if(abs($user_direction*$user['x']-($enemy_direction)*$enemy['x'])<=$atkEff['eff_skill_hit_width']&&abs($user['y']-$enemy['y'])<=$atkEff['eff_skill_hit_henght']){
 		 // 		return $atkEff;
 		 // 	}
 		 	else {
 		 		return null;
 		 	}
	}


	public function getconstantEff($skill_id,$occurtime,$user,$enemy,$clientID,$enemy_clientId,$user_direction,$enemy_direction,$constantEff){
		$atkEffModel=new AtkEffectionMst();
		$skillModel=new SkillMstModel();
		$buffEffModel= new BuffEffectionMst();
		if($clientID>$enemy_clientId){
			$user['x']=-$user['x'];
		}
		if(isset($constantEff['enemy_buff_eff_id'])&&time()-$occurtime<$constantEff['enemy_buff_constant_time']){
			$result['enemy_buff']=$this->buffStatus($constantEff['enemy_buff_eff_id']);

		}

		if(isset($constantEff['atk_eff_id'])){
			$atkEff=$atkEffModel->where('atk_eff_id',$skillData['atk_eff_id'])->first();
			if($atkEff['eff_skill_move_distance']>0){
				if($constantEff['start_x']+$atkEff['eff_skill_move_distance']-$user['x']<=2){
				 if(abs($user_direction*$user['x']-($enemy_direction)*$enemy['x'])<=$atkEff['eff_skill_hit_width']&&abs($user['y']-$enemy['y'])<=$atkEff['eff_skill_hit_width']){
 		 			$result['atkEff']=$atkEff;
				}

			}
			else if(time()-$occurtime<$constantEff['atk_constant_time']) {
				if(abs($user_direction*$user['x']-($enemy_direction)*$enemy['x'])<=$atkEff['eff_skill_hit_width']&&abs($user['y']-$enemy['y'])<=$atkEff['eff_skill_hit_width']){
 		 			$result['atkEff']=$atkEff;
 		 		}
			}
			} 
		}
		return $result;
	}

	public function buffStatus($buff_id){
		$buffEff=$buffEffModel->where('eff_id',$buff_id)->first();
		switch ($buffEff['eff_buff_type']) {
			case 7:
				$eff_ch_res_per=$buffEff['eff_value1'];
				$result['eff_ch_res_per']=$eff_ch_res_per;
				break;
			case 9:
				$eff_ch_uncontrollable=$buffEff['eff_value1'];
				$result['eff_ch_uncontrollable']=$eff_ch_uncontrollable;
				break;
			case 6;
			   	$eff_ch_invincible=$buffEff['eff_value1'];
				$result['eff_ch_invincible']=$eff_ch_invincible;
				break;

			}
			return $result;

	} 

	public function checkEffConstant($skill_id,$x){
    	$skillModel=new SkillMstModel();
    	$skill_data=$skillModel->where('skill_id',$skill_id)->first();
    	if($skill_data['buff_constant_time']!=0){
    		$result['self_buff_eff_id']=$skill_data['self_buff_eff_id'];
    		$result['buff_constant_time']=$skill_data['buff_constant_time'];
    		$result['buff_last_time']=$skill_data['buff_constant_time'];
    	}
    	if($skill_data['enemy_buff_constant_time']!=0){
    		$result['enemy_buff_eff_id']=$skill_data['enemy_buff_eff_id'];
    		$result['enemy_buff_constant_time']=$skill_data['enemy_buff_constant_time'];
    		$result['enemy_buff_last_time']=$skill_data['enemy_buff_constant_time'];
    	}
    	if($skill_data['atk_constant_time']!=0){
    		$atkEffModel=new AtkEffectionMst();
    		$atkEff=$atkEffModel->where('atk_eff_id',$skill_id)->first();
    		$result['atk_eff_id']=$skill_data['atk_eff_id'];
    		$result['start_x']=$x;
    		$result['atk_constant_time']=$skill_data['atk_constant_time'];
    		$result['atk_last_time']=$skill_data['atk_constant_time'];
    	}
    	return $result;
    }
    
    public function haveEffConstant($constantEff,$skill_occur_time){
    		if(isset($constantEff['buff_constant_time'])&&time()-$skill_occur_time<$constantEff['buff_constant_time']){
    		$result['self_buff_eff_id']=$skill_data['self_buff_eff_id'];
    		$result['buff_constant_time']=$skill_data['buff_constant_time'];
    		$result['buff_last_time']=time()-$skill_occur_time-$skill_data['buff_constant_time'];
    	}
    	if(isset($constantEff['enemy_buff_constant_time'])&&time()-$skill_occur_time<$constantEff['enemy_buff_constant_time']){
    		$result['enemy_buff_eff_id']=$skill_data['enemy_buff_eff_id'];
    		$result['enemy_buff_constant_time']=$skill_data['enemy_buff_constant_time'];
    		$result['enemy_buff_last_time']=time()-$skill_occur_time-$skill_data['enemy_buff_constant_time'];
    	}
    	if(isset($constantEff['atk_constant_time'])&&time()-$skill_occur_time<$constantEff['atk_constant_time']){
    		if(isset($constantEff['start_x'])){
    			$result['start_x']=$constantEff['start_x'];
    		}
    		$result['atk_eff_id']=$skill_data['atk_eff_id'];
    		$result['atk_constant_time']=$skill_data['atk_constant_time'];
    		$result['atk_last_time']=time()-$skill_occur_time-$skill_data['atk_constant_time'];
    	}
    	return $result;
    }
	private function checkHit($map_id,$atkEff,$direction,$user,$enemy)
	{
		$mapUtil=new MapTrapUtil();
		$defindMst=new DefindMstModel();
		$defindData=$defindMst->where('defind_id',17)->first();

 				
			$distance=0;
			$effXfrom=$user['x'];
			$effYfrom=$user['x'];
			// if($atkEff['eff_skill_circle_center']==0){
			// 	if($direction>=0)
			// 		{
			// 			$effX=$user['x']+$defindData['value1'];
						
			// 		}
			// 		else {
			// 			$effX=$user['x']-$defindData['value1'];
			// 		}
			// 		$effY=$user['y'];				
			// }
 		// 	else if($atkEff['eff_skill_circle_center']==1){
 		// 		$effX=$user['x'];
 		// 		$effY=$user['y'];			
 		// 	}

 		// 	else if($atkEff['eff_skill_circle_center']==2){
 		// 		$effY=$user['y']+$defindData['value2'];
 		// 		$effX=$user['x'];	
 		// 	}
 			// $radius=$atkEff['eff_skill_radius'];

 			$interrput=$this->checkSkillInterrput($map_id,$effX,$effY,$radius,$atkEff['eff_skill_interrupt']);
 			$end=$interrput['end'];
 			if($interrput['interrput']){
 				return ['hit'=>0,'end'=>1];
 			}
 			else {
 		 	$effXto=$effXfrom+$atkEff['eff_skill_hit_width'];
 		 	$effYto=$effYfrom+$atkEff['eff_skill_hit_lenght'];
 		 	if($enemy['x']>=$effXfrom&&$enemy['x']<=$effXto&&$enemy['y']>=$effYfrom&&$enemy['y']<=$effYto){
 		 		return ['hit'=>1,'end'=>1];
 		 	}else{
 		 		return ['hit'=>0,'end'=>0];
 		 	}
			}
		}


	public function getMillisecond() {
		list($t1, $t2) = explode(' ', microtime());
		return (float)sprintf('%.0f',(floatval($t1)+floatval($t2))*1000);
}

    private function checkSkillInterrput($map_id,$effX,$effY,$effR,$interrput){
    	$mapUtil=new MapTrapUtil();
    	$interrput=0;
    	$end=1;
    		if($interrput==3||$interrput==2){
    			$interrput=$mapUtil->checkEffstone($map_id,$effX,$effY,$effR,$effAngle);
    		}
    		else if($interrput==0){
    			$end=0;
    		}
    	return ['interrput'=>$interrput,'end'=>$end];
    } 



}
