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
	function getTrapEff($map_id,$x,$y)
	{

		$mapRelation=new MapTrapRelationMst();
		$effect=new EffectionMstModel();
		$trap=new TrapMstModel();
		$mapData=$mapRelation->Where('map_id',$map_id)->where('trap_x_from','<=',abs($x)->where('trap_x_to','>',abs($x)->where('trap_y_from','<=',abs($y)->where('trap_y_to','>',abs($y)->first();
		$trapData=$trap->where('trap_id',$mapData['trap_id']);
		$mapEff=$effect->where('eff_id',$trapData['eff_id']);
		$mapEff['trap_id']=$mapData['trap_id'];
		return $mapEff;
	}
	function nearStone($map_id,$x,$y,$enemyX,$enemyY){
		$mapRelation=new MapTrapRelationMst();
		$effect=new EffectionMstModel();
		$trap=new TrapMstModel();

		$mapData=$mapRelation->where(function($query){
        				$query->Where('map_id',$map_id)->where('trap_id',3)->where('trap_x_to',abs($x)+1)->where('trap_y_from','<=',abs($y)->where('trap_y_to','>',abs($y)
            				->orWhere(function($query){
                				->Where('map_id',$map_id)->where('trap_id',3)->where('trap_x_to',abs($x)-1)->where('trap_y_from','<=',abs($y)->where('trap_y_to','>',abs($y)
           				});
   					})->first();

        if($mapData){
        	if($mapData['trap_y_from']==abs($enemyY)-1&&$mapData['trap_y_from']==abs($enemyY)+1){
        		return true;
        	}
        	else {
        		return false;
        	}
        }
        return false;


}