<?php
namespace App\Util;
use App\Http\Requests;
use App\EffectionMstModel;
use App\SkillMstModel;
use App\EffectionMstModel;
use App\MapStoneRelationMst;
use App\AtkCircleEffModel;
use App\AtkRecEffModel;
use App\Util\MapTrapUtil;
use App\DefindMstModel;
use Exception;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\Redis;
use App\RaEffModel;

class AttackHitUtil()
{

	public function getEff($skill_id,$user,$enemy,$occurtime){
		$skillModel=new SkillMstModel();
		$atkCircleEffModel=new AtkCircleEffModel();
 		$buffEffModel= new BuffEffModel();
 		$atkRecEffModel=new AtkRecEffModel();
		$skillData=$skillModel->where('skill_id',$skill_id)->first();
		if(isset($skillData['self_buff_eff_id']){
			$buffEff=$buffEffModel->where('buff_eff_id',$skillData['self_eff_id'])->first();
			return ['selfbuff'=>$buffEff];
		}
		if(isset($skillData['atk_eff_id']){
			$eff_group=$skillData['eff_group_id'];
			if($eff_group==2){
				$atkEff=$atkRecEffModel->where('atk_re_eff_id',$skillData['atk_eff_id'])->first();
				$ishit=$this->longDisEff($map_id,$atkEff,$effX,$effY,$occurtime,$direction,$enemyX,$enemyY);
				if($ishit){
					return ['hit'=>1,'atkeff'=>$atkEff];
				}
			}
			else {
				$atkEff=$atkEffModel->where('atk_eff_id',$skillData['atk_eff_id'])->first();
				$ishit=$this->checkHit($atkEff,$user['direction'],$user['x'],$user['y'],$enemy['x'],$enemy['y'],$enemy['char_hp'],$occurtime);
				if($ishit){
					return ['hit'=>1,'atkeff'=>$atkEff];
				}

			}

		}
		else if(isset($skillData['enemy_buff_eff_id']){
			$enemyBuffEff=$buffEffModel->where('buff_eff_id',$skillData['enemy_buff_eff_id'])->first();
			return ['enemyBuff'=>$enemyBuffEff];
		}

	}


	private function checkHit($map_id,$atkEff,$direction,$user['x'],$user['y'],$enemy['x'],$enemy['y'],$occurtime,$eme)
	{
		$mapUtil=new MapTrapUtil();
		$defindMst=new DefindMstModel();
		$defindData=$defindMst->where('defind_id',17)->first();

		$mileSecond=$this->getMillisecond();
 		if($mileSecond-$occurtime<=$atkEff['eff_skill_dur']){
 				
			$distance=0;
			if($atkEff['eff_skill_circle_center']==0){
				if($direction>=0)
					{
						$effX=$user['x']+$defindData['value1'];
						
					}
					else {
						$effX=$user['x']-$defindData['value1'];
					}
					$effY=$user['y'];				
			}
 			else if($atkEff['eff_skill_circle_center']==1){
 				$effX=$user['x'];
 				$effY=$user['y'];			
 			}

 			else if($atkEff['eff_skill_circle_center']==2){
 				$effY=$user['y']+$defindData['value2'];
 				$effX=$user['x'];	
 			}
 			$radius=$atkEff['eff_skill_radius'];
 			if($this->checkSkillInterrput($map_id,$effX,$effY,$radius,$atkEff['eff_skill_interrupt'])){
 				return false;
 			}
 			else{
 		 		$distance=sqrt(pow(($effX-$enemy['x']),2)+pow(($effY-$enemy['y']),2));
 				$agnle=asin($effX/$distance);
 				$atkEffAngle=$atkEff['eff_skill_angle'];
				if($distance<=$radius&&$agnle<=$atkEffAngle)
 				{	if($atkEff['eff_condtion']==2){
 						if($atkEff[''])
 					}
 						return true;
 				}
				}
				else {
						return false;
				}
			 }
 	}
}


	public function longDisEff($map_id,$atkEff,$effX,$effY,$effTime,$direction,$enemyX,$enemyY){
				$effModel=new AtkEffModel();
				$eff_id=$atkEff['atk_re_eff_id'];
				$mileSecond=$this->getMillisecond();
				$stayTime=$mileSecond-$effTime;
				$defindMst=new DefindMstModel();
				$defindData=$defindMst->where('defind_id',17)->first();
				$stone=false;
				$hit=false;
				$end=false;
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
				else{
				
					$distance=sqrt(pow(($effLastX-$enemy['x']),2)+pow(($effY-$enemy['y']),2))-$eff['eff_skill_radius'];
					if($distance<=$defindData['value1']){
						$hit=true;
					}else{
						$skillDur=$eff['eff_skill_dur'];
						if($skillDur<=$stayTime){
							$end=ture;
						}
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
    	$interrput=false;
    		if($interrput==3||$interrput==2){
    			$interrput=$mapUtil->checkEffstone($map_id,$effX,$effY,$effR,$effAngle);

    		}
    	return $interrput;
    } 

  



}
