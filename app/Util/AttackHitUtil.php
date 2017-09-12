<?php
namespace App\Util;
use App\Http\Requests;
use App\EffectionMstModel;
use App\SkillMstModel;
use App\EffectionMstModel;
use App\MapStoneRelationMst;
use App\AtkEffModel;
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
		$atkEffModel=new AtkEffModel();
 		$buffEffModel= new BuffEffModel();
		$skillData=$skillModel->where('skill_id',$skill_id)->first();
		if(isset($skillData['self_buff_eff_id']){
			$buffEff=$buffEffModel->where('buff_eff_id',$skillData['self_eff_id'])->first();
			return ['selfbuff'=>$buffEff];
		}
		if(isset($skillData['atk_eff_id']){
			$eff_group=$skillData['eff_group_id'];
			$atkEff=$atkEffModel->where('atk_eff_id',$skillData['atk_eff_id'])->first();
			$ishit=$this->checkHit($atkEff,$user['direction'],$user['x'],$user['y'],$enemy['x'],$enemy['y'],$occurtime);
			if($ishit){
				return ['atkeff'=>$atkEff];

				}
			else return null;
		}
		else if(isset($skillData['enemy_buff_eff_id']){
			$enemyBuffEff=$buffEffModel->where('buff_eff_id',$skillData['enemy_buff_eff_id'])->first();

			return ['enemyBuff'=>$enemyBuffEff];
		}

	}


	private function checkHit($map_id,$atkEff,$direction,$user['x'],$user['y'],$enemy['x'],$enemy['y'],$occurtime)
	{
		$mapUtil=new MapTrapUtil();
		$defindMst=new DefindMstModel();
		$defindData=$defindMst->where('defind_id',17)->first();

		if($atkEff['$eff_skill_dur']==0)
		{
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
 			else{
 			$distance=sqrt(pow(($user['x']-$enemy['x']),2)+pow(($user['y']-$enemy['y']),2))-$defindData['value1'];
 		 	if($distance<=$atkEff['eff_skill_radius'])
 			{
				return true;
			}
			else {
				return false;
				}
 			}

 		}
		else if($atkEff['$eff_skill_dur']>0)
		{ 
			$this->longDisEff($map_id,$eff_id,$effX,$effY,$occurtime,$direction,$enemyX,$enemyY);
		}	
}


	public function longDisEff($map_id,$eff_id,$effX,$effY,$effTime,$direction,$enemyX,$enemyY){
				$effModel=new AtkEffModel();
				$eff=$effModel->where('eff_id',$eff_id)->first();
				$mileSecond=$this->getMillisecond();
				$stayTime=$mileSecond-$effTime;
				$defindMst=new DefindMstModel();
				$defindData=$defindMst->where('defind_id',17)->first();
				$stone=false;
				$hit=false;
				$end=false;
				if($direction>0){
					$effLastX=$eff['eff_skill_spd']*$stayTime()+$effX;
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


	private function getMillisecond() {
		list($t1, $t2) = explode(' ', microtime());
		return (float)sprintf('%.0f',(floatval($t1)+floatval($t2))*1000);
}

    private function trap($effX,$effLastX,$effY){



    }

  



}
