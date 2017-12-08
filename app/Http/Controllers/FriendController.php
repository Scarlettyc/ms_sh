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
		$result['searched_friend']=$friend;
		$response=json_encode($result,TRUE);
		return base64_encode($response);	
	}

	public function suggest_friend(Request $request){

		$req=$request->getContent();
		$json=base64_decode($req);
	 	//dd($json);
		$data=json_decode($json,TRUE);
		$usermodel=new UserModel();
		$lastweek=date('Y-m-d H:i:s', strtotime('last week'));
		$lastweekUser=$usermodel->where('pass_tutorial',1)->where('createdate','>=',$lastweek)->take(10)->get();
		$response=json_encode($lastweekUser,TRUE);
		return  base64_encode($response);
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
				return base64_encode($response);
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
		$characterModel=new CharacterModel();
		$result=[];
		if(isset($requestlist)){
			foreach($requestlist as $friend){
			$friendArr=json_decode($friend);
			$frData['u_id']=$friendArr->u_id;
			$frData['friend_id']=$friendArr->friend_id;
			$frData['time']=time()-($friendArr->time);
			$ch_title=$characterModel->select('ch_title')->where('u_id',$friendArr->u_id)->first();
			$frData['ch_title']=$ch_title['ch_title'];
			$result[]=$frData;
			
		}
	}
		$final['friend_request']=$result;
		$response=json_encode($final,TRUE);
		return base64_encode($response);

	}

	public function friend_list(Request $request){
		$req=$request->getContent();
		$json=base64_decode($req);
	 	//dd($json);
		$data=json_decode($json,TRUE);
		$usefriend=new UserFriendModel();
		$characterModel=new CharacterModel();
		$friend_list=$usefriend->where('u_id',$data['u_id'])->where('friend_status',1)->get();
		$key='friend_request_'.$data['u_id'];
		$requestCount=Redis::HLEN($key);
		$friend_user_ids=[];
		if($friend_list){
			$result['friend_list']=$friend_list;
			$result['requestCount']=$requestCount;
			$response=json_encode($result,TRUE);
			return base64_encode($response);
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
			return base64_encode($response);
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
			$result['friend_list']=$friend_List;
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
 				$sentTo=$friendCoinModel->where('u_id',$u_id)->where('friend_u_id',$friend['u_id'])->where('sent_dmy',$dmy)->first();
 				$sentCount=$friendCoinModel->where('u_id',$u_id)->where('sent_dmy',$dmy)->count();
 	 			if(!isset($sentTo)&&$sentCount<$defindFriend['value1'])
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
					$sentData["updated_at"]=$datetime;
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
				$received=$receiveCoinModel->where('u_id',$u_id)->where('friend_u_id',$friend['u_id'])->where('received_dmy',$dmy)->first();
 				$receivedCount=$friendCoinModel->where('u_id',$u_id)->where('received_dmy',$dmy)->where('received_dmy',1)->count();
 	 			if($sentTo&&!isset($received)&&$receivedCount<$defindFriend['value1'])
 				{	
 					$userCoin=$defindFriend['value2']+$user['u_coin'];
 					$userresult=$usermodel->where('u_id',$u_id)->update(['u_coin'=>$userCoin,'updated_at'=>$datetime]);
 					$friendCoinModel->where('u_id',$friend['u_id'])->where('friend_u_id',$u_id)->update(['fcoin_status'=>2,'received_dmy'=>$dmy,'updated_at'=>$datetime,'createdate'=>$datetime]);
 					$recieveData["u_id"]=$u_id;
					$recieveData["friend_u_id"]=$friend['u_id'];
					$recieveData["rcoin_quanitty"]=$defindFriend['value2'];
					$recieveData["rcoin_status"]=1;
					$recieveData["updated_at"]=$datetime;
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
		$result["friend_request"]=$requestlist;
		$response=json_encode($result,TRUE);
		return $response;

	}
	public function friend_details(Request $request){
		$req=$request->getContent();
		$json=base64_decode($req);
	 	//dd($json);
		$data=json_decode($json,TRUE);
		$u_id=$data['u_id'];
		$friend_id=$data['friend_id'];
		$usermodel=new UserModel();
		$friend=$usermodel->where('friend_id',$friend_id)->first();
		$characterModel=new CharacterModel();
		$friendCharacter=$characterModel->where('u_id',$friend['u_id'])->frist();
		$result["friend_details"]=$friendCharacter;
		$response=json_encode($result,TRUE);
	}

	public function like_friend(Request $request){
		$req=$request->getContent();
		$json=base64_decode($req);
		$data=json_decode($json,TRUE);
		$u_id=$data['u_id'];
		$friend_id=$data['friend_id'];

		$userModel=new UserModel();
		$userFriend=new UserFriendModel();
		$friend=$userModel->select('like_number','u_id')->where('friend_id',$friend_id);
		$likeStatus=$userFriend->select('like_status')->where('u_id',$u_id)->where('friend_u_id',$friend['u_id'])->first();
		if($likeStatus==0){
			$friendLIkeNum=$friend['like_number']+1;
			$userModel->update(['like_number'=>$friendLIkeNum])->where('u_id',$friend['u_id']);
			$userFriend->update(['like_status'=>1])->where('u_id',$u_id)->where('friend_u_id',$friend['u_id']);
			$result['u_id']=$u_id;
			$result['friend_id']=$friend_id;
			$result['like_status']=1;
			$result['like_number']=$friendLIkeNum;
			$response=json_encode($result,TRUE);
			return $response;
		}
		else if($likeStatus==1){
			throw new Exception("you already liked this friend", 1);
		}
	}

	public function sendMessage(Request $request){
		$req=$request->getContent();
		$json=base64_decode($req);
		$data=json_decode($json,TRUE);
		$u_id=$data['u_id'];
		$friend_id=$data['friend_id'];
		$message=$data['message'];
		$useModel=new UserModel();	
		$friend=$usermodel->where('friend_id',$friend_id)->first();
		$key='friend_message'.$u_id.'_'.$friend['u_id'];
		$sendMessage['message']=$message;
		$sendMessage['time']=time();
		$messageJson=json_encode($sendMessage,TRUE);
		Redis::LPUSH($key,$messageJson);
		$result['u_id']=$u_id;
		$result['friend_id']=$friend_id;
		$result['message']=$message;
		$response=json_encode($result,TRUE);
		return $response;
	}

	public function receiveMessage(Request $request){
		$req=$request->getContent();
		$json=base64_decode($req);
		$data=json_decode($json,TRUE);
		$u_id=$data['u_id'];
		$friend_id=$data['friend_id'];
		$useModel=new UserModel();	
		$friend=$usermodel->where('friend_id',$friend_id)->first();
		$key='friend_message_'.$friend['u_id'].'_'.$u_id;
		$redislist=Redis::LRANGE($key,1,10);
		$messageList=json_decode($redislist,TRUE);
		return $messageList;
	}

   public function deleteAllMessageHistory(Request $request){
   		$req=$request->getContent();
		$json=base64_decode($req);
		$data=json_decode($json,TRUE);
		$u_id=$data['u_id'];
		$friend_id=$data['friend_id'];
		$useModel=new UserModel();	
		$friend=$usermodel->where('friend_id',$friend_id)->first();
		$key='friend_message'.$friend['u_id'].'_'.$u_id;
		Redis::LTRIM($key,1,0);
		return 'success delete all message';
   }

   public function friendMatchRequest(Request $request){
   		$req=$request->getContent();
		$json=base64_decode($req);
		$data=json_decode($json,TRUE);
		$u_id=$data['u_id'];
		$friend_id=$data['friend_id'];
		$friend=$usermodel->where('friend_id',$friend_id)->first();
		$dmy=$now->format( 'Ymd' );
		$friend_login=Redis::HGET('login_data',$dmy.$friend['u_id']);
		$status=0;
		if($friend_login['status']==0){
			$result['time']=time();
			$result['status']=0;
			$key="friend_match_".$u_id;
			Redis::HSET($key,$friend['u_id'],$result);
			return 'success send friend match request';
		}
		else {
			throw new Exception("you friend not online", 1);
		}

   }
   public function rejectMatchRequest(Request $request){
   		$req=$request->getContent();
		$json=base64_decode($req);
		$data=json_decode($json,TRUE);
		$u_id=$data['u_id'];
		$friend_id=$data['friend_id'];
		$friend=$usermodel->where('friend_id',$friend_id)->first();
		$key="friend_match_".$friend['u_id'];
		$friend_match=Redis::HGET($key,$u_id);
		$friendList=json_decode($friend_match,TRUE);
		$result['time']=$friendList['time'];
		$result['status']=2;
		$reply=json_decode($result,TRUE);
		Redis::HSET($key,$friend['u_id'],$reply);
		return $reply;

   }

   public function approveMatchRequest(Request $request){
   	  	$req=$request->getContent();
		$json=base64_decode($req);
		$data=json_decode($json,TRUE);
		$u_id=$data['u_id'];
		
		$friend_id=$data['friend_id'];
		$friend=$usermodel->where('friend_id',$friend_id)->first();
		$key="friend_match_".$friend['u_id'];
		$friend_match=Redis::HGET($key,$u_id);
		$friendList=json_decode($friend_match,TRUE);
		$defindModel=new DefindMstModel();
		$defindTime=$defindModel->Where('defind_id',12)->first();
		if(time()-$result['time']>=$defindTime){
			throw new Exception("time out", 1);
		}
		else if($friendList['status']==0) {
		$result['time']=$friendList['time'];
		$result['status']=1;
		$reply=json_decode($result,TRUE);
		Redis::HSET($key,$friend['u_id'],$reply);
		}
		return $reply;
   }


   public function cancelFriendMatch(Request $request){
   	  	$req=$request->getContent();
		$json=base64_decode($req);
		$data=json_decode($json,TRUE);
		$u_id=$data['u_id'];
		
		$friend_id=$data['friend_id'];
		$friend=$usermodel->where('friend_id',$friend_id)->first();
		$key="friend_match_".$u_id;
		$friend_match=Redis::HGET($key,$friend['u_id']);
		$friendList=json_decode($friend_match,TRUE);
		$result['time']=$friendList['time'];
		$result['status']=3;
		$reply=json_decode($result,TRUE);
		Redis::HSET($key,$friend['u_id'],$reply);
		return $reply;
   }

   public function waittingMatch(Request $request){
   		$req=$request->getContent();
		$json=base64_decode($req);
		$data=json_decode($json,TRUE);
		$u_id=$data['u_id'];
		$friend_id=$data['friend_id'];
		$friend=$usermodel->where('friend_id',$friend_id)->first();
		$key="friend_match_".$friend['u_id'];
		$friend_match=Redis::HGET($key,$friend['u_id']);
		$friendList=json_decode($friend_match,TRUE);
		$defindModel=new DefindMstModel();
		$defindTime=$defindModel->Where('defind_id',12)->first();
   	 	while(time()-$friendList['time']<$defindTime){
   	 		$friend_match=Redis::HGET($key,$friend['u_id']);
			$friendList=json_decode($friend_match,TRUE);
			if($friendList['status']!=0){
				break;
			}

   	 	}
   	 	return 'stop wait';
   }


 }
