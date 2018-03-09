<?php
namespace App\Util;
use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\UserBaggageResModel;
use App\UserBaggageEqModel;
use App\UserBaggageScrollModel;
use App\EquUpgradeMstModel;
use App\ScrollMstModel;
use App\ResourceMstModel;
use App\ItemMstModel;
use App\EquipmentMstModel;
use App\EquUpgradeReMstModel;
use Exception;
use App\EqAttrmstModel;
use App\SkillMstModel;
use App\Exceptions\Handler;
use Illuminate\Http\Response;
use Carbon\Carbon;
use DateTime;
use App\UserModel;
use Illuminate\Support\Facades\Redis;
use App\Http\Controllers\MissionController;

class BaggageUtil
{
	//show the quantity and icon for every item in the baggage
	function getResource($u_id)
	{
		// if($baggage_u_id!=null)
		// {
			$UserBaggageResModel=new UserBaggageResModel();
			$ItemMstModel=new ItemMstModel();
			$result=[];

			$BaggageResource=$UserBaggageResModel->select('user_br_id','br_id','br_icon','br_quantity')->where('u_id',$u_id)->where('status','=',0)->orderBy('br_rarity','DESC')->get();

			foreach ($BaggageResource as $obj) 
			{	$arry['baggage_id']=$obj['user_br_id'];
				$arry['item_id']=$obj['br_id'];
				$arry['item_type']=1;
				$arry['equ_type']=0;
				// $arry['item_icon']=$obj['br_icon'];
				$arry['item_quantity']=$obj['br_quantity'];
				// $arry['item_type']=1;
				$result[]=$arry;
			}
		 return $result;
	}
		function getResourceInfo ($Item_Id)
	{
			$ResourceMstModel=new ResourceMstModel();
			$resource=[];
			$result=[];
			$ResourceInfo = $ResourceMstModel->select('r_id','r_name','r_rarity','r_type','r_img_path','r_description')->where('r_id','=',$Item_Id)->where('br_quantity','>',0)->first();

			$resource['item_id']=$ResourceInfo['r_id'];
			$resource['item_name']=$ResourceInfo['r_name'];
			$resource['item_rarity']=$ResourceInfo['r_rarity'];
			$resource['item_img']=$ResourceInfo['r_img_path'];
			$resource['item_price']=0;
			$result['item_data']=$resource;
			$response=json_encode($result,TRUE);
		return $response;
	}

