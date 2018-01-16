<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use Exception;
use DateTime;
use App\CharacterModel;
use App\UserFriendModel;
use App\UserModel;
use Illuminate\Support\Facades\Redis;


class UpdateController extends Controller
{
	public function updateEmail(Request $request){

		$req=$request->getContent();
		$json=base64_decode($req);
		$now   = new DateTime;
		$datetime=$now->format( 'Y-m-d h:m:s' );
		$dmy=$now->format( 'Ymd' );
	 	//dd($json);
		$data=json_decode($json,TRUE);
		$redis_login=Redis::connection('default');
		$loginToday=$redis_login->HGET('login_data',$dmy.$data['u_id']);
		$loginTodayArr=json_decode($loginToday);
		$access_token=$loginTodayArr->access_token;
		if(isset($data['u_id'])&&$access_token==$data['access_token']){
		$email=$data['email'];
		$u_id=$data['u_id'];
		$usermodel=new UserModel();
		$usermodel->where('u_id',$u_id)->update(['email'=>$email,'updated_at'=>$datetime]);
		return base64_encode('successfully');	
		}
		else {
			throw new Exception("there have some error of you access_token");
		}
	}

	public function updatePassword(Request $request){
		$req=$request->getContent();
		$json=base64_decode($req);
	 	//dd($json);
		$data=json_decode($json,TRUE);
		$now   = new DateTime;
		$datetime=$now->format( 'Y-m-d h:m:s' );
		$dmy=$now->format( 'Ymd' );
		$redis_login=Redis::connection('default');
		$loginToday=$redis_login->HGET('login_data',$dmy.$data['u_id']);
		$loginTodayArr=json_decode($loginToday);
		$access_token=$loginTodayArr->access_token;
		$usermodel=new UserModel();
		if(isset($data['u_id'])&&$access_token==$data['access_token']){
			$newPw=$data['new_password'];
			$u_id=$data['u_id'];
			$password=$usermodel->select('password')->where('u_id',$u_id)->first();
				$usermodel->where('u_id',$u_id)->update(['password'=>$newPw,'updated_at'=>$datetime]);
				return base64_encode('successfully');
		}
		else {
			throw new Exception("there have some error of you access_token");
		}
	}
	public function refreshSetting(Request $request){
		$req=$request->getContent();
		$json=base64_decode($req);
		$data=json_decode($json,TRUE);
		$now   = new DateTime;
		$datetime=$now->format( 'Y-m-d h:m:s' );
		$dmy=$now->format( 'Ymd' );
		$usermodel=new UserModel();
		$redis_login=Redis::connection('default');
		$loginToday=$redis_login->HGET('login_data',$dmy.$data['u_id']);
		$loginTodayArr=json_decode($loginToday);
		$access_token=$loginTodayArr->access_token;
		$CharacterModel=new CharacterModel();
		if(isset($data['u_id'])&&$access_token==$data['access_token']){
			$userData=$usermodel->select('u_id','profile_img','email','fb_id')->where('u_id',$u_id)->first();
			$userDetails=$CharacterModel->select('ch_img','ch_title')->where('u_id',$u_id)->first();
			$result['u_id']=$userData['u_id'];
			$result['email']=$userData['email'];
			$result['fb_id']=$userData['fb_id'];
			$result['profile_img']=$userData['profile_img'];
			$result['ch_img']=strval($userDetails['ch_img']);
			$result['ch_title']=$userDetails['ch_title'];
			$response=json_encode($result,TRUE);
			return base64_encode($response);

		}
				else {
			throw new Exception("there have some error of you access_token");
		}
	}
 }
