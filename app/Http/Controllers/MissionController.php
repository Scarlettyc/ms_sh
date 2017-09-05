<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\MissionRewardsModel;
use App\UserModel;
use App\CharacterModel;
use Exception;
use DateTime;
use Illuminate\Support\Facades\Redis;
use App\ScrollMstModel;
use App\ResourceMstModel;
use App\EquipmentMstModel;
use App\Util\BaggageUtil;

class MissionController extends Controller
{

	public function levelMissionReward(Request $request){
		$req=$request->getContent();
		$json=base64_decode($req);
		$data=json_decode($json,TRUE);
		$uid=$data['u_id'];

		$key='mission_level'.$u_id;
		$charModel=new CharacterModel();
		$charData=$charModel->Where('u_id',$uid)->first();
		$user_lv=$charData['ch_lv'];
		$baggageUtil=new BaggageUtil();
		$missionModel=new MissionRewardsModel();
		$missionReward=$missionModel->where('mission_type',2)->where('user_lv_to',$user_lv)->get();
        if($missionReward){
			$missionList=$baggageUtil->insertToBaggage($missionReward);
			$history['u_id']=$u_id;
			$history['level']=$lv;
			$history['time']=time();
			$history['mission_id_list']=$missionList;
			$response=json_encode($history,TRUE);
			$MisstionResult=Redis::LPUSH($key,$resultJson);
		}
	}

	public function listMisstion(Request $request){
		$req=$request->getContent();
		$json=base64_decode($req);
		$data=json_decode($json,TRUE);
		$uid=$data['u_id'];
		$key='mission_level'.$uid;
		$missionModel=new MissionRewardsModel();
		$charModel=new CharacterModel();
		$charData=$charModel->Where('u_id',$uid)->first();
		$user_lv=$charData['ch_lv'];
		$MisstionResult=Redis::LRANGE($key,1,1);
		$datetime=$now->format('Y-m-d h:m:s');
		$misstionList=$missionModel->where('mission_type',2)->where('start_date','<=',$datetime)->where('end_date','<=',$datetime)->get();
		$baggageUtil=new BaggageUtil();
		if(isset($MisstionResult)){
			$tookRewardlevel=$MisstionResult['level'];
			$resutl=[];
			foreach ($misstionList as $key => $mission) {
				$reward=$baggageUtil->getReward($mission);
				if($misstion['user_lv_to']>$tookRewardlevel&&$misstion['user_lv_to']<=$user_lv){
					$reward['mission_status']=0;
				}
				else {
					$reward['mission_status']=1;
				}
				$result['mission_list'][]=$reward;
			}
		}
		else {
			foreach ($misstionList as $key => $mission) {
				$reward=$baggageUtil->getReward($mission);
				if($misstion['user_lv_to']<=$user_lv){
					$reward['mission_status']=0;
				}
				else {
					$reward['mission_status']=1;
				}
				$result['mission_list'][]=$reward;
			}
		}	
			$response=json_encode($result,TRUE);
			return  base64_encode($response);
	}

 }
