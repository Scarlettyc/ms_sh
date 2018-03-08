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
use App\EquUpgradeReMstModel;
use App\Util\BaggageUtil;
use App\Util\ItemInfoUtil;
use Exception;
use App\Exceptions\Handler;
use Illuminate\Http\Response;
use Carbon\Carbon;
use DateTime;
use DB;
use Log;
use Illuminate\Support\Facades\Redis;
// use App\Util\CharSkillEffUtil;

class BaggageItemController extends Controller
{
	//according to the select, display items in the baggage
	public function baggage(Request $request)
	{
		$req=$request->getContent();
		$json=base64_decode($req);
		$data=json_decode($json,TRUE);
		$BaggageUtil=new BaggageUtil();
		$result=[];
		$u_id=$data['u_id'];
		$item_type=$data['item_type'];
		$equ_type=$data['equ_type'];
		if($item_type==1){
			$result['Baggage_data']=$BaggageUtil->getResource($u_id);
		}
		else if($item_type==3){
			$result['Baggage_data']=$BaggageUtil->getScroll($u_id);
		}
		else if($item_type==2){
			$result['Baggage_data']=$BaggageUtil->getEquipment($u_id,$equ_type,0);
		}

		$response=json_encode($result,TRUE);
		return base64_encode($response);
	}
	public function workshop(Request $request){
		$req=$request->getContent();
		$json=base64_decode($req);
		$data=json_decode($json,TRUE);

		$CharacterModel=new CharacterModel();
		$EquipmentMstModel=new EquipmentMstModel();
		$result=[];
		$weaponData=[];
		$movementData=[];
		$coreData=[];

		$u_id=$data['u_id'];
			$characterDetail=$CharacterModel->where('u_id',$u_id)->first();
			$UserBaggageEqModel=new UserBaggageEqModel();
			$equ_data=$UserBaggageEqModel->select('user_beq_id as baggage_id','b_equ_id as item_id','b_equ_type as equ_type')->where('u_id',$u_id)->where('status',1)->get();
			$result['ch_equ']=$equ_data;
			$result['ch_stam']=$characterDetail['ch_stam'];
			$result['ch_atk']=$characterDetail['ch_atk'];
			$result['ch_armor']=$characterDetail['ch_armor'];
			$result['ch_crit']=$characterDetail['ch_crit'];
			
			$response=json_encode($result,TRUE);
				return base64_encode($response);
		}

	//show the detail information when user click the item in the baggage
	public function getItemInfo (Request $request)
	{
		$req=$request->getContent();
		$json=base64_decode($req);
		$data=json_decode($json,TRUE);
		$ItemInfoUtil=new ItemInfoUtil();
		$result=[];
		$item_id=$data['item_id']; //there are three different types: itemtype_1(Resource)/itemtype_2(Equipment)/itemtype_3(Scroll)
		$u_id=$data['u_id'];
		$item_type=$data['item_type'];
		$equ_type=$data['equ_type'];
		$baggage_id=$data['baggage_id'];
		$u_id=$data['u_id'];
		$BaggageUtil=new BaggageUtil();

			if($item_type == 1)
			{	$result = $BaggageUtil->getResourceInfo($item_id);
			}
			else if($item_type == 2)
			{	$result = $BaggageUtil->getEquipmentInfo($item_id,$u_id,$baggage_id,$equ_type);
			}else if($item_type == 3)
			{	
				$result = $BaggageUtil->getScrollInfo($item_id,$u_id,$user_bsc_id);
			}
			$response=json_encode($result,TRUE);
			return base64_encode($response);
	}

