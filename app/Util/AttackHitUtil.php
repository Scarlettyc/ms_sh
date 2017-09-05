<?php
namespace App\Util;
use App\Http\Requests;
use App\EffectionMstModel;
use App\SkillMstModel;
use App\EffectionMstModel;
use App\MapStoneRelationMst;
use App\Util\MapTrapUtil;
use Exception;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\Redis;
use App\RaEffModel;

class AttackHitUtil()
{

	public function getEff($skill_id,$user,$enemy,$direction){
		$skillModel=new SkillMstModel();
		$normalEff=new NormalEffectionMstModel();
 		$jumpEff= new JumpEffModel();
 		$RaEffModel=new RaEffModel();
		$skillData=$skillModel->where('skill_id',$skill_id)->first();
		$eff_group=$skillData['eff_group_id'];

		if($eff_group==1||$eff_group==2){
 			$atkEff=$normalEff->where('normal_eff_id',$user_ef['eff_id'])->first();
 			$areaPoints=$this->effCases($direction,0,$user['x1'],$user['x2'],$user['y'],$atkEff['eff_skill_x'],$atkEff['eff_skill_y']);
 			 $effXfrom=$user['x1']-$atkEff['eff_skill_x_right'];
 			$effXto=$user['x2']+$atkEff['eff_skill_x_right'];
 			$effYfrom=$user['y']+2+$atkEff['eff_skill_y_top'];
 			$effXto=$user['y']-$atkEff['eff_skill_y_down'];
 		
 		}
 		else if($eff_group==3){
 			$atkEff=$jumpEff->where('jump_eff_id',$user_ef['eff_id'])->first();
 			$areaPoints=$this->effCases($direction,$atkEff['eff_ch_distance'],$user['x1'],$user['x2'],$user['y'],$atkEff['eff_skill_x'],$atkEff['eff_skill_y']);
 		}
 		else if($eff_group==4){
 			$atkEff=$RaEffModel->where('radiation_eff_id',$user_ef['eff_id'])->first();
 			$effXfrom=$user['x1']-$atkEff['eff_skill_x_right'];
 			$effXto=$user['x2']+$atkEff['eff_skill_x_right'];
 			$effYfrom=$user['y']+2+$atkEff['eff_skill_y_top'];
 			$effXto=$user['y']-$atkEff['eff_skill_y_down'];

 		}

 		$this->checkHit($atkEff,$effXfrom,$effXto,$effYfrom,$effXto);
	}

	private function effCases($direction,$jump,$userX1,$userX2,$userY,$effX,$effY){
		if($jump==0){
			if($direction==0)
			{
				$effXfrom=$userX2;
				$effXto=$efffrom+$effX;
			}
			else if($direction==1){
				$effXto=$userX1;
				$effXfrom=$efffrom-$effX;
			}
		}
		else {
			if($direction==0)
			{
				$effXfrom=$userX2+$jump;
				$effXto=$efffrom+$effX;
			}
			else if($direction==1){
				$effXto=$userX1-$jump;
				$effXfrom=$efffrom-$effX;
			}

		}

		if($eff==1){
			$effYfrom=$userY+1;
			$effYto=$userY+1;

		}
		else if($effY==2){
			$effYfrom=$userY+1;
			$effYto=$userY+2;
		}
		else if($effY==3){
			$effYfrom=$userY;
			$effYto=$userY+2;
		}
		else if($effY==4){
			$effYfrom=$userY-1;
			$effYto=$userY+2;
		}
		else if($effY==5){
			$effYfrom=$userY-1;
			$effYto=$userY+3;
		}
		else if($effY==7){
			$effYfrom=$userY-2;
			$effYto=$userY+4;
		}
	return ['effXfrom'=>$effXfrom,'effXto'=>$effXto,'effYfrom'=>$effYfrom,'effYto'=>$effYto]；
	}


	private function checkHit($atkEff,$effXfrom,$effXto,$effYfrom,$effXto){
		$mapUtil=new MapTrapUtil();

		if($atkEff['eff_disable_stone_block']==0&&$atkEff['$eff_skill_dur']==0){
			$mapUtil

		}



	}

    private function trap($){

    }

  



}
