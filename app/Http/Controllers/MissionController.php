<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\MissionRewardsModel;
use App\MissionListMstModel;
use App\UserModel;
use App\CharacterModel;
use Exception;
use DateTime;
use Illuminate\Support\Facades\Redis;
use App\ScrollMstModel;
use App\ResourceMstModel;
use App\EquipmentMstModel;
use App\Util\BaggageUtil;
use App\Util\CharSkillEffUtil;

class MissionController extends Controller
{

	public function levelMissionReward(Request $request){
		$req=$request->getContent();
		$json=base64_decode($req);
		$data=json_decode($json,TRUE);
		$uid=$data['u_id'];
		$redis_mission=Redis::connection('default');
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
			$MisstionResult=$redis_mission->LPUSH($key,$resultJson);
		}
	}
	public function getmisson(Request $request){
		$req=$request->getContent();
		$json=base64_decode($req);
		$data=json_decode($json,TRUE);
		$u_id=$data['u_id'];
		$mission_type=$data['mission_type'];
		$missionModel=new MissionListMstModel();
		$missionReward=new MissionRewardsModel();
		$redis_mission=Redis::connection('default');
		$now   = new DateTime;
		$dmy=$now->format( 'Ymd' );
		$datetime=$now->format( 'Y-m-d h:m:s' );
		$usermodel=new UserModel();
		$charModel=new CharacterModel();
		$chaData=$charModel->where('u_id',$u_id)->first();
		if($mission_type==2){
		$missionList=$missionModel->select('mission_id','description')->where('mission_type',$mission_type)->where('start_date','<=',$datetime)->where('end_date','>=',$datetime)->get();
		$mission_key='mission_daily_'.$dmy.'_'.$u_id;
		}
		else{ 
			$missionList=$missionModel->select('mission_id','description')->where('mission_type',$mission_type)->where('user_lv_from')->where('start_date','<=',$datetime)->where('end_date','>=',$datetime)->get();
			$mission_key='mission_level_'.'_'.$u_id;
		}
		$result=[];
		foreach ($missionList as $key => $mission) {
			$record=$redis_mission->HGET($key,$value['mission_id']);
			$rewards=$missionReward->select('item_org_id', 'item_quantity', 'item_rarilty', 'item_type')->where('mission_id',$mission['mission_id'])->Get();
			if(!$record){
				$tmp['mission_id']=$mission['mission_id'];
				$tmp['description']=$mission['description'];
				$tmp['rewards']=$rewards;
				$result[]=$tmp;
			}
		}
		$response=json_encode($result,TRUE);
			return  base64_encode($response);
	}

	public function missionList(Request $request){
		$req=$request->getContent();
		$json=base64_decode($req);
		$data=json_decode($json,TRUE);
		$u_id=$data['u_id'];
		$mission_type=$data['mission_type'];
		$missionModel=new MissionRewardsModel();
		$redis_mission=Redis::connection('default');
		$now   = new DateTime;
		$dmy=$now->format( 'Ymd' );
		$datetime=$now->format( 'Y-m-d h:m:s' );
		// $CharSkillEffUtil=new CharSkillEffUtil();
		// $access_token=$data['access_token'];
		// $checkToken=$CharSkillEffUtil->($access_token,$u_id);
		$usermodel=new UserModel();
		$charModel=new CharacterModel();
		// if($checkToken){
			$chaData=$charModel->where('u_id',$u_id)->first();
			$missionReward=$missionModel->select('mission_id','item_org_id','item_type','item_quantity','coin','gem','exp','times','description')->where('user_lv_from','<=',$chaData['ch_lv'])->where('user_lv_to','>=',$chaData['ch_lv'])->where('mission_type',$mission_type)->where('start_date','<=',$datetime)->where('end_date','>=',$datetime)->get();
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
			
			$result['daily_mission'][]=$value;
			}
			$response=json_encode($result,TRUE);
			return  base64_encode($response);
		// }
		
	}
	public function collectMission(Request $request){
		$req=$request->getContent();
		$json=base64_decode($req);
		$data=json_decode($json,TRUE);
		$u_id=$data['u_id'];
		$mission_type=$data['mission_type'];
		$mission_id=$data['mission_id'];
		$missionModel=new MissionRewardsModel();
		$usermodel=new UserModel();
		$charModel=new CharacterModel();
		$CharSkillEffUtil=new CharSkillEffUtil();
		$now   = new DateTime;
		$dmy=$now->format( 'Ymd' );
		$datetime=$now->format( 'Y-m-d h:m:s' );
		$redis_mission=Redis::connection('default');
		$charaData=$CharacterModel->select('ch_id','ch_lv','ch_exp')->where('u_id',$u_id)->first();
		$missionReward=$missionModel->select('item_org_id', 'item_quantity', 'item_rarilty', 'item_type')->where('mission_id',$mission_id)->get();
		$userData=$usermodel->where('u_id',$u_id)->first();
		$BaggageUtil=new BaggageUtil();
		$result=[];
		foreach ($missionReward as $key => $rewards) {
			if($rewards['item_type']==6){

				$usermodel->where('u_id',$u_id)->update(['u_coin'=>$userData['u_coin']+$rewards['item_quantity'],'updated_at'=>$datetime]);
			}
			else if($rewards['item_type']==7){
				$usermodel->where('u_id',$u_id)->update(['u_coin'=>$userData['u_gem']+$rewards['item_quantity'],'updated_at'=>$datetime]);
			}
			else if($rewards['item_type']==99){
				$CharSkillEffUtil->levelUP($u_id,$rewards['item_quantity']);
			}
			else if($rewards['item_type']==3){
				$scroll_list=$ScrollMstModel->select('sc_id')->where('sc_rarity',$rewards['item_rarity'])->orderBy(DB::raw('RAND()'))->first();
				$rewards['item_org_id']=$scroll_list['sc_id'];
				$result[]=$rewards;
			}
			else if($rewards['item_type']==1 &&$rewards['item_type']==2){
				$result[]=$rewards;
			}
		}
		$BaggageUtil->insertToBaggage('u_id',$result);
		if($mission_type==2){
			$mission_key='mission_daily_'.$dmy.'_'.$u_id;
		}
		else{
			$mission_key='mission_'.$u_id;
		}
		$redis_mission->HSET($key,$mission_id]);
		return base64_encode('successfully');
	}

	public function getLevelMission(Request $request){
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
		// $CharSkillEffUtil=new CharSkillEffUtil();
		// $access_token=$data['access_token'];
		// $checkToken=$CharSkillEffUtil->($access_token,$u_id);
		$usermodel=new UserModel();
		$charModel=new CharacterModel();
		// if($checkToken){
			$chaData=$charModel->where('u_id',$u_id)->first();
			$missionReward=$missionModel->select('mission_id','user_lv_from as lv')->where('mission_type',$mission_type)->where('start_date','<=',$datetime)->where('end_date','>=',$datetime)->get();

			$reslut['daily_mission']=$missionReward;
			$response=json_encode($reslut,TRUE);
			return  base64_encode($response);
		// }
	}

	public function getMissionDetails(Request $request){
		$req=$request->getContent();
		$json=base64_decode($req);
		$data=json_decode($json,TRUE);
		$u_id=$data['u_id'];
		$mission_type=$data['mission_type'];
		$mission_id=$data['mission_id'];
		$now   = new DateTime;
		$dmy=$now->format( 'Ymd' );
		$datetime=$now->format( 'Y-m-d h:m:s' );
		$redis_mission=Redis::connection('default');
		// $CharSkillEffUtil=new CharSkillEffUtil();
		// $access_token=$data['access_token'];
		// $checkToken=$CharSkillEffUtil->($access_token,$u_id);
		$missionModel=new MissionRewardsModel();
		$usermodel=new UserModel();
		$charModel=new CharacterModel();
		// if($checkToken){
			$chaData=$charModel->where('u_id',$u_id)->first();
			$missionReward=$missionModel->select('mission_id','user_lv_from as lv','item_org_id','item_type','item_quantity','coin','gem','exp','times','description')->where('mission_id',$mission_id)->where('mission_type',$mission_type)->where('start_date','<=',$datetime)->where('end_date','>=',$datetime)->first();
			$key='mission_daily_'.$dmy.'_'.$u_id;
			$missionJson=$redis_mission->HGET($key,$mission_id);
			if($missionJson){
				$missionStatus=json_decode($missionJson,TRUE);
				if($missionStatus['times']<$missionReward['times']){
					$missionReward['times']=$recordData['times'];
				}

				$result['archive']=$missionStatus['times'];
				$result['status']=$missionStatus['status'];
			}
				else{

				$missionReward['status']=0;
				$missionReward['archive']=0;
			}
			$response=json_encode($missionReward,TRUE);
			return  base64_encode($response);
		// }

	}


	public function archiveMission($mission_id,$mission_type,$u_id,$times){
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
		// $CharSkillEffUtil=new CharSkillEffUtil();
		// $access_token=$data['access_token'];
		// $checkToken=$CharSkillEffUtil->($access_token,$u_id);
		$usermodel=new UserModel();
		$charModel=new CharacterModel();
		// if($checkToken){
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
		// }

	}

 }
