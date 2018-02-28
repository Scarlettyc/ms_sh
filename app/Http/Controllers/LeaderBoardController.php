<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use App\CharacterModel;
use DateTime;
// use App\Util\CharSkillEffUtil;

class LeaderBoardController extends Controller
{
  public function getLeaderBoardList(Request $request){
      $req=$request->getContent();
     $json=base64_decode($req);
      $data=json_decode($json,TRUE);
  		$now   = new DateTime;
		  $dmy=$now->format( 'Ymd' );
		  $datetime=$now->format( 'Y-m-d h:m:s' );
    	$char=new CharacterModel();
      $u_id=$data['u_id'];
      $leaderRanking=$char->select('u_id','ch_title','w_id','m_id','core_id','ch_ranking')->orderBy('ch_ranking', 'desc')->limit(10);
      $myRanking=$char->select('ch_ranking','ch_title')->where('u_id',$u_id)->first();
      $result['leader_board']=$leaderRanking;
      $result['user_ranking']=$myRanking;
    	$response=json_encode($result,TRUE);
        return base64_encode($response);
    	}

  // }
}
