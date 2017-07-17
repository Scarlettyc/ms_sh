<?php

namespace App\Http\Controllers;
use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\UserModel;
use App\CharacterModel;
use App\EquipmentMstModel;
use App\SkillMstModel;
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

		$characterModel=new CharacterModel();
		$equipmentMstModel=new EquipmentMstModel();
		$skillMstModel=new SkillMstModel();
		$result=[];

		$charData=$data;
		$ch_org_id=$charData['ch_org_id'];
		$ch_ws_id=$charData['ch_ws_id'];

		if($ch_org_id=$ch_ws_id)
		{
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
			
		}else{
			$characterData=$characterModel->where('ch_id','=',$ch_ws_id)->first();
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
	}

	public function getEquipmentInfo(Request $request)
	{
		$req=$request->getContent();
		$equipmentID=json_decode($req,TRUE);

		$equipmentMstModel=new EquipmentMstModel();
		$effectionMstModel=new EffectionMstModel();
		$result=[];

		$equipmentInfo = $equipmentMstModel->where('equ_id', '=', $equipmentID)->first();
		if(isset($equipmentInfo))
		{
			$result['equipment_d']['equipment_i']=$equipmentInfo;

			$eff_id=$equipmentInfo['eff_id'];

			$effInfo=$effectionMstModel->where('eff_id','=', $eff_id)->first();
			$result['eff_d']['eff_i']=$effInfo;

			$response=json_encode($result,TRUE);
			return $response;
		}else{
			throw new Exception("Wrong equipment ID");
			$response=[
			'status' => 'Wrong',
			'error' => "please check Equipment ID",
			];
		}
		
	}

	public function getSkillInfo(Request $request)
	{
		$req=$request->getContent();
		$skillID=json_decode($req,TRUE);

		$skillMstModel=new SkillMstModel();
		$effectionMstModel=new EffectionMstModel();
		$result=[];

		$skillInfo = $skillMstModel->where('skill_id', '=', $skillID)->first();
		if(isset($skillInfo))
		{
			$result['skill_d']['skill_i']=$skillInfo;

			$eff_id=$skillInfo['eff_id'];

			$effInfo=$effectionMstModel->where('eff_id', '=', $eff_id)->first();
			$result['eff_d']['eff_i']=$effInfo;

			$response=json_encode($result,TRUE);
			return $response;
		}else{
			throw new Exception("Wrong skill ID");
			$response=[
			'status' => 'Wrong',
			'error' => "please check Skill ID",
			];
		}
	}



	/*public function getRightWeapon($w_id_r){
		$W_R_Info = DB::select('select * from Equipment_mst where equ_id = ?', [$w_id_r]);

		return $W_R_Info;
	}

	public function getLeftWeapon($w_id_l){
		$W_L_Info = DB::select('select * from Equipment_mst where equ_id = ?', [$w_id_l]);

		return $W_L_Info;
	}

	public function getMoveEquipment($m_id){
		$M_Info = DB::select('select * from Equipment_mst where equ_id = ?', [$m_id]);

		return $M_Info;
	}*/


}