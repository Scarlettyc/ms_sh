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
use DB;

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
		$missionLv=0;
		$result=[];
		if($mission_type==2){

			$missionList=$missionModel->select('mission_id','description','user_lv_from','times')->where('mission_type',$mission_type)->where('start_date','<=',$datetime)->where('end_date','>=',$datetime)->get();
		$mission_key='mission_daily_'.$dmy.'_'.$u_id;
			foreach ($missionList as $key => $mission) {
			$recordJson=$redis_mission->HGET($mission_key,$mission['mission_id']);
			$record=json_decode($recordJson,TRUE);

			$rewards=$missionReward->select(DB::raw('item_id, item_equ_type,item_quantity, item_rarilty, item_type,if(item_type=3, CONCAT("Scroll_Random_",item_rarilty),"") as sc_img_path'))->where('mission_id',$mission['mission_id'])->Get();
				$tmp['mission_id']=$mission['mission_id'];
				$tmp['rewards']=$rewards;
				// if($rewards['item_type']==3){
				// 	$tmp['sc_img_path']='Scroll_Random_'.$mission['item_rarilty'];
				// }
				if($mission['times']!=1){
					if(is_array($record)){
					$tmp['status']=$record['status'];
					$tmp['description']=$mission['description'].' ('.$record['times'].'|'.$mission['times'].')';
					}
					else{
						$tmp['status']=0;
						$tmp['description']=$mission['description'].' (0'.'|'.$mission['times'].')';
					}
				}
				else{
					if(is_array($record)){	
						$tmp['status']=$record['status'];
					}
					else{
						$tmp['status']=0;
					}
					$tmp['description']=$mission['description'];
				}
				$tmp['mission_lv']=$mission['user_lv_from'];
				array_push($result,$tmp);
			}
		}
		else{ 
			$missionList=$missionModel->select('mission_id','description','user_lv_from','times')->where('mission_type',$mission_type)->where('start_date','<=',$datetime)->where('end_date','>=',$datetime)->get();
			$mission_key='mission'.'_'.$u_id;
			$user_lv=$chaData['ch_lv'];
			foreach ($missionList as $key => $mission) {
				$recordJson=$redis_mission->HGET($mission_key,$mission['mission_id']);
				$record=json_decode($recordJson,TRUE);
				$rewards=$missionReward->select(DB::raw('item_id, item_equ_type,item_quantity, item_rarilty, item_type,if(item_type=3, CONCAT("Scroll_Random_",item_rarilty),"") as sc_img_path'))->where('mission_id',$mission['mission_id'])->Get();
				$tmp['mission_id']=$mission['mission_id'];
				$tmp['rewards']=$rewards;
				if(is_array($record)){
					$tmp['status']=$record['status'];
				}
				else if($user_lv>=$mission['user_lv_from']){
					$tmp['status']=1;
				}
				else {
					$tmp['status']=0;
				}
				$tmp['description']=$mission['description'];
				$tmp['mission_lv']=$mission['user_lv_from'];
				array_push($result,$tmp);
			}
		}
	
	
			$final['mission_list']=$result;
			$response=json_encode($final,TRUE);
			return  base64_encode($response);
	}

	public function collectMission(Request $request){
		$req=$request->getContent();
		$json=base64_decode($req);
		$data=json_decode($json,TRUE);
		$u_id=$data['u_id'];
		$mission_type=$data['mission_type'];
		$mission_id=$data['mission_id'];
		$missionList=new MissionListMstModel();
		$missionRewardsModel=new MissionRewardsModel();
		$ScrollMstModel=new ScrollMstModel();
		$usermodel=new UserModel();
		$charModel=new CharacterModel();
		$CharSkillEffUtil=new CharSkillEffUtil();
		$now   = new DateTime;
		$dmy=$now->format( 'Ymd' );
		$datetime=$now->format( 'Y-m-d h:m:s' );
		$redis_mission=Redis::connection('default');
		$charaData=$charModel->select('ch_id','ch_lv','ch_exp')->where('u_id',$u_id)->first();
	
		$userData=$usermodel->where('u_id',$u_id)->first();
		$BaggageUtil=new BaggageUtil();
		$result=[];
		if($mission_type==2){
			$mission_key='mission_daily_'.$dmy.'_'.$u_id;
		}
		else{
			$mission_key='mission_'.$u_id;
		}
		$recordJson=$redis_mission->HGET($mission_key,$mission_id);
		$missionReward=$missionRewardsModel->select('item_id','item_equ_type','item_quantity', 'item_rarilty', 'item_type')->where('mission_id',$mission_id)->get();
		$record=json_decode($recordJson,TRUE);
		foreach ($missionReward as $key => $rewards) {
			if($rewards['item_type']==6){

				$usermodel->where('u_id',$u_id)->update(['u_coin'=>$userData['u_coin']+$rewards['item_quantity'],'updated_at'=>$datetime]);
			}
			else if($rewards['item_type']==7){
				$usermodel->where('u_id',$u_id)->update(['u_gem'=>$userData['u_gem']+$rewards['item_quantity'],'updated_at'=>$datetime]);
			}
			else if($rewards['item_type']==99){
				$CharSkillEffUtil->levelUP($u_id,$rewards['item_quantity']);
			}
			// else if($rewards['item_type']==2){
			// 	$scroll_list=$ScrollMstModel->select('sc_id')->where('sc_rarity',$rewards['item_rarity'])->orderBy(DB::raw('RAND()'))->first();
			// 	$rewards['item_id']=$scroll_list['sc_id'];
			// 	$result[]=$rewards;
			// }
			else if($rewards['item_type']==1 ||$rewards['item_type']==2||$rewards['item_type']==3){
				$result[]=$rewards;
			}
		}
		$BaggageUtil->insertToBaggage('u_id',$result);
		
		$status=2;
		$times=$record['times'];
		$redis_mission->HSET($mission_key,$mission_id,json_encode(['status'=>$status,'times'=>$times]));
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
			$chaData=$charModel->where('u_id',$u_id)->first();
			$missionData=$missionModel->select('description','mission_id')->where('mission_id',$mission_id)->where('mission_type',$mission_type)->first();
			$rewards=$missionReward->select('item_id', 'item_equ_type','item_quantity', 'item_rarilty', 'item_type')->where('mission_id',$mission_id)->get();
			if($mission_type==2){
				$key='mission_daily_'.$dmy.'_'.$u_id;
			}
			else{
				$key='mission_'.$u_id;
			}
			
			$recordJson=$redis_mission->HGET($key,$mission_id);
			$record=json_decode($recordJson,TRUE);
			if($record){
				$result['status']=$record['times'];
				$result['times']=$record['status'];
			}
				else{
				$result['status']=0;
				$result['times']=0;
			}
			$result['rewards']=$rewards;
			$response=json_encode($result,TRUE);
			return  base64_encode($response);
	}


	public function achieveMission($mission_id,$u_id,$times){
		$missionModel=new MissionRewardsModel();
		$now   = new DateTime;
		$dmy=$now->format( 'Ymd' );
		$datetime=$now->format( 'Y-m-d h:m:s' );
		$redis_mission=Redis::connection('default');
		$charModel=new CharacterModel();
		$userModel=new UserModel();
		$missionModel=new MissionListMstModel();
		$missionData=$missionModel->select('user_lv_from','times','mission_type')->where('mission_id',$mission_id)->first();
		$chaData=$charModel->select('ch_lv')->where('u_id',$u_id)->first();
		$mission_update=0;
		if($missionData['mission_type']==2){
			$key='mission_daily_'.$dmy.'_'.$u_id;
			$mission_update=1;
		}
		else{
			$key='mission_'.$u_id;
			if($chaData['ch_lv']==$missionData['user_lv_from']){
				$mission_update=1;
			}

		}
		if($mission_update==1){
			$recordJson=$redis_mission->HGET($key,$mission_id);
			$record=json_decode($recordJson,TRUE);
			if(is_array($record)&&$record['times']==$missionData['times']){
				$result['status']=1;
				$result['times']=$times;
			}
			else if(is_array($record)&&$record['times']>$missionData['times']){
				$result['status']=3;
				$result['times']=$missionData['times'];
			}else if(is_array($record)&&$record['times']<$missionData['times']){
				$result['status']=1;
				$result['times']=$times;
			}
			else{
				$result['status']=1;
				$result['times']=$times;
			}
			$resultJson=json_encode($result,TRUE);
			$redis_mission->HSET($key,$mission_id,$resultJson);
		}
	}
 }
