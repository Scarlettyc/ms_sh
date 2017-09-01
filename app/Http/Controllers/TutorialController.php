<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\Article;
use App\UserModel;
use App\CharacterModel;
use Exception;
use DateTime;
use Illuminate\Support\Facades\Redis;
class TutorialController extends Controller
{
	public function createChar(Request $request){
		$req=$request->getContent();
		$json=base64_decode($req);
	 	//dd($json);
		$data=json_decode($json,TRUE);
		$now   = new DateTime;
		$dmy=$now->format( 'Ymd' );
		$data=json_decode($json,TRUE);
		$loginToday=Redis::HGET('login_data',$dmy.$data['u_id']);
		$loginTodayArr=json_decode($loginToday);
		$access_token=$loginTodayArr->access_token;

		if($access_token==$data['access_token'])
		{
			$uid=$data['u_id'];
			$characterModel=new CharacterModel();
		//dd($characterModel->isExist('u_id',$uid));
			if($characterModel->isExist('u_id',$uid)==0)
			{
 				$now   = new DateTime;
				$datetime=$now->format('Y-m-d h:m:s');
				$char['ch_title']=$data['title'];
				$char['createdate']=$datetime;
				$char['u_id']=$uid;
				$characterModel->insert($char);
				$finalChar=$characterModel->where('u_id',$uid)->first();
				$response['user_data']['character_info']=json_encode($finalChar,TRUE);
				return base64_encode($response);
			}
			else {
				throw new Exception("char already exist");
			}
		}
		else {
			throw new Exception("there have some error of you access_token");
		}
	   	
	}
	public function passTu(Request $request){
		$req=$request->getContent();
		$json=base64_decode($req);
		$data=json_decode($json,TRUE);
		$now   = new DateTime;
		$dmy=$now->format( 'Ymd' );
		$loginToday=Redis::HGET('login_data',$dmy.$data['u_id']);
		$loginTodayArr=json_decode($loginToday);
		$access_token=$loginTodayArr->access_token;

		if(isset($data['u_id'])&&$access_token==$data['access_token'])
		{
			$uid=$data['u_id'];
			$usermodel=new UserModel();
			$characterModel=new CharacterModel();
			$usermodel->where('u_id',$data['u_id'])->update(["pass_tutorial"=>1]);
			$finalUser=$usermodel->where('u_id',$uid)->first();
			$response['user_data']['user_info']=json_encode($finalUser,TRUE);
		return base64_encode($response);
		}
		else {
			throw new Exception("there have some error of you access_token");
		}

	}
 }
