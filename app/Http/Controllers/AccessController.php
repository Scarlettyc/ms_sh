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

				if($userData['pass_tutorial']&&$characterModel->isExist('ch_id',$userData['ch_id']))
				{	
					$userChar=$characterModel->where('ch_id','=',$userData['ch_id'])->first();
					$result['user_data']['character_info']=$userCharacter;
				}
			}
			else {
				$usermodel->createNew($data);
			}

			$lastweek=date("Ymd",strtotime("-1 week"));
			$logindata['u_id']=$userData['u_id'];
			$logindata['uuid']=$userData['uuid'];
			$logindata['os']=$userData['os'];
			$logindata['login']=time(); 
			$logindata['logoff']=0; 
			$logindata['status']=0;//online 0, in backend 1, logof 2 
			$logindata['createdate']=time(); 
			$loginToday=Redis::HGET('login_data',$dmy);
				if($loginToday){
					$loginTodayArr=json_decode($loginToday);
  					foreach ($loginTodayArr as $key => $value) {
  						if($value->u_id!=$u_id){
  						$loginCount=$userData['u_login_count']+1;
						$usermodel->where('u_id',$u_id)->update(["u_login_count"=>$loginCount]);
						$loghistory=Redis::HGET('login_data',$dmy);
						$loginlist=json_decode($loghistory,TRUE);
						array_push($loginlist,$logindata);	
						Redis::HSET('login_data',$dmy,json_encode($loginlist,TRUE));
  						}
  						else if($value->logoff!=0){
  							$loghistory=Redis::HGET('login_data',$dmy);
  							$loginlist=json_decode($loghistory,TRUE);
							array_push($loginlist,$logindata);	
			   				Redis::HSET('login_data',$dmy,json_encode($loginlist,TRUE));
  						}
  					}
  				}
			else {
				$loginlist[]=$logindata;
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

	Redis::connection('default');
	$now   = new DateTime;
	$dmy=$now->format( 'Ymd' );
	$u_id="ui100000001";
				$loginToday=Redis::HGET('login_data',$dmy);
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
	

 	}
}
