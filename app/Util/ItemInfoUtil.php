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
use App\EqAttrmstModel;
use App\UserBaggageEqModel;
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
			// $scroll['item_description']=$ScrollInfo['sc_description'];
			$scroll['item_price']=$ScrollInfo['sc_sale_price'];
			$scroll['coin_need']=$ScrollInfo['sc_coin'];

			$resource=[];
			// $resource1=[];
			$resouceUse=[];

			$resouce[]=$this->getResourceName($r_id_1,$rd1_quantity,$u_id);
			$resouce[]=$this->getResourceName($r_id_2,$rd2_quantity,$u_id);
			$resouce[]=$this->getResourceName($r_id_3,$rd3_quantity,$u_id);
			$resouce[]=$this->getResourceName($r_id_4,$rd4_quantity,$u_id);
			$resouce[]=$this->getResourceName($r_id_5,$rd5_quantity,$u_id);

			foreach ($variable as $resouce) {
				if($variable!=null){
				$resouceUse[]=$variable;
				}
			}
			$scroll['resouce']=$resouceUse;
			$user=$UserModel->select('u_coin')->where('u_id',$u_id)->first();
			$scroll['coin_have']=$user['u_coin'];

			$response=json_encode($scroll,TRUE);

		}else{
			throw new Exception("No Scroll ID");
			$response=[
			'status' => 'Wrong',
			'error' => "please check Scroll id",
			];
		}
		return $response;
	}

	private function getResourceName($r_id,$r_quantity,$u_id){
		$useBaggageRe=new UserBaggageResModel();
			if($r_quantity){
				$resource=$ResourceMstModel->select('r_name','r_img_path')->where('r_id',$r_id)->first();
				$resource['rd1_quantity']=$r_quantity;
				$resource['r_id_1']=$r_id;
				$userRe=$useBaggageRe->select('br_quantity')->where('u_id',$u_id)->where('br_id',$r_id)->first();
				if($userRe){
					$resource['u_have']=$userRe['br_quantity'];
				}
				else{
					$resource['u_have']=0;
				}
				return $resource;
			}
			return null;

	}


	// get the information of a Equipment: name, type, icon, effection
	function getEquipmentInfo ($Item_Id,$u_id)
	{
			$EquipmentMstModel=new EquipmentMstModel();
			$skillMstModel=new SkillMstModel();
			$eqAttrmstModel=new EqAttrmstModel();
			$userBaggage=new UserBaggageEqModel();
			$equipment=[];
			$result=[];
			$baggeData=$userBaggage->where('u_id',$u_id)->where('b_equ_id',$Item_Id)->first();

			$EquipmentInfo = $EquipmentMstModel->where('equ_id','=',$Item_Id)->first(); 
			$equipment['user_beq_id']=$baggeData['user_beq_id'];
			$equipment['item_id']=$EquipmentInfo['equ_id'];
			$equipment['item_name']=$EquipmentInfo['equ_name'];
			$equipment['item_rarity']=$EquipmentInfo['equ_rarity'];
			//$equipment['item_img']=$EquipmentInfo['icon_path'];
			$equipment['item_description']=$EquipmentInfo['equ_description'];
			$equipment['item_description']=$EquipmentInfo['equ_description'];
			$equipment['item_price']=$EquipmentInfo['equ_price'];


			$eqAtr=$eqAttrmstModel->where('equ_att_id',$EquipmentInfo['equ_attribute_id'])->first();

			$equipment['eff_ch_stam']=$eqAtr['eff_ch_stam'];
			$equipment['eff_ch_atk']=$eqAtr['eff_ch_atk'];
			$equipment['eff_ch_armor']=$eqAtr['eff_ch_armor'];
			if($equipment!=0){
				$equipment['eff_ch_crit_per']=$eqAtr['eff_ch_crit_per'];
			}

			$skillInfo = $skillMstModel->select('skill_id','skill_info','skill_name','skill_icon')->where('skill_id',$EquipmentInfo['special_skill_id'])->first();
			$equipment['skill_id']=$skillInfo['skill_id'];
			$equipment['skill_name']=$skillInfo['skill_name'];
			$equipment['skill_info']=$skillInfo['skill_info'];
			$equipment['skill_icon']=$skillInfo['skill_icon'];

		return $equipment;
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
			$EquEffclickInfo=$EffectionMstModel->where('eff_id',$EquEffclickId)->pluck('eff_description');
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
				$EquEffnowInfo=$EffectionMstModel->where('eff_id',$EquEffnowId)->pluck('eff_description');
				$result['item_data_2']['item_info']['effection']=$EquEffnowInfo;
			}
		}
		return $result;
	}

	//get the information of a skill: name, icon, info, effection
	function getSkillInfo ($skill_id)
	{
		$Skill_Id=$skill_id;

			$SkillMstModel=new SkillMstModel();
			$EffectionMstModel=new EffectionMstModel();
			$skill=[];
			$result=[];

			$SkillInfo=$SkillMstModel->where('skill_id',$Skill_Id)->first();

			$SkillEff_id=$SkillInfo['eff_id'];
			$SkillEffInfo=$EffectionMstModel->where('eff_id',$SkillEff_id)->pluck('eff_description');


			$skill['skill_id']=$SkillInfo['skill_id'];
			$skill['skill_name']=$SkillInfo['skill_name'];
			$skill['skill_icon']=$SkillInfo['skill_icon'];
			$skill['skill_info']=$SkillInfo['skill_info'];
			$skill['skill_eff']=$SkillEffInfo;

			$result['Skill_data']=$skill;

			$response=$result;
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
			$OriginEquEffection=$EffectionMstModel->where('eff_id',$equOrEff_id)->pluck('eff_description');

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
			$UpgradeEquEffection=$EffectionMstModel->where('eff_id',$equUpEff_id)->pluck('eff_description');

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
			$UpgradeSkillEff=$EffectionMstModel->where('eff_id',$skillUpeff_id)->pluck('eff_description');

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