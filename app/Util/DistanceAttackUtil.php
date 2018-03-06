<?php
namespace App\Util;
use App\Http\Requests;
use App\MapTrapRelationMst;
use App\MapModel;
use App\TrapMstModel;
use App\SkillMstModel;
use App\EffectionMstModel;
use App\MapStoneRelationMst;
use Exception;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\Redis;
use App\RaEffModel;

class DistanceAttackUtil
{
	function originalLoc ($x1,$x2,$y,$face,$skill_id)
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
		$result=[];
		if($face == 1)
		{
			if($skill_y == 1)
			{
				$start_x=$x2+1;
				$start_y=$y+1;
				$end_x=$x2+$skill_x;
				$end_y=$y+1;
				$skill_position['x_s']=$start_x;
				$skill_position['y_s']=$start_y;
				$skill_position['x_e']=$end_x;
				$skill_position['y_e']=$end_y;

				$b_start_x=$start_x;
				$b_start_y=$start_y;
				$b_end_x=$x2+$bullet_width;
				$b_end_y=$end_y;
				$bullet_position['x_s']=$b_start_x;
				$bullet_position['y_s']=$b_start_y;
				$bullet_position['x_e']=$b_end_x;
				$bullet_position['y_e']=$b_end_y;
			}else if($skill_y == 2)
			{
				$start_x=$x2+1;
				$start_y=$y+1;
				$end_x=$x2+$skill_x;
				$end_y=$y+2;
				$skill_position['x_s']=$start_x;
				$skill_position['y_s']=$start_y;
				$skill_position['x_e']=$end_x;
				$skill_position['y_e']=$end_y;

				$b_start_x=$start_x;
				$b_start_y=$start_y;
				$b_end_x=$x2+$bullet_width;
				$b_end_y=$end_y;
				$bullet_position['x_s']=$b_start_x;
				$bullet_position['y_s']=$b_start_y;
				$bullet_position['x_e']=$b_end_x;
				$bullet_position['y_e']=$b_end_y;
			}else if($skill_y == 3)
			{
				$start_x=$x2+1;
				$start_y=$y;
				$end_x=$x2+$skill_x;
				$end_y=$y+2;
				$skill_position['x_s']=$start_x;
				$skill_position['y_s']=$start_y;
				$skill_position['x_e']=$end_x;
				$skill_position['y_e']=$end_y;

				$b_start_x=$start_x;
				$b_start_y=$start_y;
				$b_end_x=$x2+$bullet_width;
				$b_end_y=$end_y;
				$bullet_position['x_s']=$b_start_x;
				$bullet_position['y_s']=$b_start_y;
				$bullet_position['x_e']=$b_end_x;
				$bullet_position['y_e']=$b_end_y;
			}else if($skill_y == 4)
			{
				$start_x=$x2+1;
				$start_y=$y-1;
				$end_x=$x2+$skill_x;
				$end_y=$y+2;
				$skill_position['x_s']=$start_x;
				$skill_position['y_s']=$start_y;
				$skill_position['x_e']=$end_x;
				$skill_position['y_e']=$end_y;

				$b_start_x=$start_x;
				$b_start_y=$start_y;
				$b_end_x=$x2+$bullet_width;
				$b_end_y=$end_y;
				$bullet_position['x_s']=$b_start_x;
				$bullet_position['y_s']=$b_start_y;
				$bullet_position['x_e']=$b_end_x;
				$bullet_position['y_e']=$b_end_y;
			}else if($skill_y == 5)
			{
				$start_x=$x2+1;
				$start_y=$y-1;
				$end_x=$x2+$skill_x;
				$end_y=$y+3;
				$skill_position['x_s']=$start_x;
				$skill_position['y_s']=$start_y;
				$skill_position['x_e']=$end_x;
				$skill_position['y_e']=$end_y;

				$b_start_x=$start_x;
				$b_start_y=$start_y;
				$b_end_x=$x2+$bullet_width;
				$b_end_y=$end_y;
				$bullet_position['x_s']=$b_start_x;
				$bullet_position['y_s']=$b_start_y;
				$bullet_position['x_e']=$b_end_x;
				$bullet_position['y_e']=$b_end_y;
			}
			$result['skill_position']=$skill_position;
			$result['bullet_position']=$bullet_position;
			$response=$result;
		}else if($face == 0)
		{
			if($skill_y == 1)
			{
				$start_x=$x1-1;
				$start_y=$y+1;
				$end_x=$x2-$skill_x;
				$end_y=$y+1;
				$skill_position['x_s']=$start_x;
				$skill_position['y_s']=$start_y;
				$skill_position['x_e']=$end_x;
				$skill_position['y_e']=$end_y;

				$b_start_x=$start_x;
				$b_start_y=$start_y;
				$b_end_x=$x2-$bullet_width;
				$b_end_y=$end_y;
				$bullet_position['x_s']=$b_start_x;
				$bullet_position['y_s']=$b_start_y;
				$bullet_position['x_e']=$b_end_x;
				$bullet_position['y_e']=$b_end_y;
			}else if($skill_y == 2)
			{
				$start_x=$x1-1;
				$start_y=$y+1;
				$end_x=$x2-$skill_x;
				$end_y=$y+2;
				$skill_position['x_s']=$start_x;
				$skill_position['y_s']=$start_y;
				$skill_position['x_e']=$end_x;
				$skill_position['y_e']=$end_y;

				$b_start_x=$start_x;
				$b_start_y=$start_y;
				$b_end_x=$x2-$bullet_width;
				$b_end_y=$end_y;
				$bullet_position['x_s']=$b_start_x;
				$bullet_position['y_s']=$b_start_y;
				$bullet_position['x_e']=$b_end_x;
				$bullet_position['y_e']=$b_end_y;
			}else if($skill_y == 3)
			{
				$start_x=$x1-1;
				$start_y=$y;
				$end_x=$x2-$skill_x;
				$end_y=$y+2;
				$skill_position['x_s']=$start_x;
				$skill_position['y_s']=$start_y;
				$skill_position['x_e']=$end_x;
				$skill_position['y_e']=$end_y;

				$b_start_x=$start_x;
				$b_start_y=$start_y;
				$b_end_x=$x2-$bullet_width;
				$b_end_y=$end_y;
				$bullet_position['x_s']=$b_start_x;
				$bullet_position['y_s']=$b_start_y;
				$bullet_position['x_e']=$b_end_x;
				$bullet_position['y_e']=$b_end_y;
			}else if($skill_y == 4)
			{
				$start_x=$x1-1;
				$start_y=$y-1;
				$end_x=$x2-$skill_x;
				$end_y=$y+2;
				$skill_position['x_s']=$start_x;
				$skill_position['y_s']=$start_y;
				$skill_position['x_e']=$end_x;
				$skill_position['y_e']=$end_y;

				$b_start_x=$start_x;
				$b_start_y=$start_y;
				$b_end_x=$x2-$bullet_width;
				$b_end_y=$end_y;
				$bullet_position['x_s']=$b_start_x;
				$bullet_position['y_s']=$b_start_y;
				$bullet_position['x_e']=$b_end_x;
				$bullet_position['y_e']=$b_end_y;
			}else if($skill_y == 5)
			{
				$start_x=$x1-1;
				$start_y=$y-1;
				$end_x=$x2-$skill_x;
				$end_y=$y+3;
				$skill_position['x_s']=$start_x;
				$skill_position['y_s']=$start_y;
				$skill_position['x_e']=$end_x;
				$skill_position['y_e']=$end_y;

				$b_start_x=$start_x;
				$b_start_y=$start_y;
				$b_end_x=$x2-$bullet_width;
				$b_end_y=$end_y;
				$bullet_position['x_s']=$b_start_x;
				$bullet_position['y_s']=$b_start_y;
				$bullet_position['x_e']=$b_end_x;
				$bullet_position['y_e']=$b_end_y;
			}
			$result['skill_position']=$skill_position;
			$result['bullet_position']=$bullet_position;
			$response=$result;
		}else{
			throw new Exception("Wrong face value");
			$response=[
			'status' => 'Wrong',
			'error' => "please check if face value equal 1 or 0",
			];
		}
		return $response;
	}

	function outOfRange($t0,$t1,$skill_x,$bullet_width,$skill_spd)
	{
		$t=$t1-$t0;
		$bullet_dis=$skill_spd*$t;
		$skill_dis=$skill_x-$bullet_width;
		$ifOutRange=0;
		if($bullet_dis > $skill_dis)
		{
			$ifOutRange=1;
		}
		return $ifOutRange;
	}

	function beginNearStone($x_s,$x_e,$y_s,$y_e,$map_id)
	{
		$MapStoneRelationMst=new MapStoneRelationMst;

		$mapStone_x=$MapStoneRelationMst->where('map_id',$map_id)->where('trap_id',3)->where('trap_y','>=',$y_s)->where('trap_y','<=',$y_e)->pluck('trap_x');
		$hasStone=0;
		foreach($mapStone_x as $obj)
		{
			if($obj>=$x_s&&$obj<=$x_e)
			{
				$hasStone=1;
			}
		}
		return $hasStone;
	}

	function nearStone($t0,$t1,$x_e,$y_s,$y_e,$skill_spd,$map_id,$face_b)
	{
		$MapStoneRelationMst=new MapStoneRelationMst;

		$mapStone_x=$MapStoneRelationMst->where('map_id',$map_id)->where('trap_id',3)->where('trap_y','>=',$y_s)->where('trap_y','<=',$y_e)->pluck('trap_x');
		$hasStone=0;
		foreach($mapStone_x as $obj)
		{
			$t=$t1-$t0;
			$move=$skill_spd*$t;
			if($face_b == 1)
			{
				$bullet_loc=ceil($x_e+$move);
				if($bullet_loc=$obj)
				{
					$hasStone=1;
				}
			}else if($face_b == 0)
			{
				$bullet_loc=floor($x_e-$move);
				if($bullet_loc=$obj)
				{
					$hasStone=1;
				}
			}
		}
		return $hasStone;
	}

	function beginNearEnemy($x_s,$x_e,$y_s,$y_e,$x1,$x2,$y)
	{
		$hitY=$y+1;
		$nearEnemy=0;
		if($x1>=$x_s&&$x1<=$x_e&&$hitY>=$y_s&&$hitY<=$y_e)
		{
			$nearEnemy=1;
		}else if($x2>=$x_s&&$x2<=$x_e&&$hitY>=$y_s&&$hitY<=$y_e)
		{
			$nearEnemy=1;
		}
		return $nearEnemy;
	}

	function nearEnemy($t0,$t1,$x_e,$y_s,$y_e,$x1,$x2,$y,$skill_spd,$face_b,$face_e)
	{
		$nearEnemy=0;
		$hitY=$y+1;
		$t=$t1-$t0;
		$move=$skill_spd*$t;
		if($face_e == 1)
		{
			if($face_b == 1)
			{
				$bullet_loc=$x_e+$move;
				if($bullet_loc>=$x1&&$bullet_loc<=$x2)
				{
					if($hitY>=$y_s&&$hitY<=$y_e)
					{
						$nearEnemy=1;
					}
				}
			}else if($face_b == 0)
			{
				$bullet_loc=$x_e-$move;
				if($bullet_loc>=$x1&&$bullet_loc<=$x2)
				{
					if($hitY>=$y_s&&$hitY<=$y_e)
					{
						$nearEnemy=1;
					}
				}
			}
		}else if($face_e == 0)
		{
			if($face_b == 1)
			{
				$bullet_loc=$x_e+$move;
				if($bullet_loc>=$x2&&$bullet_loc<=$x1)
				{
					if($hitY>=$y_s&&$hitY<=$y_e)
					{
						$nearEnemy=1;
					}
				}
			}else if($face_b == 0)
			{
				$bullet_loc=$x_e-$move;
				if($bullet_loc>=$x2&&$bullet_loc<=$x1)
				{
					if($hitY>=$y_s&&$hitY<=$y_e)
					{
						$nearEnemy=1;
					}
				}
			}
		}		
		return $nearEnemy;
	}

	public function raSkills($effID,$x1,$x2,$y,$enmeyX1,$enemyX2,$enmeyY){

		$raEff=new RaEffModel();
		$eff=$raEff->where('radiation_eff_id',$effID)->first();
		if($x1<$x2){
		$effX_from=$x1+$eff['eff_skill_x_left'];
		$effX_to=$x2+$eff['eff_skill_x_right'];
		}
		else {
		$effX_from=$x2-$eff['eff_skill_x_left'];
		$effX_to=$x1+$eff['eff_skill_x_right'];
		}

		$effY_from=($y+1)-$eff['eff_skill_y_top'];
		$effY_to=($y+1)+$eff['eff_skill_y_down'];

		if($enmeyX1>=$effX_from&&$enmeyX1<=$effX_to&&$effY_from<=$enmeyY&&$effY_to>=$enmeyY||$enmeyX2>=$effX_from&&$enmeyX2<=$effX_to&&$effY_from<=$enmeyY&&$effY_to>=$enmeyY){
			return true;
		}
		else {
			return false;
		}
	}

	
}