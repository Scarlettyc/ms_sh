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
		if(isset($data['uuid']))
		{  
			if($data['os']='ios'&&strlen($data['uuid'])==40)
			{
				if($usermodel->isExist('uuid',$data['uuid'])>0)
				{	
					$userData=$usermodel->where('uuid','=',$data['uuid'])->first();
					$u_id=$userData['u_id'];
					$userChar=$characterModel->where('u_id','=',$userData['u_id'])->first();
					if($userData['pass_tutorial']&&$userChar)
					{	
						$result['user_data']['character_info']=$userChar;
						$result['user_data']['equipment_info']=$this->getEquip($userChar);
					}
				}
				else {
						$usermodel->createNew($data);
				}
			}
			else if(strlen($data['uuid'])==37){
				if($usermodel->isExist('uuid',$data['uuid'])>0)
				{	
					$userData=$usermodel->where('uuid','=',$data['uuid'])->first();
					$u_id=$userData['u_id'];
					$userChar=$characterModel->where('u_id','=',$userData['u_id'])->first();
					if($userData['pass_tutorial']&&$userChar)
					{	
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
			
			$userfinal=$usermodel->where('uuid','=',$data['uuid'])->first();
			$userfinal['access_token']=$token;
			$result['user_data']['user_info']=$userfinal;
			date_default_timezone_set("UTC");

			$response=json_encode($result,TRUE);
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

