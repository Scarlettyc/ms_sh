<?php

namespace App\Http\Controllers;
use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\UserModel;
use App\CharacterModel;
use App\EquipmentMstModel;
use App\UserBaggageEqModel;
use App\UserBaggageResModel;
use App\EqAttrmstModel;
use App\EquUpgradeReMstModel;
use App\EquUpgradeMstModel;
use App\SkillMstModel;
use App\ImgMstModel;
use App\Util\ItemInfoUtil;
use Exception;
use App\Util\CharSkillEffUtil;
use App\Exceptions\Handler;
use Illuminate\Http\Response;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\Redis;

class WorkshopController extends Controller
{
	public function workshop(Request $request)
	{
		$req=$request->getContent();
		$json=base64_decode($req);
		$data=json_decode($json,TRUE);

		$CharacterModel=new CharacterModel();
		$EquipmentMstModel=new EquipmentMstModel();
		$SkillMstModel=new SkillMstModel();
		$eqUpgrade=new EquUpgradeReMstModel();
		$result=[];
		$weaponData=[];
		$movementData=[];
		$coreData=[];

		$u_id=$data['u_id'];
		if($u_id)
		{
			$characterDetail=$CharacterModel->where('u_id',$u_id)->first();
			$characterInfo=$CharacterModel->where('u_id',$u_id)->first();

			$result['w_id']=$characterDetail['w_id'];
			$wData=$EquipmentMstModel->select('equ_code','equ_lv')->where('equ_id',$result['w_id'])->first();
			$result['w_bag_id']=$characterDetail['w_bag_id'];

			$wUP=$eqUpgrade->where('equ_code',$wData['equ_code'])->where('lv',$wData['equ_lv']+1)->count();
			if($wUP>0){
				$result['upgrade_w']=1;
			}
			else{
				$result['upgrade_w']=0;
			}
			$result['m_id']=$characterDetail['m_id'];
			$result['m_bag_id']=$characterDetail['m_bag_id'];
			$mData=$EquipmentMstModel->select('equ_code','equ_lv')->where('equ_id',$result['m_id'])->first();
			$mUP=$eqUpgrade->where('equ_code',$mData['equ_code'])->where('lv',$mData['equ_lv']+1)->count();
			if($mUP>0){
				$result['upgrade_m']=1;
			}
			else{
				$result['upgrade_m']=0;
			}
			$result['core_id']=$characterDetail['core_id'];
			$result['core_bag_id']=$characterDetail['core_bag_id'];
			$cData=$EquipmentMstModel->select('equ_code','equ_lv')->where('equ_id',$result['core_id'])->first();
			$cUP=$eqUpgrade->where('equ_code','like',$cData['equ_code'].'%')->where('lv',$cData['equ_lv']+1)->count();
			if($cUP>0){
				$result['upgrade_c']=1;
			}
			else{
				$result['upgrade_c']=0;
			}
			$result['ch_stam']=$characterDetail['ch_stam'];
			$result['ch_atk']=$characterDetail['ch_atk'];
			$result['ch_armor']=$characterDetail['ch_armor'];
			$result['ch_crit']=$characterDetail['ch_crit'];
			
			$response=json_encode($result,TRUE);
				return base64_encode($response);
		}
	
	}

	public function showEquipmentInfo (Request $request)
	{
		$req=$request->getContent();
		$json=base64_decode($req);
		$data=json_decode($json,TRUE);

		$EquipmentMstModel=new EquipmentMstModel();
		$ItemInfoUtil=new ItemInfoUtil();
		$result=[];

		$Item_Id=$data['user_beq_id'];
		if(isset($Item_Id))
		{
			$EquipmentDetail = $ItemInfoUtil->getEquipmentInfo($Item_Id);
			$response=json_encode($EquipmentDetail,TRUE);

		}else
		{
			throw new Exception("Wrong Equipment ID data");
			$response=[
			'status' => 'Wrong',
			'error' => "please check Equipment ID data",
			];
		}
		return base64_encode($response);
	}

	public function showSkillInfo (Request $request)
	{
		$req=$request->getContent();
		$json=base64_decode($req);
		$data=json_decode($json,TRUE);

		$SkillMstModel=new SkillMstModel();
		$ItemInfoUtil=new ItemInfoUtil();
		$result=[];

		$skill_id=$data['skill_id'];
		if(isset($skill_id))
		{
			$SkillDetail = $ItemInfoUtil->getSkillInfo($skill_id);
			$response=$SkillDetail;
		}else
		{
			throw new Exception("Wrong Skill ID data");
			$response=[
			'status' => 'Wrong',
			'error' => "please check Skill ID data",
			];
		}
		return base64_encode($response);
	}

