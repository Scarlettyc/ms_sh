<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\Luck_draw_rewardsModel;
use App\CharacterModel;
use App\UserModel;
use Exception;

use DateTime;
class LuckdrawController extends Controller
{
 	public function draw(Request $request){
 		$req=$request->getContent();
		$json=base64_decode($req);
	 	//dd($json);
		$data=json_decode($json,TRUE);
		Redis::connection('default');
		$now   = new DateTime;
		$date=$now->format( 'Y-m-d h:m:s' );
		$gotToday=Redis::HGET('luckdraw',$dmy.$data['u_id']);
		$result=[];
		if($gotToday){
			$result['luckdraw']['timeuntil']=time()-$gotToday['createtime'];
		}
		else {
		   $luckdraw=new Luck_draw_rewardsModel();
		   $characterModel=new CharacterModel();
		   $chardata=$characterModel->where('u_id',$data['u_id'])->first();
		   $rate=rand(1, 10000);
		   $luckdraw->where('start_date','<=',$date)->where('end_date','>=',$date)->where('user_lv_from','<=',$chardata['ch_lv'])->where('user_lv_to','>=',$chardata['ch_lv'])->where('star_from','<=',$chardata['ch_star'])->where('star_to','>=',$chardata['ch_star'])->where('star_from','<=',$chardata['ch_star'])->where('star_to','>=',$chardata['ch_star'])->where('star_lv_from','<=',$chardata['ch_star_lv'])->where('rate_from','>=',$rate)->where('star_lv_to','>=',$rate)->first();
		   $draw['u_id']=$data['u_id'];
		   $draw['item_org_id']=$luckdraw['item_org_id'];
		   $draw['item_quantity']=$luckdraw['item_quantity'];
		   $draw['item_type']=$luckdraw['item_type'];
		   $draw['createtime']=time();
		   Redis::SGET('luckdraw',$dmy.$data['u_id'],$json_encode($draw,TRUE));
		   $result['luckdraw']=$draw;
		}
		$response=json_encode($result,TRUE);
 	    return $response;
 	}

 	public function buydraw(Request $request){
 		$req=$request->getContent();
		$json=base64_decode($req);
	 	//dd($json);
		$data=json_decode($json,TRUE);
		$usermodel=new UserModel();
		$userData=$usermodel->where('u_id',$data['u_id'])->first();
		$characterModel=new CharacterModel();
		$chardata=$characterModel->where('u_id',$data['u_id'])->first();
		$luckdraw=new Luck_draw_rewardsModel();
		$now   = new DateTime;
		$date=$now->format( 'Y-m-d h:m:s' );
		if($data['draw_type']==1)
		{
 		 $rate=rand(1, 10000);
		 $luckdraw->where('start_date','<=',$date)->where('end_date','>=',$date)->where('user_lv_from','<=',$chardata['ch_lv'])->where('user_lv_to','>=',$chardata['ch_lv'])->where('star_from','<=',$chardata['ch_star'])->where('star_to','>=',$chardata['ch_star'])->where('star_from','<=',$chardata['ch_star'])->where('star_to','>=',$chardata['ch_star'])->where('star_lv_from','<=',$chardata['ch_star_lv'])->where('rate_from','>=',$rate)->where('star_lv_to','>=',$rate)->where('draw_price','<',$userData['u_coin'])->first();
		 	
		 	if($luckdraw){
		 		$draw['u_id']=$data['u_id'];
		   		$draw['item_org_id']=$luckdraw['item_org_id'];
		   		$draw['item_quantity']=$luckdraw['item_quantity'];
		   		$draw['item_type']=$luckdraw['item_type'];
		  		$draw['createtime']=time();
		   		Redis::SGET('luckdraw',$date.$data['u_id'].'ac',$json_encode($draw,TRUE));
		   		$result['luckdraw']=$draw;
		 	}
		 	else {
		 		throw new Exception("sorry, you dont' have enought coin");

		 	}
		}
		else if ($data['draw_type']=1){
					 $rate=rand(5000, 10000);
					 $luckdraw->where('start_date','<=',$date)->where('end_date','>=',$date)->where('user_lv_from','<=',$chardata['ch_lv'])->where('user_lv_to','>=',$chardata['ch_lv'])->where('star_from','<=',$chardata['ch_star'])->where('star_to','>=',$chardata['ch_star'])->where('star_from','<=',$chardata['ch_star'])->where('star_to','>=',$chardata['ch_star'])->where('star_lv_from','<=',$chardata['ch_star_lv'])->where('rate_from','>=',$rate)->where('star_lv_to','>=',$rate)->where('draw_gem','<',$userData['u_gem'])->first();
					 if($luckdraw){
					    $draw['u_id']=$data['u_id'];
		   				$draw['item_org_id']=$luckdraw['item_org_id'];
		   				$draw['item_quantity']=$luckdraw['item_quantity'];
		   				$draw['item_type']=$luckdraw['item_type'];
		  				$draw['createtime']=time();
		   				Redis::SGET('luckdraw',$date.$data['u_id'].'ag'.time(),$json_encode($draw,TRUE));
		   				$result['luckdraw']=$draw;

					 }
					 else {
		 				throw new Exception("sorry, you dont' have enought gem");

		 				}
			}
					$response=json_encode($result,TRUE);
 	    			return $response;
			
		}

 }
