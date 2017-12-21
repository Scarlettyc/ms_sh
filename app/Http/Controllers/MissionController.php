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

	public function dailyMission(Request $request){
		$req=$request->getContent();
		$json=base64_decode($req);
		$data=json_decode($json,TRUE);
		$u_id=$data['u_id'];
		$mission_type=$data['mission_type'];
		$missionModel=new MissionRewardsModel();
		$now   = new DateTime;
		$dmy=$now->format( 'Ymd' );
		$datetime=$now->format( 'Y-m-d h:m:s' );
		$redis_mission=Redis::connection('default');
		$loginToday=$redis_mission->HGET('login_data',$dmy.$data['u_id']);
		$loginTodayArr=json_decode($loginToday);
		$access_token=$loginTodayArr->access_token;
		$usermodel=new UserModel();
		$charModel=new CharacterModel();
		if($access_token==$data['access_token']){
			$chaData=$charModel->where('u_id',$u_id)->first();
			$missionReward=$missionModel->select('mission_id','item_org_id','item_type','item_quantity','coin','gem','exp','times','description')->where('user_lv_from','<=',$chaData['ch_lv'])->where('user_lv_to','>',$chaData['ch_lv'])->where('mission_type',1)->where('start_date','<=',$datetime)->where('end_date','>=',$datetime)->get();
			$key='mission_daily_'.$dmy.'_'.$u_id;
			$result=[];
			foreach ($missionReward as $value) {
				$record=$redis_mission->HGET($key,$value['mission_id']);
				if($record){
				$recordData=json_decode($record,TRUE);
				if($recordData['times']<$value['times']){
					$value['times']=$recordData['times'];
				}

				$value['archive']=$recordData['times'];
				$value['status']=$recordData['status'];
			}
			else{
				$value['status']=0;
				$value['archive']=0;
			}
			
			$reslut['daily_mission'][]=$value;
			}
			$response=json_encode($reslut,TRUE);
			return  base64_encode($response);
		}
		else{
			throw new Exception("there is something wrong with token");

		}
		
	}


	public function archiveMission($mission_id,$u_id,$times){
		$missionModel=new MissionRewardsModel();
		$now   = new DateTime;
		$dmy=$now->format( 'Ymd' );
		$datetime=$now->format( 'Y-m-d h:m:s' );
		$redis_mission=Redis::connection('default');
		$charModel=new CharacterModel();
		$userModel=new UserModel();
		
		$chaData=$charModel->where('u_id',$u_id)->first();
		$missionReward=$missionModel->select('mission_id','item_org_id','item_type','item_quantity','coin','gem','exp','times','description')->where('mission_id',$mission_id)->where('user_lv_from','<=',$chaData['ch_lv'])->where('user_lv_to','>',$chaData['ch_lv'])->where('mission_type',1)->where('start_date','<=',$datetime)->where('end_date','>=',$datetime)->first();
		$key='mission_daily_'.$dmy.'_'.$u_id;
		if($missionReward['times']<=$times){
			$status=1;
		}else {
			$status=0;
		}

		$userRecord['times']=$missionReward['times'];
		$userRecord['status']=$status;
		$userRecord['datetime']=time();
		$record=json_encode($userRecord,TRUE);
		$redis_mission->HSET($key,$mission_id,$record);
	}

	public function collectMissionReward(Request $request){
		$req=$request->getContent();
		$json=base64_decode($req);
		$data=json_decode($json,TRUE);
		$u_id=$data['u_id'];
		$now   = new DateTime;
		$dmy=$now->format( 'Ymd' );
		$datetime=$now->format( 'Y-m-d h:m:s' );
		$mission_id=$data['mission_id'];
		$mission_type=$data['mission_type'];
		$missionModel=new MissionRewardsModel();
		$BaggageUtil=new BaggageUtil();
		$CharacterModel=new CharacterModel();
		$userModel=new UserModel();
		$redis_mission=Redis::connection('default');
		$loginToday=$redis_mission->HGET('login_data',$dmy.$data['u_id']);
		$loginTodayArr=json_decode($loginToday);
		$access_token=$loginTodayArr->access_token;
		$usermodel=new UserModel();
		$charModel=new CharacterModel();
		if($access_token==$data['access_token']){
			$charaData=$CharacterModel->select('ch_id','ch_lv','ch_exp')->where('u_id',$u_id)->first();
			$missionReward=$missionModel->select('mission_id','item_org_id','item_type','item_quantity','coin','gem','exp','times','description')->where('mission_id',$mission_id)->where('user_lv_from','<=',$charaData['ch_lv'])->where('user_lv_to','>',$charaData['ch_lv'])->where('mission_type',$mission_type)->where('start_date','<=',$datetime)->where('end_date','>=',$datetime)->first();
			if($missionReward['item_id']>0){
			$BaggageUtil->updateBaggageResource($u_id,$missionReward['item_id'],$missionReward['item_type'],$missionReward['item_quantity']);
			}
			$exp=$charaData['ch_exp']+$missionReward['exp'];
			$CharacterModel->where('u_id',$u_id)->update(['ch_exp'=>$exp,'updated_at'=>$datetime]);
			$userData=$userModel->select('u_gem','u_coin')->where('u_id',$u_id)->first();
			$coin=$userData['u_coin']+$missionReward['coin'];
			$gem=$userData['u_gem']+$missionReward['gem'];
			$userModel->where('u_id',$u_id)->update(['u_coin'=>$coin,'u_gem'=>$gem,'updated_at'=>$datetime]);
			$key='mission_daily_'.$dmy.'_'.$u_id;
			$missionJson=$redis_mission->HGET($key,$mission_id);
			$missionData=json_decode($missionJson,TRUE);
			$userRecord['times']=$missionData['times'];
			$userRecord['status']=2;
			$userRecord['datetime']=time();
			$record=json_encode($userRecord,TRUE);
			$redis_mission->HSET($key,$mission_id,$record);
		return base64_encode('successfully');
		}
		else{
			throw new Exception("there is something wrong with token");
		}

	}
	// public function listMisstion(Request $request){
	// 	$req=$request->getContent();
	// 	$json=base64_decode($req);
	// 	$data=json_decode($json,TRUE);
	// 	$uid=$data['u_id'];
	// 	$key='mission_level'.$uid;
	// 	$missionModel=new MissionRewardsModel();
	// 	$charModel=new CharacterModel();
	// 	$charData=$charModel->Where('u_id',$uid)->first();
	// 	$user_lv=$charData['ch_lv'];
	// 	$MisstionResult=Redis::LRANGE($key,1,1);
	// 	$datetime=$now->format('Y-m-d h:m:s');
	// 	$misstionList=$missionModel->where('mission_type',2)->where('start_date','<=',$datetime)->where('end_date','<=',$datetime)->get();
	// 	$baggageUtil=new BaggageUtil();
	// 	if(isset($MisstionResult)){
	// 		$tookRewardlevel=$MisstionResult['level'];
	// 		$resutl=[];
	// 		foreach ($misstionList as $key => $mission) {
	// 			$reward=$baggageUtil->getReward($mission);
	// 			if($misstion['user_lv_to']>$tookRewardlevel&&$misstion['user_lv_to']<=$user_lv){
	// 				$reward['mission_status']=0;
	// 			}
	// 			else {
	// 				$reward['mission_status']=1;
	// 			}
	// 			$result['mission_list'][]=$reward;
	// 		}
	// 	}
	// 	else {
	// 		foreach ($misstionList as $key => $mission) {
	// 			$reward=$baggageUtil->getReward($mission);
	// 			if($misstion['user_lv_to']<=$user_lv){
	// 				$reward['mission_status']=0;
	// 			}
	// 			else {
	// 				$reward['mission_status']=1;
	// 			}
	// 			$result['mission_list'][]=$reward;
	// 		}
	// 	}	
	// 		$response=json_encode($result,TRUE);
	// 		return  base64_encode($response);
	// }

 }
