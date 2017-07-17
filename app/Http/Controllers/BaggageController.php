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
class BaggageController extends Conroller
{
	public function Baggage(Request $request)
	{
		$req=$request->getContent();
		$data=json_decode($req,TRUE);

		$CharacterModel=new CharacterModel();
		$EquipmentMstModel=new EquipmentMstModel();
		$UserBaggageModel=new UserBaggageModel();
		$result=[];

		$userBaggageData=$data;
		$u_id=$userBaggageData['u_id'];
		$select=$userBaggageData['select'];

		if($select='all')
		{
			$baggageResouce=$UserBaggageModel->where('u_id','=',$u_id)
				->where(function($query){
					$query->where('item_type','=','1')
				})->get();

			$baggageEquipmentWeapon=$UserBaggageModel->where('u_id','=',$u_id)
				->where(function($query){
					$query->where('item_type','=','2')
						->where(function($query){
							$query->where('item_org_id','<','7')
						});
				})->get();

			

			$baggageEquipmentCore=$UserBaggageModel->where('u_id','=',$u_id)
				->where(function($query){
					$query->where('item_type','=','2')
						->where(function($query){
							$query->where('item_org_id','=','7')
						});
				})->get();

			$baggageScroll=$UserBaggageModel->where('u_id','=',$u_id)
				->where(function($query){
					$query->where('item_type','=','3')
				})->get();














			$baggageData=$UserBaggageModel->where('u_id','=',$u_id)->get();

			$item_type=$baggageData['item_type'];
			$item_org_id=$baggageData['item_org_id'];
			if($item_type==1)
			{

			}


		}
		












		if()
	}
}