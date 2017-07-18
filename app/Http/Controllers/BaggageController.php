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
use Exception;
use App\Exceptions\Handler;
use Illuminate\Http\Response;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\Redis;

class BaggageController extends Controller
{
	public function baggage(Request $request)
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

		/*$datetime=$now->format( 'Y-m-d h:m:s' );
		$dmy=$now->format( 'Ymd' );
		$loginToday=Redis::HGET('login_data',$dmy.$uid);
		$loginTodayArr=json_decode($loginToday);
		$access_token=$loginTodayArr->access_to;
		&&$access_token==$data['access_token']*/

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
				$response=json_encode($result,TRUE);
			}elseif($select = 'R')
			{
				$Resource=getResource($u_id);
				$result['Baggage_data']['Baggage_info']=$Resource;
				$response=json_encode($result,TRUE);
			}elseif($select = 'S')
			{
				$Scroll=getScroll($u_id);
				$result['Baggage_data']['Baggage_info']=$Scroll;
				$response=json_encode($result,TRUE);
			}elseif($select = 'W')
			{
				$Weapon=getWeapon($u_id);
				$result['Baggage_data']['Baggage_info']=$Weapon;
				$response=json_encode($result,TRUE);
			}elseif($select = 'M')
			{
				$Movement=getMovement($u_id);
				$result['Baggage_data']['Baggage_info']=$Movement;
				$response=json_encode($result,TRUE);
			}elseif($select = 'C')
			{
				$Core=getCore($u_id);
				$result['Baggage_data']['Baggage_info']=$Core;
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

	public function getResourceInfo (Request $request)
	{
		$req=$request->getContent();
		$ResId=json_decode($req,TRUE);

		if(isset($ResId))
		{
			$ResourceMstModel=new ResourceMstModel();
			$result=[];

			$ResourceInfo = $ResourceMstModel->where('r_id','=',$ResId)->first();
			$result['Resource_data']['Resource_info']=$ResourceInfo;
			$response=json_encode($result,TRUE);
		}else{
			throw new Exception("Wrong Resource ID");
			$response=[
			'status' => 'Wrong',
			'error' => "please check Resource ID",
			];
		}
		return $response;
	}

	public function getScrollInfo (Request $request)
	{
		$req=$request->getContent();
		$data=json_decode($req,TRUE);

		$ScrollId=$data['Scroll'];
		$u_id=$data['u_id'];

		if(isset($ScrollId))
		{
			$ScrollMstModel=new ScrollMstModel();
			$EquipmentMstModel=new EquipmentMstModel();
			$SkillMstModel=new SkillMstModel();
			$ResourceMstModel=new ResourceMstModel();
			$EffectionMstModel=new EffectionMstModel();
			$UserBaggageResModel=new UserBaggageResModel();	
			$result=[];

			$ScrollInfo = $ScrollMstModel->where('sc_id','=',$ScrollId)->first();
			$result['Scroll_data']['Scroll_info']=$ScrollInfo;

			//get equipment information and skill information from database
			$equ_id=$ScrollInfo['equ_id'];
			$EquipmentInfo = $EquipmentMstModel->where('equ_id','=',$equ_id)->first();
			$result['Equipment_data']['Equipment_info']=$EquipmentInfo;

			$EquEff_id = $EquipmentInfo['eff_id'];
			$EquEffectInfo = $EffectionMstModel->where('eff_id','=',$EquEff_id)->first();
			$result['Equipment_data']['EquEffect_info']=$EquEffectInfo;

			$Skill_id = $EquipmentInfo['skill_id'];
			$SkillInfo = $SkillMstModel->where('skill_id','=',$Skill_id)->first();
			$result['Skill_data']['Skill_info']=$SkillInfo;

			$SkillEff_id = $SkillInfo['eff_id'];
			$SkillEffectInfo = $EffectionMstModel->where('eff_id','=',$SkillEff_id)->first();
			$result['Skill_data']['SkillEffect_info']=$SkillEffectInfo;

			//get rare resource icon and the number of resource that user already had
			$rare_res_id=$ScrollInfo['r_id_1'];
			$RareResInfo = $ResourceMstModel->where('r_id','=',$rare_res_id)->pluck('r_img_path');
			$result['Rare_Resource_data']['Rare_Resource_icon']=$RareResInfo;
			$RareResQuantity = $UserBaggageResModel->where('u_id','=',$u_id)->where('br_id','=',$rare_res_id)->pluck('br_quantity');
			$result['Rare_Resource_data']['Rare_Resource_quantity']=$RareResQuantity;

			//get the number of coin that user already had
			$coin_res_id=$ScrollInfo['r_id_2'];
			$CoinResQuantity = $UserBaggageResModel->where('u_id','=',$u_id)->where('br_id','=',$coin_res_id)->pluck('br_quantity');

			//get normal resource 1 icon and the number or resource that user already had
			$normal_res_id_1=$ScrollInfo['r_id_3'];
			$NormalRes1Info = $ResourceMstModel->where('r_id','=',$normal_res_id_1)->pluck('r_img_path');
			$result['Normal_Resource1_data']['Normal_Resource1_icon']=$NormalResInfo;
			$NormalRes1Quantity = $UserBaggageResModel->where('u_id','=',$u_id)->where('br_id','=',$normal_res_id_1)->pluck('br_quantity');
			$result['Normal_Resource1_data']['Normal_Resource1_quantity']=$NormalResQuantity;

			//if have normal resource 2, get normal resource 2 icon and the number or resource that user already had
			$Normal_res_id_2=$ScrollInfo['r_id_4'];
			if(isset($Normal_res_id_2))
			{
				$NormalRes2Info = $ResourceMstModel->where('r_id','=',$normal_res_id_2)->pluck('r_img_path');
				$result['Normal_Resource2_data']['Normal_Resource2_icon']=$NormalRes2Info;
				$NormalRes2Quantity = $UserBaggageResModel->where('u_id','=',$u_id)->where('br_id','=',$normal_res_id_2)->pluck('br_quantity');
				$result['Normal_Resource2_data']['Normal_Resource2_quantity']=$NormalRes2Quantity;
			}

			//if have normal resource 3, get normal resource 3 icon and the number or resource that user already had
			$Normal_res_id_3=$ScrollInfo['r_id_5'];
			if(isset($Normal_res_id_3))
			{
				$NormalRes3Info = $ResourceMstModel->where('r_id','=',$normal_res_id_3)->pluck('r_img_path');
				$result['Normal_Resource3_data']['Normal_Resource3_icon']=$NormalRes3Info;
				$NormalRes3Quantity = $UserBaggageResModel->where('u_id','=',$u_id)->where('br_id','=',$normal_res_id_3)->pluck('br_quantity');
				$result['Normal_Resource3_data']['Normal_Resource3_quantity']=$NormalRes3Quantity;
			}
			$response=json_encode($result,TRUE);
		}else{
			throw new Exception("No Scroll ID");
			$response=[
			'status' => 'Wrong',
			'error' => "please check Scroll id",
			];
		}
		return $response;		
	}

	public function getEquipmentInfo (Request $request)
	{
		$req=$request->getContent();
		$EquipmentId=json_decode($req,TRUE);

		if(isset($EquipmentId))
		{
			$EquipmentMstModel=new EquipmentMstModel();
			$EffectionMstModel=new EffectionMstModel();
			$result=[];

			$EquipmentInfo = $EquipmentMstModel->where('equ_id','=',$WeaponId)->first();
			$result['Equipment_data']['Equipment_info']=$EquipmentInfo;

			$EquipmentEff_id = $EquipmentInfo['eff_id'];
			$EquipmentEffInfo = $EffectionMstModel->where('eff_id','=',$EquipmentEff_id)->first();
			$result['Equipment_data']['EquipmentEff_info']=$EquipmentEffInfo;

			$response=json_encode($result,TRUE);
		}else{
			throw new Exception("Wrong Equipment ID");
			$response=[
			'status' => 'Wrong',
			'error' => "please check Equipment ID",
			];
		}
		return $response;
	}

	public function getResource (Request $request)
	{
		$req=$request->getContent();
		$baggage_u_id=json_decode($req,TRUE);
		
		if(isset($baggage_u_id))
		{
			$UserBaggageResModel=new UserBaggageResModel();
			$result=[];

			$baggageResource=$UserBaggageResModel->where('u_id','=',$baggage_u_id)->where('status','=',0)->get();
			$result['Baggage_data']['Baggage_resource']=$baggageResource;

			$response=json_encode($result,TRUE);
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

			$baggageScroll=$UserBaggageScrollModel->where('u_id','=',$baggage_u_id)->where('status','=',0)->get();
			$result['Baggage_data']['Baggage_Scroll']=$baggageScroll;

			$response=json_encode($result,TRUE);
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

			$baggageWeapon=$UserBaggageEqModel->where('u_id','=',$baggage_u_id)->where('status','=',0)->where('b_equ_type','=',1)->get();
			$result['Baggage_data']['Baggage_weapon']=$baggageWeapon;

			$response=json_encode($result,TRUE);
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

			$baggageMovement=$UserBaggageEqModel->where('u_id','=',$baggage_u_id)->where('status','=',0)->where('b_equ_type','=',2)->get();
			$result['Baggage_data']['Baggage_movement']=$baggageMovement;

			$response=json_encode($result,TRUE);
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

			$baggageCore=$UserBaggageEqModel->where('u_id','=',$baggage_u_id)->where('status','=',0)->where('b_equ_type','=',3)->get();
			$result['Baggage_data']['Baggage_Core']=$baggageCore;

			$response=json_encode($result,TRUE);
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