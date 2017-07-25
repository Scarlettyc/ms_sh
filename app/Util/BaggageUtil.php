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
	//show the quantity and icon for every item in the baggage
	function getResource($baggage_u_id)
	{
		if(isset($baggage_u_id))
		{
			$UserBaggageResModel=new UserBaggageResModel();
			$ItemMstModel=new ItemMstModel();
			$result=[];

			$BaggageResource=$UserBaggageResModel->select('br_id','br_icon','br_quantity')->where('u_id',$baggage_u_id)->where('status','=',0)->get();

			foreach ($BaggageResource as $obj) {
				$arry['item_id']=$obj['br_id'];
				$arry['item_icon']=$obj['br_icon'];
				$arry['item_quantity']=$obj['br_quantity'];
				$arry['item_type']=1;
				$result=$arry;
			}

			/*$item_id=$UserBaggageResModel->where('u_id',$baggage_u_id)->where('status','=',0)->pluck('br_id');
			$item_icon=$UserBaggageResModel->where('u_id',$baggage_u_id)->where('status','=',0)->pluck('br_icon');
			$item_quantity=$UserBaggageResModel->where('u_id',$baggage_u_id)->where('status','=',0)->pluck('br_quantity');*/

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
			$response=$baggageScroll;
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

			$baggageWeapon=$UserBaggageEqModel->select('u_id','b_equ_id','b_icon_path')->where('u_id','=',$baggage_u_id)->where('status','=',0)->where('b_equ_type','=',1)->get();
			$response=$baggageWeapon;
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

			$baggageCore=$UserBaggageEqModel->select('u_id','b_equ_id','b_icon_path')->where('u_id','=',$baggage_u_id)->where('status','=',0)->where('b_equ_type','=',3)->get();
			$response=$baggageCore;
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