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
use App\UserFriendCoinHistoryModel;
use App\UserFriendCoinReceiveModel;

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
		$req=$request->getContent();
		$json=base64_decode($req);
	 	//dd($json);
		$data=json_decode($json,TRUE);
		$usefriend=new UserFriendModel();
		$characterModel=new CharacterModel();
		$friend_list=$usefriend->where('u_id',$data['u_id'])->where('friend_status',1)->get();
		$friend_user_ids=[];
		if($friend_list){
			$response=json_encode($friend_list,TRUE);
			return $response;
		}
		else {
			return "no friend in list";
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
		$now   = new DateTime;
		$dmy=$now->format( 'Ymd' );
		$datetime=$now->format( 'Y-m-d h:m:s' );
	 	//dd($json);
		$data=json_decode($json,TRUE);
		$u_id=$data['u_id'];
		$friend_id=$data['friend_id'];
		$useModel=new UserModel();	
		$friendModel=new UserFriendModel();
		$friendData=$useModel->where('friend_id',$friend_id)->first();
		$friend=$friendModel->where('u_id',$u_id)->where('friend_u_id',$friendData['u_id'])->first();	
		if($friend){
			$friendModel->where('friend_u_id',$friendData['u_id'])->where('u_id',$u_id)->update(['friend_status'=>2,'updated_at'=>$datetime]);
			$friendModel->where('friend_u_id',$u_id)->where('u_id',$friendData['u_id'])->update(['friend_status'=>2,'updated_at'=>$datetime]);
			$friend_List=$friendModel->where('u_id',$u_id)->get();
			$result['friend_list']=$friendData;
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
		$data=json_decode($json,TRUE);
		if(isset($data['u_id'])&&isset($data['friend_id']))
		{
			$u_id=$data['u_id'];
			$friend_id=$data['friend_id'];
			$usermodel=new UserModel();
			$friendmodel=new UserFriendModel();
			$now   = new DateTime;
			$dmy=$now->format( 'Ymd' );
			$datetime=$now->format( 'Y-m-d h:m:s' );
 			$defind=new DefindMstModel();
 			$friendCoinModel=new UserFriendCoinHistoryModel();
 			$defindFriend=$defind->where('defind_id',1)->first();
			$friend=$usermodel->where('friend_id',$data['friend_id'])->first();
			$user=$usermodel->where('u_id',$u_id)->first();


			$friendmodel->where('u_id',$u_id)->where('friend_u_id',$friend['u_id'])->first();
			if($friendmodel){
 				$sentTo=$friendCoinModel->where('u_id',$u_id)->where('friend_u_id',$friend['u_id'])->where('sent_dmy',$dmy)->get();
 				$sentCount=$friendCoinModel->where('u_id',$u_id)->where('sent_dmy',$dmy)->count();
 	 			if(!$sentTo&&$sentCount<$defindFriend['value1'])
 				{
 					$friendCoin=$defindFriend['value2']+$friend['u_coin'];
 					$userCoin=$defindFriend['value2']+$user['u_coin'];
 					$usermodel->where('u_id',$friend['u_id'])->update(['u_coin'=>$friendCoin,'updated_at'=>$datetime]);
 					$usermodel->where('u_id',$u_id)->update(['u_coin'=>$userCoin,'updated_at'=>$datetime]);
 					$sentData["u_id"]=$u_id;
					$sentData["friend_u_id"]=$friend['u_id'];
					$sentData["fcoin_quanitty"]=$defindFriend['value2'];
					$sentData["fcoin_status"]=1;
					$sentData["sent_dmy"]=$dmy;
					$sentData["update_at"]=$datetime;
					$sentData["createdate"]=$datetime;
					$friendCoinModel->insert($sentData);
     				$response=json_encode($sentData,TRUE);
     			return $response;
 				}
 				else {
 				throw new Exception("you aleady sent to this friend");
 				}
 			}
 			else {
 				throw new Exception("there is some of error of this friend");
 			}
		}
	}

	public function recieveCoinList(Request $request){
		$req=$request->getContent();
		$json=base64_decode($req);
		$data=json_decode($json,TRUE);
		$friendCoinModel=new UserFriendCoinHistoryModel();
		if(isset($data['u_id']))
		{
			$now   = new DateTime;
			$dmy=$now->format( 'Ymd' );
			$datetime=$now->format( 'Y-m-d h:m:s' );
			$u_id=$data['u_id'];
			$coinList=$friendCoinModel->where('u_id',$data['u_id'])->where('fcoin_status',1)->get();
			$final['coin_list']=$coinList;
			$response=json_encode($final,TRUE);
			return $response;
		}
	}
	
	public function recieveCoin(Request $request){
		$req=$request->getContent();
		$json=base64_decode($req);
		$data=json_decode($json,TRUE);
		if(isset($data['u_id'])&&isset($data['friend_id']))
		{
			$u_id=$data['u_id'];
			$friend_id=$data['friend_id'];
			$usermodel=new UserModel();
			$friendmodel=new UserFriendModel();
			$now   = new DateTime;
			$dmy=$now->format( 'Ymd' );
			$datetime=$now->format( 'Y-m-d h:m:s' );
 			$defind=new DefindMstModel();
 			$friendCoinModel=new UserFriendCoinHistoryModel();
 			$receiveCoinModel=new UserFriendCoinReceiveModel();
 			$defindFriend=$defind->where('defind_id',1)->first();
			$friend=$usermodel->where('friend_id',$data['friend_id'])->first();
			$user=$usermodel->where('u_id',$u_id)->first();
			$friendmodel->where('u_id',$u_id)->where('friend_u_id',$friend['u_id'])->first();

			if($friendmodel){
				$sentTo=$friendCoinModel->where('u_id',$friend['u_id'])->where('friend_u_id',$u_id)->first();
				$received=$receiveCoinModel->where('u_id',$u_id)->where('friend_u_id',$friend['u_id'])->where('received_dmy',$dmy)->get();
 				$receivedCount=$friendCoinModel->where('u_id',$u_id)->where('received_dmy',$dmy)->where('received_dmy',1)->count();
 	 			if($sentTo&&!$received&&$receivedCount<$defindFriend['value1'])
 				{	
 					$userCoin=$defindFriend['value2']+$user['u_coin'];
 					$userresult=$usermodel->where('u_id',$u_id)->update(['u_coin'=>$userCoin,'updated_at'=>$datetime]);
 					$friendCoinModel->where('u_id',$friend['u_id'])->where('friend_u_id',$u_id)->update(['fcoin_status'=>2,'received_dmy'=>$dmy,'update_at'=>$datetime,'createdate'=>$datetime]);
 					$recieveData["u_id"]=$u_id;
					$recieveData["friend_u_id"]=$friend['u_id'];
					$recieveData["rcoin_quanitty"]=$defindFriend['value2'];
					$recieveData["rcoin_status"]=1;
		
					$recieveData["update_at"]=$datetime;
					$recieveData["createdate"]=$datetime;
 					$receiveCoinModel->insert($recieveData);
     				$result['received_coin']=$recieveData;
     				$response=json_encode($result,TRUE);
     			return $response;
 				}
 				else {
 				throw new Exception("you aleady sent to this friend");
 				}
		}
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
