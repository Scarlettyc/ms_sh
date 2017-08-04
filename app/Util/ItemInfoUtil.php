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
use App\EquUpgradeMstModel;
use App\CharacterModel;
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
			$EquEff_id = $EquipmentInfo['eff_id'];
			$EquEffectInfo = $EffectionMstModel->where('eff_id','=',$EquEff_id)->first();
			
			$scroll_equ['equ_name']=$EquEffectInfo['equ_name'];
			$scroll_equ['equ_icon']=$EquEffectInfo['icon_path'];
			$scroll_equ['equ_eff']=$EquEffectInfo;

			$result['item_data']['item_info']['scroll_equ']=$scroll_equ;
			
			$scroll_skill=[];
			$Skill_id = $EquipmentInfo['skill_id'];
			$SkillInfo = $SkillMstModel->where('skill_id','=',$Skill_id)->first();
			$SkillEff_id = $SkillInfo['eff_id'];
			$SkillEffectInfo = $EffectionMstModel->where('eff_id','=',$SkillEff_id)->first();

			$scroll_skill['skill_name']=$SkillInfo['skill_name'];
			$scroll_skill['skill_icon']=$SkillInfo['skill_icon'];
			$scroll_skill['skill_eff']=$SkillEffectInfo;

			$result['item_data']['item_info']['scroll_skill']=$scroll_skill;

			$resource=[];
			$resource1=[];
			$resource1['r_id']=$ScrollInfo['r_id_1'];
			$resource1['r_quantity']=$ScrollInfo['rd1_quantity'];
			$resource[]=$resource1;

			$resource2=[];
			$resource2['r_id']=$ScrollInfo['r_id_2'];
			$resource2['r_quantity']=$ScrollInfo['rd2_quantity'];
			$resource[]=$resource2;

			$resource3=[];
			if(isset($ScrollInfo['r_id_3'])){
				$resource3['r_id']=$ScrollInfo['r_id_3'];
				$resource3['r_quantity']=$ScrollInfo['rd3_quantity'];
				$resource[]=$resource3;
			}
			
			$resource4=[];
			if(isset($ScrollInfo['r_id_4']))
			{
				$resource4['r_id']=$ScrollInfo['r_id_4'];
				$resource4['r_quantity']=$ScrollInfo['rd4_quantity'];
				$resource[]=$resource4;
			}

			$scroll_check=1;

			//get every resource and check if enough
			foreach ($resource as $obj) 
			{
				$resinfo=$ResourceMstModel->where('r_id',$obj['r_id'])->first();
				$resquantity=$UserBaggageResModel->where('u_id',$u_id)->where('br_id',$obj['r_id'])->first();
				$scroll_resource['item_had']=$resquantity['br_quantity'];
				$scroll_resource['item_need']=$obj['r_quantity'];
				$scroll_resource['item_icon']=$resinfo['r_img_path'];
				if($scroll_resource['item_had']>=$scroll_resource['item_need'])
				{
					$scroll_resource['item_check']=1;
				}else{
					$scroll_resource['item_check']=0;
					$scroll_check=0;
				}
				$scroll_detail[]=$scroll_resource;
			}
			$result['item_data']['item_info']['scroll_detail']=$scroll_detail;

			//get the number of coin that user already had and how much coin that user need
			$scroll_resource_coin=[];
			$CoinResQuantity = $UserModel->where('u_id','=',$u_id)->first();

			$scroll_resource_coin['item_had']=$CoinResQuantity['u_coin'];
			$scroll_resource_coin['item_need']=$ScrollInfo['sc_coin'];
			if($scroll_resource_coin['item_had']>=$scroll_resource_coin['item_need'])
			{
				$scroll_resource_coin['item_check']=1;
			}else{
				$scroll_resource_coin['item_check']=0;
				$scroll_check=0;
			}

			$result['item_data']['item_info']['scroll_coin']=$scroll_resource_coin;

			$result['item_data']['item_info']['scroll_check']=$scroll_check;

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

	//get the information of the equipment that user click and compare with the equipment that on the same position.
	function compareEquipment ($u_id,$Item_Id)
	{
		$u_id=$u_id;
		$EquipmentId=$Item_Id;
		if(isset($EquipmentId))
		{
			$EquipmentMstModel=new EquipmentMstModel();
			$EffectionMstModel=new EffectionMstModel();
			$CharacterModel=new CharacterModel();
			$SkillMstModel=new SkillMstModel();
			$Equ_now=[];
			$Equ_click=[];
			$result=[];

			$EquClickInfo = $EquipmentMstModel->where('equ_id',$EquipmentId)->first();

			$Equ_click['item_id']=$EquClickInfo['equ_id'];
			$Equ_click['item_name']=$EquClickInfo['equ_name'];
			$Equ_click['item_rarity']=$EquClickInfo['equ_rarity'];
			$Equ_click['item_img']=$EquClickInfo['icon_path'];
			$Equ_click['item_description']=$EquClickInfo['equ_description'];
			$Equ_click['item_price']=$EquClickInfo['equ_price'];

			$result['item_data_1']=$Equ_click;

			$EquEffclickId=$EquClickInfo['eff_id'];
			$EquEffclickInfo=$EffectionMstModel->where('eff_id',$EquEffclickId)->first();
			$result['item_data_1']['item_info']['effection']=$EquEffclickInfo;

			

			$EquClickType=$EquClickInfo['equ_part'];

			if($EquClickType == 1)
			{
				$EquNowId=$CharacterModel->where('u_id',$u_id)->pluck('w_id');
				$EquNowInfo=$EquipmentMstModel->where('equ_id',$EquNowId)->first();

				$Equ_now['item_id']=$EquNowInfo['equ_id'];
				$Equ_now['item_name']=$EquNowInfo['equ_name'];
				$Equ_now['item_rarity']=$EquNowInfo['equ_rarity'];
				$Equ_now['item_img']=$EquNowInfo['icon_path'];
				$Equ_now['item_description']=$EquNowInfo['equ_description'];
				$Equ_now['item_price']=$EquNowInfo['equ_price'];

				$result['item_data_2']=$Equ_now;

				$EquEffnowId=$EquNowInfo['eff_id'];
				$EquEffnowInfo=$EffectionMstModel->where('eff_id',$EquEffnowId)->first();
				$result['item_data_2']['item_info']['effection']=$EquEffnowInfo;
			}

		}
	}

	//get the information of a skill: name, icon, info, effection
	function getSkillInfo ($skill_id)
	{
		$Skill_Id=$skill_id;
		if(isset($Skill_Id))
		{
			$SkillMstModel=new SkillMstModel();
			$EffectionMstModel=new EffectionMstModel();
			$skill=[];
			$result=[];

			$SkillInfo=$SkillMstModel->where('skill_id',$Skill_Id)->first();

			$SkillEff_id=$SkillInfo['eff_id'];
			$SkillEffInfo=$EffectionMstModel->where('eff_id',$SkillEff_id)->first();


			$skill['skill_id']=$SkillInfo['skill_id'];
			$skill['skill_name']=$SkillInfo['skill_name'];
			$skill['skill_icon']=$SkillInfo['skill_icon'];
			$skill['skill_info']=$SkillInfo['skill_info'];
			$skill['skill_eff']=$SkillEffInfo;

			$result['Skill_data']=$skill;

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

	//get Equipment upgrade detail information: name and every resource that this equipment needed. also display the detail information of the equipment and skills.
	function getEquipmentUpgradeInfo ($Item_Id,$u_id)
	{
		$equ_id=$Item_Id;
		$u_id=$u_id;

		if(isset($equ_id))
		{
			$UserModel=new UserModel();
			$EquUpgradeMstModel=new EquUpgradeMstModel();
			$EquipmentMstModel=new EquipmentMstModel();
			$SkillMstModel=new SkillMstModel();
			$ResourceMstModel=new ResourceMstModel();
			$EffectionMstModel=new EffectionMstModel();
			$UserBaggageResModel=new UserBaggageResModel();	
			$result=[];
			$equipmentLV=[];

			//get original equipment information
			$equipment=[];
			$OriginEquInfo=$EquipmentMstModel->where('equ_id',$equ_id)->first();

			$equipment['item_id']=$OriginEquInfo['equ_id'];
			$equipment['item_name']=$OriginEquInfo['equ_name'];
			$equipment['item_rarity']=$OriginEquInfo['equ_rarity'];
			$equipment['item_img']=$OriginEquInfo['icon_path'];
			$equipment['item_description']=$OriginEquInfo['equ_description'];
			$equipment['item_price']=$OriginEquInfo['equ_price'];

			$result['item_data']=$equipment;

			$equOrEff_id=$OriginEquInfo['eff_id'];
			$OriginEquEffection=$EffectionMstModel->where('eff_id',$equOrEff_id)->first();

			$result['item_data']['item_info']['Origin_eff']=$OriginEquEffection;

			$OriginEquLv=$OriginEquInfo['equ_lv'];
			$equipmentLV[]=$OriginEquLv;

			//get upgraded equipment information
			$upgrade_id=$OriginEquInfo['upgrade_id'];
			$EquUpgradeInfo=$EquUpgradeMstModel->where('upgrade_id',$upgrade_id)->first();

			$equUpgrade=[];
			$equUp_id=$EquUpgradeInfo['equ_upgrade_id'];
			$UpgradeEquInfo=$EquipmentMstModel->where('equ_id',$equUp_id)->first();
			
			$equUpEff_id=$UpgradeEquInfo['eff_id'];
			$UpgradeEquEffection=$EffectionMstModel->where('eff_id',$equUpEff_id)->first();

			$equUpgrade['item_id']=$UpgradeEquInfo['equ_id'];
			$equUpgrade['item_name']=$UpgradeEquInfo['equ_name'];
			$equUpgrade['item_rarity']=$UpgradeEquInfo['equ_rarity'];
			$equUpgrade['item_img']=$UpgradeEquInfo['icon_path'];
			$equUpgrade['item_description']=$UpgradeEquInfo['equ_description'];
			$equUpgrade['item_effection']=$UpgradeEquEffection;

			$result['item_data']['item_info']['upgrade_equ']=$equUpgrade;

			$UpgradeEquLv=$UpgradeEquInfo['equ_lv'];
			$equipmentLV[]=$UpgradeEquLv;

			$result['item_data']['item_info']['equ_lv']=$equipmentLV;

			//get skill information
			$skillUpgrade=[];
			$skillUp_id=$UpgradeEquInfo['skill_id'];
			$UpgradeSkillInfo=$SkillMstModel->where('skill_id',$skillUp_id)->first();
			$skillUpeff_id=$UpgradeSkillInfo['eff_id'];
			$UpgradeSkillEff=$EffectionMstModel->where('eff_id',$skillUpeff_id)->first();

			$skillUpgrade['skill_id']=$UpgradeSkillInfo['skill_id'];
			$skillUpgrade['skill_name']=$UpgradeSkillInfo['skill_name'];
			$skillUpgrade['skill_icon']=$UpgradeSkillInfo['skill_icon'];
			$skillUpgrade['skill_info']=$UpgradeSkillInfo['skill_info'];
			$skillUpgrade['skill_eff']=$UpgradeSkillEff;

			$result['item_data']['item_info']['upgrade_skill']=$skillUpgrade;

			$upgrade_detail=[];
			$resource=[];
			$resource1=[];
			$resource1['r_id']=$EquUpgradeInfo['r_id_1'];
			$resource1['r_quantity']=$EquUpgradeInfo['rd1_quantity'];
			$resource[]=$resource1;

			$resource2=[];
			$resource2['r_id']=$EquUpgradeInfo['r_id_2'];
			$resource2['r_quantity']=$EquUpgradeInfo['rd2_quantity'];
			$resource[]=$resource2;

			$resource3=[];
			if(isset($EquUpgradeInfo['r_id_3'])){
				$resource3['r_id']=$EquUpgradeInfo['r_id_3'];
				$resource3['r_quantity']=$EquUpgradeInfo['rd3_quantity'];
				$resource[]=$resource3;
			}
			
			$resource4=[];
			if(isset($EquUpgradeInfo['r_id_4']))
			{
				$resource4['r_id']=$EquUpgradeInfo['r_id_4'];
				$resource4['r_quantity']=$EquUpgradeInfo['rd4_quantity'];
				$resource[]=$resource4;
			}

			$upgrade_check=1;

			//get every resource information and check if enough
			foreach ($resource as $obj) 
			{
				$resinfo=$ResourceMstModel->where('r_id',$obj['r_id'])->first();
				$resquantity=$UserBaggageResModel->where('u_id',$u_id)->where('br_id',$obj['r_id'])->first();
				$upgrade_resource['item_had']=$resquantity['br_quantity'];
				$upgrade_resource['item_need']=$obj['r_quantity'];
				$upgrade_resource['item_icon']=$resinfo['r_img_path'];
				if($upgrade_resource['item_had']>=$upgrade_resource['item_need'])
				{
					$upgrade_resource['item_check']=1;
				}else{
					$upgrade_resource['item_check']=0;
					$upgrade_check=0;
				}
				$upgrade_detail[]=$upgrade_resource;
			}
			$result['item_data']['item_info']['upgrade_detail']=$upgrade_detail;

			//get the number of coin that user already had and how much coin that user need	and check if coin is enough		
			$equ_resource_coin=[];
			$CoinResQuantity = $UserModel->where('u_id','=',$u_id)->first();

			$equ_resource_coin['item_had']=$CoinResQuantity['u_coin'];
			$equ_resource_coin['item_need']=$EquUpgradeInfo['equ_coin'];
			if($equ_resource_coin['item_had']>=$equ_resource_coin['item_need'])
			{
				$equ_resource_coin['item_check']=1;
			}else{
				$equ_resource_coin['item_check']=0;
				$upgrade_check=0;
			}

			$result['item_data']['item_info']['upgrade_coin']=$equ_resource_coin;
			$result['item_data']['item_info']['upgrade_check']=$upgrade_check;

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
}