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

		$UserModel=new UserModel();
		$characterModel=new CharacterModel();
		$equipmentMstModel=new EquipmentMstModel();
		$skillMstModel=new SkillMstModel();
		$result=[];

			$userData=$data;
			$userData=$UserModel->where('uuid','=',$data['uuid'])->first();
			$ch_id=$userData['ch_id'];
			$characterData=$characterModel->where('ch_id','=',$data['ch_id'])->first();
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


		/*if(isset($data["uuid"]))
		{
			$userData=$data;
			$userData=$UserModel->where('uuid','=',$data['uuid'])->first();
			$ch_id=$userData['ch_id'];
			$characterData=$characterModel->where('ch_id','=',$data['ch_id'])->first();
			$result['character_data']['character_info']=$CharacterData;

			$w_id_l=$characterData['w_id_l'];
			$w_id_r=$characterData['w_id_r'];
			$m_id=$characterData['m_id'];
			$equ_id_1=$characterData['equ_id_1'];
			$equ_id_2=$characterData['equ_id_2'];
			$equ_id_3=$characterData['equ_id_3'];

			$equipmentData=$equipmentMstModel->whereIn('equ_id',[$w_id_l,$w_id_r,$m_id,$equ_id_1,$equ_id_2,$equ_id_3])->get();
			$result['equipment_data']['equipment_info']=$EquipmentData;

			$Equipment_a=$equipmentMstModel->where('equ_id','=',$w_id_r)->first();
			$skill_a=$Equipment_a['skill_id'];
			$Equipment_b=$equipmentMstModel->where('equ_id','=',$w_id_l)->first();
			$skill_b=$Equipment_b['skill_id'];
			$Equipment_c=$equipmentMstModel->where('equ_id','=',$m_id)->first();
			$skill_c=$Equipment_c['skill_id'];
			
			$skillData=$SkillMstModel->whereIn('skill_id',[$skill_a,$skill_b,$skill_c])->get();
			$result['skill_data']['skill_info']=$SkillData;

			$response=json_encode($result,TRUE);
			$response=base64_decode($response);		
		}
		else{
			throw new Exception("something went wrong!");
			$response = [
                'status' => 'wrong',
                'error' => "please refresh",
                    ];
		}
		return $response; **/
	}
}