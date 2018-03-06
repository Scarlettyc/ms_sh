<?php

namespace App\Http\Controllers;
use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\UserModel;
use App\CharacterModel;
use App\EquipmentMstModel;
use App\SkillMstModel;
use App\ScrollMstModel;
use App\ResourceMstModel;
use App\UserBaggageResModel;
use App\UserBaggageEqModel;
use App\UserBaggageScrollModel;
use App\EquUpgradeReMstModel;
use App\Util\BaggageUtil;
use App\Util\ItemInfoUtil;
use Exception;
use App\Exceptions\Handler;
use Illuminate\Http\Response;
use Carbon\Carbon;
use DateTime;
use DB;
use Log;
use Illuminate\Support\Facades\Redis;
// use App\Util\CharSkillEffUtil;

class BaggageController extends Controller
{
	//according to the select, display items in the baggage
	public function baggage(Request $request)
	{
		$req=$request->getContent();
		$json=base64_decode($req);
		$data=json_decode($json,TRUE);
		$BaggageUtil=new BaggageUtil();
		$result=[];
		$u_id=$data['u_id'];
		$select=$data['eq_choose'];
		// $CharSkillEffUtil=new CharSkillEffUtil();
		// $access_token=$data['access_token'];
		// $checkToken=$CharSkillEffUtil->($access_token,$u_id);
		
		// if($checkToken){ //there are five different types: All/R/S/W/C
			if($select ==="All")//get all the item from baggage
			{
				$Resource=$BaggageUtil->getResource($u_id);
				$Scroll=$BaggageUtil->getScroll($u_id);
				$Weapon=$BaggageUtil->getWeapon($u_id);
				$Movement=$BaggageUtil->getMovement($u_id);
				$Core=$BaggageUtil->getCore($u_id);
				$result['Baggage_data']=array_merge($Resource,$Scroll,$Weapon,$Movement,$Core);
				$response=json_encode($result,TRUE);
			}else if($select ==="R")//select Resource
			{
				$Resource=$BaggageUtil->getResource($u_id);
				$result['Baggage_data']=$Resource;
				$response=json_encode($result,TRUE);
			}else if($select ==="S")//select Scroll
			{
				$Scroll=$BaggageUtil->getScroll($u_id);
				$result['Baggage_data']=$Scroll;
				$response=json_encode($result,TRUE);
			}else if($select ==="W")//select Weapon
			{
				$Weapon=$BaggageUtil->getWeapon($u_id);
				$result['Baggage_data']=$Weapon;
				$response=json_encode($result,TRUE);
			}else if($select ==="M")
			{
				$Movement=$BaggageUtil->getMovement($u_id);
				$result['Baggage_data']=$Movement;
				$response=json_encode($result,TRUE);
			}else if($select ==="C")//select Core
			{
				$Core=$BaggageUtil->getCore($u_id);
				$result['Baggage_data']=$Core;
				$response=json_encode($result,TRUE);
			}
			else {
				return base64_encode($json);
			}
			return base64_encode($response);
		// }else{
		// 	throw new Exception("there have some error of you access_token");
		// }
		
	}

	//show the detail information when user click the item in the baggage
	public function getItemInfo (Request $request)
	{
		$req=$request->getContent();
		$json=base64_decode($req);
		$data=json_decode($json,TRUE);
		$ItemInfoUtil=new ItemInfoUtil();
		$result=[];
		$ItemType=$data['item_type']; //there are three different types: itemtype_1(Resource)/itemtype_2(Equipment)/itemtype_3(Scroll)
		$ItemId=$data['item_id'];
		$u_id=$data['u_id'];

			if($ItemType == 1)
			{
				$result = $ItemInfoUtil->getResourceInfo($ItemId);
			}else if($ItemType == 2)
			{	$user_beq_id=$data['user_beq_id'];
				$result = $ItemInfoUtil->getEquipmentInfo($ItemId,$u_id,$user_beq_id);
			}else if($ItemType == 3)
			{	$user_bsc_id=$data['user_bsc_id'];
				$result = $ItemInfoUtil->getScrollInfo($ItemId,$u_id,$user_bsc_id);
			}
			$response=json_encode($result,TRUE);
			return base64_encode($response);
	}

