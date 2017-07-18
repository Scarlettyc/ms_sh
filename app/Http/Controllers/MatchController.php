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
		$match=$matchrange->where('star_from','<=',$chardata['ch_star'])->where('star_to','<=',$chardata['ch_star'])->first();
		if($chardata['ch_lv']<$maxLv){
			$matchKey='match_below_maxlv_star'.$match['star_from'].'to'.$match['star_to'];
		}
		else{
			$matchKey='match_maxlv_star'.$maxLv.'star'.$maxStar;
		}
		$matchList=Redis::LLEN($matchKey);

		if($matchList==0||!$matchList){
			Redis::LPUSH($matchKey,$u_id);
			return "wait in list";
		}
		else {
			$match_uid=Redis::LRANGE($matchKey,0,1);
			if($matchList==1&&$match_uid[0]==$u_id){
				return "wait in list";
			}
			else{
				$match_uid=Redis::LPOP($matchKey);
				$result['match_result']=$match_uid;
				$response=json_encode($result,TRUE);
				return $response;
			}
		}
        
    }
 }
