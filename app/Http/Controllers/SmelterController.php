<?php

namespace App\Http\Controllers;
use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\UserModel;
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

class SmelterController extends Controllers
{
	public function scrollmerageInfo(Request $request)
	{
		$req=$request->getContent();
		$data=json_decode($req,TRUE);

		$ItemInfoUtil=new ItemInfoUtil();
		$result=[];

		$u_id=$data['u_id'];
		$ItemId=$data['scroll_id'];
		if(isset($u_id))
		{
			$ScrollInfo = $ItemInfoUtil->getScrollInfo($ItemId,$u_id);
			$response=$ScrollInfo;
		}else{
			throw new Exception("there have some error of you access_token");
			$response=[
			'status' => 'Wrong',
			'error' => "please check u_id",
			];
		}
		return $response;
	}

	public function scrollmerage(Request $request)
	{
		
	}

	public function upgradeEquipmentInfo(Request $request)
	{
		$req=$request->getContent();
		$data=json_decode($req,TRUE);

		$ItemInfoUtil=new ItemInfoUtil();
		$result=[];

		$u_id=$data['u_id'];
		$ItemId=$data['equ_id'];
		if(isset($u_id))
		{
			$UpgradeEquInfo = $ItemInfoUtil->getEquipmentUpgradeInfo($ItemId,$u_id);
			$response=$UpgradeEquInfo;
		}else{
			throw new Exception("there have some error of you access_token");
			$response=[
			'status' => 'Wrong',
			'error' => "please check u_id",
			];
		}
		return $response;
	}

	public function upgradeEquipment(Request $request)
	{
		
	}






























}