	//sell item in the baggage
	public function sellItem (Request $request)
	{
		$req=$request->getContent();
		$json=base64_decode($req);
		$data=json_decode($json,TRUE);
		$now=new DateTime;
		$datetime=$now->format( 'Y-m-d h:m:s' );
		$dmy=$now->format( 'Ymd' );

		$UserModel=new UserModel();
		$UserBaggageResModel=new UserBaggageResModel();
		$UserBaggageEqModel=new UserBaggageEqModel();
		$UserBaggageScrollModel=new UserBaggageScrollModel();
		$result=[];

		$u_id=$data['u_id'];
		$ItemType=$data['item_type'];//itemtype:2(Equipment)/itemtype:3(Scroll)
		$ItemId=$data['item_id'];
		// $CharSkillEffUtil=new CharSkillEffUtil();
		// $access_token=$data['access_token'];
		// $checkToken=$CharSkillEffUtil->($access_token,$u_id);
		// if($checkToken){
			if($ItemType == 2)//sell Equipment
			{	$EquipmentMstModel=new EquipmentMstModel();
				$bag_id=$data['user_beq_id'];

				$UserBaggageEqModel->where('u_id',$u_id)->where('status','=',0)->where('user_beq_id',$bag_id)->update(array('status'=>9,'updated_at'=>$datetime));
				$eqData=$EquipmentMstModel->where('equ_id',$ItemId)->first();
				$UserData=$UserModel->where('u_id',$u_id)->first();
				$ItemPrice=$eqData['equ_price'];
				$updateCoin=$UserData['u_coin']+$ItemPrice;
				$UserModel->where('u_id',$u_id)->update(['u_coin'=>$updateCoin,'updated_at'=>$datetime]);
				$response="sold Equipment";
			}else if($ItemType == 3)//sell Scroll
			{	
			$ScrollMstModel=new ScrollMstModel();

			$bag_id=$data['user_bsc_id'];
			$UserBaggageScrollModel->where('u_id',$u_id)->where('status','=',0)->where('user_bsc_id',$bag_id)->update(['status'=>9,'updated_at'=>$datetime]);
			$scData=$ScrollMstModel->where('sc_id',$ItemId)->first();
			$ItemPrice=$scData['sc_coin'];
			$UserData=$UserModel->where('u_id',$u_id)->first();
			$updateCoin=$UserData['u_coin']+$ItemPrice;
			$UserModel->where('u_id',$u_id)->update(['u_coin'=>$updateCoin,'updated_at'=>$datetime]);
			$response="sold Scroll";
		}else{
			throw new Exception("No ItemType");
			$response=[
			'status' => 'Wrong',
			'error' => "please check ItemType",
			];
		}
		return base64_encode($response);
		// }
	}


	public function scrollMerge (Request $request)
	{
		$req=$request->getContent();
		$json=base64_decode($req);
		$data=json_decode($json,TRUE);
		$now=new DateTime;
		$datetime=$now->format( 'Y-m-d h:m:s' );
		$dmy=$now->format( 'Ymd' );

		$UserModel=new UserModel();
		$UserBaggageResModel=new UserBaggageResModel();
		$UserBaggageEqModel=new UserBaggageEqModel();
		$UserBaggageScrollModel=new UserBaggageScrollModel();
		$EquUpgradeReMstModel=new EquUpgradeReMstModel();
		$ScrollMstModel=new ScrollMstModel();
		$EquipmentMstModel=new EquipmentMstModel();
		$ItemInfoUtil=new ItemInfoUtil();
		$result=[];

		$u_id=$data['u_id'];
		$scrollId=$data['scroll_id'];
		$bag_id=$data['user_bsc_id'];

			$UserBaggageScrollModel->where('u_id',$u_id)->where('status','=',0)->where('bsc_id',$scrollId)->where('user_bsc_id',$bag_id)->update(array('status'=>2,'updated_at'=>$datetime));
			$scrollInfo=$ScrollMstModel->select('sc_id','sc_coin','upgrade_id')->where('sc_id',$scrollId)->first();
			$eqUpgradInfo=$EquUpgradeReMstModel->where('upgrade_id',$scrollInfo['upgrade_id'])->get();
			$equipmentInfo=$EquipmentMstModel->where('upgrade_id',$scrollInfo['upgrade_id'])->first();

			$ItemInfoUtil->validateResource($u_id,$eqUpgradInfo,$scrollInfo['sc_coin']);

			$UserBaggageEqModel->insert(['u_id'=>$u_id,'b_equ_id'=>$equipmentInfo['equ_id'],'b_equ_rarity'=>$equipmentInfo['equ_rarity'],'b_equ_type'=>$equipmentInfo['equ_type'],'b_icon_path'=>$equipmentInfo['icon_path'],'status'=>0,'updated_at'=>$datetime,'created_at'=>$datetime]);
			$UserBaggageScrollModel->where('u_id',$u_id)->where('user_bsc_id',$bag_id)->update(['status'=>9,'updated_at'=>$datetime]);

			$response='Successfully Meraged';

		return base64_encode($response);
	}


