<?php

namespace App\Http\Controllers;
use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\UserModel;
use App\CharacterModel;
use App\EquipmentMstModel;
use App\SkillMstModel;
use App\EffectionMstModel;
use App\Util\ItemInfoUtil;
use Exception;
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
		$result=[];
		$weaponData=[];
		$movementData=[];
		$coreData=[];

		$u_id=$data['u_id'];
		if(isset($u_id))
		{
			$characterDetail=$CharacterModel->where('u_id',$u_id)->first();
			$characterInfo=$CharacterModel->select('ch_id','ch_title','ch_lv','ch_star','ch_hp_max','ch_atk','ch_def','ch_res','ch_crit','ch_cd','ch_spd','ch_img')->where('u_id',$u_id)->first();
			$result['Workshop_Data']['Character_info']=$characterInfo;

			$WeaponId=$characterDetail['w_id'];
			$weaponDetail=$EquipmentMstModel->select('skill_id','icon_path')->where('equ_id',$WeaponId)->first();
			$MovementId=$characterDetail['m_id'];
			$movementDetail=$EquipmentMstModel->select('skill_id','icon_path')->where('equ_id',$MovementId)->first();
			$CoreId=$characterDetail['core_id'];
			$coreDetail=$EquipmentMstModel->select('skill_id','icon_path')->where('equ_id',$CoreId)->first();
			$WeaSkillId=$weaponDetail['skill_id'];
			$weaSkillDetail=$SkillMstModel->select('skill_icon')->where('skill_id',$WeaSkillId)->first();
			$MoveSkillId=$movementDetail['skill_id'];
			$moveSkillDetail=$SkillMstModel->select('skill_icon')->where('skill_id',$MoveSkillId)->first();
			$CoreSkillId=$coreDetail['skill_id'];
			$coreSkillDetail=$SkillMstModel->select('skill_icon')->where('skill_id',$CoreSkillId)->first();

			$weaponData['equ_id']=$characterDetail['w_id'];
			$weaponData['equ_icon']=$weaponDetail['icon_path'];
			$weaponData['skill_id']=$weaponDetail['skill_id'];
			$weaponData['skill_icon']=$weaSkillDetail['skill_icon'];
			$result['Workshop_Data']['Weapon_Data']=$weaponData;

			$movementData['equ_id']=$characterDetail['m_id'];
			$movementData['equ_icon']=$movementDetail['icon_path'];
			$movementData['skill_id']=$movementDetail['skill_id'];
			$movementData['skill_icon']=$moveSkillDetail['skill_icon'];
			$result['Workshop_Data']['Movement_Data']=$movementData;

			$coreData['equ_id']=$characterDetail['core_id'];
			$coreData['equ_icon']=$coreDetail['icon_path'];
			$coreData['skill_id']=$coreDetail['skill_id'];
			$coreData['skill_icon']=$coreSkillDetail['skill_icon'];
			$result['Workshop_Data']['Core_Data']=$coreData;

			$response=json_encode($result,TRUE);
		}else
		{
			throw new Exception("there have some error of you access_token");
			$response=[
			'status' => 'Wrong',
			'error' => "please check u_id",
			];
		}
		return base64_encode($response);
	}

	public function showEquipmentInfo (Request $request)
	{
		$req=$request->getContent();
		$json=base64_decode($req);
		$data=json_decode($json,TRUE);

		$EquipmentMstModel=new EquipmentMstModel();
		$EffectionMstModel=new EffectionMstModel();
		$ItemInfoUtil=new ItemInfoUtil();
		$result=[];

		$Item_Id=$data['equ_id'];
		if(isset($Item_Id))
		{
			$EquipmentDetail = $ItemInfoUtil->getEquipmentInfo($Item_Id);
			$response=$EquipmentDetail;
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
		$EffectionMstModel=new EffectionMstModel();
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
}