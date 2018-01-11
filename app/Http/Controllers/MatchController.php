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
    public function match($clientID,$u_id,$access_token)
    {
    	$now   = new DateTime;
		$dmy=$now->format( 'Ymd' );
		// $u_id=$data['u_id'];
		$redisMatch= Redis::connection('default');
		$loginToday=$redisMatch->HGET('login_data',$dmy.$u_id);
		$loginTodayArr=json_decode($loginToday,TRUE);
		$access_token2=$loginTodayArr["access_token"];

		if($access_token2==$access_token){
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
				$matchKey='battle_match'.$match['user_ranking'].'star'.$match['star_from'].'to'.$match['star_to'].$dmy;
				$matchList=$redis_battle->HEXISTS($matchKey,$u_id);
				$matchCount=$redis_battle->HLEN($matchKey);
				$list['u_id_1']=$u_id;
				$list['client_id']=$clientID;
				$list['create_date']=time();
				$list_data=json_encode($list,TRUE);
			if($matchList==1||$matchCount==0){
				$redis_battle->HSET($matchKey,$u_id,$list_data);
				return $clientID;
			}
			else {
				$match_uid=$redis_battle->HKEYS($matchKey);
				if($matchList==1&&$match_uid==$u_id){
					return $clientID;
				}
				else{
					//$effect=$charSkillUtil->getCharSkill($chardata['ch_id']);

					$mapData=$this->chooseMap();
					$match_result=$redis_battle->HGET($matchKey,$u_id);
					$resultList=json_decode($match_result,TRUE);

					$resultList['u_id_2']=$u_id;
					$resultList['client_id_2']=$clientID;
					$resultList['map_id']=$mapData;
					
					//$enmeydata=$usermodel->where('u_id',$match_uid)->first();
					
					$battleKey='battle_status'.$dmy;
					$enmeyBattle=$redis_battle->HGET($battleKey,$match_uid);
					$enmeyBattleData=json_decode($enmeyBattle,TRUE);
					if(isset($enmeyBattleData)){
						$match_id=$enmeyBattleData['match_id'];
						$map_id=$enmeyBattleData['map_id'];
					}
					else{
						$match_id='m_'.time();
						$map_id=$mapData;

					}
					$matchResult=json_encode(['u_id'=>$u_id,'enemy_uid'=>$match_uid,'match_id'=>$match_id,'map_id'=>$mapData,'status'=>1,'create_date'=>time()]);

                    $inBattle=$redis_battle->HSET($battleKey,$u_id,$matchResult);
                   	$redis_battle->HDEL($matchKey,$u_id);
					$resultList['match_id']=$match_id;
					return $resultList;
				}
			}
		}
 			return "error";
 		}
 		else{
 			return null;
 		}
	 }


	 public function finalMatchResult ($u_id,$enemy_uid,$match_id,$mapData){
	 	$result['match_id']=$match_id;
		$response=json_encode($result,TRUE);
		return $response;

	 }
	 public function closeMatch($u_id,$access_token){
	 	$now   = new DateTime;
		$dmy=$now->format( 'Ymd' );
		$redisMatch= Redis::connection('default');
		$loginToday=$redisMatch->HGET('login_data',$dmy.$u_id);
		$loginTodayArr=json_decode($loginToday,TRUE);
		$access_token2=$loginTodayArr["access_token"];
		$characterModel=new CharacterModel();
		$matchrange=new MatchRangeModel();
		if($access_token2==$access_token){
			$redis_battle=Redis::connection('battle');
			$chardata=$characterModel->where('u_id',$u_id)->first();
     		$ch_star=$chardata['ch_star'];
     		$match=$matchrange->where('user_ranking',$chardata['ch_ranking'])->where('star_from','<=',$ch_star)->where('star_to','>=',$ch_star)
		 		->first();
		 	$matchKey='battle_match'.$match['user_ranking'].'star'.$match['star_from'].'to'.$match['star_to'].$dmy;
		 	$redis_battle->HDEL($matchKey,$u_id);
		 	return null;
		}

	 }


	public function testWebsocket(Request $request){
		$req=$request->getContent();
		$json=base64_decode($req);
		$data=json_decode($json,TRUE);
		$u_id=$data['u_id'];
		$access_token=$data['access_token'];
		$result=$this->closeMatch($u_id,$access_token);
		return $result;
}
    private function chooseMap(){
    	$defindmst=new DefindMstModel();
    	$defindData=$defindmst->where('defind_id',10)->first();
    	$mapID=rand($defindData['value1'],$defindData['value2'] );
    	return $mapID;

    }

}
