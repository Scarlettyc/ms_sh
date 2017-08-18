<?php
namespace App\Util;
use App\Http\Requests;
use App\MapTrapRelationMst;
use App\MapModel;
use App\TrapMstModel;
use App\EffectionMstModel;
use App\SkillMstModel;
use App\EffectionMstModel;
use Exception;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\Redis;

class DistanceAttackUtil
{
	function originalLoc ($x1,$x2,$x3,$y,$face,$skill_id)
	{
		$SkillMstModel=new SkillMstModel();
		$EffectionMstModel=new EffectionMstModel();
		$skillInfo=$SkillMstModel->where('skill_id',$skill_id)->first();
		$eff_id=$skillInfo['eff_id'];
		$effInfo=$EffectionMstModel->where('eff_id',$eff_id)->first();
		$skill_x=$effInfo['eff_skill_x'];
		$skill_y=$effInfo['eff_skill_y'];
		$bullet_width=$effInfo['eff_bullet_width'];
		$skill_position=[];
		$bullet_position=[];
		if($face == 1)
		{
			if($skill_y == 1)
			{
				$start_x=$x3+1;
				$start_y=$y+1;
				$end_x=$x3+$skill_x;
				$end_y=$y+1;
				$skill_position['x_s']=$start_x;
				$skill_position['y_s']=$start_y;
				$skill_position['x_e']=$end_x;
				$skill_position['y_e']=$end_y;

				$b_start_x=$start_x;
				$b_start_y=$start_y;
				$b_end_x=$x3+$bullet_width;
				$b_end_y=$end_y;
				$bullet_position['x_s']=$b_start_x;
				$bullet_position['y_s']=$b_start_y;
				$bullet_position['x_e']=$b_end_x;
				$bullet_position['y_e']=$b_end_y;
			}else if($skill_y == 2)
			{
				$start_x=$x3+1;
				$start_y=$y+1;
				$end_x=$x3+$skill_x;
				$end_y=$y+2;
				$skill_position['x_s']=$start_x;
				$skill_position['y_s']=$start_y;
				$skill_position['x_e']=$end_x;
				$skill_position['y_e']=$end_y;

				$b_start_x=$start_x;
				$b_start_y=$start_y;
				$b_end_x=$x3+$bullet_width;
				$b_end_y=$end_y;
				$bullet_position['x_s']=$b_start_x;
				$bullet_position['y_s']=$b_start_y;
				$bullet_position['x_e']=$b_end_x;
				$bullet_position['y_e']=$b_end_y;
			}else if($skill_y == 3)
			{
				$start_x=$x3+1;
				$start_y=$y;
				$end_x=$x3+$skill_x;
				$end_y=$y+2;
				$skill_position['x_s']=$start_x;
				$skill_position['y_s']=$start_y;
				$skill_position['x_e']=$end_x;
				$skill_position['y_e']=$end_y;

				$b_start_x=$start_x;
				$b_start_y=$start_y;
				$b_end_x=$x3+$bullet_width;
				$b_end_y=$end_y;
				$bullet_position['x_s']=$b_start_x;
				$bullet_position['y_s']=$b_start_y;
				$bullet_position['x_e']=$b_end_x;
				$bullet_position['y_e']=$b_end_y;
			}else if($skill_y == 4)
			{
				$start_x=$x3+1;
				$start_y=$y-1;
				$end_x=$x3+$skill_x;
				$end_y=$y+2;
				$skill_position['x_s']=$start_x;
				$skill_position['y_s']=$start_y;
				$skill_position['x_e']=$end_x;
				$skill_position['y_e']=$end_y;

				$b_start_x=$start_x;
				$b_start_y=$start_y;
				$b_end_x=$x3+$bullet_width;
				$b_end_y=$end_y;
				$bullet_position['x_s']=$b_start_x;
				$bullet_position['y_s']=$b_start_y;
				$bullet_position['x_e']=$b_end_x;
				$bullet_position['y_e']=$b_end_y;
			}else if($skill_y == 5)
			{
				$start_x=$x3+1;
				$start_y=$y-1;
				$end_x=$x3+$skill_x;
				$end_y=$y+3;
				$skill_position['x_s']=$start_x;
				$skill_position['y_s']=$start_y;
				$skill_position['x_e']=$end_x;
				$skill_position['y_e']=$end_y;

				$b_start_x=$start_x;
				$b_start_y=$start_y;
				$b_end_x=$x3+$bullet_width;
				$b_end_y=$end_y;
				$bullet_position['x_s']=$b_start_x;
				$bullet_position['y_s']=$b_start_y;
				$bullet_position['x_e']=$b_end_x;
				$bullet_position['y_e']=$b_end_y;
			}
		}else
		{
			if($skill_y == 1)
			{
				
			}
		}
	}
}