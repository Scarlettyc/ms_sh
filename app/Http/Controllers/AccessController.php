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
use App\Http\Controllers\MissionController;
use App\BattleRewardExpModel;
use DB;
use Log;
use DateTime;
class AccessController extends Controller
{

	public function quickLogin(Request $request){
		$req=$request->getContent();
		$json=base64_decode($req);
		$data=json_decode($json,TRUE);
		$now   = new DateTime;
		$dmy=$now->format( 'Ymd' );
		$datetime=$now->format( 'Y-m-d h:m:s' );
		$usermodel=new UserModel();
		$characterModel=new CharacterModel();

		if($data['uuid'])
		{  	if(($data['os']='ios'&&strlen($data['uuid'])==40)||($data['os']='android'&&strlen($data['uuid'])==37))
			{		
					$userData=$usermodel->createNew($data);	
			}
		}
		else {
			throw new Exception("oppos, give me a correct uuid");
		}
		$redis_login=Redis::connection('default');
		$userfinal=$usermodel->where('u_id',$userData['u_id'])->first();
		$token=$usermodel->createTOKEN(16);
					$logindata['u_id']=$userData['u_id'];
					$logindata['uuid']=$userData['uuid'];
					$logindata['os']=$data['os'];
					$logindata['lastlogin']=time(); 
					$logindata['access_token']=$token;
					$logindata['logoff']=0; 
					$logindata['status']=0; ;//online 0, in backend 1, logoff 2 
					$logindata['createdate']= time();
					$loginlist=json_encode($logindata,TRUE);
					$redis_login->HSET('login_data',$userData['u_id'],$loginlist);
			$haveChar=$characterModel->where('u_id',$userData['u_id'])->count();
			$result['u_id']=$userfinal['u_id'];;
			$result['access_token']=$token;
			$result['email']=$userfinal['email'];
			$result['fb_id']=$userfinal['fb_id'];
			$result['pass_tutorial']=$userfinal['pass_tutorial'];
			$result['u_vip_lv']=$userfinal['u_vip_lv'];
			$result['u_login_count']=1;
			$result['uuid']=$userfinal['uuid'];
			$result['u_get_reward']=0;
			$result['haveChar']=$haveChar;
			
			$response=json_encode($result,TRUE);
			return  base64_encode($response);
		
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
		$mission=new MissionController();
		$result=[];
		$now   = new DateTime;
		$dmy=$now->format( 'Ymd' );
	    $redis_login=Redis::connection('default');
        $userData=$data;
		if(isset($data['user_name'])&&isset($data['password']))
		{  	
			if(strpos($data['user_name'],'@')){ 
				$userData=$usermodel->where('email','=',$data['user_name'])->
				where('password',$data['password'])->first();

			}
			else {
				$userData=$usermodel->where('friend_id','=',$data['user_name'])->
				where('password',$data['password'])->first();
			}
		}
		else if($data['fb_id']){
			$userData=$usermodel->where('fb_id','=',$data['fb_id'])->first();
		}
		else {
			throw new Exception("no info of this account");	
		}
		if(isset($userData)){
		
			$u_id=$userData['u_id'];	
			$loginToday=$redis_login->HGET('login_data',$userData['u_id']);
			$logoff=0;
			$token='';
			$firstLogin=$userData['u_get_reward'];
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
					$loginlist=json_encode($logindata,TRUE);
				}
			// 	$missionKey='mission_daily_'.$dmy.'_'.$u_id;
			// $missionRecord=$redis_login->HGET($missionKey,1);
			// if(!$missionRecord){
			// 	$mission->archiveMission(1,$u_id,1);
			// }
			$spentKey='daily_spend_'.$dmy;
			$spendRecord=$redis_login->HGET($spentKey,$u_id);
			if(!$spendRecord){
				$spend['coin']=0;
				$spend['gem']=0;
				$spendjson=json_encode($spend);
				$redis_login->HSET($spentKey,$u_id,$spendjson);
			}
			
			$userfinal=$usermodel->where('u_id',$userData['u_id'])->first();
			$charData=$characterModel->select('ch_img','w_bag_id')->where('u_id',$userData['u_id'])->first();
			$equ_data=DB::table('User_Baggage_Eq')
					->join('Equipment_mst','Equipment_mst.equ_id','=','User_Baggage_Eq.b_equ_id')
					->select('User_Baggage_Eq.b_equ_rarity as item_rarity','Equipment_mst.equ_code','Equipment_mst.equ_lv')
					->first();

			$result['u_id']=$userfinal['u_id'];
			$result['ch_img']=$charData['charData'];
			$result['equ_id']=$equ_data['equ_id'];
			$result['item_rarity']=$equ_data['item_rarity'];
			$result['equ_lv']=$equ_data['equ_lv'];
			$result['access_token']=$token;
			$result['email']=$userfinal['email'];
			$result['fb_id']=$userfinal['fb_id'];
			$result['pass_tutorial']=$userfinal['pass_tutorial'];
			$result['u_vip_lv']=$userfinal['u_vip_lv'];
			$result['u_login_count']=$userfinal['u_login_count'];
			$result['uuid']=$userfinal['uuid'];
			$result['get_reward']=$userData['u_get_reward'];
			$result['haveChar']=$haveChar;
			$redis_login->HSET('login_data',$userData['u_id'],$loginlist);
			$response=json_encode($result,TRUE);

			return  base64_encode($response);
		}
		else {

			throw new Exception("no available account");
		}
	}

	public function showStatus(Request $request){
		$req=$request->getContent();
		$json=base64_decode($req);
		$data=json_decode($json,TRUE);
		$userModel=new UserModel();
		$charModel=new CharacterModel();
		$u_id=$data['u_id'];
		$userMoney=$userModel->select('u_id','u_coin','u_gem','profile_img')->where('u_id',$u_id)->first();
		$userDetails=$charModel->select('ch_img','ch_title','ch_lv','ch_exp','ch_ranking')->where('u_id',$u_id)->first();
		$result['u_id']=$userMoney['u_id'];
		$result['u_coin']=$userMoney['u_coin'];
		$result['u_gem']=$userMoney['u_gem'];
		$result['profile_img']=$userMoney['profile_img'];
		$result['ch_img']=strval($userDetails['ch_img']);
		$result['ch_title']=$userDetails['ch_title'];
		$result['ch_lv']=$userDetails['ch_lv'];
		$result['ch_exp']=$userDetails['ch_exp'];
		$result['ch_ranking']=$userDetails['ch_ranking'];
		$response=json_encode($result,TRUE);
		
		 return base64_encode($response);
	}

	public function test (Request $request){
 	phpinfo();
		// return view('testview');
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
	 	$now   = new DateTime;
		$dmy=$now->format( 'Ymd' );
		$data=json_decode($json,TRUE);
		$redis_login=Redis::connection('default');
		$datetime=$now->format( 'Y-m-d h:m:s' );
		$usermodel=new UserModel();
       
		if(isset($data['u_id'])){
			$u_id=$data['u_id'];
			$redis= Redis::connection('default');
        	$loginToday=$redis->HGET('login_data',$u_id);
        	$loginTodayArr=json_decode($loginToday);
			$result='';
			$logindata['u_id']=$data['u_id'];
			$logindata['uuid']=$loginTodayArr->uuid;
			$logindata['os']=$loginTodayArr->os;
			$logindata['lastlogin']=$loginTodayArr->lastlogin; 
			$logindata['access_token']=$data['access_token'];
			$logindata['logoff']=time(); 
			$logindata['status']=2; ;//online 0, in backend 1, logoff 2 
			$logindata['createdate']=$loginTodayArr->createdate; 
			$loginlist=json_encode($logindata,TRUE);
			$redis_login->HSET('login_data',$u_id,$loginlist);
			$usermodel->where('u_id',$u_id)->update(['updated_at'=>$datetime]);
			$response="success logout";
			return  base64_encode($response);
		}
	}
}

