<?php

namespace App\Http\Controllers;
use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\UserModel;
use App\CharacterModel;
use App\UserLoginHistoryModel;
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
		$userLoginHistoryModel=new UserLoginHistoryModel();
		$result=[];

		$usrGem=UserModel::get();
		$result['mst_data']['user_gem']=$userGem;
		Redis::connection('default');
		if(isset($data["uuid"]))
		{  
			$userData=$data;
			if($usermodel->isExist('uuid',$data['uuid'])>0)
			{
				$userData=$usermodel->where('uuid','=',$data['uuid'])->first();
				$result['user_data']['user_info']=$userData;
				if($usermodel['pass_tutorial'])
				{
					$u_id=$userData['u_id'];
					$userChar=$charactermodel->where('u_id','=',$u_id)->first();
					$userLoginCount=UserLoginHistoryModel::where('u_id','=',$u_id)->get()->distinct('loginday')->count('loginday');
					$result['user_data']['login_count']=$userLoginCount+1;
					$result['user_data']['character_info']=$userCharacter;
				}
			}
			else {
				$usermodel->createNew($data);
				$userData=$usermodel->where('uuid','=',$data['uuid'])->first();
				$result['user_data']['user_info']=$userData;

			}

			date_default_timezone_set("UTC");
			$now   = new DateTime;
			$dmy=$now->format( 'Ymd' );

			$logindata['u_id']=$userData['u_id'];
			$logindata['uuid']=$data['uuid']);
			$logindata['os']=$data['os']);
			$logindata['login']="UTC:".time(); 
			$logindata['logoff']=0; 
			$logindata['createdate']="UTC:".time(); 
			$logindata=json_encode($logindata,TRUE);
			if(Redis::HEXISTS('login_data',$dmy))
			{ 	$loghistory=Redis::HGET('login_data',$dmy);
				$logindata=$loghistory.$logindata;
			}
		    Redis::HSET('login_data',$dmy,$logindata);
			$userLoginHistory->createNew($userData);

			$response=json_encode($result,TRUE);
			$response=base64_encode($response);
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
		echo '<h3>Redis Server Connect Success</h3>';
		$now   = new DateTime;
		$dmy=$now->format( 'Ymd' );
		if(!Redis::HEXISTS('login_data','20170623'))
		{ 
			Redis::HSET('login_data','20170623','test2');

		}
       
}
}