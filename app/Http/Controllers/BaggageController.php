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
use App\Util\BaggageUtil;
use App\Util\ItemInfoUtil;
use Exception;
use App\Exceptions\Handler;
use Illuminate\Http\Response;
use Carbon\Carbon;
use DateTime;
use DB;
use Illuminate\Support\Facades\Redis;

class BaggageController extends Controller
{
	//according to the select, display items in the baggage
	public function baggage(Request $request)
	{
		$req=$request->getContent();
		$json=base64_decode($req);
		$data=json_decode($req,TRUE);

		$BaggageUtil=new BaggageUtil();
		$result=[];

		/*$now   = new DateTime;
		$datetime=$now->format( 'Y-m-d h:m:s' );
		$dmy=$now->format( 'Ymd' );
		$loginToday=Redis::HGET('login_data',$dmy.$uid);
		$loginTodayArr=json_decode($loginToday);
		$access_token=$loginTodayArr->access_token;*/

		$userBaggageChoice=$data;
		$u_id=$userBaggageChoice['u_id'];
		$select=$userBaggageChoice['select']; //there are five different types: All/R/S/W/C
		if(isset($u_id)/*&&$access_token==$data['access_token']*/)
		{
			if($select === "All")//get all the item from baggage
			{
				$Resource=$BaggageUtil->getResource($u_id);
				$result['Baggage_data']['itemtype_1']=$Resource;
				$Scroll=$BaggageUtil->getScroll($u_id);
				$result['Baggage_data']['itemtype_3']=$Scroll;
				$Weapon=$BaggageUtil->getWeapon($u_id);
				$result['Baggage_data']['itemtype_2']=$Weapon;
				$Core=$BaggageUtil->getCore($u_id);
				$result['Baggage_data']['itemtype_2']=$Core;
				$response=json_encode($result,TRUE);
			}else if($select === "R")//select Resource
			{
				$Resource=$BaggageUtil->getResource($u_id);
				$result['Baggage_data']['itemtype_1']=$Resource;
				$response=json_encode($result,TRUE);
			}else if($select === "S")//select Scroll
			{
				$Scroll=$BaggageUtil->getScroll($u_id);
				$result['Baggage_data']['itemtype_3']=$Scroll;
				$response=json_encode($result,TRUE);
			}else if($select === "W")//select Weapon
			{
				$Weapon=$BaggageUtil->getWeapon($u_id);
				$result['Baggage_data']['itemtype_2']=$Weapon;
				$response=json_encode($result,TRUE);
			}else if($select === "C")//select Core
			{
				$Core=$BaggageUtil->getCore($u_id);
				$result['Baggage_data']['itemtype_2']=$Core;
				$response=json_encode($result,TRUE);
			}else
			{
				throw new Exception("Wrong select data");
				$response=[
				'status' => 'Wrong',
				'error' => "please check select data",
				];
			}
		}else
		{
			throw new Exception("there have some error of you access_token");
			$response=[
			'status' => 'Wrong',
			'error' => "please check u_id",
			];
		}
		return base64_encode($response);
	}

	//show the detail information when user click the item in the baggage
	public function getItemInfo (Request $request)
	{
		$req=$request->getContent();
		$json=base64_decode($req);
		$data=json_decode($req,TRUE);

		$ItemInfoUtil=new ItemInfoUtil();
		$result=[];

		$ItemType=$data['type']; //there are three different types: itemtype_1(Resource)/itemtype_2(Equipment)/itemtype_3(Scroll)
		$ItemId=$data['Item_Id'];
		$u_id=$data['u_id'];

		/*$now   = new DateTime;
		$datetime=$now->format( 'Y-m-d h:m:s' );
		$dmy=$now->format( 'Ymd' );
		$loginToday=Redis::HGET('login_data',$dmy.$uid);
		$loginTodayArr=json_decode($loginToday);
		$access_token=$loginTodayArr->access_token;*/

		if(isset($u_id)/*&&$access_token==$data['access_token']*/)
		{
			if($ItemType === "itemtype_1")
			{
				$ResInfo = $ItemInfoUtil->getResourceInfo($ItemId);
				$response=$ResInfo;
			}else if($ItemType === "itemtype_2")
			{
				$EquInfo = $ItemInfoUtil->getEquipmentInfo($ItemId);
				$response=$EquInfo;
			}else if($ItemType === "itemtype_3")
			{
				$ScrollInfo = $ItemInfoUtil->getScrollInfo($ItemId,$u_id);
				$response=$ScrollInfo;
			}else
			{
				throw new Exception("Wrong itemtype data");
				$response=[
				'status' => 'Wrong',
				'error' => "please check itemtype data",
				];
			}

		}else{
			throw new Exception("there have some error of you access_token");
			$response=[
			'status' => 'Wrong',
			'error' => "please check u_id",
			];
		}
		return base64_encode($response);
	}

	//sell item in the baggage
	public function sellItem (Request $request)
	{
		$req=$request->getContent();
		$json=base64_decode($req);
		$data=json_decode($req,TRUE);
		$now=new DateTime;
		$datetime=$now->format( 'Y-m-d h:m:s' );
		$dmy=$now->format( 'Ymd' );

		$UserModel=new UserModel();
		$UserBaggageResModel=new UserBaggageResModel();
		$UserBaggageEqModel=new UserBaggageEqModel();
		$UserBaggageScrollModel=new UserBaggageScrollModel();
		$result=[];

		$u_id=$data['u_id'];
		$ItemType=$data['type'];//itemtype_2(Equipment)/itemtype_3(Scroll)
		$ItemPrice=$data['Item_Price'];
		$ItemId=$data['Item_Id'];

		if($ItemType === "itemtype_2")//sell Equipment
		{
			$UserBaggageEqModel->where('u_id',$u_id)->where('status','=',0)->where('b_equ_id',$ItemId)->limit(1)->update(array('status'=>1,'updated_at'=>$datetime));
			$UserData=$UserModel->where('u_id',$u_id)->first();
			$updateCoin=$UserData['u_coin']+$ItemPrice;
			$UserModel->where('u_id',$u_id)->update(['u_coin'=>$updateCoin,'updated_at'=>$datetime]);
			$response="update Equipment";
		}else if($ItemType === "itemtype_3")//sell Scroll
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
}