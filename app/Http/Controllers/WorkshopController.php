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
class WorkshopController extends Controller
{
	public function workshop(Request $request)
	{
		$req=$request->getContent();
		//$json=base64_decode($req);
		$data=json_decode($req,TRUE);

		$CharacterModel=new CharacterModel();
		$EquipmentMstModel=new EquipmentMstModel();
		$SkillMstModel=new SkillMstModel();
		$result=[];

		$u_id=$data['u_id'];
		if(isset($u_id))
		{
			$characterDetail=$CharacterModel->select('ch_id','ch_title','w_id','m_id','core_id','ch_lv','ch_star','ch_hp_max','ch_atk','ch_def','ch_res','ch_crit','ch_ch','ch_spd','ch_img')->where('u_id',$u_id)->first();
			$result['Workshop_Data']['Character_info']=$characterDetail;
			$WeaponId=$characterDetail['w_id'];
			$weaponDetail=$EquipmentMstModel->select('skill_id','icon_path')->where('equ_id',$WeaponId)->first();
			$result['Workshop_Data']['Weapon_icon']=$weaponDetail;
			$MovementId=$characterDetail['m_id'];
			$movementDetail=$EquipmentMstModel->select('skill_id','icon_path')->where('equ_id',$MovementId)->first();
			$result['Workshop_Data']['Movement_icon']=$movementDetail;
			$CoreId=$characterDetail['core_id'];
			$coreDetail=$EquipmentMstModel->select('skill_id','icon_path')->where('equ_id',$CoreId)->first();
			$result['Workshop_Data']['Core_icon']=$coreDetail;

			$WeaSkillId=$weaponDetail['skill_id'];
			$weaSkillDetail=$SkillMstModel->select('skill_icon')->where('skill_id',$WeaSkillId)->first();
			$result['Workshop_Data']['Weapon_skill']=$weaSkillDetail;
			$MoveSkillId=$movementDetail['skill_id'];
			$moveSkillDetail=$SkillMstModel->select('skill_icon')->where('skill_id',$MoveSkillId)->first();
			$result['Workshop_Data']['Movement_skill']=$moveSkillDetail;
			$CoreSkillId=$coreDetail['skill_id'];
			$coreSkillDetail=$SkillMstModel->select('skill_icon')->where('skill_id',$CoreSkillId)->first();
			$result['Workshop_Data']['Core_skill']=$coreSkillDetail;
			$response=json_encode($result,TRUE);
		}else
		{
			throw new Exception("there have some error of you access_token");
			$response=[
			'status' => 'Wrong',
			'error' => "please check u_id",
			];
		}
	}

	public function showEquipmentInfo (Request $request)
	{
		$req=$request->getContent();
		$data=json_decode($req,TRUE);

		$EquipmentMstModel=new EquipmentMstModel();
		$EffectionMstModel=new EffectionMstModel();
		$result=[];

		$equ_id=$data['equ_id'];
		if(isset($equ_id))
		{
			$EquipmentDetail = $ItemInfoUtil->getEquipmentInfo($equ_id);
			$response=$EquipmentDetail;
		}else
		{
			throw new Exception("Wrong Equipment ID data");
			$response=[
			'status' => 'Wrong',
			'error' => "please check Equipment ID data",
			];
		}
	}

	public function showSkillInfo (Request $request)
	{
		$req=$request->getContent();
		$data=json_decode($req,TRUE);

		$SkillMstModel=new SkillMstModel();
		$EffectionMstModel=new EffectionMstModel();
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
	}
}