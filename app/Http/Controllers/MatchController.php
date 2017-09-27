<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\UserModel;
use App\MatchRangeModel;
use App\MapTrapRelationMst;
use App\DefindMstModel;
use App\CharacterModel;
use App\MapModel;
use App\Util\CharSkillEffUtil;
use Illuminate\Support\Facades\Redis;
use DateTime;
use Exception;
use DB;
class MatchController extends Controller
{
    public function match($clientID,$data)
    {
  //   	$req=$request->getContent();
		// $json=base64_decode($req);
	 // 	//dd($json);
		// $data=json_decode($json,TRUE);
		$u_id=$data;
		//push to match list
		$now   = new DateTime;;
		$dmy=$now->format( 'Ymd' );
		// $data=json_decode($json,TRUE);
		$redis_battle=Redis::connection('battle');
		// $loginToday=Redis::HGET('login_data',$dmy.$u_id);
		// $loginTodayArr=json_decode($loginToday);
		// $access_token=$loginTodayArr->access_token;
		// if($access_token==$data['access_token'])
		// {
     		$usermodel=new UserModel();
     		$matchrange=new MatchRangeModel();
     		$characterModel=new CharacterModel();
     		$charSkillUtil=new CharSkillEffUtil();
     		$chardata=$characterModel->where('u_id',$u_id)->first();
     		if(isset($chardata)){
     			$maxLv=$matchrange->max('user_lv_to');
     			$maxStar=$matchrange->max('star_from');
		 		$match=$matchrange->where('star_from','<=',$chardata['ch_star'])->where('star_to','>=',$chardata['ch_star'])->first();
		 	

			if($chardata['ch_lv']<$maxLv){
				$matchKey='match_below_maxlv_star'.$match['star_from'].'to'.$match['star_to'];
			}
			else{
				$matchKey='match_maxlv_star'.$match['star_from'].'star'.$match['star_to'];
			}
			$matchList=$redis_battle->LLEN($matchKey);
			$list['u_id_1']=$u_id;
			$list['client_id']=$clientID;
			$list_data=json_encode($list,TRUE);
			if($matchList==0||!$matchList){
				$redis_battle->LPUSH($matchKey,$list_data);
				return null;
			}
			else {
				$match_uidJson=$redis_battle->LRANGE($matchKey,0,1);
				
				$match_uid=json_decode($match_uidJson[0],TRUE);
				if($matchList==1&&$match_uid['u_id_1']==$u_id){
					var_dump($match_uid);
					return null;
				}
				else{
					//$effect=$charSkillUtil->getCharSkill($chardata['ch_id']);
					$mapData=$this->chooseMap();
					$match_result=$redis_battle->LPOP($matchKey);
					$resultList=json_decode($match_result,TRUE);

					$resultList['u_id_2']=$u_id;
					//$result['match_result']=$match_uid;
					// $enmeydata=$usermodel->where('u_id',$match_uid)->first();
					
					// $match=json_encode(['u_id'=>$u_id,'enemy_uid'=>$match_uid,'map_id'=>$mapData['map_id']],TRUE);
					$match_id='m'.time();
					$redis_battle->HSET('match_list',$match_id,$match);
					// $result['match_id']=$match_id;
					// $result['userData']['eff']=$effect;
					// $result['userData']['char']=$chardata;
					// $result['mapData']=$mapData;
					// $result['enemyData']=$enmeydata;

					// $response=json_encode($enmeydata,TRUE);
					return $resultList;
				}
			}
		}
 			return null;
	 }

	public function testWebsocket($data){
		return $data."lalalla";
}
    private function chooseMap(){
    	$defindmst=new DefindMstModel();
    	$defindData=$defindmst->where('defind_id',10)->first();
    	$mapID=rand($defindData['value1'],$defindData['value2'] );
    	$map=new MapModel();
    	$trapData = DB::table('Map_Trap_Relation_mst')
            ->join('Trap_mst', 'Map_Trap_Relation_mst.trap_id','=','Trap_mst.trap_id')
            ->select('Map_Trap_Relation_mst.map_id', 'Map_Trap_Relation_mst.trap_id','Trap_mst.trap_type','Trap_mst.trap_name', 'Map_Trap_Relation_mst.trap_x_from','Map_Trap_Relation_mst.trap_x_to','Map_Trap_Relation_mst.trap_y_from','Map_Trap_Relation_mst.trap_y_to','Trap_mst.trap_icon','Trap_mst.trap_chartlet')
            ->where('map_id',$mapID)
            ->get();
    	return $trapData;

    }

}
