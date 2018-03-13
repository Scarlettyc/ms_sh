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
	public function getMission(Request $request){
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
			$mission_key='mission'.'_'.$u_id;
		}
		$result=[];
		foreach ($missionList as $key => $mission) {
			$recordJson=$redis_mission->HGET($key,$value['mission_id']);
			$record=json_decode($recordJson,TRUE);
			$rewards=$missionReward->select('item_org_id', 'item_quantity', 'item_rarilty', 'item_type')->where('mission_id',$mission['mission_id'])->Get();
				$tmp['mission_id']=$mission['mission_id'];
				$tmp['description']=$mission['description'];
				$tmp['rewards']=$rewards;
				$tmp['status']=$record['status'];
				$tmp['times']=$record['times'];
				$result[]=$tmp;
			}
			$response=json_encode($result,TRUE);
			return  base64_encode($response);
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
		$status=2;
		$redis_mission->HSET($key,$mission_id,$status);
		return base64_encode('successfully');
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

		$missionModel=new MissionListMstModel();
		$missionReward=new MissionRewardsModel();
		$usermodel=new UserModel();
		$charModel=new CharacterModel();
		// if($checkToken){
			$chaData=$charModel->where('u_id',$u_id)->first();
			$missionData=$missionModel->select('description','mission_id')->where('mission_id',$mission_id)->where('mission_type',$mission_type)->first();
			$rewards=$missionReward->select('item_org_id', 'item_quantity', 'item_rarilty', 'item_type')->where('mission_id',$mission_id)->get();
			if($mission_type==2){
				$key='mission_daily_'.$dmy.'_'.$u_id;
			}
			else{
				$key='mission_'.$u_id;
			}
			
			$status=$redis_mission->HGET($key,$mission_id);
			if($status){
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
		if($mission_type==2){
			$key='mission_daily_'.$dmy.'_'.$u_id;
		}
		else{
			$key='mission_'.$u_id;
		}
		$status=1;
		$redis_mission->HSET($key,$mission_id,$status);
	}


 }
