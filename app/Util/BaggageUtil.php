<?php
namespace App\Util;
use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\UserBaggageResModel;
use App\UserBaggageEqModel;
use App\UserBaggageScrollModel;
use App\ItemMstModel;
use Exception;
use App\Exceptions\Handler;
use Illuminate\Http\Response;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\Redis;

class BaggageUtil
{
	function getResource($baggage_u_id)
	{
		if(isset($baggage_u_id))
		{
			$UserBaggageResModel=new UserBaggageResModel();
			$ItemMstModel=new ItemMstModel();
			$result=[];

			$baggageResource=$UserBaggageResModel->select('u_id','br_id','br_type','br_quantity')->where('u_id','=',$baggage_u_id)->where('status','=',0)->get();
			$result['Baggage_data']['Baggage_info_Res']=$baggageResource;
			$result['Baggage_data']['item_type']=1;
			$response=$result;
		}else{
			throw new Exception("No User ID");
			$response=[
			'status' => 'Wrong',
			'error' => "please check u_id",
			];
		}
		return $response;
	}

	function getScroll ($baggage_u_id)
	{
		if(isset($baggage_u_id))
		{
			$UserBaggageScrollModel=new UserBaggageScrollModel();
			$result=[];

			$baggageScroll=$UserBaggageScrollModel->select('u_id','bsc_id','bsc_icon')->where('u_id','=',$baggage_u_id)->where('status','=',0)->get();
			$result['Baggage_data']['Baggage_info_Scr']=$baggageScroll;
			$result['Baggage_data']['item_type']=3;
			$response=$result;
		}else{
			throw new Exception("No User ID");
			$response=[
			'status' => 'Wrong',
			'error' => "please check u_id",
			];
		}
		return $response;
	}

	function getWeapon ($baggage_u_id)
	{
		if(isset($baggage_u_id))
		{
			$UserBaggageEqModel=new UserBaggageEqModel();
			$result=[];

			$baggageWeapon=$UserBaggageEqModel->select('u_id','b_equ_id','b_equ_type','b_icon_path')->where('u_id','=',$baggage_u_id)->where('status','=',0)->where('b_equ_type','=',1)->get();
			$result['Baggage_data']['Baggage_info_Wea']=$baggageWeapon;
			$result['Baggage_data']['item_type']=2;
			$response=$result;
		}else{
			throw new Exception("No User ID");
			$response=[
			'status' => 'Wrong',
			'error' => "please check u_id",
			];
		}
		return $response;
	}

	function getCore ($baggage_u_id)
	{
		if(isset($baggage_u_id))
		{
			$UserBaggageEqModel=new UserBaggageEqModel();
			$result=[];

			$baggageCore=$UserBaggageEqModel->select('u_id','b_equ_id','b_equ_type','b_icon_path')->where('u_id','=',$baggage_u_id)->where('status','=',0)->where('b_equ_type','=',3)->get();
			$result['Baggage_data']['Baggage_info_Core']=$baggageCore;
			$result['Baggage_data']['item_type']=2;
			$response=$result;
		}else{
			throw new Exception("No User ID");
			$response=[
			'status' => 'Wrong',
			'error' => "please check u_id",
			];
		}
		return $response;
	}
}