	//sell item in the baggage
	public function sellItem (Request $request)
	{
		$req=$request->getContent();
		$json=base64_decode($req);
		$data=json_decode($json,TRUE);
		$now=new DateTime;
		$datetime=$now->format( 'Y-m-d h:m:s' );
		$dmy=$now->format( 'Ymd' );

		$UserModel=new UserModel();
		$UserBaggageResModel=new UserBaggageResModel();
		$UserBaggageEqModel=new UserBaggageEqModel();
		$UserBaggageScrollModel=new UserBaggageScrollModel();
		$result=[];

		$u_id=$data['u_id'];
		$itemType=$data['item_type'];//itemtype:2(Equipment)/itemtype:3(Scroll)
		$itemId=$data['item_id'];
		$equ_type=$data['equ_type'];
		$baggage_id=$data['baggage_id'];
			if($itemType == 2)//sell Equipment
			{	$EquipmentMstModel=new EquipmentMstModel();
				$UserBaggageEqModel->where('u_id',$u_id)->where('status','=',0)->where('user_beq_id',$baggage_id)->update(array('status'=>9,'updated_at'=>$datetime));
				$eqData=$EquipmentMstModel->where('equ_id',$itemId)->first();
				$UserData=$UserModel->where('u_id',$u_id)->first();
				$ItemPrice=$eqData['equ_price'];
				$updateCoin=$UserData['u_coin']+$ItemPrice;
				$UserModel->where('u_id',$u_id)->update(['u_coin'=>$updateCoin,'updated_at'=>$datetime]);
				$response="sold Equipment";
			}else if($itemType == 3)//sell Scroll
			{	
				$ScrollMstModel=new ScrollMstModel();

				$bag_id=$data['user_bsc_id'];
				$UserBaggageScrollModel->where('u_id',$u_id)->where('status','=',0)->where('user_bsc_id',$bag_id)->update(['status'=>9,'updated_at'=>$datetime]);
				$scData=$ScrollMstModel->where('sc_id',$itemId)->first();
				$ItemPrice=$scData['sc_coin'];
				$UserData=$UserModel->where('u_id',$u_id)->first();
				$updateCoin=$UserData['u_coin']+$ItemPrice;
				$UserModel->where('u_id',$u_id)->update(['u_coin'=>$updateCoin,'updated_at'=>$datetime]);
				$response="sold Scroll";
		}else{
			throw new Exception("No itemType");
			$response=[
			'status' => 'Wrong',
			'error' => "please check itemType",
			];
		}
		return base64_encode($response);
	}
	public function compareEquipment (Request $request)
	{
		$req=$request->getContent();
		$json=base64_decode($req);
		$data=json_decode($json,TRUE);
		$BaggageUtil=new BaggageUtil();
		$u_id=$data['u_id'];
		$equ_id=$data['item_id'];
		$equ_type=$data['equ_type'];
		$item_type=$data['item_type'];
		$baggage_id=$data['baggage_id'];
		$result=$BaggageUtil->compareUpgradeEQ($u_id,$equ_id,$equ_type,0,0,0);
		$response=json_encode($result,TRUE);			
		return base64_encode($response);
	}
	public function scrollMerge (Request $request)
	{
		$req=$request->getContent();
		$json=base64_decode($req);
		$data=json_decode($json,TRUE);
		$now=new DateTime;
		$datetime=$now->format( 'Y-m-d h:m:s' );
		$dmy=$now->format( 'Ymd' );

		$BaggageUtil=new BaggageUtil();
		$UserBaggageScrollModel=new UserBaggageScrollModel();
		$ScrollMstModel=new ScrollMstModel();
		$EquipmentMstModel=new EquipmentMstModel();

		$u_id=$data['u_id'];
		$scrollId=$data['item_id'];
		$equ_type=$data['equ_type'];
		$item_type=$data['item_type'];
		$baggage_id=$data['baggage_id'];

		
			$UserBaggageScrollModel->where('u_id',$u_id)->where('status','=',0)->where('bsc_id',$scrollId)->where('user_bsc_id',$bag_id)->update(array('status'=>2,'updated_at'=>$datetime));
			$scrollInfo=$ScrollMstModel->select('sc_id','sc_coin','upgrade_id')->where('sc_id',$scrollId)->first();
			$equipmentInfo=$EquipmentMstModel->where('upgrade_id',$scrollInfo['upgrade_id'])->first();
			$upgarde=$BaggageUtil->compareUpgradeEQ($u_id,$equipmentInfo['equ_id'],$equipmentInfo['equ_type'],$scrollInfo['sc_coin'],1,0);
			if($upgarde){
				$UserBaggageScrollModel->where('u_id',$u_id)->where('user_bsc_id',$bag_id)->update(['status'=>9,'updated_at'=>$datetime]);

				$response='Successfully Meraged';
				return base64_encode($response);
			}
			else{
				throw new Exception("upgradeInfo is null");
			}

		
	}