	//compare two equipments in the workshop. show the details of equipments and the skills.
	public function compareEquipment (Request $request)
	{
		$req=$request->getContent();
		$json=base64_decode($req);
		$data=json_decode($json,TRUE);

		$EquipmentMstModel=new EquipmentMstModel();
		$CharacterModel=new CharacterModel();
		$SkillMstModel=new SkillMstModel();
		$ItemInfoUtil=new ItemInfoUtil();
		$EqAttrmstModel=new EqAttrmstModel();
		$UserBaggageEqModel=new UserBaggageEqModel();
		$EquUpgradeReMstModel=new EquUpgradeReMstModel();
		$UserBaggageResModel =new UserBaggageResModel();
		$result=[];

		$u_id=$data['u_id'];
		$equ_id=$data['equ_id'];
		$user_beq_id=$data['user_beq_id'];

			// $bagData=$UserBaggageEqModel->where('user_beq_id',$user_beq_id)->where('u_id')->first();
			$equData=$EquipmentMstModel->where('equ_id',$equ_id)->first();
			$equAtr=$EqAttrmstModel->where('equ_att_id',$equData['equ_attribute_id'])->first();
			$eqUpData=$EquUpgradeReMstModel->where('upgrade_id',$equData['upgrade_id'])->get();

			$eqNextData=$EquUpgradeReMstModel->where('equ_code',$equData['equ_code'])->where('lv',$equData['equ_lv']+1)->first();

			$comEqData=$EquipmentMstModel->where('equ_id',$eqNextData['upgrade_id'])->first();
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
			$resource=[];
				foreach ($eqUpData as $key => $each) {
					$tmp['r_id']=$each->r_id;
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
			$response=json_encode($result,TRUE);			
		return base64_encode($response);
	}

	//after user change equipment, adjust the attributes of chararcter and change the character image
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
		$ImgMstModel=new ImgMstModel();
		$charUtil=new CharSkillEffUtil();
		$UserBaggageEqModel=new UserBaggageEqModel();
		$result=[];

		$u_id=$data['u_id'];
		$equ_id=$data['equ_id'];
		$user_beq_id=$data['user_beq_id'];

			$characterDetail=$CharacterModel->where('u_id',$u_id)->first();
			$w_id=$characterDetail['w_id'];
			$m_id=$characterDetail['m_id'];
			$core_id=$characterDetail['core_id'];
			$hp=$characterDetail['ch_hp_max'];
			$atk=$characterDetail['ch_atk'];
			$def=$characterDetail['ch_def'];
			$crit=$characterDetail['ch_crit'];
			$cd=$characterDetail['ch_cd'];

			$EquNew=$EquipmentMstModel->where('equ_id',$equ_id)->first();
			$Equ_part=$EquNew['equ_part'];

		
			if($Equ_part==1){
				$UserBaggageEqModel->equipNewEq($u_id,$equ_id,$characterDetail['w_bag_id'],$user_beq_id);
				$CharacterModel->where('u_id',$u_id)->update(['w_id'=>$equ_id,'w_bag_id'=>$user_beq_id,'updated_at'=>$datetime]);
				$newchar=$charUtil->calculatCharEq($u_id);
				return base64_encode("success");
			}
			else if($Equ_part==2){
				$UserBaggageEqModel->equipNewEq($u_id,$equ_id,$characterDetail['m_bag_id'],$user_beq_id);
				$CharacterModel->where('u_id',$u_id)->update(['m_id'=>$equ_id,'m_bag_id'=>$user_beq_id,'updated_at'=>$datetime]);
				$newchar=$charUtil->calculatCharEq($u_id);
				return base64_encode("success");

			}
			else if($Equ_part==3){
				$UserBaggageEqModel->equipNewEq($u_id,$equ_id,$characterDetail['core_bag_id'],$user_beq_id);
				$CharacterModel->where('u_id',$u_id)->update(['core_id'=>$equ_id,'core_bag_id'=>$user_beq_id,'updated_at'=>$datetime]);
				$newchar=$charUtil->calculatCharEq($u_id);
				return base64_encode("success");
			}else{
					throw new Exception("there have some error of you access_token");
				$response=[
					'status' => 'Wrong',
					'error' => "please check u_id",
					];

			}
			
	}
}