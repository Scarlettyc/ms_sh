<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use App\CharacterModel;
use DateTime;
use App\UserModel;
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
      $usermodel=new UserModel();
      $u_id=$data['u_id'];
      $leaderRanking=$char->select('u_id','ch_title','ch_ranking','ch_star','ch_lv')->orderBy('ch_ranking', 'desc')->limit(10)->get();
      $leaders=[];
      foreach ($leaderRanking as $key => $leader) {
        $friend=$usermodel->select('friend_id')->where('u_id',$leader['u_id'])->first();
        $leader['friend_id']=$friend['friend_id'];
        $leader['winRounds']=0;
        $leader['loseRounds']=0;
        $leaders[]=$leader;
      }
      $myRanking=$char->select('ch_ranking','ch_title','u_id','ch_title','ch_ranking','ch_star','ch_lv')->where('u_id',$u_id)->first();
      $myRanking['winRounds']=0;
      $myRanking['loseRounds']=0;
      $myfriend=$usermodel->select('friend_id')->where('u_id',$u_id)->first();
      $myRanking['friend_id']=$myfriend['friend_id'];
      $result['leader_board']=$leaders;
      $result['user_ranking']=$myRanking;
    	$response=json_encode($result,TRUE);
        return base64_encode($response);
    	}

  // }
}
