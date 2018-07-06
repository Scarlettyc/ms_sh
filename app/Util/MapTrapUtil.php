<?php
namespace App\Util;
use App\Http\Requests;
use App\MapTrapRelationMst;
use App\Map_Stone_Relation_mst;
use App\MapModel;
use App\TrapMstModel;
use App\EffectionMstModel;
use Exception;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\Redis;
use DB;
class MapTrapUtil
{
	//show the quantity and icon for every item in the baggage
	// function getTrapEff($map_id,$x1,$x2,$x3,$y)
	// { 
	// 	$mapRelation=new MapTrapRelationMst();
	// 	$effect=new EffectionMstModel();
	// 	$trap=new TrapMstModel();
	// 	$mapEff=[];
	// 	$grass=$mapRelation->where(function($query){
 //        				$query->Where('map_id',$map_id)->where('trap_id',1)
 //        				->where('trap_x_from','>=',abs($x1))
 //        				->where('trap_x_to','<=',abs($x2))->where('trap_y_from','<=',abs($y))->where('trap_y_to','>',abs($y))
 //            				->orWhere(function($query){
 //                				Where('map_id',$map_id)
 //                                ->where('trap_id',1)
 //                				->where('trap_x_from','<=',abs($x2))
 //                				->where('trap_x_to','>=',abs($x3))->where('trap_y_from','<=',abs($y))->where('trap_y_to','>',abs($y))
 //           				})
 //   					})->first();

 //         if(!$grass){
 //         	$brambles=$mapRelation
 //        				->Where('map_id',$map_id)->where('trap_id',1)
 //        				->where('trap_x_from','>=',abs($x1))
 //        				->where('trap_x_to','<=',abs($x3))->where('trap_y_from','<=',abs($y))->where('trap_y_to','>',abs($y))
 //   					->first();
         
 //        	if($brambles){
 //         		$trapData=$trap->where('trap_id',$brambles['trap_id'])->first();
 //        		$mapEff=$effect->where('eff_id',$brambles['eff_id'])->first();
	// 			$mapEff['trap_id']=$mapData['trap_id'];
 //         	}
 //     	else{
	// 			$trapData=$trap->where('trap_id',$grass['trap_id'])->first();
	// 			$mapEff=$effect->where('eff_id',$grass['eff_id'])->first();
	// 			$mapEff['trap_id']=$mapData['trap_id'];
 //     	}
 //     }

	// 	return $mapEff;
	// }

    function isHitStone($map_id,$effX,$effLastX,$effY){
        $mapRelation=new MapTrapRelationMst();
            $mapData=$mapRelation->Where('map_id',$map_id)->where('trap_id',1)->where('trap_x_from','<=',$effX)->where('trap_x_to','>=',$effLastX)->where('trap_y_from','<=',$effY)->where('trap_y_from','<=',$effY)->where('trap_y_to','>=',$effY)->get();
            return $mapData;
    }

    public function getMapData($map_id){
        $mapModel=new MapModel();
        $trapMst=new TrapMstModel();
        // $mapRelation=new MapTrapRelationMst();
        $mapData=$mapModel->where('map_id',$map_id)->first();
        $mapList=DB::table('Map_mst')
                    ->join('Map_Trap_Relation_mst','Map_mst.map_id','=','Map_Trap_Relation_mst.map_id')
                    ->join('Trap_mst','Trap_mst.trap_id','=','Map_Trap_Relation_mst.trap_id')
                    ->select('Map_Trap_Relation_mst.trap_id','Map_Trap_Relation_mst.x','Map_Trap_Relation_mst.y', 'Trap_mst.trap_id',  'Trap_mst.trap_name',  'Trap_mst.destroyable',  'Trap_mst.passable',  'Trap_mst.TL_x_a',  'Trap_mst.TL_y_a',  'Trap_mst.BR_x_a',  'Trap_mst.BR_y_a',  'Trap_mst.CD',  'Trap_mst.DMG',  'Trap_mst.slow_presentage',  'Trap_mst.created_at',  'Trap_mst.updated_at')
                    ->where('Map_mst.map_id',$map_id)
                    ->get();
        var_dump($mapList);
        return   $mapList;      
        
    }

    //     function checkEffstone($map_id,$effX,$effY,$effR,$effAngle)
    // {       $mapRelation=new MapTrapRelationMst();
    //         $mapStone=new MapStoneRelationMst();

    //         $mapData=$mapRelation->where(function($query){
    //                  $query->Where('map_id',$map_id)->where('trap_id',1)->get();


    //         $result=[];

    //         foreach($mapData as $trap){
    //             $trapLength=abs($trap['trap_x_to']-$trap['trap_x_from']);
    //             $trapHeight=abs($trap['trap_y_to']-$trap['trap_y_from']);
    //             $intersects=$this->intersects($effR,$effAngle,$effX,$effY,$trap['trap_center_x'],$trap['trap_center_y'],$trapLength,$trapHeight);
    //             if($intersects){
    //                 return true;
    //                 break;
    //             }
    //             return false;
    //         }
    //         return false;
    // } 

//     function intersects($circleR,$effAngle,$circleX,$circleY,$RectX,$RectY,$RecWidth,$RecHeight)
// {
//     $circleDistanceX = abs($circleX - $RectX);
//     $circleDistanceY = abs($circleY - $RectY);

//     $distance=sqrt(pow(($circleDistanceX),2)+pow($circleDistanceY,2));
//     $agnle=asin($circleDistanceX/$distance);
//     if ($circleDistanceX > ($RecWidth/2 + $circleR)||$agnle>$effAngle) { return false; }
//     if ($circleDistanceY > ($RecHeight/2 +$circleR)) { return false; }

//    if ($circleDistanceX <=($RecWidth/2 + $circleR)&&$agnle<=$effAngle) { return true; }
//     if ($circleDistanceY <= ($RecHeight/2 +$circleR)&&$agnle<=$effAngle) { return true; }

//     $cornerDistance_sq = ($circleDistanceX- $RecWidth/2)^2 +
//                          ($circleDistanceY - $RecHeight/2)^2;
   

//     return ($cornerDistance_sq <= (($circleR^2)&&$agnle<=$effAngle);
// }

}