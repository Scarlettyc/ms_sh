<?php
namespace App\Util;
use App\Http\Requests;
use Illuminate\Http\Request;
use App\UserModel;
use App\EquipmentMstModel;
use App\EffectionMstModel;
use App\SkillMstModel;
use App\ScrollMstModel;
use App\ResourceMstModel;
use App\UserBaggageResModel;
use Exception;
use App\Exceptions\Handler;
use Illuminate\Http\Response;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\Redis;

class ItemInfoUtil
{
	//get Resource detail information: name, type, icon, description
	function getResourceInfo ($Item_Id)
	{
		$ResId=$Item_Id;

		if(isset($ResId))
		{
			$ResourceMstModel=new ResourceMstModel();
			$resource=[];
			$result=[];

			$ResourceInfo = $ResourceMstModel->select('r_id','r_name','r_rarity','r_type','r_img_path','r_description')->where('r_id','=',$ResId)->first();

			$resource['item_id']=$ResourceInfo['r_id'];
			$resource['item_name']=$ResourceInfo['r_name'];
			$resource['item_rarity']=$ResourceInfo['r_rarity'];
			$resource['item_img']=$ResourceInfo['r_img_path'];
			$resource['item_description']=$ResourceInfo['r_description'];
			$resource['item_price']=0;
			$resource['item_info']=null;

			$result['item_data']=$resource;
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

	//get Scroll detail information: name and every resource that this Scorll needed. also display the detail information of the equipment and skills.
	function getScrollInfo ($Item_Id,$u_id)
	{
		$ScrollId=$Item_Id;
		$u_id=$u_id;

		if(isset($ScrollId))
		{
			$UserModel=new UserModel();
			$ScrollMstModel=new ScrollMstModel();
			$EquipmentMstModel=new EquipmentMstModel();
			$SkillMstModel=new SkillMstModel();
			$ResourceMstModel=new ResourceMstModel();
			$EffectionMstModel=new EffectionMstModel();
			$UserBaggageResModel=new UserBaggageResModel();	
			$result=[];

			$ScrollInfo = $ScrollMstModel->where('sc_id','=',$ScrollId)->first();
			$ScrollDataInfo = $ScrollMstModel->select('sc_name','rd1_quantity','rd2_quantity','rd3_quantity','rd4_quantity','rd5_quantity','sc_rarity','sc_description','sc_img_path','sc_sale_price')->where('sc_id','=',$ScrollId)->first();
			$result['Scroll_data']['Scroll_info']=$ScrollDataInfo;

			//get equipment information and skill information from database
			$equ_id=$ScrollInfo['equ_id'];
			$EquipmentInfo = $EquipmentMstModel->where('equ_id','=',$equ_id)->first();
			$EquipmentDataInfo = $EquipmentMstModel->select('equ_name','equ_part','equ_type','icon_path')->where('equ_id','=',$equ_id)->first();
			$result['Equipment_data']['Equipment_info']=$EquipmentDataInfo;

			$EquEff_id = $EquipmentInfo['eff_id'];
			$EquEffectInfo = $EffectionMstModel->where('eff_id','=',$EquEff_id)->first();
			$result['Equipment_data']['EquEffect_info']=$EquEffectInfo;

			$Skill_id = $EquipmentInfo['skill_id'];
			$SkillInfo = $SkillMstModel->where('skill_id','=',$Skill_id)->first();
			$SkillDataInfo = $SkillMstModel->select('skill_name','skill_icon','skill_info')->where('skill_id','=',$Skill_id)->first();
			$result['Skill_data']['Skill_info']=$SkillDataInfo;

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
			$CoinResQuantity = $UserModel->where('u_id','=',$u_id)->pluck('u_coin');

			//get normal resource 1 icon and the number or resource that user already had
			$normal_res_id_1=$ScrollInfo['r_id_3'];
			$NormalRes1Info = $ResourceMstModel->where('r_id','=',$normal_res_id_1)->pluck('r_img_path');
			$result['Normal_Resource1_data']['Normal_Resource1_icon']=$NormalRes1Info;
			$NormalRes1Quantity = $UserBaggageResModel->where('u_id','=',$u_id)->where('br_id','=',$normal_res_id_1)->pluck('br_quantity');
			$result['Normal_Resource1_data']['Normal_Resource1_quantity']=$NormalRes1Quantity;

			//if have normal resource 2, get normal resource 2 icon and the number or resource that user already had
			$normal_res_id_2=$ScrollInfo['r_id_4'];
			if(isset($normal_res_id_2))
			{
				$NormalRes2Info = $ResourceMstModel->where('r_id','=',$normal_res_id_2)->pluck('r_img_path');
				$result['Normal_Resource2_data']['Normal_Resource2_icon']=$NormalRes2Info;
				$NormalRes2Quantity = $UserBaggageResModel->where('u_id','=',$u_id)->where('br_id','=',$normal_res_id_2)->pluck('br_quantity');
				$result['Normal_Resource2_data']['Normal_Resource2_quantity']=$NormalRes2Quantity;
			}

			//if have normal resource 3, get normal resource 3 icon and the number or resource that user already had
			$normal_res_id_3=$ScrollInfo['r_id_5'];
			if(isset($normal_res_id_3))
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

	// get the information of a Equipment: name, type, icon, effection
	function getEquipmentInfo ($Item_Id)
	{
		$EquipmentId=$Item_Id;
		if(isset($EquipmentId))
		{
			$EquipmentMstModel=new EquipmentMstModel();
			$EffectionMstModel=new EffectionMstModel();
			$result=[];

			$EquipmentInfo = $EquipmentMstModel->where('equ_id','=',$EquipmentId)->first();
			$EquipmentDataInfo = $EquipmentMstModel->select('equ_name','equ_part','equ_type','equ_price','icon_path')->where('equ_id','=',$EquipmentId)->first();
			$result['Equipment_data']['Equipment_info']=$EquipmentDataInfo;

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

	function getSkillInfo ($skill_id)
	{
		$Skill_Id=$skill_id;
		if(isset($Skill_Id))
		{
			$SkillMstModel=new SkillMstModel();
			$EffectionMstModel=new EffectionMstModel();
			$result=[];

			$SkillInfo=$SkillMstModel->where('skill_id',$Skill_Id)->first();
			$SkillDataInfo=$SkillMstModel->select('skill_name','skill_icon','skill_info')->where('skill_id',$Skill_Id)->first();
			$result['Skill_data']['Skill_info']=$SkillDataInfo;

			$SkillEff_id=$SkillInfo['eff_id'];
			$SkillEffInfo=$EffectionMstModel->where('eff_id',$SkillEff_id)->first();
			$result['Skill_data']['SkillEff_info']=$SkillEffInfo;

			$response=json_encode($result,TRUE);
		}else
		{
			throw new Exception("Wrong Skill ID");
			$response=[
			'status' => 'Wrong',
			'error' => "please check Skill ID",
			];
		}
		return $response;
	}
}