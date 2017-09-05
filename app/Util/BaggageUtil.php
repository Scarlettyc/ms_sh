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

	function getMovement ($baggage_u_id)
	{
		if(isset($baggage_u_id))
		{
			$UserBaggageEqModel=new UserBaggageEqModel();
			$result=[];

			$baggageMovement=$UserBaggageEqModel->select('b_equ_id','b_icon_path')->where('u_id','=',$baggage_u_id)->where('status','=',0)->where('b_equ_type','=',2)->orderBy('b_equ_rarity','DESC')->get();

			foreach ($baggageMovement as $obj)
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
	function insertToBaggage($u_id,$rewards){

		$UserBaggageEqModel=new UserBaggageEqModel();
		$UserBaggageResModel=new UserBaggageResModel();
		$UserBaggageScrollModel=new UserBaggageScrollModel();
		$eqModel=new EquipmentMstModel();
		$scrModel=new ScrollMstModel();
		$reModel=new ResourceMstModel();
		$datetime=$now->format('Y-m-d h:m:s');
		try{
		foreach($rewards as $reward){
			if($reward['item_type']==1){
				$reData=$reModel->where('r_id',$reward['item_org_id'])->first();
				$result['u_id']=$u_id;
				$result['br_id']=$reData['r_id'];
				$result['br_icon']=$reData['r_img_path'];
				$result['br_rarity']=$reData['r_rarity'];
				$result['br_quantity']=$reward['item_quantity'];
				$result['status']=0;
				$result['updated_at']=$datetime;
				$result['created_at']=$datetime;
				$UserBaggageEqModel->insert($result);
			}
			else if($reward['item_type']==2){
				for ($i=0;;$i<$reward['item_quantity'];$i++) {
					$eqData=$eqModel->where('equ_id',$reward['item_org_id'])->first();
					$result['u_id']=$u_id;
					$result['b_equ_id']=$eqData['equ_id'];
					$result['b_icon_path']=$eqData['icon_path'];
					$result['b_equ_rarity']=$eqData['equ_rarity'];
					$result['b_equ_type']=$eqData['equ_type'];
					$result['status']=0;
					$result['updated_at']=$datetime;
					$result['created_at']=$datetime;
					$UserBaggageEqModel->insert($result);
				}
			}
			else if($reward['item_type']==3){
				for ($i=0;;$i<$reward['item_quantity'];$i++) {
					$scrData=$scrModel->where('sc_id',$reward['item_org_id'])->first();
					$result['u_id']=$u_id;
					$result['bsc_id']=$scrData['sc_id'];
					$result['bsc_icon']=$scrData['sc_img_path'];
					$result['bsc_rarity']=$scrData['sc_rarity'];
					$result['status']=0;
					$result['updated_at']=$datetime;
					$result['created_at']=$datetime;
					$UserBaggageScrollModel->insert($result);
				}
			}
			$missionlist[]=$reward['misson_id'];
		 }
		 return $missionlist;

		}catch(Exception $e){
			throw new Exception("there have some errors of insert to baggage");
		}

	}
	public function getReward($mission){
		$eqModel=new EquipmentMstModel();
		$scrModel=new ScrollMstModel();
		$reModel=new ResourceMstModel();
		if($mission['item_type']==1){
			$reData=$reModel->where('r_id',$mission['item_org_id'])->first();
			$result['item_name']=$reData['r_name'];
			$result['item_description']=$reData['r_description'];
			$result['item_image']=$reData['r_img_path'];
		}
		else if($mission['item_type']==2){
			$$eqData=$eqModel->where('equ_id',$mission['item_org_id'])->first();
			$result['item_name']=$reData['equ_name'];
			$result['item_description']=$reData['equ_description'];
			$result['item_image']=$reData['icon_path'];
		}
		else if($mission['item_type']==3){
			$scrData=$scrModel->where('sc_id',$mission['item_org_id'])->first();
			$result['item_name']=$reData['sc_name'];
			$result['item_description']=$reData['sc_description'];
			$result['item_image']=$reData['sc_img_path'];
		}
			$result['item_org_id']=$mission['item_org_id'];
			$result['item_quantity']=$mission['item_quantity'];
			$result['item_type']=$mission['item_type'];
			
			return $result;
	}
}