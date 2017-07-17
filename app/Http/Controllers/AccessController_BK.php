<?php

namespace App\Http\Controllers;
use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\UserModel;
use App\CharacterModel;
use App\EquipmentMstModel;
use App\Login_rewardsModel;
use App\UserBaggageModel;
use Exception;
use App\Exceptions\Handler;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Redis;
use Carbon\Carbon;
use Log;
use DateTime;
class AccessController extends Controller
{
	public function login(Request $request)
	{
		$req=$request->getContent();
		$json=base64_decode($req);
	 	//dd($json);
		$data=json_decode($json,TRUE);
		$usermodel=new UserModel();
		$characterModel=new CharacterModel();
		$result=[];
		$equipMstModel=new EquipmentMstModel();
		$logindRewardsModel=new Login_rewardsModel();
		$userBaggageModel=new UserBaggageModel();
		$now   = new DateTime;
		$dmy=$now->format( 'Ymd' );
	    Redis::connection('default');
        $userData=$data;
		if(isset($data['uuid']))
		{  
			if($data['os']='ios'&&strlen($data['uuid'])==40)
			{
				if($usermodel->isExist('uuid',$data['uuid'])>0)
				{	
					$userData=$usermodel->where('uuid','=',$data['uuid'])->first();
					$u_id=$userData['u_id'];

					if($userData['pass_tutorial']&&$characterModel->isExist('ch_id',$userData['ch_id']))
					{	
						$userChar=$characterModel->where('ch_id','=',$userData['ch_id'])->first();
						$result['user_data']['character_info']=$userChar;
						$result['user_data']['equipment_info']=$this->getEquip($userChar);
					}


				//	dd($result);
				}
				else {

						$usermodel->createNew($data);
				//	dd($data);
				}
			}
			else if(strlen($data['uuid'])==37){
				if($usermodel->isExist('uuid',$data['uuid'])>0)
				{	
					$userData=$usermodel->where('uuid','=',$data['uuid'])->first();
					$u_id=$userData['u_id'];

					if($userData['pass_tutorial']&&$characterModel->isExist('ch_id',$userData['ch_id']))
					{	
						$userChar=$characterModel->where('ch_id','=',$userData['ch_id'])->first();
						$result['user_data']['character_info']=$userChar;
						$result['user_data']['equipment_info']=$this->getEquip($userChar);
					}
				}
				else {
						$usermodel->createNew($data);
				}

			}
			else{

			throw new Exception("oppos, give me a correct uuid");
			$response = [
			'status' => 'wrong',
			'error' => "please send uuid",
			];

			}


			$lastweek=date("Ymd",strtotime("-1 week"));

			$loginToday=Redis::HGET('login_data',$dmy.$userData['u_id']);
			$haveLogin=false;
			$logoff=false;
			$token='';
				if($loginToday){
					$loginTodayArr=json_decode($loginToday);
					$token=$usermodel->createTOKEN(16);

					//dd($loginTodayArr);
					$status=$loginTodayArr->status;
					$logoff=$loginTodayArr->logoff;
					// if($logoff!=0){
						$logindata['u_id']=$userData['u_id'];
						$logindata['uuid']=$userData['uuid'];
						$logindata['os']=$userData['os'];
						$logindata['lastlogin']=time(); 
						$logindata['access_token']=$token; 
						$logindata['logoff']=0; 
						$logindata['status']=0; ;//online 0, in backend 1, logoff 2 
						$logindata['createdate']=$loginTodayArr->createdate; 
						$loginlist=json_encode($logindata,TRUE);
						//Redis::HSET('login_data',$dmy.$userData['u_id'],$loginlist);
					}
					// else {
					// 	throw new Exception("login error");
					// 	}
  			// 		}
				else {
					$token=$usermodel->createTOKEN(16);
					$logindata['u_id']=$userData['u_id'];
					$logindata['uuid']=$userData['uuid'];
					$logindata['os']=$userData['os'];
					$logindata['lastlogin']=time(); 
					$logindata['access_token']=$token; 
					$logindata['logoff']=0; 
					$logindata['status']=0;//online 0, in backend 1, logof 2 
					$logindata['createdate']=time(); 
					$loginCount=$userData['u_login_count']+1;

					$datetime=$now->format( 'Y-m-d h:m:s' );
					$loginrewards=$logindRewardsModel->where('days',$loginCount)
					->where('start_date','<',$datetime)
					->where('end_date','>',$datetime)
					->first();

					if($loginrewards['item_type']==1&&$loginrewards['item_org_id']==1){
						$userCoin=$userData['u_coin']+$loginrewards['item_quantity'];
						$usermodel->where('u_id',$u_id)->update(["u_coin"=>$userCoin]);
					}
					else if($loginrewards['item_type']==3){
						for($i=0;$i<$loginrewards['item_quantity'];$i++){
						$baggage['u_id']=$userData['u_id'];
						$baggage['item_type']=$loginrewards['item_type'];
						$baggage['item_org_id']=$loginrewards['item_org_id'];
						$baggage['item_quantity']=1;
						$baggage['status']=0;
						$baggage['createdate']=$datetime;
						$userBaggageModel->insert($baggage);
						}
					}
					else{
						$hasItem=$userBaggageModel->where('u_id',$userData['u_id'])->where('item_org_id',$loginrewards['item_org_id'])->where('item_type',$loginrewards['item_type'])
						->first();
						$baggage['u_id']=$userData['u_id'];
						$baggage['item_type']=$loginrewards['item_type'];
						$baggage['item_org_id']=$loginrewards['item_org_id'];
						if($hasItem){
							$baggage['item_quantity']=$hasItem['item_quantity']+$loginrewards['item_quantity'];
						}
						else{
							$baggage['item_quantity']=$loginrewards['item_quantity'];

						}
						$baggage['status']=0;
						$baggage['createdate']=$datetime;
						$userBaggageModel->insert($baggage);
					}
					$reward_history['u_id']=$userData['u_id'];
					$reward_history['item_type']=$loginrewards['item_type'];
					$reward_history['item_type']=$loginrewards['item_org_id'];
					$reward_history['item_quantity']=$loginrewards['item_quantity'];
					$reward_history['login_count']=$loginCount;
					$reward_history['createtime']=time(); 


					Redis::LPUSH('reward_history',json_encode($reward_history,TRUE));

					$usermodel->where('u_id',$u_id)->update(["u_login_count"=>$loginCount]);
			    	Redis::HSET('login_data',$dmy.$userData['u_id'],json_encode($logindata,TRUE));
			    	$result['user_data']['login_reward']=$reward_history;
				}
			
			$userfinal=$usermodel->where('uuid','=',$data['uuid'])->first();
			$userfinal['access_token']=$token;
			$result['user_data']['user_info']=$userfinal;
			date_default_timezone_set("UTC");

			$response=json_encode($result,TRUE);
			 //Log:info("user_info".$result);
			//$response=base64_encode($response);
		}
		else {

			throw new Exception("oppos, you nee Need UUId");
			$response = [
			'status' => 'wrong',
			'error' => "please send uuid",
			];
		}
		return  $response;
	}

