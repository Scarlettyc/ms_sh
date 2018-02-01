<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use App\EventMstModel;
use DateTime;
use App\MessageMstModel;

class EventController extends Controller
{
  public function getEventList(Request $request){

  		$now   = new DateTime;
		$dmy=$now->format( 'Ymd' );
		$datetime=$now->format( 'Y-m-d h:m:s' );
    	$event=new EventMstModel();
    	$redis=Redis::connection('default');
    	$loginToday=$redis->HGET('login_data',$dmy.$data['u_id']);
		$loginTodayArr=json_decode($loginToday);
		$access_token=$loginTodayArr->access_token;
    	if($access_token==$data['access_token']){
    		$eventList=$event->select('banner_path','web_path')->where('start_date','<=',$datetime)->where('end_date','>=',$datetime)->get();
    		$reslut['event_list']=$eventList;
    		$response=json_encode($reslut,TRUE);
    		return $response;
    	}

  }
}
