<?php

use App\UserBaggageScrollModel;
use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Exception;
use App\Exceptions\Handler;
use Illuminate\Http\Response;
use Carbon\Carbon;
use DateTime;

function getScroll ($baggage_u_id)
{
	if(isset($baggage_u_id))
	{
		$UserBaggageScrollModel=new UserBaggageScrollModel();
		$result=[];

		$baggageScroll=$UserBaggageScrollModel->where('u_id','=',$baggage_u_id)->where('status','=',0)->get();
		$result['Baggage_data']['Baggage_Scroll']=$baggageScroll;

		$response=$result;
	}else{
		throw new Exception("No User ID");
		$response=[
		'status' => 'Wrong',
		'error' => "please check u_id",
		];
	}
	return $response;
}