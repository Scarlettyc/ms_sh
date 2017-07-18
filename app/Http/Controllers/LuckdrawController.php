<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\Luck_draw_rewardsModel;
use App\CharacterModel;
use App\UserModel;
use App\UserBaggageEqModel;
use App\UserBaggageResModel;
use App\UserBaggageScrollModel;
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
		$data=json_decode($json,TRUE);
		$loginToday=Redis::HGET('login_data',$dmy.$data['u_id']);
		$loginTodayArr=json_decode($loginToday);
		$access_token=$loginTodayArr->access_token;

		if(isset($data['u_id'])&&$access_token==$data['access_token'])
		{
			$result=[];
			$drawtype=$data['draw_type'];
			$luckdraw=new Luck_draw_rewardsModel();
			$characterModel=new CharacterModel();
			$defindMstModel=new DefindMstModel();
			$gotToday=Redis::HGET('luckdrawfree'.$drawtype,$dmy.$data['u_id']);
			if($gotToday){
				$todaydraw=json_decode($gotToday,TRUE);
				$luckdata=$luckdraw->where('draw_type',$drawtype)->first();
				$result['luckdrawfree'.$drawtype]['timeuntil']=($luckdata['free_draw_duration']+$todaydraw['createtime'])-time();
				$response=json_encode($result,TRUE);
 	    		return $response;
                     
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

		  	 		$draw=$this->chooseBaggage($drawresult,$data);
		 			Redis::HSET('luckdrawfree'.$drawtype,$dmy.$data['u_id'],json_encode($draw,TRUE));
		   			$result['luckdraw']=$draw;
					$response=json_encode($result,TRUE);
 	    			return $response;
 					}
				else{
					throw new Exception("sorry, no avaliable prize");
				}

 			}
    	}
    	else{
    	throw new Exception("there have some error of you access_token");
   		 }
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
		$defindMstModel=new DefindMstModel();
		$usermodel=new UserModel();

		$loginToday=Redis::HGET('login_data',$dmy.$data['u_id']);
		$loginTodayArr=json_decode($loginToday);
		$access_token=$loginTodayArr->access_token;
		if($access_token==$data['access_token']){
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
				$draw=$this->chooseBaggage($drawresult,$data);

		   		if($drawtype==1){
		   			$draw['spent_coin']=$payBy;
		   			$userCoin=$userData['u_coin']-$payBy;
		   	 		$usermodel->where('u_id',$data['u_id'])->update(["u_coin"=>$userCoin]);
		   		}
		   		else {
		   			$draw['spent_gem']=$payBy;
		   			$userGem=$userData['u_gem']-$payBy;
		   	 		$usermodel->where('u_id',$data['u_id'])->update(["u_gem"=>$userCoin]);
		   		}

		   		Redis::HSET('luckdraw'.$drawtype,$date.$data['u_id'],json_encode($draw,TRUE));
		   		$result['luckdraw']=$draw;

		   	$response=json_encode($result,TRUE);
 	    	return $response;
		   	}
		 
			else{
				throw new Exception("sorry, no avaliable prize");
			}
		}
		else{
    	throw new Exception("there have some error of you access_token");
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
		$drawtype=$data['draw_type'];
		$luckdraw=new Luck_draw_rewardsModel();
		$characterModel=new CharacterModel();
		$defindMstModel=new DefindMstModel();
		$usermodel=new UserModel();
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
		   		
		   			$result[]=$this->chooseBaggage($drawresult,$data);
					}   
				}		
		   		$final['luckdraw']=$result;
		   		$response=json_encode($final,TRUE);
 	    		return $response;
 	    	

 		}

 		private function chooseBaggage($drawresult,$data){
 			$baReModel=new UserBaggageResModel();
			$baEqModel=new UserBaggageEqModel();
			$baScModel=new UserBaggageScrollModel();

			$rescourceModel=new ResourceMstModel();
			$scrollModel=new ScrollMstModel();
			$equipmentModel=new EquipmentMstModel();
			$now   = new DateTime;
			$date=$now->format( 'Y-m-d h:m:s' );

		   		$draw['u_id']=$data['u_id'];
		   		$draw['item_org_id']=$drawresult['item_org_id'];
		   		$draw['item_quantity']=$drawresult['item_quantity'];
		   		$draw['item_type']=$drawresult['item_type'];
		   		$draw['createtime']=time();
		   		$draw['duration']=$drawresult['free_draw_duration'];
		   		$draw['draw_type']=$data['draw_type'];

 			if($drawresult['item_type']==1){
		   			$rescourceData=$rescourceModel->where('r_id',$drawresult['item_org_id'])->first();
		   			$draw['item_name']=$rescourceData['r_name'];
		   			$draw['item_img_path']=$rescourceData['r_img_path'];
		   			$draw['description']=$rescourceData['r_description'];
		   			$baRedata=$baReModel->where('u_id',$data['u_id'])->where('br_id',$drawresult['item_org_id'])->first();
		   			if(isset($baRedata)){
		   				$br_quanitty=$baRedata['br_quantity']+$drawresult['item_quantity'];
		   				$baReModel->where('u_id',$data['u_id'])->where('br_id',$drawresult['item_org_id'])->update(['br_quantity'=>$br_quanitty,'updated_at'=>$date]);
		   			}
		   			else{
		   				$baReNew['u_id']=$data['u_id'];
		   				$baReNew['br_id']=$drawresult['item_org_id'];
		   				$baReNew['br_icon']=$rescourceData['r_img_path'];
		   				$baReNew['br_rarity']=$rescourceData['r_rarity'];
		   				$baReNew['br_type']=$rescourceData['r_type'];
		   				$baReNew['br_quantity']=$drawresult['item_quantity'];
		   				$baReNew['status']=0;
		   				$baReNew['updated_at']=$date;
		   				$baReNew['creatdate']=$date;
		   				$baReModel->insert($baReNew);
		   			}
		   		}

		   		else if ($drawresult['item_type']==2){
		   			$equData=$equipmentModel->where('equ_id',$drawresult['item_org_id'])->first();
		   			$draw['item_name']=$equData['equ_name'];
		   			$draw['item_img_path']=$equData['icon_path'];
		   			$draw['description']=$rescourceData['equ_description'];
		   			for($i=0;$i<=$drawresult['item_quantity'];$i++){
		   				$baEqNew['u_id']=$data['u_id'];
		   				$baEqNew['b_equ_id']=$equData['equ_id'];
		   				$baEqNew['b_equ_rarity']=$equData['equ_rarity'];
		   				$baEqNew['b_icon_path']=$equData['icon_path'];
		   				$baEqNew['status']=0;
		   				$baEqNew['updatedate']=$date;
		   				$baEqNew['creatdate']=$date;
		   				$baEqModel->insert($baEqNew);
		   				}
		   			}
		   		else if ($drawresult['item_type']==3){
		   			$scData=$scrollModel->where('sc_id',$drawresult['item_org_id'])->first();
		   			$draw['item_name']=$scData['sc_name'];
		   			$draw['item_img_path']=$scData['sc_img_path'];
		   			$draw['description']=$scData['sc_description'];
					for($i=0;$i<=$drawresult['item_quantity'];$i++){
		   				$baScNew['u_id']=$data['u_id'];
		   				$baScNew['bsc_id']=$equData['equ_id'];
		   				$baScNew['bsc_rarity']=$equData['equ_rarity'];
		   				$baScNew['bsc_icon']=$equData['icon_path'];
		   				$baScNew['status']=0;
		   				$baScNew['updatedate']=$date;
		   				$baScNew['creatdate']=$date;
		   				$baScModel->insert($baEqNew);
		   				}		   			
		 			}
		 		return $draw;

 		}
}