	function getEquipment($u_id,$equ_type,$status){
			$EquipmentMstModel=new EquipmentMstModel();
			$UserBaggageEqModel=new UserBaggageEqModel();
				$result=[];
			$baggageWeapon=$UserBaggageEqModel->select('user_beq_id','b_equ_id','b_icon_path')->where('u_id','=',$u_id)->where('status','=',$status)->where('b_equ_type','=',$equ_type)->orderBy('b_equ_rarity','DESC')->orderBy('b_equ_id','DESC')->get();
			foreach ($baggageWeapon as $obj) 
			{	$arry['baggage_id']=$obj['user_beq_id'];
				$arry['item_id']=$obj['b_equ_id'];
				$arry['item_type']=2;
				$arry['equ_type']=$equ_type;
				$arry['item_quantity']=1;
				$result[]=$arry;
			}
		return $result;
	}
	function getEquipmentInfo ($Item_Id,$u_id,$user_beq_id,$equ_type)
	{
			$EquipmentMstModel=new EquipmentMstModel();
			$skillMstModel=new SkillMstModel();
			$eqAttrmstModel=new EqAttrmstModel();
			$userBaggage=new UserBaggageEqModel();
			$eqUpgrade=new EquUpgradeReMstModel();
			$equipment=[];
			$result=[];
			$baggeData=$userBaggage->where('u_id',$u_id)->where('user_beq_id',$user_beq_id)->where('status','!=',2)->first();

			$EquipmentInfo = $EquipmentMstModel->where('equ_id','=',$Item_Id)->first(); 
			$equipment['baggage_id']=$user_beq_id;
			$equipment['item_id']=$EquipmentInfo['equ_id'];
			$equipment['item_name']=$EquipmentInfo['equ_name'];
			$equipment['item_rarity']=$EquipmentInfo['equ_rarity'];
			$equipment['item_price']=$EquipmentInfo['equ_price'];
			if($equ_type==2){
				$countUp=$eqUpgrade->where('equ_code',substr($EquipmentInfo['equ_code'], 0,4))->where('lv',$EquipmentInfo['equ_lv']+1)->count();
			}
			else{	
			$countUp=$eqUpgrade->where('equ_code',$EquipmentInfo['equ_code'])->where('lv',$EquipmentInfo['equ_lv']+1)->count();
			}

			if($countUp>0){
				$equipment['upgrade']=1;
			}
			else {
				$equipment['upgrade']=0;
			}
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


	function getScroll ($baggage_u_id)
	{
			$UserBaggageScrollModel=new UserBaggageScrollModel();
			$result=[];
			$scrollMstModel=new ScrollMstModel();

			$baggageScroll=$UserBaggageScrollModel->select('user_bsc_id','bsc_id','bsc_icon')->where('u_id','=',$baggage_u_id)->where('status','=',0)->orderBy('bsc_rarity','DESC')->get();

			foreach ($baggageScroll as $obj) 
			{	$arry['baggage_id']=$obj['user_bsc_id'];
				$arry['item_id']=$obj['bsc_id'];
				$arry['item_type']=3;
				$arry['item_quantity']=1;
				$arry['equ_type']=0;
				$scrollMst=$scrollMstModel->select('sc_img_path')->where('sc_id',$obj['bsc_id'])->first();
				$arry['sc_img_path']=$scrollMst['sc_img_path'];
				$result[]=$arry;
			}
			return $result;
	}

	function getScrollInfo ($Item_Id,$u_id,$user_bsc_id)
	{
		$ScrollId=$Item_Id;
		$u_id=$u_id;

			$UserModel=new UserModel();
			$ScrollMstModel=new ScrollMstModel();
			$EquipmentMstModel=new EquipmentMstModel();
			$SkillMstModel=new SkillMstModel();
			$ResourceMstModel=new ResourceMstModel();
			$EquUpgradeReMstModel=new EquUpgradeReMstModel();

			$UserBaggageResModel=new UserBaggageResModel();
			$userData=$UserModel->where('u_id',$u_id)->first();
			$scrollData=$ScrollMstModel->where('sc_id',$Item_Id)->first();
			$result['coin_have']=$userData['u_coin'];
			$result['coin_need']=$scrollData['sc_coin'];
			$result['item_id']=$scrollData['sc_id'];
			$result['item_type']=3;
			$result['equ_type']=0;
			$result['sc_name']=$scrollData['sc_name'];
			$result['sc_coin']=$scrollData['sc_coin'];
			$result['sc_img_path']=$scrollData['sc_img_path'];
			$upgradeRES=$EquUpgradeReMstModel->select('equ_code','upgrade_id','lv','r_id','r_quantity')->where('upgrade_id',$scrollData['upgrade_id'])->get();
			$resource=[];
			foreach ($upgradeRES as $key => $each) {
				$tmp['r_id']=$each['r_id'];
				$rQu=$UserBaggageResModel->where('u_id',$u_id)->where('br_id',$each->r_id)->first();
				$tmp['r_qu_need']=$each->r_quantity;
				if($rQu['br_quantity']){
				$tmp['r_qu_have']=$rQu['br_quantity'];
				}
				else{
				$tmp['r_qu_have']=0;
				}
				$resource[]=$tmp; 
			}
			$result['resource']=$resource;

		return $result;
	}

	function compareUpgradeEQ($u_id,$equ_id,$equ_type,$coin,$upgarde,$status){
		$EquipmentMstModel=new EquipmentMstModel();
		$EqAttrmstModel=new EqAttrmstModel();
		$UserBaggageEqModel=new UserBaggageEqModel();
		$EquUpgradeReMstModel=new EquUpgradeReMstModel();
		$UserBaggageResModel =new UserBaggageResModel();
		$UserModel=new UserModel();
		$now=new DateTime;
		$datetime=$now->format( 'Y-m-d h:m:s' );
		$dmy=$now->format( 'Ymd' );
		$resource=[];
		$userValue=$UserModel->select('u_coin')->where('u_id',$u_id)->first();


		$equData=$EquipmentMstModel->where('equ_id',$equ_id)->first();
		$equAtr=$EqAttrmstModel->where('equ_att_id',$equData['equ_attribute_id'])->first();
		if($userValue['u_coin']-$coin<0&&$upgarde==1){
			throw new Exception("no enough coin");
		}
		$eqUpData=$EquUpgradeReMstModel->where('upgrade_id',$equData['upgrade_id'])->get();
		if($equ_type==2){
			$eqNextData=$EquUpgradeReMstModel->where('equ_code','like', substr($equData['equ_code'],0,4))->where('lv',$equData['equ_lv']+1)->first();
		}else{
			$eqNextData=$EquUpgradeReMstModel->where('equ_code',$equData['equ_code'])->where('lv',$equData['equ_lv']+1)->first();
		}
		$comEqData=$EquipmentMstModel->where('upgrade_id',$eqNextData['upgrade_id'])->where('equ_code',$equData['equ_code'])->first();

				foreach ($eqUpData as $key => $each) {
					$tmp['r_id']=$each->r_id;
					$rQu=$UserBaggageResModel->where('u_id',$u_id)->where('br_id',$each->r_id)->first();
					$tmp['r_qu_need']=$each->r_quantity;
					if($rQu['br_quantity']){
						$tmp['r_qu_have']=$rQu['br_quantity'];
						if($upgarde==1&&$rQu['br_quantity']<$each->r_quantity&&$coinLeft){
						throw new Exception("no enough resouce id".$each->r_id);
							}
						else{
						$UserBaggageResModel->where('u_id',$u_id)->where('br_id',$each->r_id)->update(['br_quantity'=>$rQu['br_quantity']-$each->r_quantity,'updated_at'=>$datetime]);
						}
					}
					else{
					$tmp['r_qu_have']=0;
					}
					$resource[]=$tmp;
				}
					
				if($upgarde==1){
					if($status==1){
						if($equ_type==1){
							$charmodel->where('u_id',$u_id)->update(['w_id'=>$upgradeEquId,'w_bag_id'=>$w_bag_id,'updated_at'=>$datetime]);
						}
						else if($equ_type==2){
							$charmodel->where('u_id',$u_id)->update(['core_id'=>$upgradeEquId,'core_bag_id'=>$w_bag_id,'updated_at'=>$datetime]);
						}

					}
					$w_bag_id=$UserBaggageEqModel->insertGetId(['u_id'=>$u_id,'b_equ_id'=>$comEqData['equ_id'],'b_equ_rarity'=>$comEqData['equ_rarity'],'b_equ_type'=>$comEqData['equ_type'],'b_icon_path'=>$comEqData['icon_path'],'status'=>$status,'updated_at'=>$datetime,'created_at'=>$datetime]);
					$UserModel->where('u_id',$u_id)->update(['u_coin'=>$userValue['u_coin']-$coin,'updated_at'=>$datetime]);
				return ['equ_id'=>$comEqData['equ_id'],'baggage_id'=>$w_bag_id];
				}

			if($upgarde==0){
			$comEquAtr=$EqAttrmstModel->where('equ_att_id',$comEqData['equ_attribute_id'])->first();
			$result['equ_name']=$equData['equ_name'];
			$result['coin']=$equData['upgrade_coin'];
			$result['equ_atr']['equ_id']=$equ_id;
			$result['equ_atr']['eff_ch_stam']=$equAtr['eff_ch_stam'];
			$result['equ_atr']['eff_ch_atk']=$equAtr['eff_ch_atk'];
			$result['equ_atr']['eff_ch_armor']=$equAtr['eff_ch_armor'];
			$result['equ_atr']['eff_ch_crit_per']=$equAtr['eff_ch_crit_per'];
			$result['up_equ']['equ_id']=$comEqData['equ_id'];
			$result['up_equ']['eff_ch_stam']=$comEquAtr['eff_ch_stam'];
			$result['up_equ']['eff_ch_atk']=$comEquAtr['eff_ch_atk'];
			$result['up_equ']['eff_ch_armor']=$comEquAtr['eff_ch_armor'];
			$result['up_equ']['eff_ch_crit_per']=$comEquAtr['eff_ch_crit_per'];
			$result['resource']=$resource;
			return $result;
		}

	}

	// public function validateResource($u_id,$data,$coin){
	// 	$UserModel=new UserModel();
	// 	$UserBaggageResModel=new UserBaggageResModel();
	// 	$now=new DateTime;
	// 	$datetime=$now->format( 'Y-m-d h:m:s' );
	// 	$dmy=$now->format( 'Ymd' );
	// 	$userValue=$UserModel->select('u_coin')->where('u_id',$u_id)->first();
	// 		if($userValue['u_coin']<$coin){
	// 			throw new Exception("no enough coin");
	// 			$response=[
	// 					'status' => 'Wrong',
	// 					'error' => "no enough resources",
	// 			];
	// 			}
	// 		else{
	// 			$coin=$userValue['u_coin']-$coin;
	// 			}
	// 			foreach ($data as $key => $resources) {
	// 				$rQu=$UserBaggageResModel->where('u_id',$u_id)->where('br_id',$resources->r_id)->first();
	// 				if($rQu['br_quantity']<$resources->r_quantity){
	// 				throw new Exception("no enough resouce id".$resources->r_id);
	// 				}
	// 				else{
	// 					$UserBaggageResModel->where('u_id',$u_id)->where('br_id',$resources->r_id)->update(['br_quantity'=>$rQu['br_quantity']-$resources->r_quantity,'updated_at'=>$datetime]);
	// 				}
	// 			# code...
	// 		}
	// 			$UserModel->where('u_id',$u_id)->update(['u_coin'=>$coin,'updated_at'=>$datetime]);
	// 	}

	function insertToBaggage($u_id,$rewards){

		$UserBaggageEqModel=new UserBaggageEqModel();
		$UserBaggageResModel=new UserBaggageResModel();
		$UserBaggageScrollModel=new UserBaggageScrollModel();
		$UserModel=new UserModel();
		$eqModel=new EquipmentMstModel();
		$scrModel=new ScrollMstModel();
		$reModel=new ResourceMstModel();
		$now   = new DateTime;
		$datetime=$now->format('Y-m-d h:m:s');
		$resource=[];
		// try{
		$userData=$UserModel->select('u_coin','u_gem')->where('u_id',$u_id)->first();
		foreach($rewards as $reward){
			if($reward['item_type']==1){

				$reData=$reModel->where('r_id',$reward['item_org_id'])->first();
				$quantity=$UserBaggageResModel->select('br_quantity')->where('br_id',$reward['item_org_id'])->where('u_id',$u_id)->first();


				if(isset($quantity['br_quantity'])&&$quantity['br_quantity']>0){
					$result['br_quantity']=$reward['item_quantity']+$quantity['br_quantity'];
					$UserBaggageResModel->where('br_id',$reward['item_org_id'])->where('u_id',$u_id)->update(['br_quantity'=>$result['br_quantity'],'updated_at'=>$datetime]);
				}
				else{
					$result['u_id']=$u_id;
					$result['br_id']=$reData['r_id'];
					$result['br_icon']=$reData['r_img_path'];
					$result['br_rarity']=$reData['r_rarity'];
					$result['br_type']=$reData['r_type'];
					$result['br_quantity']=$reward['item_quantity'];
					$result['status']=0;
					$result['updated_at']=$datetime;
					$result['created_at']=$datetime;
					$UserBaggageResModel->insert($result);
				}
				if(array_key_exists($reward['item_org_id'],$resource)){
						$resource[$reward['item_org_id']]=$resource[$reward['item_org_id']]+$reward['item_quantity'];

					}else{
						$resource[$reward['item_org_id']]=$reward['item_quantity'];
				}
				
			}
			else if($reward['item_type']==2){
				for ($i=0;$i<$reward['item_quantity'];$i++) {
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
				for ($i=0;$i<$reward['item_quantity'];$i++) {
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
			else if($reward['item_type']==6){
				 $UserModel->where('u_id',$u_id)->update(['u_coin'=>$userData['u_coin']+$reward['item_quantity'],'updated_at'=>$datetime]);
			}
			else if($reward['item_type']==7){
				$UserModel->where('u_id',$u_id)->update(['u_gem'=>$userData['u_gem']+$reward['item_quantity'],'updated_at'=>$datetime]);
			}
			// $missionlist[]=$reward['misson_id'];
		 }
		 return TRUE;
		 // return $missionlist;

		// }catch(Exception $e){
		// 	throw new Exception("there have some errors of insert to baggage");
		// }

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
			$eqData=$eqModel->where('equ_id',$mission['item_org_id'])->first();
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

	public function updateBaggageResource($u_id,$item_id,$item_type,$quantity){
		$UserBaggageEqModel=new UserBaggageEqModel();
		$UserBaggageResModel=new UserBaggageResModel();
		$UserBaggageScrollModel=new UserBaggageScrollModel();
		$rescourceModel=new ResourceMstModel();
		$ScrollMstModel= new ScrollMstModel();
				$now   = new DateTime;
		$date=$now->format( 'Y-m-d h:m:s' );
		if($item_type==1){
			$itemData=$UserBaggageResModel->where('u_id',$u_id)->where('br_id',$item_id)->first();
		   	$rescourceData=$rescourceModel->where('r_id',$item_id)->first();
			if($itemData){
				$br_quanitty=$itemData['br_quantity']+$quantity;
		   				$UserBaggageResModel->where('u_id',$u_id)->where('br_id',$item_id)->update(['br_quantity'=>$br_quanitty,'updated_at'=>$date]);
			}
			else{
					   	$baReNew['u_id']=$u_id;
		   				$baReNew['br_id']=$item_id;
		   				$baReNew['br_rarity']=$rescourceData['r_rarity'];
		   				$baReNew['br_type']=$item_type;
		   				$baReNew['br_quantity']=$quantity;
		   				$baReNew['status']=0;
		   				$baReNew['updated_at']=$date;
		   				$baReNew['created_at']=$date;
		   				$UserBaggageResModel->insert($baReNew);
			}

		}
		else if($item_type==3){
			$scrollData=$ScrollMstModel->where('sc_id',$item_id)->first();
						$baScNew['u_id']=$u_id;
		   				$baScNew['bsc_id']=$item_id;
		   				$baScNew['bsc_rarity']=$scrollData['sc_rarity'];
		   				$baScNew['status']=0;
		   				$baScNew['updated_at']=$date;
		   				$baScNew['created_at']=$date;
		   				$UserBaggageScrollModel->insert($baScNew);
		}
	}

	 	public function RecordSpend($u_id,$coin,$gem){
 				$mission=new MissionController();
 				$now=new DateTime;
				$datetime=$now->format( 'Y-m-d h:m:s' );
				$dmy=$now->format( 'Ymd' );
 				$spentKey='daily_spend_'.$dmy;
 				$redisShop=Redis::connection('default');
				$dailySpend=$redisShop->HGET($spentKey,$u_id);
				$dailySpendData=json_decode($dailySpend,TRUE);
				$spendData['coin']=$dailySpendData['coin']+$coin;
				$spendData['gem']=$dailySpendData['gem']+$gem;
				$spendJson=json_encode($spendData,TRUE);
				if($spendData['coin']>0){
				$mission->archiveMission(5,$u_id,$spendData['coin']);
				}
				if($spendData['gem']>0){
				$mission->archiveMission(6,$u_id,$spendData['gem']);
				}
				$redisShop->HSET($spentKey,$u_id,$spendJson);
 		}
}