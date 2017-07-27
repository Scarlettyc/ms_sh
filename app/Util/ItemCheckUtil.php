<?php
namespace App\Util;
use App\Http\Requests;
use Illuminate\Http\Request;
use App\UserModel;
use App\EquipmentMstModel;
use App\EffectionMstModel;
use App\SkillMstModel;
use App\ScrollMstModel;
use App\ResourceMstModel;
use App\UserBaggageResModel;
use App\EquipmentUpgradeMstModel;
use Exception;
use App\Exceptions\Handler;
use Illuminate\Http\Response;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\Redis;

class ItemCheckUtil
{
	function CheckResource ($Item_had,$Item_need)
	{
		$had=$Item_had;
		$need=$Item_need;

		if($had>=$need)
		{
			$response=1;
		}else{
			$response=0;
		}
		return $response;
	}
}