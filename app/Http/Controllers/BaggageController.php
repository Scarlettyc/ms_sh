<?php

namespace App\Http\Controllers;
use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\UserModel;
use App\CharacterModel;
use App\EquipmentMstModel;
use App\SkillMstModel;
use Exception;
use App\Exceptions\Handler;
use Illuminate\Http\Response;
use Carbon\Carbon;
use DateTime;
class BaggageController extends Conroller
{
	public function Baggage(Request $request)
	{
		$req=$request->getContent();
		$data=json_decode($req,TRUE);

		$CharacterModel=new CharacterModel();
		$EquipmentMstModel=new EquipmentMstModel();
		$ResourceMstModel=new ResourceMstModel();
		$ScrollMstModel=new ScrollMstModel();
		$UserBaggageEqModel=new UserBaggageEqModel();
		$UserBaggageResModel=new UserBaggageResModel();
		$UserBaggageScrollModel=new UserBaggageScrollModel();
		$result=[];

		$userBaggageChoice=$data;
		$u_id=$userBaggageChoice['u_id'];
		$select=$userBaggageChoice['select'];
		if(isset($u_id))
		{
			if($select = 'All')
			{
				$Resource=getResource($u_id);
				$result['Baggage_data']['Baggage_info']=$Resource;
				$Scroll=getScroll($u_id);
				$result['Baggage_data']['Baggage_info']=$Scroll;
				$Weapon=getWeapon($u_id);
				$result['Baggage_data']['Baggage_info']=$Weapon;
				$Movement=getMovement($u_id);
				$result['Baggage_data']['Baggage_info']=$Movement;
				$Core=getCore($u_id);
				$result['Baggage_data']['Baggage_info']=$Core;
			}elseif($select = 'R')
			{
				$Resource=getResource($u_id);
				$result['Baggage_data']['Baggage_info']=$Resource;
			}elseif($select = 'S')
			{
				$Scroll=getScroll($u_id);
				$result['Baggage_data']['Baggage_info']=$Scroll;
			}elseif($select = 'W')
			{
				$Weapon=getWeapon($u_id);
				$result['Baggage_data']['Baggage_info']=$Weapon;
			}elseif($select = 'M')
			{
				$Movement=getMovement($u_id);
				$result['Baggage_data']['Baggage_info']=$Movement;
			}elseif($select = 'C')
			{
				$Core=getCore($u_id);
				$result['Baggage_data']['Baggage_info']=$Core;
			}else{
				throw new Exception("Wrong select data");
				$response=[
				'status' => 'Wrong',
				'error' => "please check select data",
				]
			}
		}else
		{
			throw new Exception("No User ID");
			$response=[
			'status' => 'Wrong',
			'error' => "please check u_id",
			]
		}
		$response=json_encode($result,TRUE);
		return $response;
	}

	public function getResourceInfo (Request $request)
	{
		$req=$request->getContent();
		$ResId=json_decode($req,TRUE);

		if(isset($ResId))
		{
			$ResourceMstModel=new ResourceMstModel();
			$result=[];

			$ResourceInfo = $ResourceMstModel->where('r_id','=',$ResId)->first();
			$result=['Resource_data']['Resource_info']=$ResourceInfo;
		}else{
			throw new Exception("Wrong Resource ID");
			$response=[
			'status' => 'Wrong',
			'error' => "please check Resource ID",
			];
		}
		$response=json_encode($result,TRUE);
		return $response;
	}

	public function getScrollInfo (Request $request)
	{
		$req=$request->getContent();
		$ScrollId=json_decode($req,TRUE);

		if(isset($Scroll))
		{
			$ScrollMstModel=new ScrollMstModel();
			$EquipmentMstModel=new EquipmentMstModel();
			$SkillMstModel=new SkillMstModel();
			$ResourceMstModel=new ResourceMstModel();	
			$result[];

			$ScrollInfo = $ScrollMstModel->where('sc_id','=',$ScrollId)->first();
			$result['Scroll_data']['Scroll_info']=$ScrollInfo;

			$equ_id=$ScrollInfo['equ_id'];
			$rare_res_id=$ScrollInfo['r_id_1'];
			$coin_res_id=$ScrollInfo['r_id_2'];
			$Normal_res_id_1=$ScrollInfo['r_id_3'];
			$Normal_res_id_2=$ScrollInfo['r_id_4'];
			$Normal_res_id_3=$ScrollInfo['r_id_5'];

		}
	}




















	function getResource ($baggage_u_id)
	{
		if(isset($baggage_u_id))
		{
			$UserBaggageResModel=new UserBaggageResModel();
			$result=[];

			$baggageResource=$UserBaggageResModel->where('u_id','=',$baggage_u_id)->where('status','=',0)->get();
			$result['Baggage_data']['Baggage_resource']=$baggageResource;

			$response=json_encode($result,TRUE);
			return $response;
		}else{
			throw new Exception("No User ID");
			$response=[
			'status' => 'Wrong',
			'error' => "please check u_id",
			]
		}
	}

	function getScroll ($baggage_u_id)
	{
		if(isset($baggage_u_id))
		{
			$UserBaggageScrollModel=new UserBaggageScrollModel();
			$result=[];

			$baggageScroll=$UserBaggageScrollModel->where('u_id','=',$baggage_u_id)->where('status','=',0)->get();
			$result['Baggage_data']['Baggage_Scroll']=$baggageScroll;

			$response=json_encode($result,TRUE);
			return $response;
		}else{
			throw new Exception("No User ID");
			$response=[
			'status' => 'Wrong',
			'error' => "please check u_id",
			]
		}
	}

	function getWeapon ($baggage_u_id)
	{
		if(isset($baggage_u_id))
		{
			$UserBaggageEqModel=new UserBaggageEqModel();
			$result=[];

			$baggageWeapon=$UserBaggageEqModel->where('u_id','=',$baggage_u_id)->where('status','=',0)->where('b_equ_type','=',1)->get();
			$result['Baggage_data']['Baggage_weapon']=$baggageWeapon;

			$response=json_encode($result,TRUE);
			return $response;
		}else{
			throw new Exception("No User ID");
			$response=[
			'status' => 'Wrong',
			'error' => "please check u_id",
			]
		}
	}

	function getMovement ($baggage_u_id)
	{
		if(isset($baggage_u_id))
		{
			$UserBaggageEqModel=new UserBaggageEqModel();
			$result=[];

			$baggageMovement=$UserBaggageEqModel->where('u_id','=',$baggage_u_id)->where('status','=',0)->where('b_equ_type','=',2)->get();
			$result['Baggage_data']['Baggage_movement']=$baggageMovement;

			$response=json_encode($result,TRUE);
			return $response;
		}else{
			throw new Exception("No User ID");
			$response=[
			'status' => 'Wrong',
			'error' => "please check u_id",
			]
		}
	}

	function getCore ($baggage_u_id)
	{
		if(isset($baggage_u_id))
		{
			$UserBaggageEqModel=new UserBaggageEqModel();
			$result=[];

			$baggageCore=$UserBaggageEqModel->where('u_id','=',$baggage_u_id)->where('status','=',0)->where('b_equ_type','=',3)->get();
			$result['Baggage_data']['Baggage_Core']=$baggageCore;

			$response=json_encode($result,TRUE);
			return $response;
		}else{
			throw new Exception("No User ID");
			$response=[
			'status' => 'Wrong',
			'error' => "please check u_id",
			]
		}
	}
}