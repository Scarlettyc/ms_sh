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

			$BaggageResource=$UserBaggageResModel->select('br_id','br_icon','br_quantity')->where('u_id',$baggage_u_id)->where('status','=',0)->orderBy('br_rarity','DESC')->get();

			foreach ($BaggageResource as $obj) 
			{
				$arry['item_id']=$obj['br_id'];
				$arry['item_icon']=$obj['br_icon'];
				$arry['item_quantity']=$obj['br_quantity'];
				$arry['item_type']=1;
				$result[]=$arry;
			}
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

			$baggageScroll=$UserBaggageScrollModel->select('bsc_id','bsc_icon')->where('u_id','=',$baggage_u_id)->where('status','=',0)->orderBy('bsc_rarity','DESC')->get();

			foreach ($baggageScroll as $obj) 
			{
				$arry['item_id']=$obj['bsc_id'];
				$arry['item_icon']=$obj['bsc_icon'];
				$arry['item_quantity']=1;
				$arry['item_type']=3;
				$result[]=$arry;
			}
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

			$baggageWeapon=$UserBaggageEqModel->select('b_equ_id','b_icon_path')->where('u_id','=',$baggage_u_id)->where('status','=',0)->where('b_equ_type','=',1)->orderBy('b_equ_rarity','DESC')->get();

			foreach ($baggageWeapon as $obj) 
			{
				$arry['item_id']=$obj['b_equ_id'];
				$arry['item_icon']=$obj['b_icon_path'];
				$arry['item_quantity']=1;
				$arry['item_type']=2;
				$result[]=$arry;
			}
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

			$baggageCore=$UserBaggageEqModel->select('b_equ_id','b_icon_path')->where('u_id','=',$baggage_u_id)->where('status','=',0)->where('b_equ_type','=',3)->orderBy('b_equ_rarity','DESC')->get();

			foreach ($baggageCore as $obj) 
			{
				$arry['item_id']=$obj['b_equ_id'];
				$arry['item_icon']=$obj['b_icon_path'];
				$arry['item_quantity']=1;
				$arry['item_type']=2;
				$result[]=$arry;
			}
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