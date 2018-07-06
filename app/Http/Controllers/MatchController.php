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
		// $loginToday=$redisMatch->HGET('login_data',$u_id);
		// $loginTodayArr=json_decode($loginToday,TRUE);
		// $access_token2=$loginTodayArr["access_token"];

		// if($access_token2==$access_token){
			$redis_battle=Redis::connection('battle');
			$redis_user=Redis::connection('battle_user');
     		$usermodel=new UserModel();
     		$matchrange=new MatchRangeModel();
     		$characterModel=new CharacterModel();
     		$charSkillUtil=new CharSkillEffUtil();
     		$chardata=$characterModel->where('u_id',$u_id)->first();
     		$ch_star=$chardata['ch_star'];

	
     		if(isset($chardata)){
		 		$match=$matchrange->where('user_ranking',$chardata['ch_ranking'])->where('star_from','<=',$ch_star)->where('star_to','>=',$ch_star)
		 		->first();
				$matchKey='battle_match'.$match['user_ranking'].'start'.$match['star_from'].'to'.$match['star_to'].$dmy;
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
				if($matchList==1&&$match_uid[0]==$u_id){
					return $clientID;
						}
				else{
					//$effect=$charSkillUtil->getCharSkill($chardata['ch_id']);
					$mapData=$this->chooseMap();

					$match_result=$redis_battle->HGET($matchKey,$match_uid[0]);
					$waitUser=json_decode($match_result,TRUE);
					
				
					// Log::info($match_result);
					$resultList=json_decode($match_result,TRUE);
					$resultList['u_id_1']=$match_uid[0];
					$resultList['client_id']=$waitUser['client_id'];
					$resultList['u_id_2']=$u_id;
					$resultList['client_id_2']=$clientID;
					$resultList['map_id']=$mapData;
					
					//$enmeydata=$usermodel->where('u_id',$match_uid)->first();
					
					$battleKeyUser='battle_status'.$u_id.$dmy;
					$battleKeyEnemy='battle_status'.$match_uid[0].$dmy;
					$myBattle=$redis_battle->HGET($battleKeyUser,'status');
					$enmeyBattle=$redis_battle->HGET($battleKeyEnemy,'status');

					if($myBattle==1||$enmeyBattle==1){
						//$match_id=$enmeyBattleData['match_id'];
						//$map_id=$enmeyBattleData['map_id'];
						return null;
					}
					else{
						$match_id='m_'.time();
						$map_id=$mapData;

				
						$matchResult=json_encode(['u_id'=>$u_id,'enemy_uid'=>$match_uid[0],'match_id'=>$match_id,'map_id'=>$mapData,'status'=>1,'client'=>$clientID,'enmey_client'=>$waitUser['client_id'],'create_date'=>time()]);

						$matchResult2=json_encode(['u_id'=>$match_uid[0],'enemy_uid'=>$u_id,'match_id'=>$match_id,'map_id'=>$mapData,'status'=>1,'client'=>$waitUser['client_id'],'enmey_client'=>$clientID,'create_date'=>time()]);

                   		$redis_battle->HSET($battleKeyUser,'u_id',$u_id);
                   		$redis_battle->HSET($battleKeyUser,'enemy_uid',$match_uid[0]);
                   		$redis_battle->HSET($battleKeyUser,'match_id',$match_id);
                   		$redis_battle->HSET($battleKeyUser,'map_id',$mapData);
                   		$redis_battle->HSET($battleKeyUser,'status',1);
                   		$redis_battle->HSET($battleKeyUser,'client',$clientID);
						$redis_battle->HSET($battleKeyUser,'enmey_client',$waitUser['client_id']);
						$redis_battle->HSET($battleKeyUser,'create_date',time());
						$redis_battle->HSET($battleKeyUser,'match_key',$matchKey);

						$redis_battle->HSET($battleKeyEnemy,'enemy_uid',$u_id);
                   		$redis_battle->HSET($battleKeyEnemy,'u_id',$match_uid[0]);
                   		$redis_battle->HSET($battleKeyEnemy,'match_id',$match_id);
                   		$redis_battle->HSET($battleKeyEnemy,'map_id',$mapData);
                   		$redis_battle->HSET($battleKeyEnemy,'status',1);
                   		$redis_battle->HSET($battleKeyEnemy,'enmey_client',$clientID);
						$redis_battle->HSET($battleKeyEnemy,'client',$waitUser['client_id']);
						$redis_battle->HSET($battleKeyEnemy,'create_date',time());
						$redis_battle->HSET($battleKeyEnemy,'match_range',$matchKey);
       					$battle_status_key='battle'.$u_id;
       					$battle_status_key_enemy='battle'.$match_uid[0];
						$redis_user->HSET($battle_status_key,'ch_hp_max',1000);
						$redis_user->HSET($battle_status_key_enemy,'ch_hp_max',1000);
						$redis_user->HSET($battle_status_key_enemy,'match_key',$matchKey);
						// $redis_battle->HDEL($matchKey,$match_uid[0]);
						// $redis_battle->HDEL($matchKey,$u_id);
						$resultList['match_id']=$match_id;

					return $resultList;
					}
				}
			}
		}
 			return "error";
 		// }
 		// else{
 		// 	return null;
 		// }
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

	public function validateMatch($u_id){
		$now   = new DateTime;
		$dmy=$now->format( 'Ymd' );
		$redis_battle=Redis::connection('battle');
		$characterModel=new CharacterModel();
		$matchrange=new MatchRangeModel();
		$chardata=$characterModel->where('u_id',$u_id)->first();
     	$ch_star=$chardata['ch_star'];
     	$match=$matchrange->where('user_ranking',$chardata['ch_ranking'])->where('star_from','<=',$ch_star)->where('star_to','>=',$ch_star)
		 		->first();
		$waitmatch='battle_match'.$match['user_ranking'].'star'.$match['star_from'].'to'.$match['star_to'].$dmy;
		$matchKey=$redis_battle->HKEYS($waitmatch);
		$battleKey='battle_status'.$u_id.$dmy;
		foreach ($matchKey as $key) {
			$battleStatus=$redis_battle->HGET($battleKey,'status');
			if($battleStatus&&$battleStatus==1){
				// Log::info($key);
				$redis_battle->HDEL($waitmatch,$key);
			}
		}
		$myStatus=$redis_battle->HGET($battleKey,'status');
		$mybattleList=json_decode($myStatus,TRUE);
		if($mybattleList&&$mybattleList['status']==1){
			$redis_battle->HDEL($waitmatch,$u_id);
		}
	}
	public function testWebsocket(Request $request){
		$req=$request->getContent();
		$json=base64_decode($req);
		$data=json_decode($json,TRUE);
		$u_id=$data['u_id'];
		$access_token=$data['access_token'];
		$result=$this->match(2,$u_id,$access_token);
		return $result;
}
    private function chooseMap(){
    	$defindmst=new DefindMstModel();
    	$defindData=$defindmst->where('defind_id',10)->first();
    	return $defindData['value1'];

    }

}
