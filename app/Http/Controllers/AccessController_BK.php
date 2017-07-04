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
		
		//$data2=json_encode($request->header(),TRUE);
		//dd($data2);

		//Log:info('test access');
		
		$usermodel=new UserModel();
		$characterModel=new CharacterModel();
		$result=[];
		$equipMstModel=new EquipmentMstModel();
		$now   = new DateTime;
		$dmy=$now->format( 'Ymd' );
	//	Redis::connection('default');
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
					$status=$loginTodayArr[0]->status;
					if($logoff!=0){
						$logindata['u_id']=$userData['u_id'];
						$logindata['uuid']=$userData['uuid'];
						$logindata['os']=$userData['os'];
						$logindata['lastlogin']=time(); 
						$logindata['access_token']=$token; 
						$logindata['logoff']=$loginTodayArr[0]->logoff; 
						$logindata['status']=$loginTodayArr[0]->status; ;//online 0, in backend 1, logoff 2 
						$logindata['createdate']=$loginTodayArr[0]->createdate; 
						$loginlist=json_decode($loghistory,TRUE);
						Redis::HSET('login_data',$dmy.$userData['u_id'],json_encode($loginlist,TRUE));
					}
					else {
						throw new Exception("login error");
						}
  					}
				else {
					$logindata['u_id']=$userData['u_id'];
					$logindata['uuid']=$userData['uuid'];
					$logindata['os']=$userData['os'];
					$logindata['lastlogin']=time(); 
					$logindata['access_token']=$token; 
					$logindata['logoff']=0; 
					$logindata['status']=0;//online 0, in backend 1, logof 2 
					$logindata['createdate']=time(); 
					$loginlist[]=$logindata;
			    	Redis::HSET('login_data',$dmy.$userData['u_id'],json_encode($loginlist,TRUE));
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

	public function update(Request $request)
	{
		$req=$request->getContent();
        $json=base64_decode($req);
        $data=json_decode($json,TRUE);
        $usermodel=new UserModel();
        $usermodel->createNew($data);
        $responseData=UserModel::where('u_id',$data['u_id'])->get();

        return json_encode($responseData);
	}

	public function test (Request $request){

/*	 Redis::connection('default');
	 $now   = new DateTime;
	 $dmy=$now->format( 'Ymd' );
	 $u_id="ui100000001";
	 			$loginToday=Redis::HGET('login_data','20170701');
dd($loginToday);
				if($loginToday){
 				$loginTodayArr=json_decode($loginToday);
  					foreach ($loginTodayArr as $key => $value) {
  						echo ($value->u_id);
   						echo ($u_id);
   						if($value->u_id!=$u_id){
   							dd("not same");
   						$loginCount=$userData['u_login_count']+1;
						$usermodel->where('u_id',$u_id)->update(["u_login_count"=>$loginCount]);
 					}
  					}
   				}
		$req=$request->getContent();
		$json=base64_decode($req);
		print_r($json);
		$data=json_decode($json,TRUE);
		Log:info('test access');
		log:info($json);*/


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
 		$req=$request->getContent();
		$json=base64_decode($req);
	 	//dd($json);
		$data=json_decode($json,TRUE);
		
		if(isset($data['uuid'])){
			$u_id=$data['u_id'];
			$now   = new DateTime;
			$dmy=$now->format( 'Ymd' );
			$loginToday=Redis::HGET('login_data',$dmy);
			$result='';
			$logindata['u_id']=$userData['u_id'];
			$logindata['uuid']=$userData['uuid'];
			$logindata['os']=$userData['os'];
			$logindata['lastlogin']=time(); 
			$logindata['access_token']=$token; 
			$logindata['logoff']=1; 
			$logindata['status']=$loginTodayArr[0]->status; ;//online 0, in backend 1, logoff 2 
			$logindata['createdate']=$loginTodayArr[0]->createdate; 
			$loginlist=json_decode($loghistory,TRUE);
			Redis::HSET('login_data',$dmy.$userData['u_id'],json_encode($loginlist,TRUE));
			$response=json_encode($loginlist,TRUE);
			return  $response;
	}
	else {
			throw new Exception("oppos, need u_id");

	}
}