	public function equipmentUpgrade (Request $request)
	{
		$req=$request->getContent();
		$json=base64_decode($req);
		$data=json_decode($json,TRUE);
		$now=new DateTime;
		$datetime=$now->format( 'Y-m-d h:m:s' );
		$dmy=$now->format( 'Ymd' );

		$BaggageUtil=new BaggageUtil();
		$UserBaggageEqModel=new UserBaggageEqModel();
		$EquipmentMstModel=new EquipmentMstModel();
		$EquUpgradeReMstModel=new EquUpgradeReMstModel();
		$result=[];
		$charmodel=new CharacterModel();

		$u_id=$data['u_id'];
		$equ_id=$data['item_id'];
		$equ_type=$data['equ_type'];
		$item_type=$data['item_type'];
		$baggage_id=$data['baggage_id'];

		$equData=$EquipmentMstModel->select('equ_id','equ_type','equ_code','equ_rarity','equ_lv')->where('equ_id',$equipmentId)->first();
		$eqDetail=$UserBaggageEqModel->where('u_id',$u_id)->where('user_beq_id',$baggage_id)->where('b_equ_id',$equipmentId)->first();
		$upgarde=$BaggageUtil->compareUpgradeEQ($u_id,$equ_id,$equ_type,$equData['upgrade_coin'],1,$eqDetail['status']);
		if($upgarde){
			$UserBaggageEqModel->where('user_beq_id',$baggage_id)->where('u_id',$u_id)->update(['status'=>2,'updated_at'=>$datetime]);
			$nextUpgarde=$EquUpgradeReMstModel->where('equ_code',$equData['equ_code'])->where('lv',$equData['equ_lv']+2)->count();
			if($nextUpgarde>0){
				$result['upgrade']=1;
			}else{
				$result['upgrade']=0;
			}
			$result['equ_id']=$upgarde['equ_id'];
			$result['baggage_id']=$upgarde['baggage_id'];
			$response=json_encode($result,TRUE);

		return base64_encode($response);

		}	
		
	}
		public function equipEquipment (Request $request)
	{
		$req=$request->getContent();
		$json=base64_decode($req);
		$data=json_decode($json,TRUE);
		$now=new DateTime;
		$datetime=$now->format( 'Y-m-d h:m:s' );
		$dmy=$now->format( 'Ymd' );

		$CharacterModel=new CharacterModel();
		$EquipmentMstModel=new EquipmentMstModel();
		$charUtil=new CharSkillEffUtil();
		$UserBaggageEqModel=new UserBaggageEqModel();
		$result=[];

		$u_id=$data['u_id'];
		$equ_id=$data['item_id'];
		$equ_type=$data['equ_type'];
		$item_type=$data['item_type'];
		$baggage_id=$data['baggage_id'];

			$characterDetail=$CharacterModel->where('u_id',$u_id)->first();
			$w_id=$characterDetail['w_id'];
			$m_id=$characterDetail['m_id'];
			$core_id=$characterDetail['core_id'];
			$EquNew=$EquipmentMstModel->where('equ_id',$equ_id)->first();
			$Equ_part=$EquNew['equ_part'];

			if($equ_type==1){
				$UserBaggageEqModel->equipNewEq($u_id,$equ_id,$characterDetail['w_bag_id'],$user_beq_id);
				$CharacterModel->where('u_id',$u_id)->update(['w_id'=>$equ_id,'w_bag_id'=>$user_beq_id,'updated_at'=>$datetime]);
			}
			else if($equ_type==3){
				$UserBaggageEqModel->equipNewEq($u_id,$equ_id,$characterDetail['m_bag_id'],$user_beq_id);
				$CharacterModel->where('u_id',$u_id)->update(['m_id'=>$equ_id,'m_bag_id'=>$user_beq_id,'updated_at'=>$datetime]);
			}
			else if($equ_type==2){
				$UserBaggageEqModel->equipNewEq($u_id,$equ_id,$characterDetail['core_bag_id'],$user_beq_id);
				$CharacterModel->where('u_id',$u_id)->update(['core_id'=>$equ_id,'core_bag_id'=>$user_beq_id,'updated_at'=>$datetime]);
			}else{
					throw new Exception("there have some error of you access_token");
			}
			$newchar=$charUtil->calculatCharEq($u_id);
			return base64_encode("success");
			
	}

}