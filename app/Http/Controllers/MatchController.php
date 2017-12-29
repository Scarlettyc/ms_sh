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
use Log;
class MatchController extends Controller
{
    public function match($clientID,$data)
    {
    	$now   = new DateTime;
		$dmy=$now->format( 'Ymd' );
		$u_id=$data['u_id'];
		$access_token=$data['access_token'];
		$redisMatch= Redis::connection('default');
		$loginToday=$redisMatch->HGET('login_data',$dmy.$data['u_id']);
		$loginTodayArr=json_decode($loginToday);
		$access_token=$loginTodayArr->access_token;
		$now   = new DateTime;;
		$dmy=$now->format( 'Ymd' );
		// $data=json_decode($json,TRUE);
		if($access_token==$data['access_token']){
		$redis_battle=Redis::connection('battle');
     		$usermodel=new UserModel();
     		$matchrange=new MatchRangeModel();
     		$characterModel=new CharacterModel();
     		$charSkillUtil=new CharSkillEffUtil();
     		$chardata=$characterModel->where('u_id',$u_id)->first();
     		$ch_star=$chardata['ch_star'];
	
     		if(isset($chardata)){
		 		$match=$matchrange->where('user_ranking',$chardata['ch_ranking'])->where('star_from','<=',$ch_star)->where('star_to','>=',$ch_star)
		 		->first();
				$matchKey='battle_match'.$match['user_ranking'].'star'.$match['star_from'].'to'.$match['star_to'];
				$matchList=$redis_battle->LLEN($matchKey);
				$list['u_id_1']=$u_id;
				$list['client_id']=$clientID;
				$list_data=json_encode($list,TRUE);
			if($matchList==0||!$matchList){
				$redis_battle->LPUSH($matchKey,$list_data);
				return 'waiting';
			}
			else {
				$match_uidJson=$redis_battle->LRANGE($matchKey,0,1);
				
				$match_uid=json_decode($match_uidJson[0],TRUE);
				if($matchList==1&&$match_uid['u_id_1']==$u_id){
					return "waiting";
				}
				else{
					//$effect=$charSkillUtil->getCharSkill($chardata['ch_id']);
					$mapData=$this->chooseMap();
					$match_result=$redis_battle->LPOP($matchKey);
					$resultList=json_decode($match_result,TRUE);

					$resultList['u_id_2']=$u_id;
					$resultList['client_id_2']=$clientID;
					$resultList['map_id']=$mapData;
					
					$enmeydata=$usermodel->where('u_id',$match_uid)->first();
					
					$match=json_encode(['u_id'=>$u_id,'enemy_uid'=>$match_uid,'map_id'=>$mapData],TRUE);
					$match_id='m'.time();
					$redis_battle->HSET('match_list',$match_id,$match);
					$resultList['match_id']=$match_id;

					return $resultList;
				}
			}
		}

 			return 'error';
 		}
 		else{
 			return 'error';
 		}
	 }


	 public function finalMatchResult ($u_id,$enemy_uid,$match_id,$mapData){
	 	$usermodel=new UserModel();
     	$matchrange=new MatchRangeModel();
     	$characterModel=new CharacterModel();
     	$charSkillUtil=new CharSkillEffUtil();
     	$chardata=$characterModel->where('u_id',$u_id)->first();
	 	$effect=$charSkillUtil->getCharSkill($chardata['ch_id']);
	 	$enmeydata=$usermodel->where('u_id',$enemy_uid)->first();
	 	
	 	$result['match_id']=$match_id;
		$result['userData']['eff']=$effect;
		$result['userData']['char']=$chardata;
		$result['mapData']=$mapData;
		$result['enemyData']=$enmeydata;
		$response=json_encode($result,TRUE);
		return $response;

	 }


	public function testWebsocket(Request $request){
		$req=$request->getContent();
		$json=base64_decode($req);
		$data=json_decode($json,TRUE);
		$this->match(2,$data);
}
    private function chooseMap(){
    	$defindmst=new DefindMstModel();
    	$defindData=$defindmst->where('defind_id',10)->first();
    	$mapID=rand($defindData['value1'],$defindData['value2'] );
    	return $mapID;

    }

}
