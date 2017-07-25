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
			
			$scroll=[];
			$scroll_detail=[];
			$ScrollInfo = $ScrollMstModel->where('sc_id','=',$ScrollId)->first();
			$ScrollDataInfo = $ScrollMstModel->select('sc_name','rd1_quantity','rd2_quantity','rd3_quantity','rd4_quantity','rd5_quantity','sc_rarity','sc_description','sc_img_path','sc_sale_price')->where('sc_id','=',$ScrollId)->first();

			$scroll['item_id']=$ScrollInfo['sc_id'];
			$scroll['item_name']=$ScrollInfo['sc_name'];
			$scroll['item_rarity']=$ScrollInfo['sc_rarity'];
			$scroll['item_img']=$ScrollInfo['sc_img_path'];
			$scroll['item_description']=$ScrollInfo['sc_description'];
			$scroll['item_price']=$ScrollInfo['sc_sale_price'];

			$result['item_data']=$scroll;





			//get equipment information and skill information from database
			$scroll_equ=[];
			$equ_id=$ScrollInfo['equ_id'];
			$EquipmentInfo = $EquipmentMstModel->where('equ_id','=',$equ_id)->first();
			$EquipmentDataInfo = $EquipmentMstModel->select('equ_name','equ_part','equ_type','icon_path')->where('equ_id','=',$equ_id)->first();
			//$result['Equipment_data']['Equipment_info']=$EquipmentDataInfo;

			$EquEff_id = $EquipmentInfo['eff_id'];
			$EquEffectInfo = $EffectionMstModel->where('eff_id','=',$EquEff_id)->first();
			//$result['Equipment_data']['EquEffect_info']=$EquEffectInfo;

			$scroll_equ['equ_name']=$EquEffectInfo['equ_name'];
			$scroll_equ['equ_icon']=$EquEffectInfo['icon_path'];
			$scroll_equ['equ_eff']=$EquEffectInfo;

			$result['item_data']['item_info']['scroll_equ']=$scroll_equ;
			




			$scroll_skill=[];
			$Skill_id = $EquipmentInfo['skill_id'];
			$SkillInfo = $SkillMstModel->where('skill_id','=',$Skill_id)->first();
			$SkillDataInfo = $SkillMstModel->select('skill_name','skill_icon','skill_info')->where('skill_id','=',$Skill_id)->first();
			//$result['Skill_data']['Skill_info']=$SkillDataInfo;

			$SkillEff_id = $SkillInfo['eff_id'];
			$SkillEffectInfo = $EffectionMstModel->where('eff_id','=',$SkillEff_id)->first();
			//$result['Skill_data']['SkillEffect_info']=$SkillEffectInfo;

			$scroll_skill['skill_name']=$SkillInfo['skill_name'];
			$scroll_skill['skill_icon']=$SkillInfo['skill_icon'];
			$scroll_skill['skill_eff']=$SkillEffectInfo;

			$result['item_data']['item_info']['scroll_skill']=$scroll_skill;





			//get rare resource icon and the number of resource that user already had
			$scroll_resource1=[];
			$rare_res_id=$ScrollInfo['r_id_1'];
			$RareResInfo = $ResourceMstModel->where('r_id','=',$rare_res_id)->first();
			//$result['Rare_Resource_data']['Rare_Resource_icon']=$RareResInfo;
			$RareResQuantity = $UserBaggageResModel->where('u_id','=',$u_id)->where('br_id','=',$rare_res_id)->first();
			//$result['Rare_Resource_data']['Rare_Resource_quantity']=$RareResQuantity;

			$scroll_resource1['item_had']=$RareResQuantity['br_quantity'];
			$scroll_resource1['item_need']=$ScrollInfo['rd1_quantity'];
			$scroll_resource1['item_icon']=$RareResInfo['r_img_path'];

			$scroll_detail[]=$scroll_resource1;



			//get the number of coin that user already had
			$scroll_resource2=[];
			$CoinResQuantity = $UserModel->where('u_id','=',$u_id)->first();

			$scroll_resource2['item_had']=$CoinResQuantity['u_coin'];
			$scroll_resource2['item_need']=$ScrollInfo['rd2_quantity'];
			$scroll_resource2['item_icon']=null;

			$scroll_detail[]=$scroll_resource2;





			//get normal resource 1 icon and the number or resource that user already had
			$scroll_resource3=[];
			$normal_res_id_1=$ScrollInfo['r_id_3'];
			$NormalRes1Info = $ResourceMstModel->where('r_id','=',$normal_res_id_1)->first();
			//$result['Normal_Resource1_data']['Normal_Resource1_icon']=$NormalRes1Info;
			$NormalRes1Quantity = $UserBaggageResModel->where('u_id','=',$u_id)->where('br_id','=',$normal_res_id_1)->first();
			//$result['Normal_Resource1_data']['Normal_Resource1_quantity']=$NormalRes1Quantity;

			$scroll_resource3['item_had']=$NormalRes1Quantity['br_quantity'];
			$scroll_resource3['item_need']=$ScrollInfo['rd3_quantity'];
			$scroll_resource3['item_icon']=$NormalRes1Info['r_img_path'];

			$scroll_detail[]=$scroll_resource3;




			//if have normal resource 2, get normal resource 2 icon and the number or resource that user already had
			$normal_res_id_2=$ScrollInfo['r_id_4'];
			$scroll_resource4=[];
			if(isset($normal_res_id_2))
			{
				$NormalRes2Info = $ResourceMstModel->where('r_id','=',$normal_res_id_2)->first();
				//$result['Normal_Resource2_data']['Normal_Resource2_icon']=$NormalRes2Info;
				$NormalRes2Quantity = $UserBaggageResModel->where('u_id','=',$u_id)->where('br_id','=',$normal_res_id_2)->first();
				//$result['Normal_Resource2_data']['Normal_Resource2_quantity']=$NormalRes2Quantity;

				$scroll_resource4['item_had']=$NormalRes2Quantity['br_quantity'];
				$scroll_resource4['item_need']=$ScrollInfo['rd4_quantity'];
				$scroll_resource4['item_icon']=$NormalRes2Info['r_img_path'];

				$scroll_detail[]=$scroll_resource4;
			}






			//if have normal resource 3, get normal resource 3 icon and the number or resource that user already had
			$normal_res_id_3=$ScrollInfo['r_id_5'];
			$scroll_resource5=[];
			if(isset($normal_res_id_3))
			{
				$NormalRes3Info = $ResourceMstModel->where('r_id','=',$normal_res_id_3)->first();
				//$result['Normal_Resource3_data']['Normal_Resource3_icon']=$NormalRes3Info;
				$NormalRes3Quantity = $UserBaggageResModel->where('u_id','=',$u_id)->where('br_id','=',$normal_res_id_3)->first();
				//$result['Normal_Resource3_data']['Normal_Resource3_quantity']=$NormalRes3Quantity;
				$scroll_resource5['item_had']=$NormalRes3Quantity['br_quantity'];
				$scroll_resource5['item_need']=$ScrollInfo['rd5_quantity'];
				$scroll_resource5['item_icon']=$NormalRes3Info['r_img_path'];

				$scroll_detail[]=$scroll_resource5;
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
			$equipment=[];
			$result=[];

			$EquipmentInfo = $EquipmentMstModel->where('equ_id','=',$EquipmentId)->first(); 

			$equipment['item_id']=$EquipmentInfo['equ_id'];
			$equipment['item_name']=$EquipmentInfo['equ_name'];
			$equipment['item_rarity']=$EquipmentInfo['equ_rarity'];
			$equipment['item_img']=$EquipmentInfo['icon_path'];
			$equipment['item_description']=$EquipmentInfo['equ_description'];
			$equipment['item_price']=$EquipmentInfo['equ_price'];

			$result['item_data']=$equipment;

			$EquipmentEff_id = $EquipmentInfo['eff_id'];
			$EquipmentEffInfo = $EffectionMstModel->where('eff_id','=',$EquipmentEff_id)->first();
			$result['item_data']['item_info']['equipment_eff']=$EquipmentEffInfo;
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