<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\Luck_draw_rewardsModel;
use App\CharacterModel;
use App\UserModel;
use App\UserBaggageModel;
use App\Equipment_mst;
use App\Scroll_mst;
use App\Rescource_mst;
use App\DefindMstModel;
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
		$gotToday=Redis::HGET('luckdraw'.$drawtype,$dmy.$data['u_id']);
		$result=[];
		$drawtype=$data['draw_type'];
		$luckdraw=new Luck_draw_rewardsModel();
		$characterModel=new CharacterModel();
		$baggageModel=new UserBaggageModel();
		$rescourceModel=new Rescource_mst();
		$scrollModel=new Scroll_mst();
		$equipmentModel=new Equipment_mst();
		$defindMstModel=new DefindMstModel();

		if($gotToday){
			$todaydraw=json_decode($gotToday,TRUE);
			$luckdata=$luckdraw->where('draw_type',$drawtype)->first();
			$result['luckdrawfree'.$drawtype]['timeuntil']=time()-$todaydraw['createtime']+$luckdata['duration'];

		}
		else {
		   $chardata=$characterModel->where('u_id',$data['u_id'])->first();	
		   if('$drawtype'==1){
		   $defindData=$defindMstModel->where('defind_id',3)->first(); 
		   $rate=rand($defindData['value1'], $defindData['value2']);
			}
		   else {
		   	$defindData=$defindMstModel->where('defind_id',4)->first(); 
		   	$rate=rand($defindData['value1'], $defindData['value2']);
		   } 
		   
		   $drawresult=$luckdraw->where('draw_type',$drawtype)->where('start_date','<=',$date)->where('end_date','>=',$date)->where('user_lv_from','<=',$chardata['ch_lv'])->where('user_lv_to','>=',$chardata['ch_lv'])->where('star_from','<=',$chardata['ch_star'])->where('star_to','>=',$chardata['ch_star'])->where('rate_from','<=',$rate)->where('rate_to','>=',$rate)->first();
		   if($drawresult){
		   $draw['u_id']=$data['u_id'];
		   $draw['item_org_id']=$drawresult['item_org_id'];
		   $draw['item_quantity']=$drawresult['item_quantity'];
		   $draw['item_type']=$drawresult['item_type'];
		   $draw['createtime']=time();
		   $draw['duration']=$drawresult['free_drwa_duration'];
		   $draw['draw_type']=$drawtype;
			if($drawresult['item_type']==1){
		   			$rescourceData=$rescourceModel->where('r_id',$drawresult['item_org_id']);
		   			$draw['item_name']=$rescourceData['r_name'];
		   			$draw['item_img_path']=$rescourceData['r_img_path'];
		   			}

		   	else if ($drawresult['item_type']==2){
		   			$equData=$equipmentModel->where('equ_id',$drawresult['item_org_id']);
		   			$draw['item_name']=$equData['equ_name'];
		   			$draw['item_img_path']=$equData['icon_path'];
		   			}
		   	else if ($drawresult['item_type']==3){
		   			$scData=$scrollModel->where('sc_id',$drawresult['item_org_id']);
		   			$draw['item_name']=$scData['sc_name'];
		   			$draw['item_img_path']=$scData['sc_img_path'];
		 		}
		   	}

		   }
		   Redis::HSET('luckdrawfree'.$drawtype,$dmy.$data['u_id'],json_encode($draw,TRUE));
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

 	public function oneDraw(Request $request){
 				$req=$request->getContent();
		$json=base64_decode($req);
	 	//dd($json);
		$data=json_decode($json,TRUE);
		// Redis::connection('default');
		$now   = new DateTime;
		$date=$now->format( 'Y-m-d h:m:s' );
		$dmy=$now->format( 'Ymd' );
		$gotToday=Redis::HGET('luckdraw'.$drawtype,$dmy.$data['u_id']);
		$result=[];
		$drawtype=$data['draw_type'];
		$usermodel=new UserModel();
		$userData=$usermodel->where('u_id',$data['u_id'])->first();
		$luckdraw=new Luck_draw_rewardsModel();
		$characterModel=new CharacterModel();
		$baggageModel=new UserBaggageModel();
		$rescourceModel=new Rescource_mst();
		$scrollModel=new Scroll_mst();
		$equipmentModel=new Equipment_mst();
		$defindMstModel=new DefindMstModel();

		   $chardata=$characterModel->where('u_id',$data['u_id'])->first();	
		   if($drawtype==1){
		   $defindData=$defindMstModel->where('defind_id',3)->first(); 
		   $rate=rand($defindData['value1'], $defindData['value2']);
		   $payBy=$userData['u_coin'];
			}
		   else {
		   	$defindData=$defindMstModel->where('defind_id',4)->first(); 
		   	$rate=rand($defindData['value1'], $defindData['value2']);
		   	 $payBy=$userData['u_gem'];
		   } 
		   
		   $drawresult=$luckdraw->where('draw_type',$drawtype)->where('start_date','<=',$date)->where('end_date','>=',$date)->where('user_lv_from','<=',$chardata['ch_lv'])->where('user_lv_to','>=',$chardata['ch_lv'])->where('star_from','<=',$chardata['ch_star'])->where('star_to','>=',$chardata['ch_star'])->where('rate_from','<=',$rate)->where('rate_to','>=',$rate)->where('draw_spend','<=',$payBy)->first();
		   if($drawresult){
		   		$draw['u_id']=$data['u_id'];
		   		$draw['item_org_id']=$drawresult['item_org_id'];
		   		$draw['item_quantity']=$drawresult['item_quantity'];
		   		$draw['item_type']=$drawresult['item_type'];
		  	 	$draw['createtime']=time();
		  	 	$draw['duration']=$drawresult['free_drwa_duration'];
		  	 	$draw['draw_type']=$drawtype;
		  	 switch($drawresult['item_type']){
		   		case 1:
		   			$rescourceData=$rescourceModel->where('r_id',$drawresult['item_org_id']);
		   			$draw['item_name']=$rescourceData['r_name'];
		   			$draw['item_img_path']=$rescourceData['r_img_path'];
		   			break;

		   		case 2:
		   			$equData=$equipmentModel->where('equ_id',$drawresult['item_org_id']);
		   			$draw['item_name']=$equData['equ_name'];
		   			$draw['item_img_path']=$equData['icon_path'];
		   			break;
		   		case 3:
		   			$scData=$scrollModel->where('sc_id',$drawresult['item_org_id']);
		   			$draw['item_name']=$scData['sc_name'];
		   			$draw['item_img_path']=$scData['sc_img_path'];
		   			break;	
		   		}
		   		if($drawtype==1){
		   			$userCoin=$userData['u_coin']-$payBy;
		   	 		$usermodel->where('u_id',$data['u_id'])->update(["u_coin"=>$userCoin]);
		   		}
		   		else {
		   			$userGem=$userData['u_gem']-$payBy;
		   	 		$usermodel->where('u_id',$data['u_id'])->update(["u_gem"=>$userCoin]);
		   		}

		   }
		   Redis::HSET('luckdraw'.$drawtype,$dmy.$data['u_id'],json_encode($draw,TRUE));
		   $result['luckdraw']=$draw;
		   $baggageModel->updatebaggage($data['u_id'],$drawresult['item_type'],$drawresult['item_org_id'],$drawresult['item_quantity']);
			else{
				throw new Exception("sorry, no avaliable prize");
			}
		$response=json_encode($result,TRUE);
 	    return $response;
 	}

 	// public function buydraw(Request $request){
 	// 	$req=$request->getContent();
		// $json=base64_decode($req);
	 // 	//dd($json);
		// $data=json_decode($json,TRUE);
 	// 	//dd($data);
		// $usermodel=new UserModel();
		// $baggageModel=new UserBaggageModel();
		// $userData=$usermodel->where('u_id',$data['u_id'])->first();

		// $characterModel=new CharacterModel();
		// $chardata=$characterModel->where('u_id',$data['u_id'])->first();
		// $luckdraw=new Luck_draw_rewardsModel();
		// $now   = new DateTime;
		// $result=[];
		// $date=$now->format( 'Y-m-d h:m:s' );
		// $dateKey=$now->format( 'Y-m-d:h:m:s' );
		// $result=[];

		// 	$rate=rand(1, 10000);
		//  	$drawresult=$luckdraw->where('draw_type',$drawtype)->where('start_date','<=',$date)->where('end_date','>=',$date)->where('user_lv_from','<=',$chardata['ch_lv'])->where('user_lv_to','>=',$chardata['ch_lv'])->where('star_from','<=',$chardata['ch_star'])->where('star_to','>=',$chardata['ch_star'])->where('rate_from','<=',$rate)->where('rate_to','>=',$rate)->where('draw_coin','<=',$userData['u_coin'])->first();
		 	
		//  	if($drawresult){
		//  		$draw['u_id']=$data['u_id'];
		//    		$draw['item_org_id']=$drawresult['item_org_id'];
		//    		$draw['item_quantity']=$drawresult['item_quantity'];
		//    		$draw['item_type']=$drawresult['item_type'];
		//    		$draw['draw_coin']=$drawresult['draw_coin'];
		//   		$draw['createtime']=time();
		//    		Redis::HSET('luckdraw',$dateKey.$data['u_id'].'ac',json_encode($draw,TRUE));
		//    		$result['luckdraw']=$draw;
		//    		$userCoin=$userData['u_coin']-$drawresult['draw_coin'];
		//    	 	$usermodel->where('u_id',$data['u_id'])->update(["u_coin"=>$userCoin]);

		//  	}
		//  	else {
		//  		throw new Exception("sorry, you dont' have enought coin");

		//  	}
		// }
		// else if ($data['draw_type']==2){
		// 			 $rate=rand(5000, 10000);
		// 			 $drawresult=$luckdraw->where('start_date','<=',$date)->where('end_date','>=',$date)->where('user_lv_from','<=',$chardata['ch_lv'])->where('user_lv_to','>=',$chardata['ch_lv'])->where('star_from','<=',$chardata['ch_star'])->where('star_to','>=',$chardata['ch_star'])->where('rate_from','<=',$rate)->where('rate_to','>=',$rate)->where('draw_gem','<=',$userData['u_gem'])->first();
		// 			 if($drawresult){
		// 			    $draw['u_id']=$data['u_id'];
		//    				$draw['item_org_id']=$drawresult['item_org_id'];
		//    				$draw['item_quantity']=$drawresult['item_quantity'];
		//    				$draw['item_type']=$drawresult['item_type'];
		//    				$draw['draw_gem']=$drawresult['draw_gem'];
		//   				$draw['createtime']=time();
		//    				Redis::HSET('luckdraw',$dateKey.$data['u_id'].'ag'.time(),json_encode($draw,TRUE));
		//    				$result['luckdraw']=$draw;
		//    				$userGem=$userData['u_gem']-$drawresult['draw_gem'];
		//    	 			$usermodel->where('u_id',$data['u_id'])->update(["u_gem"=>$userGem]);

		// 			 }
		// 			 else {
		//  				throw new Exception("sorry, you dont' have enought gem");

		//  				}
		// 	}
		// 			$baggageModel->updatebaggage($data['u_id'],$drawresult['item_type'],$drawresult['item_org_id'],$drawresult['item_quantity']);
		// 			$response=json_encode($result,TRUE);
 	//     			return $response;
			
		// }

 	public function multiDraw(Request $request){
 				$req=$request->getContent();
		$json=base64_decode($req);
	 	//dd($json);
		$data=json_decode($json,TRUE);
		// Redis::connection('default');
		$now   = new DateTime;
		$date=$now->format( 'Y-m-d h:m:s' );
		$dmy=$now->format( 'Ymd' );
		$gotToday=Redis::HGET('luckdraw'.$drawtype,$dmy.$data['u_id']);
		$result=[];
		$drawtype=$data['draw_type'];
		$usermodel=new UserModel();
		$userData=$usermodel->where('u_id',$data['u_id'])->first();
		$luckdraw=new Luck_draw_rewardsModel();
		$characterModel=new CharacterModel();
		$baggageModel=new UserBaggageModel();
		$rescourceModel=new Rescource_mst();
		$scrollModel=new Scroll_mst();
		$equipmentModel=new Equipment_mst();
		$defindMstModel=new DefindMstModel();
		$draw_quantity=$defindMstModel->where('defind_id',2)->first();
		   $chardata=$characterModel->where('u_id',$data['u_id'])->first();	
		   $result=[];
		   for($i=0;$i<$draw_quantity['value1'];$i++){
		   		if($drawtype==1){
		   		$defindData=$defindMstModel->where('defind_id',3)->first(); 
		   		$rate=rand($defindData['value1'], $defindData['value2']);
		   		$payBy=$userData['u_coin'];
				}
		   else {
		   	$defindData=$defindMstModel->where('defind_id',4)->first(); 
		   	$rate=rand($defindData['value1'], $defindData['value2']);
		   	 $payBy=$userData['u_gem'];
		   } 
		   
		   $drawresult=$luckdraw->where('draw_type',$drawtype)->where('start_date','<=',$date)->where('end_date','>=',$date)->where('user_lv_from','<=',$chardata['ch_lv'])->where('user_lv_to','>=',$chardata['ch_lv'])->where('star_from','<=',$chardata['ch_star'])->where('star_to','>=',$chardata['ch_star'])->where('rate_from','<=',$rate)->where('rate_to','>=',$rate)->where('draw_spend','<=',$payBy)->first();
		   if($drawresult){
		   		$draw['u_id']=$data['u_id'];
		   		$draw['item_org_id']=$drawresult['item_org_id'];
		   		$draw['item_quantity']=$drawresult['item_quantity'];
		   		$draw['item_type']=$drawresult['item_type'];
		  	 	$draw['createtime']=time();
		  	 	$draw['duration']=$drawresult['free_drwa_duration'];
		  	 	$draw['draw_type']=$drawtype;
		  	 switch($drawresult['item_type']){
		   		case 1:
		   			$rescourceData=$rescourceModel->where('r_id',$drawresult['item_org_id']);
		   			$draw['item_name']=$rescourceData['r_name'];
		   			$draw['item_img_path']=$rescourceData['r_img_path'];
		   			break;

		   		case 2:
		   			$equData=$equipmentModel->where('equ_id',$drawresult['item_org_id']);
		   			$draw['item_name']=$equData['equ_name'];
		   			$draw['item_img_path']=$equData['icon_path'];
		   			break;
		   		case 3:
		   			$scData=$scrollModel->where('sc_id',$drawresult['item_org_id']);
		   			$draw['item_name']=$scData['sc_name'];
		   			$draw['item_img_path']=$scData['sc_img_path'];
		   			break;	
		   		}
		   		if($drawtype==1){
		   			$userCoin=$userData['u_coin']-$payBy;
		   	 		$usermodel->where('u_id',$data['u_id'])->update(["u_coin"=>$userCoin]);
		   		}
		   		else {
		   			$userGem=$userData['u_gem']-$payBy;
		   	 		$usermodel->where('u_id',$data['u_id'])->update(["u_gem"=>$userCoin]);
		   			}

		   		}
		   		$result[]=$draw;
			}
		   Redis::HSET('luckdraw'.$drawtype,$dmy.$data['u_id'],json_encode($draw,TRUE));
		   $final['luckdraw']=$result;
		   $baggageModel->updatebaggage($data['u_id'],$drawresult['item_type'],$drawresult['item_org_id'],$drawresult['item_quantity']);
			else{
				throw new Exception("sorry, no avaliable prize");
			}
			$response=json_encode($final,TRUE);
 	    return $response;

 }
