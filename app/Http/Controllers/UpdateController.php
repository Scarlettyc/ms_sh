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
use App\DefindMstModel;


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
		$email=$data['email'];
		$u_id=$data['u_id'];
		$usermodel=new UserModel();
		$usermodel->where('u_id',$u_id)->update(['email'=>$email,'updated_at'=>$datetime]);
		return base64_encode('successfully');	
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
		$usermodel=new UserModel();
			$newPw=$data['new_password'];
			$u_id=$data['u_id'];
			$password=$usermodel->select('password')->where('u_id',$u_id)->first();
				$usermodel->where('u_id',$u_id)->update(['password'=>$newPw,'updated_at'=>$datetime]);
				return base64_encode('successfully');
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
		$CharacterModel=new CharacterModel();
			$u_id=$data['u_id'];
			$userData=$usermodel->select('u_id','profile_img','email','fb_id')->where('u_id',$u_id)->first();
			$userDetails=$CharacterModel->select('ch_title')->where('u_id',$u_id)->first();
			$result['u_id']=$userData['u_id'];
			$result['email']=$userData['email'];
			$result['fb_id']=$userData['fb_id'];
			$result['profile_img']=$userData['profile_img'];
			// $result['ch_img']=strval($userDetails['ch_img']);
			$result['ch_title']=$userDetails['ch_title'];
			$response=json_encode($result,TRUE);
			return base64_encode($response);
	}
	public function updateProfile(Request $request){

		$req=$request->getContent();
		$json=base64_decode($req);
		$data=json_decode($json,TRUE);
		$now   = new DateTime;
		$datetime=$now->format( 'Y-m-d h:m:s' );
		$dmy=$now->format( 'Ymd' );
		$usermodel=new UserModel();
		$u_id=$data['u_id'];
		$profile_img=$data['profile_img'];
		try{
			$userData=$usermodel->where('u_id',$u_id)->update(['profile_img'=>$profile_img,'updated_at'=>$datetime]);
			return base64_encode("udpate image successfully");
		}
		catch(Exception $e){
				throw new Exception("there have some error occured");
		}

	}
 }
