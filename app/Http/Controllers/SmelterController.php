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

		$
	}






























}