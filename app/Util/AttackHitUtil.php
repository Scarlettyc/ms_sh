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

	public function getEnmeyEff($skill_id,$user,$enemy,$occurtime){
		$skillModel=new SkillMstModel();
 		$buffEffModel= new BuffEffectionMst();
 		$atkEffModel=new AtkEffectionMst();
		$skillData=$skillModel->where('skill_id',$skill_id)->first();
		$result=[];
		$mileSecond=$this->getMillisecond();

		if($skillData['atk_eff_id']!=0){
			$eff_bullet_width=$skillData['eff_bullet_width'];
			$atkEff=$atkEffModel->where('atk_eff_id',$skillData['atk_eff_id'])->first();
			if($mileSecond-$occurtime<=$atkEff['eff_skill_dur']){
				// if($eff_bullet_width!=0){
				// 	$hit=$this->longDisEff($map_id,$atkEff,$effX,$effY,$occurtime,$direction,$enemyX,$enemyY);
				// 	if($hit['hit']){
				// 		$result['atk_eff']=$hit['atk_eff'];
				// 		$result['hit']=1;
				// 		$result['end']=0;
				// 	}
				// 	else if($hit['end']&&!$hit['hit']){
				// 	$result['atk_eff']['eff']=$hit['atk_eff'];
				// 	$result['atk_eff']['hit']=0;
				// 	$result['atk_eff']['end']=1;
				// 		}
				// 	}
				// else {
				 	$hit=$this->checkHit($atkEff,$user['direction'],$user['x'],$user['y'],$enemy['x'],$enemy['y'],$enemy['char_hp']);
					$result['atk_eff']['eff']= $atkEff;
					$result['atk_eff']['hit']=$hit['hit'];
					$result['atk_eff']['end']=$hit['end'];
					$result['skill_group']=$$skillData['skill_group'];
				// }
			}
		}
		else if(isset($skillData['enemy_buff_eff_id'])){
			
			$enemyBuffEff=$buffEffModel->where('buff_eff_id',$skillData['enemy_buff_eff_id'])->first();
			if($mileSecond-$occurtime<=$enemyBuffEff['eff_skill_dur']){
			$result['enemyBuff']=$enemyBuffEff;
			}
		}
		return $result;

	}

	public function getatkEff($skill_id,$user,$enemy,$clientID,$enemy_clientId,$user_direction,$enemy_direction){
		$atkEffModel=new AtkEffectionMst();
		$skillModel=new SkillMstModel();
		if($clientID>$enemy_clientId){
			$user['x']=-$user['x'];
		}

		$skillData=$skillModel->select('atk_eff_id')->where('skill_id',$skill_id)->first();
		$atkEff=$atkEffModel->where('atk_eff_id',$skillData['atk_eff_id'])->first();
		$effXfrom=$enemy['x'];
		$effYfrom=$enemy['y'];
		// $effXto=$effXfrom+$atkEff['eff_skill_hit_width'];
 	// 	$effYto=$effYfrom+$atkEff['eff_skill_hit_lenght'];

 		 	// if($user['x']>=$effXfrom&&$user['x']<=$effXto&&$user['y']>=$effYfrom&&$user['y']<=$effYto){
 			if(abs($user_direction*$user['x']-$enemy_direction*$enemy['x'])<=$atkEff['eff_skill_hit_width']&&abs($user['y']-$enemy['y'])<=$atkEff['eff_skill_hit_width']){
 		 		return $atkEff;
 		 	}
 		 	else {
 		 		return null;
 		 	}


	}

// 
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


	public function longDisEff($map_id,$atkEff,$effX,$effY,$direction,$enemyX,$enemyY){
				$effModel=new AtkEffModel();
				$eff_id=$atkEff['atk_eff_id'];
				$mileSecond=$this->getMillisecond();
				$defindMst=new DefindMstModel();
				$defindData=$defindMst->where('defind_id',17)->first();
				$stone=false;
				$hit=false;
				$end=false;
				if($eff_skill_interrupt==2||$eff_skill_interrupt==3){
					if($direction>0){
						$effLastX=$eff['eff_skill_spd']*$stayTime()+$effX;
					if($atkEff['eff_skill_interrupt']>0)
						$tapData=$mapTrap->isHitStone($map_id,$effX,$effLastX,$effY);
					}else {
						$effLastX=$effX-$eff['eff_skill_spd']*$stayTime;
						$tapData=$mapTrap->isHitStone($map_id,$effLastX,$effX,$effY);
					}
					if(isset($tapData)){
						$stone=true;
					}
				}
				else{
					$distance=sqrt(pow(($effLastX-$enemy['x']),2)+pow(($effY-$enemy['y']),2))-$eff['eff_skill_radius'];
					if($distance<=$defindData['value1']){
						$hit=true;
					}
				}

			return ['hit'=>$hit,'stone'=>$stone,'end'=>$end];		
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
