<?php

namespace App\Http\Controllers;
use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\UserModel;
use App\CharacterModel;
use App\EquipmentMstModel;
use App\EffectionMstModel;
use App\SkillMstModel;
use App\ScrollMstModel;
use App\ResourceMstModel;
use App\UserBaggageResModel;
use App\UserBaggageEqModel;
use App\UserBaggageScrollModel;
use App\EquUpgradeMstModel;
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

		/*$now   = new DateTime;
		$datetime=$now->format( 'Y-m-d h:m:s' );
		$dmy=$now->format( 'Ymd' );
		$loginToday=Redis::HGET('login_data',$dmy.$uid);
		$loginTodayArr=json_decode($loginToday);
		$access_token=$loginTodayArr->access_token;*/
		
		$u_id=$data['u_id'];
		$select=$data['eq_choose']; //there are five different types: All/R/S/W/C
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


		/*$now   = new DateTime;
		$datetime=$now->format( 'Y-m-d h:m:s' );
		$dmy=$now->format( 'Ymd' );
		$loginToday=Redis::HGET('login_data',$dmy.$uid);
		$loginTodayArr=json_decode($loginToday);
		$access_token=$loginTodayArr->access_token;*/
			if($ItemType == 1)
			{
				$ResInfo = $ItemInfoUtil->getResourceInfo($ItemId);
				$result=$ResInfo;
			}else if($ItemType == 2)
			{
				$EquInfo = $ItemInfoUtil->getEquipmentInfo($ItemId,$u_id);
				$result=$EquInfo;
			}else if($ItemType == 3)
			{
				$ScrollInfo = $ItemInfoUtil->getScrollInfo($ItemId,$u_id);
				$result=$ScrollInfo;
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
		$ItemType=$data['type'];//itemtype:2(Equipment)/itemtype:3(Scroll)
		$ItemPrice=$data['Item_Price'];
		$ItemId=$data['Item_Id'];

		if($ItemType == 2)//sell Equipment
		{
			$UserBaggageEqModel->where('u_id',$u_id)->where('status','=',0)->where('b_equ_id',$ItemId)->limit(1)->update(array('status'=>1,'updated_at'=>$datetime));
			$UserData=$UserModel->where('u_id',$u_id)->first();
			$updateCoin=$UserData['u_coin']+$ItemPrice;
			$UserModel->where('u_id',$u_id)->update(['u_coin'=>$updateCoin,'updated_at'=>$datetime]);
			$response="update Equipment";
		}else if($ItemType == 3)//sell Scroll
		{
			$UserBaggageScrollModel->where('u_id',$u_id)->where('status','=',0)->where('bsc_id',$ItemId)->limit(1)->update(['status'=>1,'updated_at'=>$datetime]);
			$UserData=$UserModel->where('u_id',$u_id)->first();
			$updateCoin=$UserData['u_coin']+$ItemPrice;
			$UserModel->where('u_id',$u_id)->update(['u_coin'=>$updateCoin,'updated_at'=>$datetime]);
			$response="update Scroll";
		}else{
			throw new Exception("No ItemType");
			$response=[
			'status' => 'Wrong',
			'error' => "please check ItemType",
			];
		}
		return base64_encode($response);
	}


	public function scrollMerage (Request $request)
	{
		$req=$request->getContent();
		$data=json_decode($req,TRUE);
		$now=new DateTime;
		$datetime=$now->format( 'Y-m-d h:m:s' );
		$dmy=$now->format( 'Ymd' );

		$UserModel=new UserModel();
		$UserBaggageResModel=new UserBaggageResModel();
		$UserBaggageEqModel=new UserBaggageEqModel();
		$UserBaggageScrollModel=new UserBaggageScrollModel();
		$ScrollMstModel=new ScrollMstModel();
		$EquipmentMstModel=new EquipmentMstModel();
		$result=[];

		$u_id=$data['u_id'];
		$scrollId=$data['scroll_id'];
		if(isset($u_id))
		{
			$UserBaggageScrollModel->where('u_id',$u_id)->where('status','=',0)->where('bsc_id',$scrollId)->limit(1)->update(array('status'=>1,'updated_at'=>$datetime));
			$ScrollInfo=$ScrollMstModel->where('sc_id',$scrollId)->first();
			$equipmentId=$ScrollInfo['equ_id'];
			$equipmentInfo=$EquipmentMstModel->where('equ_id',$equipmentId)->first();
			$UserBaggageEqModel->insert(['u_id'=>$u_id,'b_equ_id'=>$equipmentId,'b_equ_rarity'=>$equipmentInfo['equ_rarity'],'b_equ_type'=>$equipmentInfo['equ_type'],'b_icon_path'=>$equipmentInfo['icon_path'],'status'=>0,'updated_at'=>$datetime,'created_at'=>$datetime]);

			$resource=[];
			$resource1=[];
			$resource1['r_id']=$ScrollInfo['r_id_1'];
			$resource1['r_quantity']=$ScrollInfo['rd1_quantity'];
			$resource[]=$resource1;

			$resource2=[];
			$resource2['r_id']=$ScrollInfo['r_id_2'];
			$resource2['r_quantity']=$ScrollInfo['rd2_quantity'];
			$resource[]=$resource2;

			$resource3=[];
			if(isset($ScrollInfo['r_id_3'])){
				$resource3['r_id']=$ScrollInfo['r_id_3'];
				$resource3['r_quantity']=$ScrollInfo['rd3_quantity'];
				$resource[]=$resource3;
			}
			
			$resource4=[];
			if(isset($ScrollInfo['r_id_4']))
			{
				$resource4['r_id']=$ScrollInfo['r_id_4'];
				$resource4['r_quantity']=$ScrollInfo['rd4_quantity'];
				$resource[]=$resource4;
			}

			foreach ($resource as $obj)
			{
				$UserBaggageData=$UserBaggageResModel->where('u_id',$u_id)->where('br_id',$obj['r_id'])->first();
				$resQuantity=$UserBaggageData['br_quantity']-$obj['r_quantity'];
				$UserBaggageResModel->where('u_id',$u_id)->where('br_id',$obj['r_id'])->update(array('br_quantity'=>$resQuantity,'updated_at'=>$datetime));
			}

			$UserData=$UserModel->where('u_id',$u_id)->first();
			$updateCoin=$UserData['u_coin']-$ScrollInfo['sc_coin'];
			$UserModel->where('u_id',$u_id)->update(['u_coin'=>$updateCoin,'updated_at'=>$datetime]);

			$response='Successfully Meraged';
		}else{
			throw new Exception("there have some error of you access_token");
			$response=[
			'status' => 'Wrong',
			'error' => "please check u_id",
			];
		}
		return $response;
	}


	public function equipmentUpgrade (Request $request)
	{
		$req=$request->getContent();
		$data=json_decode($req,TRUE);
		$now=new DateTime;
		$datetime=$now->format( 'Y-m-d h:m:s' );
		$dmy=$now->format( 'Ymd' );

		$UserModel=new UserModel();
		$UserBaggageResModel=new UserBaggageResModel();
		$UserBaggageEqModel=new UserBaggageEqModel();
		$UserBaggageScrollModel=new UserBaggageScrollModel();
		$ScrollMstModel=new ScrollMstModel();
		$EquipmentMstModel=new EquipmentMstModel();
		$EquUpgradeMstModel=new EquUpgradeMstModel();
		$result=[];
		$charmodel=new CharacterModel();

		$u_id=$data['u_id'];
		$equipmentId=$data['equ_id'];
		$user_beq_id=$data['user_beq_id'];
		$eqDetail=$UserBaggageEqModel->where('u_id',$u_id)->where('user_beq_id',$user_beq_id)->where('b_equ_id',$equipmentId)->first();

			$upgradeInfo=$EquUpgradeMstModel->where('equ_id',$equipmentId)->first();

			$upgradeEquId=$upgradeInfo['equ_upgrade_id'];
			$upgradeEquInfo=$EquipmentMstModel->where('equ_id',$upgradeEquId)->first();

			// $charmodel->where('u_id',$u_id)->update(['w_id'=>$w_bag_id])

			$userRe1=$userBag->where('u_id',$u_id)->where('br_id',$upgradeInfo['r_id_1'])->first();
				if($userRe1['br_quantity']<$upgradeInfo['rd1_quantity']){
					throw new Exception("no enough resouce1");
					$response=[
						'status' => 'Wrong',
						'error' => "please check u_id",
					];
				}
				else{
					$resouce1Qu=$userRe1['br_quantity']-$upgradeInfo['rd1_quantity'];
				}
			$userRe2=$userBag->where('u_id',$u_id)->where('br_id',$upgradeInfo['r_id_2'])->first();
				if($userRe2['br_quantity']<$upgradeInfo['rd2_quantity']){
					throw new Exception("no enough resouce2");
					$response=[
						'status' => 'Wrong',
						'error' => "please check u_id",
					];
				}
				else{
					$resouce2Qu=$userRe2['br_quantity']-$upgradeInfo['rd2_quantity'];
				}

			$userRe3=$userBag->where('u_id',$u_id)->where('br_id',$upgradeInfo['r_id_3'])->first();
				if($userRe2['br_quantity']<$upgradeInfo['rd3_quantity']){
					throw new Exception("no enough resouce3");
					$response=[
						'status' => 'Wrong',
						'error' => "please check u_id",
					];
				}
				else{
					$resouce3Qu=$userRe3['br_quantity']-$upgradeInfo['rd3_quantity'];
			}
			$userRe4=$userBag->where('u_id',$u_id)->where('br_id',$upgradeInfo['r_id_4'])->first();
				if($userRe4['br_quantity']<$upgradeInfo['rd4_quantity']){
					throw new Exception("no enough resouce1");
					$response=[
						'status' => 'Wrong',
						'error' => "please check u_id",
					];
				}
				else{
					$resouce4Qu=$userRe4['br_quantity']-$upgradeInfo['rd4_quantity'];
			}

			$userRe5=$userBag->where('u_id',$u_id)->where('br_id',$upgradeInfo['r_id_4'])->first();
				if($userRe5['br_quantity']<$upgradeInfo['rd5_quantity']){
					throw new Exception("no enough resouce1");
					$response=[
						'status' => 'Wrong',
						'error' => "please check u_id",
					];
				}
				else{
					$resouce5Qu=$userRe5['br_quantity']-$upgradeInfo['rd5_quantity'];
			}

			$UserBaggageEqModel->where('user_beq_id',$user_beq_id)->where('u_id',$u_id)->update(['status'=>2,'updated_at'=>$datetime]);
			$w_bag_id=$UserBaggageEqModel->insertGetId(['u_id'=>$u_id,'b_equ_id'=>$upgradeEquId,'b_equ_rarity'=>$upgradeEquInfo['equ_rarity'],'b_equ_type'=>$upgradeEquInfo['equ_type'],'b_icon_path'=>$upgradeEquInfo['icon_path'],'status'=>$eqDetail['status'],'updated_at'=>$datetime,'created_at'=>$datetime]);
			$UserBaggageResModel->where('u_id',$u_id)->where('br_id',$resouce1Qu)->update(['br_quantity'=>$resouce1Qu,'updated_at'=>$datetime]);
			$UserBaggageResModel->where('u_id',$u_id)->where('br_id',$resouce2Qu)->update(['br_quantity'=>$resouce2Qu,'updated_at'=>$datetime]);
			$UserBaggageResModel->where('u_id',$u_id)->where('br_id',$resouce3Qu)->update(['br_quantity'=>$resouce3Qu,'updated_at'=>$datetime]);
			$UserBaggageResModel->where('u_id',$u_id)->where('br_id',$resouce4Qu)->update(['br_quantity'=>$resouce4Qu,'updated_at'=>$datetime]);
			$UserBaggageResModel->where('u_id',$u_id)->where('br_id',$resouce5Qu)->update(['br_quantity'=>$resouce5Qu,'updated_at'=>$datetime]);
			$charmodel->where('u_id',$u_id)->update(['w_id'=>$upgradeEquId,'w_bag_id',$w_bag_id,'updated_at'=>$datetime]);
			$response="Successfully upgrade weapon"

		return base64_encode($response);;
	}
	// private funciton resourceEnough($u_id,$resouse_id,$resource_quantity){
	// 	$userBag=new UserBaggageResModel();
	// 	$userRe=$userBag->where('u_id',$u_id)->where('br_id',$resouse_id)->first();
	// 	if($userRe['br_quantity']>=$resource_quantity){
	// 		return true;
	// 	}
	// 	else {
	// 		return false;
	// 	}
	// }
}