<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\UserModel;
use Exception;
use DateTime;
use App\CharacterModel;
use App\UserFriendModel;
use Illuminate\Support\Facades\Redis;
use App\DefindMstModel;

class FriendController extends Controller
{
	public function searchFriend(Request $request){
		$req=$request->getContent();
		$json=base64_decode($req);
	 	//dd($json);
		$data=json_decode($json,TRUE);
		$usermodel=new UserModel();
		$characterModel=new CharacterModel();
		$friend=$usermodel->where('friend_id',$data['friend_id'])->first();
		$friend_char=$characterModel->where('u_id',$friend['u_id'])->first();
		$result['searched_friend']=$friend_char;
		$response=json_encode($result,TRUE);
		return $response;
		
	}

	public function suggest_friend(Request $request){

	}

	public function send_friendrequest(Request $request){
		$req=$request->getContent();
		$json=base64_decode($req);
	 	//dd($json);
		$data=json_decode($json,TRUE);
		$u_id=$data['u_id'];
		$friend_id=$data['friend_id'];
		$usermodel=new UserModel();
		$characterModel=new CharacterModel();
		$friend=$usermodel->where('friend_id',$data['friend_id'])->first();
		$urData=$usermodel->where('u_id',$u_id)->first();
		if(isset($friend)){
			if($friend['u_id']!=$u_id){
				$key='friend_request_'.$friend['u_id'];
				$myData["u_id"]=$u_id;
				$myData["friend_id"]=$urData['friend_id'];
				$myData["time"]=time();
				$myre=json_encode($myData,TRUE);
				Redis::HSET($key,$u_id,$myre);
				$myData['friend_u_id']=$friend['u_id'];
				$response=json_encode($myData,TRUE);
				return $response;
			}
		else {
			throw new Exception("cannot add yourself");
		}
	}
	else {
		throw new Exception("please send correct friend_id");
	}
		
	}

	public function get_friend_request(Request $request){
	 	$req=$request->getContent();
	 	$json=base64_decode($req);
		$data=json_decode($json,TRUE);	
		$u_id=$data['u_id'];
		$key='friend_request_'.$u_id;
		$requestlist=Redis::HVALS($key);
		foreach($requestlist as $friend){
			$friendArr=json_decode($friend);
			$frData['u_id']=$friendArr->u_id;
			$frData['friend_id']=$friendArr->friend_id;
			$frData['time']=time()-($friendArr->time);
			$result['friend_request'][]=$frData;
		}
		$response=json_encode($result,TRUE);
		return $response;

	}

	public function friend_list(Request $request){
	try{
		$req=$request->getContent();
		$json=base64_decode($req);
	 	//dd($json);
		$data=json_decode($json,TRUE);
		$usefriend=new UserFriendModel();
		$characterModel=new CharacterModel();
		$friend_list=$usefriend->where('u_id',$data['u_id'])->where('friend_status',1)->get();
		$friend_user_ids=[];
		if($friend_list){
			foreach($friend_list as $friend){
				$friend_user_ids[]=$friend['friend_u_id'];
			}
			$result['user_friends']=$characterModel->wherein('u_id',$friend_user_ids)->get();
			$response=json_encode($result,TRUE);
			return $response;
		}
		else {
			return "no friend in list";
		}
	}
		catch(Exception $e){
		 throw new Exception("friend character have error");
		}
	}

	public function addFriend(Request $request){

		$req=$request->getContent();
		$json=base64_decode($req);
	 	$now   = new DateTime;
		$dmy=$now->format( 'Ymd' );
		$datetime=$now->format( 'Y-m-d h:m:s' );
		$data=json_decode($json,TRUE);
		$u_id=$data['u_id'];
		$friend_id=$data['friend_id'];
		$usefriend=new UserFriendModel();
		$usermodel=new UserModel();
		$friend=$usermodel->where('friend_id',$data['friend_id'])->first();
		$friendModel=new UserFriendModel();
		$existFriend=$friendModel->where('u_id',$u_id)->where('friend_u_id',$friend['u_id'])->first();
		if(!$existFriend){
			$friendData['u_id']=$u_id;
			$friendData['friend_u_id']=$friend['u_id'];
			$friendData['friend_status']=1;
			$friendData['updated_at']=$datetime;
			$friendData['createdate']=$datetime;

			$insertData['u_id']=$friend['u_id'];
			$insertData['friend_u_id']=$u_id;
			$insertData['friend_status']=1;
			$insertData['updated_at']=$datetime;
			$insertData['createdate']=$datetime;

			$friendModel->insert($friendData);
			$friendModel->insert($insertData);
			$key='friend_request_'.$u_id;
			Redis::HDEL($key,$friend['u_id']);
			$result['add_friend']=$insertData;
			$response=json_encode($result,TRUE);
			return $response;
		}
		else {
			throw new Exception("this friend has added before");
		}

	}
	
	public function removeFriend(Request $request){
		$req=$request->getContent();
		$json=base64_decode($req);
	 	//dd($json);
		$data=json_decode($json,TRUE);
		$u_id=$data['u_id'];
		$friend_id=$data['friend_id'];
		$usefriend=new UserFriendModel();	
		$friendModel=new UserFriendModel();
		$friend=$friendModel->where('u_id',$u_id)->where('friend_u_id',$friend['u_id'])->first();	
		if($friend){
			$friendModel->where('friend_u_id',$friend['u_id'])->where('u_id',$u_id)->update(['friend_status',2]);
			$friendModel->where('friend_u_id',$u_id)->where('u_id',$friend['u_id'])->update(['friend_status',2]);
			$friend_List=$friendModel->where('u_id',$u_id)->get();
						$result['add_friend']=$friendData;
			$response=json_encode($result,TRUE);
			return $response;
		}
		else{
			throw new Exception("no friend found");
		}
	}

	public function sendCoin(Request $request){
		$req=$request->getContent();
		$json=base64_decode($req);
	 	//dd($json);
		$data=json_decode($json,TRUE);
		$u_id=$data['u_id'];
		$friend_id=$data['friend_id'];
		$usefriend=new UserFriendModel();
		$friend=$usermodel->where('friend_id',$data['friend_id'])->first();
 		$defind=new DefindMstModel();
 		$defindFriend=$defind->where('defind_id',1)->first();
 		$friendcoin=$defindFriend['value2']+$friend['u_coin'];
 		$usefriend->update(['u_coin',$friendcoin]);
 		$now   = new DateTime;
		$dmy=$now->format( 'Ymd' );
 		$key='friend_coin_'.$dmy.'_'.$u_id;
 		$sentCoin=Redis::HEXISTS($key,$friend['u_id']);
 		$sentFriends=Redis::HKEYS($key,$friend['u_id']);
 		if($sentCoin<1&&count($sentFriends)<10){
		Redis::HSET($key,$friend['u_id'],time());
     	$response=json_encode($sentFriends,TRUE);
     	return $response;
 		}
 		else {
 			throw new Exception("you aleady sent to this friend");
 		}
 		
	}
	


	public function reject_request(Request $request){
		$req=$request->getContent();
		$json=base64_decode($req);
	 	//dd($json);
		$data=json_decode($json,TRUE);
		$u_id=$data['u_id'];
		$friend_id=$data['friend_id'];
		$usermodel=new UserModel();
		$friend=$usermodel->where('friend_id',$friend_id)->first();
		$key='friend_request_'.$u_id;
		Redis::HDEL($key,$friend['u_id']);
		$requestlist=Redis::HKEYS($key);
		$response=json_encode($requestlist,TRUE);
		return $response;

	}


 }
