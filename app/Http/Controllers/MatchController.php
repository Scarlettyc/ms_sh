<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\UserModel;
use App\MatchRangeModel;
use App\CharacterModel;
use Illuminate\Support\Facades\Redis;
class MatchController extends Controller
{
    public function match(Request $request)
    {
    	$req=$request->getContent();
		$json=base64_decode($req);
	 	//dd($json);
		$data=json_decode($json,TRUE);
		$u_id=$data['u_id'];
		//push to match list

		$usermodel=new UserModel();
		$matchrange=new MatchRangeModel();
		$characterModel=new CharacterModel();
		$chardata=$characterModel->where('u_id',$u_id)->first();
		$maxLv=$matchrange->max('user_lv_to');
		$maxStar=$matchrange->max('star_from');
		if($chardata['ch_lv']<$maxLv){
			$match=$matchrange->where('user_lv_from','<=',$chardata['ch_lv'])->where('user_lv_from','<=',$chardata['ch_lv'])->first();
			$matchKey='lv'.$match['user_lv_from'].'to'.$match['user_lv_to'];
		}
		else if($chardata['ch_star']<$maxStar){
			$match=$matchrange->where('ch_star','<=',$chardata['ch_lv'])->where('star_to','<=',$chardata['ch_lv'])->first();
			$matchKey='star'.$match['star_from'].'to'.$match['star_to'];

		}
		else{
			$matchKey='lv'.$maxLv.'star'.$maxStar;
		}
		$matchList=Redis::LLEN('match_range',$matchKey);
		if($matchList==0||!$matchList){
			Redis::LPUSH('match_range',$matchKey,$u_id);
			return "wait in list";
		}
		else {
			$match_uid=Redis::LPOP('match_range',$matchKey);
			$result['match_result']=$match_uid;
			$response=json_encode($result,TRUE);
			return $response;
		}
        
    }
 }