	public function equipmentUpgrade (Request $request)
	{
		$req=$request->getContent();
		$json=base64_decode($req);
		$data=json_decode($json,TRUE);
		$now=new DateTime;
		$datetime=$now->format( 'Y-m-d h:m:s' );
		$dmy=$now->format( 'Ymd' );

		$UserModel=new UserModel();
		$UserBaggageResModel=new UserBaggageResModel();
		$UserBaggageEqModel=new UserBaggageEqModel();
		$UserBaggageScrollModel=new UserBaggageScrollModel();
		$ScrollMstModel=new ScrollMstModel();
		$EquipmentMstModel=new EquipmentMstModel();
		$EquUpgradeReMstModel=new EquUpgradeReMstModel();
		$result=[];
		$charmodel=new CharacterModel();
		$ItemInfoUtil=new ItemInfoUtil();
		$u_id=$data['u_id'];
		$equipmentId=$data['equ_id'];
		$user_beq_id=$data['user_beq_id'];
		$equData=$EquipmentMstModel->select('equ_id','equ_type','equ_code','equ_rarity','equ_lv')->where('equ_id',$equipmentId)->first();
		$eqDetail=$UserBaggageEqModel->where('u_id',$u_id)->where('user_beq_id',$user_beq_id)->where('b_equ_id',$equipmentId)->first();
		$upgradeInfo=$EquUpgradeReMstModel->where('upgrade_id',$equData['upgrade_id'])->get();
		$ItemInfoUtil->validateResource($u_id,$upgradeInfo,$equData['upgrade_coin']);
		$upgradeNext=$EquUpgradeReMstModel->where('equ_code','like',$equData['equ_code'].'%')->where('lv',$equData['equ_lv']+1)->first();

			if($upgradeInfo){
			$upgradeEquId=$upgradeNext['upgrade_id'];
			$upgradeEquInfo=$EquipmentMstModel->where('upgrade_id',$upgradeEquId)->first();
			}
			else{
				throw new Exception("upgradeInfo is null");
					$response=[
						'status' => 'Wrong',
						'error' => "please check u_id",
					];
			}

			
			// $charmodel->where('u_id',$u_id)->update(['w_id'=>$w_bag_id])
			
			

			$UserBaggageEqModel->where('user_beq_id',$data['user_beq_id'])->where('u_id',$u_id)->update(['status'=>2,'updated_at'=>$datetime]);	
				
					
		Log::info($eqDetail);

			if($eqDetail['status']==1){
				$w_bag_id=$UserBaggageEqModel->insertGetId(['u_id'=>$u_id,'b_equ_id'=>$upgradeEquId,'b_equ_rarity'=>$upgradeEquInfo['equ_rarity'],'b_equ_type'=>$upgradeEquInfo['equ_type'],'b_icon_path'=>$upgradeEquInfo['icon_path'],'status'=>1,'updated_at'=>$datetime,'created_at'=>$datetime]);
			
				if($eqDetail['b_equ_type']==1){
					$charmodel->where('u_id',$u_id)->update(['w_id'=>$upgradeEquId,'w_bag_id'=>$w_bag_id,'updated_at'=>$datetime]);
				}
				else if($eqDetail['b_equ_type']==2){
					$charmodel->where('u_id',$u_id)->update(['core_id'=>$upgradeEquId,'core_bag_id'=>$w_bag_id,'updated_at'=>$datetime]);
				}
				$result['user_beq_id']=$w_bag_id;

			}
			else if($eqDetail['status']==0){
				$w_bag_id=$UserBaggageEqModel->insertGetId(['u_id'=>$u_id,'b_equ_id'=>$upgradeEquId,'b_equ_rarity'=>$upgradeEquInfo['equ_rarity'],'b_equ_type'=>$upgradeEquInfo['equ_type'],'b_icon_path'=>$upgradeEquInfo['icon_path'],'status'=>0,'updated_at'=>$datetime,'created_at'=>$datetime]);
				$result['user_beq_id']=$w_bag_id;
			}

			$nextUpgarde=$EquUpgradeMstModel->where('equ_code',$equData['equ_code'])->where('lv',$equData['equ_lv']+2)->count();
			$result['equ_id']=$upgradeEquId;
			
			$result['upgrade']=$nextUpgarde;

			$response=json_encode($result,TRUE);

		return base64_encode($response);
	}

}