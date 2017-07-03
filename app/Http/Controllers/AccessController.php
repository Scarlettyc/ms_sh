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
		Log:info($request->header());
		//Log:info('test access');
		
		$usermodel=new UserModel();
		$characterModel=new CharacterModel();
		$result=[];
		$equipMstModel=new EquipmentMstModel();
		$now   = new DateTime;
		$dmy=$now->format( 'Ymd' );
	//	Redis::connection('default');
		$resourceMst=$equipMstModel::get();
        $result['mst_data']['equip_mst']=$resourceMst;
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
					$result['user_data']['character_info']=$userCharacter;
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
					$result['user_data']['character_info']=$userCharacter;
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
			$token='';
				$token=$usermodel->createTOKEN(16);

			$lastweek=date("Ymd",strtotime("-1 week"));
			$logindata['u_id']=$userData['u_id'];
			$logindata['uuid']=$userData['uuid'];
			$logindata['os']=$userData['os'];
			$logindata['login']=time(); 
			$logindata['access_token']=$token; 
			$logindata['logoff']=0; 
			$logindata['status']=0;//online 0, in backend 1, logof 2 
			$logindata['createdate']=time(); 
			$loginToday=Redis::HGET('login_data',$dmy);
			$haveLogin=false;
			$logoff=false;
				if($loginToday){
					$loginTodayArr=json_decode($loginToday);
  					foreach ($loginTodayArr as $key => $value) {
  						if($value->u_id==$u_id&&$value->logoff==0){
  							$haveLogin=true;
  						}
  						else if ($value->u_id==$u_id&&$value->logoff!=0){
  							$logoff=true;
  						}

  					}
  				}
			else {
				$loginlist[]=$logindata;
			    Redis::HSET('login_data',$dmy,json_encode($loginlist,TRUE));
			}
			if($logoff){
				$loghistory=Redis::HGET('login_data',$dmy);
  				$loginlist=json_decode($loghistory,TRUE);
				array_push($loginlist,$logindata);	
			    Redis::HSET('login_data',$dmy,json_encode($loginlist,TRUE));
			}
			if(!$haveLogin){
				$loginCount=$userData['u_login_count']+1;
				$usermodel->where('u_id',$u_id)->update(["u_login_count"=>$loginCount]);
				$loghistory=Redis::HGET('login_data',$dmy);
				$loginlist=json_decode($loghistory,TRUE);
				array_push($loginlist,$logindata);	
				Redis::HSET('login_data',$dmy,json_encode($loginlist,TRUE));
			}

		    $userfinal=$usermodel->where('uuid','=',$data['uuid'])->first();
			$result['user_data']['user_info']=$userfinal;
			date_default_timezone_set("UTC");

			$response=json_encode($result,TRUE);
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
}

