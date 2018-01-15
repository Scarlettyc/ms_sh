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
		$datetime=$now->format( 'Y-m-d h:m:s' );
		$dmy=$now->format( 'Ymd' );
		$redis_login=Redis::connection('default');
		$loginToday=$redis_login->HGET('login_data',$dmy.$data['u_id']);
		$loginTodayArr=json_decode($loginToday);
		$access_token=$loginTodayArr->access_token;
		$usermodel=new UserModel();
		if(isset($data['u_id'])&&$access_token==$data['access_token']){
			$oldPw=$data['old_password'];
			$newPw=$data['old_password'];
			$u_id=$data['u_id'];
			$password=$usermodel->select('password')->where('u_id',$u_id)->first();
			if($oldPw!=$password['password']){
				throw new Exception("wrong password");
				}
			else{
				$usermodel->where('u_id',$u_id)->update(['password'=>$newPw,'updated_at'=>$datetime]);
				return base64_encode('successfully');
			}
		}
		else {
			throw new Exception("there have some error of you access_token");
		}
	}
 }