	// public function update(Request $request)
	// {
	// 	$req=$request->getContent();
 //        $json=base64_decode($req);
 //        $data=json_decode($json,TRUE);
 //        $usermodel=new UserModel();
 //        $usermodel->createNew($data);
 //        $responseData=UserModel::where('u_id',$data['u_id'])->get();

 //        return json_encode($responseData);
	// }

	public function test (Request $request){
 	phpinfo();
 	}
 	private function getEquip($userChar){
 		$equipMstModel=new EquipmentMstModel();
 		$w_id_l=$userChar['w_id_l'];
 		$w_id_r=$userChar['w_id_r'];
 		$m_id=$userChar['m_id'];
 		$equ_id_1=$userChar['equ_id_1'];
 		$equ_id_2=$userChar['equ_id_2'];
 		$equ_id_3=$userChar['equ_id_3'];
        $equipdata=$equipMstModel->whereIn('equ_id',[$w_id_l,$w_id_r,$m_id,$equ_id_1,$equ_id_2,$equ_id_3])->get();
        return $equipdata;
 	} 
 	public function logout(Request $request){
 		$header=$request->header('Content-Type');
 		$req=$request->getContent();
		$json=base64_decode($req);
	 	//dd($json);
		$data=json_decode($json,TRUE);
		//dd($data);
		if(isset($data['u_id'])){
			$u_id=$data['u_id'];
			$now   = new DateTime;
			$dmy=$now->format( 'Ymd' );
			$loginToday=Redis::HGET('login_data',$dmy.$u_id);
			$loginTodayArr=json_decode($loginToday);	
			$result='';
			$logindata['u_id']=$data['u_id'];
			$logindata['uuid']=$loginTodayArr->uuid;
			$logindata['os']=$loginTodayArr->os;
			$logindata['lastlogin']=$loginTodayArr->lastlogin; 
			$logindata['access_token']=$loginTodayArr->access_token; 
			$logindata['logoff']=time(); 
			$logindata['status']=2; ;//online 0, in backend 1, logoff 2 
			$logindata['createdate']=$loginTodayArr->createdate; 
			$loginlist=json_encode($logindata,TRUE);
			Redis::HSET('login_data',$dmy.$u_id,$loginlist);
			$response="success logout";
			return  $response;
	}
	else {
			throw new Exception("oppos, need u_id");
	}
}
}

