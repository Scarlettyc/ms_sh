<?php

namespace App\Http\Controllers;
use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\UserModel;
use App\CharacterModel;
use App\EquipmentMstModel;
use Exception;
use App\Exceptions\Handler;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Redis;
use Carbon\Carbon;
use Log;
use DateTime;
class AccessController extends Controller
{
	public function quickLogin(Request $request){
		$req=$request->getContent();
		$json=base64_decode($req);
		$data=json_decode($json,TRUE);
		$dmy=$now->format( 'Ymd' );
		$datetime=$now->format( 'Y-m-d h:m:s' );
		$usermodel=new UserModel();
		if(isset($data['uuid']))
		{  	if(($data['os']='ios'&&strlen($data['uuid'])==40)||($data['os']='android'&&strlen($data['uuid'])==37))
			{
				if($usermodel->isExist('uuid',$data['uuid'])>0)
				{	$usermodel->where('uuid',$data['uuid'])->update(['uuid'=>0,'updated_at'=>$datetime]);
					
				}
					$usermodel->createNew($data);
			}
		}
		else {
			throw new Exception("oppos, give me a correct uuid");
		}
		
	}

	public function login(Request $request)
	{
		$req=$request->getContent();
		$json=base64_decode($req);
	 	//dd($json);
		$data=json_decode($json,TRUE);
		$usermodel=new UserModel();
		$characterModel=new CharacterModel();
		$equipmentModel=new EquipmentMstModel();
		$result=[];
		$now   = new DateTime;
		$dmy=$now->format( 'Ymd' );
	    Redis::connection('default');
        $userData=$data;
		if(isset($data['email'])&&isset($data['password']))
		{  
			$userData=$usermodel->where('email','=',$data['email'])->
			where('password',md5($data['password']))->first();
		}
		else if(isset($data['fb_id'])){
			$userData=$usermodel->where('fb_id','=',$data['fb_id'])->first();
		}
		else if (isset($data['friend_id'])&&isset($data['password']){
			$userData=$usermodel->where('friend_id','=',$data['friend_id'])->
			where('password',md5($data['password']))->first();

		}
		else {
			throw new Exception("no info of this account");
			
		}
		if(isset($userData)){
		
		$u_id=$userData['u_id'];
		$userChar=$characterModel->where('u_id','=',$userData['u_id'])->first();
		if($userData['pass_tutorial']&&$userChar)
		{	
			$result['user_data']['character_info']=$userChar;
			$result['user_data']['equipment_info']=$this->getEquip($userChar);
		}
				
			$loginToday=Redis::HGET('login_data',$dmy.$userData['u_id']);
			$logoff=0;
			$token='';
			if($loginToday){
				$loginTodayArr=json_decode($loginToday);
				$token=$usermodel->createTOKEN(16);
				$status=$loginTodayArr->status;
				$logoff=$loginTodayArr->logoff;
				if($logoff!=0){
					$logindata['u_id']=$userData['u_id'];
					$logindata['uuid']=$userData['uuid'];
					$logindata['os']=$userData['os'];
					$logindata['lastlogin']=time(); 
					$logindata['access_token']=$token; 
					$logindata['logoff']=0; 
					$logindata['status']=0; ;//online 0, in backend 1, logoff 2 
					$logindata['createdate']=$loginTodayArr->createdate; 
					$loginlist=json_encode($logindata,TRUE);
					Redis::HSET('login_data',$dmy.$userData['u_id'],$loginlist);
				}
					else {
						throw new Exception("login error!");
					}
				}
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
					$datetime=$now->format( 'Y-m-d h:m:s' );
				}
			
			$userfinal=$usermodel->where('u_id','=',$userData['u_id'])->first();
			$userfinal['access_token']=$token;
			$account['email']=$userfinal['email'];
			$account['fb_id']=$userfinal['fb_id'];
			$account['friend_id']=$userfinal['friend_id'];
			$result['user_data']['user_info']=$userfinal;
			$result['user_data']['account_info']=$account;
			date_default_timezone_set("UTC");

			$response=json_encode($result,TRUE);
			return  $response;
		}
		else {

			throw new Exception("no available account");
		}
		
	}

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

