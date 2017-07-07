<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\Luck_draw_rewardsModel;
use App\CharacterModel;
use App\UserModel;
use App\UserBaggageModel;
use Exception;
use Illuminate\Support\Facades\Redis;
use DateTime;
class LuckdrawController extends Controller
{
 	public function draw(Request $request){
		$req=$request->getContent();
		$json=base64_decode($req);
	 	//dd($json);
		$data=json_decode($json,TRUE);
		// Redis::connection('default');
		$now   = new DateTime;
		$date=$now->format( 'Y-m-d h:m:s' );
		$dmy=$now->format( 'Ymd' );
		$gotToday=Redis::HGET('luckdraw',$dmy.$data['u_id']);
		$result=[];
		if($gotToday){
			$result['luckdraw']['timeuntil']=time()-$gotToday['createtime'];
		}
		else {
		   $luckdraw=new Luck_draw_rewardsModel();
		   $characterModel=new CharacterModel();
		   $baggageModel=new UserBaggageModel();
		   $chardata=$characterModel->where('u_id',$data['u_id'])->first();	   
		   $rate=rand(1, 10000);
		   $drawresult=$luckdraw->where('start_date','<=',$date)->where('end_date','>=',$date)->where('user_lv_from','<=',$chardata['ch_lv'])->where('user_lv_to','>=',$chardata['ch_lv'])->where('star_from','<=',$chardata['ch_star'])->where('star_to','>=',$chardata['ch_star'])->where('star_lv_from','<=',$chardata['ch_star_lv'])->where('star_lv_to','>=',$chardata['ch_star_lv'])->where('rate_from','<=',$rate)->where('rate_to','>=',$rate)->first();
		   if($drawresult){
		   $draw['u_id']=$data['u_id'];
		   $draw['item_org_id']=$drawresult['item_org_id'];
		   $draw['item_quantity']=$drawresult['item_quantity'];
		   $draw['item_type']=$drawresult['item_type'];
		   $draw['createtime']=time();
		   Redis::HSET('luckdraw',$dmy.$data['u_id'],json_encode($draw,TRUE));
		   $result['luckdraw']=$draw;
		   $baggageModel->updatebaggage($data['u_id'],$drawresult['item_type'],$drawresult['item_org_id'],$drawresult['item_quantity']);
			}
			else{
				throw new Exception("sorry, no avaliable prize");
			}
		}
		$response=json_encode($result,TRUE);
 	    return $response;
 	}

 	public function buydraw(Request $request){
 		$req=$request->getContent();
		$json=base64_decode($req);
	 	//dd($json);
		$data=json_decode($json,TRUE);
 		//dd($data);
		$usermodel=new UserModel();
		$baggageModel=new UserBaggageModel();
		$userData=$usermodel->where('u_id',$data['u_id'])->first();

		$characterModel=new CharacterModel();
		$chardata=$characterModel->where('u_id',$data['u_id'])->first();
		$luckdraw=new Luck_draw_rewardsModel();
		$now   = new DateTime;
		$result=[];
		$date=$now->format( 'Y-m-d h:m:s' );
		$dateKey=$now->format( 'Y-m-d:h:m:s' );
		$result=[];
		if($data['draw_type']==1)
		{
			$rate=rand(1, 10000);
		 	$drawresult=$luckdraw->where('start_date','<=',$date)->where('end_date','>=',$date)->where('user_lv_from','<=',$chardata['ch_lv'])->where('user_lv_to','>=',$chardata['ch_lv'])->where('star_from','<=',$chardata['ch_star'])->where('star_to','>=',$chardata['ch_star'])->where('star_lv_from','<=',$chardata['ch_star_lv'])->where('star_lv_to','>=',$chardata['ch_star_lv'])->where('rate_from','<=',$rate)->where('rate_to','>=',$rate)->where('draw_coin','<',$userData['u_coin'])->first();
		 	
		 	if($drawresult){
		 		$draw['u_id']=$data['u_id'];
		   		$draw['item_org_id']=$drawresult['item_org_id'];
		   		$draw['item_quantity']=$drawresult['item_quantity'];
		   		$draw['item_type']=$drawresult['item_type'];
		   		$draw['draw_coin']=$drawresult['draw_coin'];
		  		$draw['createtime']=time();
		   		Redis::HSET('luckdraw',$dateKey.$data['u_id'].'ac',json_encode($draw,TRUE));
		   		$result['luckdraw']=$draw;
		   		$userCoin=$userData['u_coin']-$drawresult['draw_coin'];
		   	 	$usermodel->where('u_id',$data['u_id'])->update(["u_coin"=>$userCoin]);

		 	}
		 	else {
		 		throw new Exception("sorry, you dont' have enought coin");

		 	}
		}
		else if ($data['draw_type']==2){
					 $rate=rand(5000, 10000);
					 $drawresult=$luckdraw->where('start_date','<=',$date)->where('end_date','>=',$date)->where('user_lv_from','<=',$chardata['ch_lv'])->where('user_lv_to','>=',$chardata['ch_lv'])->where('star_from','<=',$chardata['ch_star'])->where('star_to','>=',$chardata['ch_star'])->where('star_lv_from','<=',$chardata['ch_star_lv'])->where('star_lv_to','>=',$chardata['ch_star_lv'])->where('rate_from','<=',$rate)->where('rate_to','>=',$rate)->where('draw_gem','<',$userData['u_gem'])->first();
					 if($drawresult){
					    $draw['u_id']=$data['u_id'];
		   				$draw['item_org_id']=$drawresult['item_org_id'];
		   				$draw['item_quantity']=$drawresult['item_quantity'];
		   				$draw['item_type']=$drawresult['item_type'];
		   				$draw['draw_gem']=$drawresult['draw_gem'];
		  				$draw['createtime']=time();
		   				Redis::SGET('luckdraw',$date.$data['u_id'].'ag'.time(),json_encode($draw,TRUE));
		   				$result['luckdraw']=$draw;
		   				$userGem=$userData['u_gem']-$drawresult['draw_gem'];
		   	 			$usermodel->where('u_id',$data['u_id'])->update(["u_gem"=>$userGem]);

					 }
					 else {
		 				throw new Exception("sorry, you dont' have enought gem");

		 				}
			}
					$baggageModel->updatebaggage($data['u_id'],$drawresult['item_type'],$drawresult['item_org_id'],$drawresult['item_quantity']);
					$response=json_encode($result,TRUE);
 	    			return $response;
			
		}

 }
