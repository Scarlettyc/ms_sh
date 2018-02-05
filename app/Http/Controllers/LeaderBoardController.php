<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use App\EventMstModel;
use DateTime;
use App\MessageMstModel;
use App\Util\CharSkillEffUtil;

class LeaderBoardController extends Controller
{
  public function getLeaderBoardList(Request $request){
  		$now   = new DateTime;
		$dmy=$now->format( 'Ymd' );
		$datetime=$now->format( 'Y-m-d h:m:s' );
    	$event=new EventMstModel();
      $CharSkillEffUtil=new CharSkillEffUtil();
      $access_token=$data['access_token'];
      $checkToken=$CharSkillEffUtil->($access_token,$u_id);
    	if($checkToken){
    		$u_id=$data['u_id'];
    		$eventList=$event->select('banner_path','web_path')->where('start_date','<=',$datetime)->where('end_date','>=',$datetime)->get();
    		$reslut['event_list']=$eventList;
    		$response=json_encode($reslut,TRUE);
    		return $response;
    	}

  }
}
