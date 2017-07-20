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

		$charData=$data;
		$ch_org_id=$charData['ch_org_id'];
		$ch_ws_id=$charData['ch_ws_id'];

		if($ch_org_id==$ch_ws_id)
		{
			
		}












		$characterData=$characterModel->where('ch_id','=',$ch_org_id)->first();
		if(isset($characterData))
		{
			$result['character_data']['character_info']=$characterData;
			$w_id_l=$characterData['w_id_l'];
			$w_id_r=$characterData['w_id_r'];
			$m_id=$characterData['m_id'];
			$equ_id_1=$characterData['equ_id_1'];
			$equ_id_2=$characterData['equ_id_2'];
			$equ_id_3=$characterData['equ_id_3'];

			$equipmentData=$equipmentMstModel->whereIn('equ_id',[$w_id_l,$w_id_r,$m_id,$equ_id_1,$equ_id_2,$equ_id_3])->get();
			$result['equipment_data']['equipment_info']=$equipmentData;

			$Equipment_a=$equipmentMstModel->where('equ_id','=',$w_id_r)->first();
			$skill_a=$Equipment_a['skill_id'];
			$Equipment_b=$equipmentMstModel->where('equ_id','=',$w_id_l)->first();
			$skill_b=$Equipment_b['skill_id'];
			$Equipment_c=$equipmentMstModel->where('equ_id','=',$m_id)->first();
			$skill_c=$Equipment_c['skill_id'];
					
			$skillData=$skillMstModel->whereIn('skill_id',[$skill_a,$skill_b,$skill_c])->get();
			$result['skill_data']['skill_info']=$skillData;

			$response=json_encode($result,TRUE);
			//$response=base64_decode($response);	

			return $response;
		}else{
			throw new Exception("Wrong Character ID!")
			$response=[
			'status' => 'Wrong',
			'error' => 'please check Character ID',
			];
		}		
	}

	public function getEquipmentInfo (Request $request)
	{
		$req=$request->getContent();
		$data=json_decode($req,TRUE);

		$EquipmentId=$data['EquId'];

		if(isset($EquipmentId))
		{
			$EquipmentMstModel=new EquipmentMstModel();
			$EffectionMstModel=new EffectionMstModel();
			$result=[];

			$EquipmentInfo = $EquipmentMstModel->where('equ_id','=',$EquipmentId)->first();
			$result['Equipment_data']['Equipment_info']=$EquipmentInfo;

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

	public function getSkillInfo(Request $request)
	{
		$req=$request->getContent();
		$data=json_decode($req,TRUE);

		$SkillId=$data['SkillId'];

		if(isset($SkillId))
		{
			$SkillMstModel=new SkillMstModel();
			$EffectionMstModel=new EffectionMstModel();
			$result=[];

			$SkillInfo = $SkillMstModel->where('skill_id', '=', $SkillId)->first();
			$result['Skill_data']['skill_info']=$SkillInfo;

			$Eff_id=$SkillInfo['eff_id'];
			$EffInfo=$EffectionMstModel->where('eff_id', '=', $Eff_id)->first();
			$result['skill_d']['eff_i']=$EffInfo;

			$response=json_encode($result,TRUE);
		}else{
			throw new Exception("Wrong skill ID");
			$response=[
			'status' => 'Wrong',
			'error' => "please check Skill ID",
			];
		}
		return $response;
	}
}