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
	public function baggage(Request $request)
	{
		$req=$request->getContent();
		$data=json_decode($req,TRUE);

		$BaggageUtil=new BaggageUtil();
		$result=[];

		/*$datetime=$now->format( 'Y-m-d h:m:s' );
		$dmy=$now->format( 'Ymd' );
		$loginToday=Redis::HGET('login_data',$dmy.$uid);
		$loginTodayArr=json_decode($loginToday);
		$access_token=$loginTodayArr->access_to;
		&&$access_token==$data['access_token']*/

		$userBaggageChoice=$data;
		$u_id=$userBaggageChoice['u_id'];
		$select=$userBaggageChoice['select']; //there are five different types: All/R/S/W/C
		if(isset($u_id))
		{
			if($select === "All")
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
			}else if($select === "R")
			{
				$Resource=$BaggageUtil->getResource($u_id);
				$result['Baggage_data']['itemtype_1']=$Resource;
				$response=json_encode($result,TRUE);
			}else if($select === "S")
			{
				$Scroll=$BaggageUtil->getScroll($u_id);
				$result['Baggage_data']['itemtype_3']=$Scroll;
				$response=json_encode($result,TRUE);
			}else if($select === "W")
			{
				$Weapon=$BaggageUtil->getWeapon($u_id);
				$result['Baggage_data']['itemtype_2']=$Weapon;
				$response=json_encode($result,TRUE);
			}else if($select === "C")
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
			throw new Exception("No User ID");
			$response=[
			'status' => 'Wrong',
			'error' => "please check u_id",
			];
		}
		return $response;
	}

	public function getItemInfo (Request $request)
	{
		$req=$request->getContent();
		$data=json_decode($req,TRUE);

		$ItemInfoUtil=new ItemInfoUtil();
		$result=[];

		$ItemType=$data['type']; //there are three different types: itemtype_1/itemtype_2/itemtype_3
		$ItemId=$data['Item_Id'];
		$u_id=$data['u_id'];


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
		return $response;
	}

	public function sellItem (Request $request)
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
		$result=[];

		$u_id=$data['u_id'];
		$ItemType=$data['type'];
		$ItemPrice=$data['Item_Price'];
		$ItemId=$data['Item_Id'];

		if($ItemType === "itemtype_2")
		{
			$UserBaggageEqModel->where('u_id',$u_id)->where('status','=',0)->where('b_equ_id',$ItemId)->update(['status'=>1,'updatedate'=>$datetime])->limit(1);
			/*$UserData=$UserModel->where('u_id',$u_id)->first();
			$updateCoin=$UserData['u_coin']+$ItemPrice;
			$UserModel->where('u_id',$u_id)->update(['u_coin'=>$updateCoin,'updated_at'=>$datetime]);*/
			$response="update Equipment";
		}else if($ItemType === "itemtype_3")
		{
			$UserBaggageScrollModel->where('u_id',$u_id)->where('status','=',0)->where('bsc_id',$ItemId)->limit(1)->update(['status'=>1,'updatedate'=>$datetime]);
			$UserCoin=$UserModel->where('u_id',$u_id)->pluck('u_coin');
			$updateCoin=$UserCoin+$ItemPrice;
			$UserModel->where('u_id',$u_id)->update(['u_coin'=>$updateCoin,'updated_at'=>$datetime]);
			$response="update Scroll";
		}else{
			throw new Exception("No ItemType");
			$response=[
			'status' => 'Wrong',
			'error' => "please check ItemType",
			];
		}
		return $response;
	}
}