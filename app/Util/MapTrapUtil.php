<?php
namespace App\Util;
use App\Http\Requests;
use App\MapTrapRelationMst;
use App\MapModel;
use App\TrapMstModel;
use App\EffectionMstModel;
use Exception;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\Redis;

class MapTrapUtil
{
	//show the quantity and icon for every item in the baggage
	function getTrapEff($map_id,$x1,$x2,$x3,$y)
	{ 
		$mapRelation=new MapTrapRelationMst();
		$effect=new EffectionMstModel();
		$trap=new TrapMstModel();
		$mapEff=[];
		$grass=$mapRelation->where(function($query){
        				$query->Where('map_id',$map_id)->where('trap_id',1)
        				->where('trap_x_from','>=',abs($x1))
        				->where('trap_x_to','<=',abs($x2))->where('trap_y_from','<=',abs($y))->where('trap_y_to','>',abs($y))
            				->orWhere(function($query){
                				->Where('map_id',$map_id)->where('trap_id',1)
                				->where('trap_x_from','<=',abs($x2))
                				->where('trap_x_to','>=',abs($x3))->where('trap_y_from','<=',abs($y))->where('trap_y_to','>',abs($y))
           				});
   					})->first();

         if(!$grass){
         	$brambles=$mapRelation
        				->Where('map_id',$map_id)->where('trap_id',1)
        				->where('trap_x_from','>=',abs($x1))
        				->where('trap_x_to','<=',abs($x3))->where('trap_y_from','<=',abs($y))->where('trap_y_to','>',abs($y))
   					->first();
         
        	if($brambles){
         		$trapData=$trap->where('trap_id',$brambles['trap_id'])->first();
        		$mapEff=$effect->where('eff_id',$brambles['eff_id'])->first();
				$mapEff['trap_id']=$mapData['trap_id'];
         	}
     	else{
				$trapData=$trap->where('trap_id',$grass['trap_id'])->first();
				$mapEff=$effect->where('eff_id',$grass['eff_id'])->first();
				$mapEff['trap_id']=$mapData['trap_id'];
     	}
     }

		return $mapEff;
	}

    function isHitStone($map_id,$effX,$effLastX,$effY){
        $mapRelation=new MapTrapRelationMst();
            $mapData=$mapRelation->Where('map_id',$map_id)->where('trap_id',1)->where('trap_x_from','<=',$effX)->where('trap_x_to','>=',$effLastX)->where('trap_y_from','<=',$effY)->where('trap_y_from','<=',$effY)->where('trap_y_to','>=',$effY)->get();
            return $mapData;
    }


        function checkEffstone($map_id,$effectXfrom,$effectXto,$effectYfrom,$effectYto)
    {       $mapRelation=new MapTrapRelationMst();
            $mapData=$mapRelation->where(function($query){
                     $query->Where('map_id',$map_id)->where('trap_id',1)->where('trap_x_from','<=',$effectXto)->get();
            $result=[];
            foreach($mapData as $trap){
            $ajoin=$this->computRectJoinUnion($effectXfrom,$effectXto,$effectYfrom,$effectYto,$trap['trap_x_from'],$trap['trap_x_to'],$trap['trap_y_from'],$trap['trap_y_to']);
                if(isset($ajoin)){
                    $effectXfrom=$ajoin['effXfrom'];
                    $effXto=$ajoin['effXto'];
                    $effYfrom=$ajoin['effYfrom'];
                    $effYto=$ajoin['effYto'];
                }

            }


                return ['effXfrom'=>$effXfrom,'effXto'=>$effXto,'effYfrom'=>$effYfrom,'effYto'=>$effYto];


    } 
}