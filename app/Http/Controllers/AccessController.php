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
use DateTime;
class AccessController extends Controller
{
	public function login(Request $request)
	{
		$req=$request->getContent();
		$json=base64_decode($req);
		$data=json_decode($json,TRUE);

		$usermodel=new UserModel();
		$characterModel=new CharacterModel();
		$result=[];
		$equipMstModel=new EquipmentMstModel();
		$now   = new DateTime;
		$dmy=$now->format( 'Ymd' );
		Redis::connection('default');
		$resourceMst=$equipMstModel::get();
        $result['mst_data']['equip_mst']=$resourceMst;
		if(isset($data["uuid"]))
		{  
			$userData=$data;
			if($usermodel->isExist('uuid',$data['uuid'])>0)
			{
				$userData=$usermodel->where('uuid','=',$data['uuid'])->first();
				$u_id=$userData['u_id'];
				$loginToday=Redis::HGET('login_data',$dmy);
				$loginTodayArr=json_decode($loginToday);
  				foreach ($loginTodayArr as $key => $value) {
  					if($value->u_id!=$u_id){
  						$loginCount=$userData['u_login_count']+1;
						$usermodel->where('u_id',$u_id)->update(["u_login_count"=>$loginCount]);
  					}
  				}
				if($userData['pass_tutorial']&&$characterModel->isExist('ch_id',$userData['ch_id']))
				{	
					$userChar=$characterModel->where('ch_id','=',$userData['ch_id'])->first();
					$result['user_data']['character_info']=$userCharacter;
				}
			}
			else {
				$usermodel->createNew($data);
			}
			$userfinal=$usermodel->where('uuid','=',$data['uuid'])->first();
			$result['user_data']['user_info']=$userfinal;
			date_default_timezone_set("UTC");

			$lastweek=date("Ymd",strtotime("-1 week"));
			$logindata['u_id']=$userfinal['u_id'];
			$logindata['uuid']=$userfinal['uuid'];
			$logindata['os']=$userfinal['os'];
			$logindata['login']=time(); 
			$logindata['logoff']=0; 
			$logindata['status']=0;//online 0, in backend 1, logof 2 
			$logindata['createdate']=time(); 
			Redis::HGET('login_data',$dmy);
			if(Redis::HEXISTS('login_data',$dmy))
			{ 	$loghistory=Redis::HGET('login_data',$dmy);
				$loginlist=json_decode($loghistory,TRUE);
				array_push($loginlist,$logindata);	
			}
			else{
				$loginlist[]=$logindata;

			}
			
		    Redis::HSET('login_data',$dmy,json_encode($loginlist,TRUE));
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

	Redis::connection('default');
	$now   = new DateTime;
	$dmy=$now->format( 'Ymd' );
	$loginUser=Redis::HGET('login_data',$dmy);
	$array=json_decode($loginUser);
	foreach ($array as $key => $value) {
		var_dump($value->u_id);
	}
	

 	}
}
