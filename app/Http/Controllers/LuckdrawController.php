<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\Luck_draw_rewardsModel;
use App\CharacterModel;
use App\UserModel;
use App\UserBaggageModel;
use App\EquipmentMstModel;
use App\ScrollMstModel;
use App\ResourceMstModel;
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

		$result=[];
		$drawtype=$data['draw_type'];
		$luckdraw=new Luck_draw_rewardsModel();
		$characterModel=new CharacterModel();
		$baggageModel=new UserBaggageModel();
		$rescourceModel=new ResourceMstModel();
		$scrollModel=new ScrollMstModel();
		$equipmentModel=new EquipmentMstModel();
		$defindMstModel=new DefindMstModel();
		$gotToday=Redis::HGET('luckdrawfree'.$drawtype,$dmy.$data['u_id']);
		if($gotToday){
			$todaydraw=json_decode($gotToday,TRUE);
			$luckdata=$luckdraw->where('draw_type',$drawtype)->first();
			$result['luckdrawfree'.$drawtype]['timeuntil']=time()-$todaydraw['createtime']+$luckdata['duration'];
                     
		}
		else {
		   $chardata=$characterModel->where('u_id',$data['u_id'])->first();	
		   if($drawtype==1){
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
		   			$rescourceData=$rescourceModel->where('r_id',$drawresult['item_org_id'])->first();
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

		 			Redis::HSET('luckdrawfree'.$drawtype,$dmy.$data['u_id'],json_encode($draw,TRUE));
		   			$result['luckdraw']=$draw;
		   			$baggageModel->updatebaggage($data['u_id'],$drawresult['item_type'],$drawresult['item_org_id'],$drawresult['item_quantity']);
	
				//	$response=json_encode($result,TRUE);
 	    	//		return $response;
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
		
		$result=[];
		$drawtype=$data['draw_type'];
		$luckdraw=new Luck_draw_rewardsModel();
		$characterModel=new CharacterModel();
		$baggageModel=new UserBaggageModel();
		$rescourceModel=new ResourceMstModel();
		$scrollModel=new ScrollMstModel();
		$equipmentModel=new EquipmentMstModel();
		$defindMstModel=new DefindMstModel();
		$usermodel=new UserModel();
		$baggageModel=new UserBaggageModel();
		$userData=$usermodel->where('u_id',$data['u_id'])->first();
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
			if($drawresult['item_type']==1){
		   			$rescourceData=$rescourceModel->where('r_id',$drawresult['item_org_id'])->first();
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
		   	
		   		if($drawtype==1){
		   			$userCoin=$userData['u_coin']-$payBy;
		   	 		$usermodel->where('u_id',$data['u_id'])->update(["u_coin"=>$userCoin]);
		   		}
		   		else {
		   			$userGem=$userData['u_gem']-$payBy;
		   	 		$usermodel->where('u_id',$data['u_id'])->update(["u_gem"=>$userCoin]);
		   		}

		   Redis::HSET('luckdraw'.$drawtype,$date.$data['u_id'],json_encode($draw,TRUE));
		   $result['luckdraw']=$draw;
		   $baggageModel->updatebaggage($data['u_id'],$drawresult['item_type'],$drawresult['item_org_id'],$drawresult['item_quantity']);

		   	$response=json_encode($result,TRUE);
 	    	return $response;
		   }
		 
			else{
				throw new Exception("sorry, no avaliable prize");
			}

 	}

 	public function multiDraw(Request $request){
 				$req=$request->getContent();
		$json=base64_decode($req);
	 	//dd($json);
		$data=json_decode($json,TRUE);
		// Redis::connection('default');
		$now   = new DateTime;
		$date=$now->format( 'Y-m-d h:m:s' );
		$dmy=$now->format( 'Ymd' );
		$result=[];
		$drawtype=$data['draw_type'];
		$luckdraw=new Luck_draw_rewardsModel();
		$characterModel=new CharacterModel();
		$baggageModel=new UserBaggageModel();
		$rescourceModel=new ResourceMstModel();
		$scrollModel=new ScrollMstModel();
		$equipmentModel=new EquipmentMstModel();
		$defindMstModel=new DefindMstModel();
		$usermodel=new UserModel();
		$baggageModel=new UserBaggageModel();
		$userData=$usermodel->where('u_id',$data['u_id'])->first();
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
		   
		   		if($drawtype==1){
		   			$userCoin=$userData['u_coin']-$payBy;
		   	 		$usermodel->where('u_id',$data['u_id'])->update(["u_coin"=>$userCoin]);
		   		}
		   		else {
		   			$userGem=$userData['u_gem']-$payBy;
		   	 		$usermodel->where('u_id',$data['u_id'])->update(["u_gem"=>$userCoin]);
		   			}
		   			Redis::HSET('luckdraw'.$drawtype,$date.$data['u_id'],json_encode($draw,TRUE));
		   			$result[]=$draw;
		   		}
		   		
			}
		   		
		   		$final['luckdraw']=$result;
		   		$baggageModel->updatebaggage($data['u_id'],$drawresult['item_type'],$drawresult['item_org_id'],$drawresult['item_quantity']);
		   			$response=json_encode($final,TRUE);
 	    		return $response;
 	    	
 		}
}
