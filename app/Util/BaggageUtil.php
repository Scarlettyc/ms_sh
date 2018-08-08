<?php
namespace App\Util;
use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\UserBaggageResModel;
use App\UserBaggageEqModel;
use App\UserBaggageScrollModel;
use App\ScrollMstModel;
use App\ScrollResourceModel;
use App\ResourceMstModel;
use App\ItemMstModel;
use App\EquipmentMstModel;
use App\EquUpgradeReMstModel;
use App\EquipLVLimitMstModel;
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
use App\DefindMstModel;
use DB;

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

			$BaggageResource=$UserBaggageResModel->select('user_r_id','r_id','br_icon','quantity')->where('u_id',$u_id)->where('status','=',0)->orderBy('r_rarity','DESC')->get();

			foreach ($BaggageResource as $obj) 
			{	$arry['baggage_id']=$obj['user_r_id'];
				$arry['item_id']=$obj['r_id'];
				$arry['item_type']=1;
				$arry['equ_type']=0;
				// $arry['item_icon']=$obj['br_icon'];
				$arry['item_quantity']=$obj['quantity'];
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
			$ResourceInfo = $ResourceMstModel->select('r_id','r_name','r_rarity','r_type','r_img_path','r_description')->where('r_id','=',$Item_Id)->where('quantity','>',0)->first();

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
			$defindMstModel=new DefindMstModel();
			$baggageLimit=$defindMstModel->where('defind_id',68)->first();
			if($equ_type==1){
				$eqLimit=$baggageLimit['value1'];
		
			}
			else {
				$eqLimit=$baggageLimit['value2'];
			}
			$baggageWeapon=$UserBaggageEqModel
				->join('Equipment_mst','Equipment_mst.equ_id','=','User_Baggage_Eq.equ_id')
				->select('User_Baggage_Eq.user_beq_id','User_Baggage_Eq.equ_id','User_Baggage_Eq.icon_path','User_Baggage_Eq.equ_rarity')->where('u_id','=',$u_id)->where('User_Baggage_Eq.status','=',$status)->where('User_Baggage_Eq.equ_type','=',$equ_type)->orderBy('Equipment_mst.equ_rarity','DESC')->orderBy('equ_lv','DESC')->orderBy('Equipment_mst.equ_group')->orderBy('User_Baggage_Eq.equ_id')->limit($eqLimit)->get();

			foreach ($baggageWeapon as $obj) 
			{	$arry['baggage_id']=$obj['user_beq_id'];
				$arry['item_id']=$obj['equ_id'];
				$arry['item_type']=2;
				$arry['equ_type']=$equ_type;
				$arry['item_rarity']=$obj['equ_rarity'];
				$eq_data=$EquipmentMstModel->select('equ_code','equ_lv')->where('equ_id',$arry['item_id'])->first();
				$arry['equ_code']=$eq_data['equ_code'];
				$arry['equ_lv']=$eq_data['equ_lv'];
				if($arry['equ_type']==1){
				$standardData=$defindMstModel->select('value1','value2')->wherein('defind_id',[29,30,31,32])->where('value1',$obj['equ_rarity'])->first();
				$arry['need_lv']=$standardData['value2'];
				}
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
			$defindMstModel=new DefindMstModel();
			$equLimitModel=new EquipLVLimitMstModel();
			$equipment=[];
			$result=[];
			$baggeData=$userBaggage->where('u_id',$u_id)->where('user_beq_id',$user_beq_id)->where('status','!=',2)->first();
			$EquipmentInfo = $EquipmentMstModel->where('equ_id','=',$Item_Id)->first(); 
			$equipment['baggage_id']=$user_beq_id;
			$equipment['equ_code']=$EquipmentInfo['equ_code'];
			$equipment['equ_lv']=$EquipmentInfo['equ_lv'];
			$equipment['item_id']=$EquipmentInfo['equ_id'];
			$equipment['item_name']=$EquipmentInfo['equ_name'];
			$equipment['item_rarity']=$EquipmentInfo['equ_rarity'];
			$equipment['item_price']=$EquipmentInfo['equ_price'];
			$standardData=$equLimitModel->select('ch_lv')->where('equ_rarity',$EquipmentInfo['equ_rarity'])->first();
			$equipment['need_lv']=$standardData['ch_lv'];
			if($EquipmentInfo['upgrade_id']==0){
				$equipment['upgrade']=0;
			}
			else {
				$equipment['upgrade']=1;
			}
			$eqAtr=$eqAttrmstModel->where('equ_att_id',$EquipmentInfo['equ_att_id'])->first();

			$equipment['eff_ch_stam']=$eqAtr['eff_ch_stam'];
			$equipment['eff_ch_atk']=$eqAtr['eff_ch_atk'];
			$equipment['eff_ch_armor']=$eqAtr['eff_ch_armor'];
			if($equipment!=0){
				$equipment['eff_ch_crit_per']=$eqAtr['eff_ch_crit_per'];
			}

			$skillInfo = $skillMstModel->select('skill_id','skill_info','skill_name','skill_icon')->where('equ_id',$EquipmentInfo['equ_id'])->first();
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

			$baggageScroll=$UserBaggageScrollModel->select('user_sc_id','sc_id','sc_icon','quantity')->where('u_id','=',$baggage_u_id)->where('status','=',0)->orderBy('sc_rarity','DESC')->get();

			foreach ($baggageScroll as $obj) 
			{	$arry['baggage_id']=$obj['user_sc_id'];
				$arry['item_id']=$obj['sc_id'];
				$arry['item_type']=3;
				$arry['item_quantity']=$obj['quantity'];
				$arry['equ_type']=0;
				$scrollMst=$scrollMstModel->select('sc_img_path')->where('sc_id',$obj['sc_id'])->first();
				$arry['sc_img_path']=$scrollMst['sc_img_path'];
				$result[]=$arry;
			}
			return $result;
	}

	function getScrollInfo ($Item_Id,$u_id,$user_sc_id)
	{
			$ScrollId=$Item_Id;
			$UserModel=new UserModel();
			$ScrollMstModel=new ScrollMstModel();
			$EquipmentMstModel=new EquipmentMstModel();
			$SkillMstModel=new SkillMstModel();
			$ResourceMstModel=new ResourceMstModel();
			$EquUpgradeReMstModel=new EquUpgradeReMstModel();
			$ScrollResourceModel=new ScrollResourceModel();
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
			$result['baggage_id']=$user_sc_id;
			$upgradeRES=$ScrollResourceModel->select('r_id','r_quantity')->where('sc_id',$Item_Id)->get();
			$resource=[];
			foreach ($upgradeRES as $key => $each) {
				$tmp['r_id']=$each['r_id'];
				$rQu=$UserBaggageResModel->where('u_id',$u_id)->where('r_id',$each->r_id)->first();
				$tmp['r_qu_need']=$each['r_quantity'];
				if($rQu['quantity']){
				$tmp['r_qu_have']=$rQu['quantity'];
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
		$equAtr=$EqAttrmstModel->where('equ_att_id',$equData['equ_att_id'])->first();
		if($userValue['u_coin']-$coin<0&&$upgarde==1){
			throw new Exception("no enough coin");
		}
		$eqUpData=$EquUpgradeReMstModel->where('upgrade_id',$equData['upgrade_id'])->get();
		// if($equ_type==2||$equ_type==3){
		// $eqNextData=$EquUpgradeReMstModel->where('equ_code','like',$equData['equ_code'].'%' )->where('lv',$equData['equ_lv']+1)->first();
		// }else{
		// 	$eqNextData=$EquUpgradeReMstModel->where('equ_code',$equData['equ_code'])->where('lv',$equData['equ_lv']+1)->first();
		// }
		$comEqData=$EquipmentMstModel->where('equ_code','like',$equData['equ_code'].'%' )->where('equ_lv',$equData['equ_lv']+1)->first();

				foreach ($eqUpData as $key => $each) {
					$tmp['r_id']=$each->r_id;
					$rQu=$UserBaggageResModel->where('u_id',$u_id)->where('r_id',$each->r_id)->first();
					$tmp['r_qu_need']=$each->r_quantity;
					if($rQu['quantity']){
						$tmp['r_qu_have']=$rQu['quantity'];
						if($upgarde==1&&$rQu['quantity']<$each->r_quantity){
						throw new Exception("no enough resouce id".$each->r_id);
							}
						else{
						$UserBaggageResModel->where('u_id',$u_id)->where('r_id',$each->r_id)->update(['quantity'=>$rQu['quantity']-$each->r_quantity,'updated_at'=>$datetime]);
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
					$w_bag_id=$UserBaggageEqModel->insertGetId(['u_id'=>$u_id,'equ_id'=>$comEqData['equ_id'],'equ_rarity'=>$comEqData['equ_rarity'],'equ_type'=>$comEqData['equ_type'],'icon_path'=>$comEqData['icon_path'],'status'=>$status,'updated_at'=>$datetime,'created_at'=>$datetime]);
					$UserModel->where('u_id',$u_id)->update(['u_coin'=>$userValue['u_coin']-$coin,'updated_at'=>$datetime]);
				return ['equ_id'=>$comEqData['equ_id'],'baggage_id'=>$w_bag_id];
				}

			if($upgarde==0){
			$comEquAtr=$EqAttrmstModel->where('equ_att_id',$comEqData['equ_att_id'])->first();
			$result['equ_name']=$equData['equ_name'];
			$result['coin']=$equData['upgrade_coin'];
			$result['equ_atr']['equ_id']=$equ_id;
			$result['equ_atr']['equ_code']=$equData['equ_code'];
			$result['equ_atr']['item_rarity']=$equData['equ_rarity'];
			$result['equ_atr']['equ_lv']=$equData['equ_lv'];

			$result['equ_atr']['eff_ch_stam']=$equAtr['eff_ch_stam'];
			$result['equ_atr']['eff_ch_atk']=$equAtr['eff_ch_atk'];
			$result['equ_atr']['eff_ch_armor']=$equAtr['eff_ch_armor'];
			$result['equ_atr']['eff_ch_crit_per']=$equAtr['eff_ch_crit_per'];
			$result['up_equ']['equ_id']=$comEqData['equ_id'];
			$result['up_equ']['equ_code']=$comEqData['equ_code'];
			$result['up_equ']['item_rarity']=$comEqData['equ_rarity'];
			$result['up_equ']['equ_lv']=$comEqData['equ_lv'];
			$result['up_equ']['eff_ch_stam']=$comEquAtr['eff_ch_stam'];
			$result['up_equ']['eff_ch_atk']=$comEquAtr['eff_ch_atk'];
			$result['up_equ']['eff_ch_armor']=$comEquAtr['eff_ch_armor'];
			$result['up_equ']['eff_ch_crit_per']=$comEquAtr['eff_ch_crit_per'];
			$result['resource']=$resource;
			return $result;
		}

	}


	public function validateResource($u_id,$data,$coin){
		$UserModel=new UserModel();
		$UserBaggageResModel=new UserBaggageResModel();
		$now=new DateTime;
		$datetime=$now->format( 'Y-m-d h:m:s' );
		$dmy=$now->format( 'Ymd' );
		$userValue=$UserModel->select('u_coin')->where('u_id',$u_id)->first();
			if($userValue['u_coin']<$coin){
				throw new Exception("no enough coin");
				$response=[
						'status' => 'Wrong',
						'error' => "no enough resources",
				];
				}
			else{
				$coin=$userValue['u_coin']-$coin;
				}
				foreach ($data as $key => $resources) {
					$rQu=$UserBaggageResModel->where('u_id',$u_id)->where('r_id',$resources->r_id)->first();
					if($rQu['quantity']<$resources->r_quantity){
					throw new Exception("no enough resouce id".$resources->r_id);
					}
					else{
						$UserBaggageResModel->where('u_id',$u_id)->where('r_id',$resources->r_id)->update(['quantity'=>$rQu['quantity']-$resources->r_quantity,'updated_at'=>$datetime]);
					}
				# code...
			}
				$UserModel->where('u_id',$u_id)->update(['u_coin'=>$coin,'updated_at'=>$datetime]);
				return true;
		}

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
				$reData=$reModel->where('r_id',$reward['item_id'])->first();
				$quantity=$UserBaggageResModel->select('quantity')->where('r_id',$reward['item_id'])->where('u_id',$u_id)->first();


				if(isset($quantity['quantity'])&&$quantity['quantity']>0){
					$result['quantity']=$reward['item_quantity']+$quantity['quantity'];
					$UserBaggageResModel->where('r_id',$reward['item_id'])->where('u_id',$u_id)->update(['quantity'=>$result['quantity'],'updated_at'=>$datetime]);
				}
				else{
					$result=[];
					$result['u_id']=$u_id;
					$result['r_id']=$reData['r_id'];
					$result['br_icon']=$reData['r_img_path'];
					$result['r_rarity']=$reData['r_rarity'];
					$result['r_type']=$reData['r_type'];
					$result['quantity']=$reward['item_quantity'];
					$result['status']=0;
					$result['updated_at']=$datetime;
					$result['created_at']=$datetime;
					$UserBaggageResModel->insert($result);
				}
				if(array_key_exists($reward['item_id'],$resource)){
						$resource[$reward['item_id']]=$resource[$reward['item_id']]+$reward['item_quantity'];

					}else{
						$resource[$reward['item_id']]=$reward['item_quantity'];
				}
				
			}
			else if($reward['item_type']==2){
					$hadEq=$UserBaggageEqModel->where('equ_id',$reward['item_id'])->where('u_id',$u_id)->count();
					if($hadEq>=1){
						throw new Exception("no enough coin");
						$response=[
						'status' => 'Wrong',
						'error' => "you already have this equipment",
						];
					}
					$result=[];
					$eqData=$eqModel->where('equ_id',$reward['item_id'])->first();
					$result['u_id']=$u_id;
					$result['equ_id']=$eqData['equ_id'];
					$result['icon_path']=$eqData['icon_path'];
					$result['equ_rarity']=$eqData['equ_rarity'];
					$result['equ_type']=$eqData['equ_type'];
					//$result['quantity']=$reward['item_quantity'];
					$result['status']=0;
					$result['updated_at']=$datetime;
					$result['created_at']=$datetime;
					$UserBaggageEqModel->insert($result);
			}
			else if($reward['item_type']==3){
				for ($i=0;$i<$reward['item_quantity'];$i++) {
					$result=[];
					$scrData=$scrModel->where('sc_id',$reward['item_id'])->first();
					$result['u_id']=$u_id;
					$result['sc_id']=$reward['item_id'];
					$result['sc_icon']=$scrData['sc_img_path'];
					$result['sc_rarity']=$scrData['sc_rarity'];
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
			$reData=$reModel->where('r_id',$mission['item_id'])->first();
			$result['item_name']=$reData['r_name'];
			$result['item_description']=$reData['r_description'];
			$result['item_image']=$reData['r_img_path'];
		}
		else if($mission['item_type']==2){
			$eqData=$eqModel->where('equ_id',$mission['item_id'])->first();
			$result['item_name']=$reData['equ_name'];
			$result['item_description']=$reData['equ_description'];
			$result['item_image']=$reData['icon_path'];
		}
		else if($mission['item_type']==3){
			$scrData=$scrModel->where('sc_id',$mission['item_id'])->first();
			$result['item_name']=$reData['sc_name'];
			$result['item_description']=$reData['sc_description'];
			$result['item_image']=$reData['sc_img_path'];
		}
			$result['item_id']=$mission['item_id'];
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
			$itemData=$UserBaggageResModel->where('u_id',$u_id)->where('r_id',$item_id)->first();
		   	$rescourceData=$rescourceModel->where('r_id',$item_id)->first();
			if($itemData){
				$br_quanitty=$itemData['quantity']+$quantity;
		   				$UserBaggageResModel->where('u_id',$u_id)->where('r_id',$item_id)->update(['quantity'=>$br_quanitty,'updated_at'=>$date]);
			}
			else{
					   	$baReNew['u_id']=$u_id;
		   				$baReNew['r_id']=$item_id;
		   				$baReNew['r_rarity']=$rescourceData['r_rarity'];
		   				$baReNew['r_type']=$item_type;
		   				$baReNew['quantity']=$quantity;
		   				$baReNew['status']=0;
		   				$baReNew['updated_at']=$date;
		   				$baReNew['created_at']=$date;
		   				$UserBaggageResModel->insert($baReNew);
			}

		}
		else if($item_type==3){
			$scrollData=$ScrollMstModel->where('sc_id',$item_id)->first();
						$baScNew['u_id']=$u_id;
		   				$baScNew['sc_id']=$item_id;
		   				$baScNew['sc_rarity']=$scrollData['sc_rarity'];
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
				// if($spendData['coin']>0){
				// $mission->archiveMission(5,$u_id,$spendData['coin']);
				// }
				// if($spendData['gem']>0){
				// $mission->archiveMission(6,$u_id,$spendData['gem']);
				// }
				$redisShop->HSET($spentKey,$u_id,$spendJson);
 		}
 		public function getEquipedCode($w_bag_id){
 				$equ_data=DB::table('User_Baggage_Eq')
					->join('Equipment_mst','Equipment_mst.equ_id','=','User_Baggage_Eq.equ_id')
					->select('User_Baggage_Eq.equ_id as item_id','User_Baggage_Eq.equ_rarity as item_rarity','Equipment_mst.equ_type','Equipment_mst.equ_code','Equipment_mst.equ_lv')
					->where('User_Baggage_Eq.user_beq_id',$w_bag_id)
					->first();
					return $equ_data;
 		}
}