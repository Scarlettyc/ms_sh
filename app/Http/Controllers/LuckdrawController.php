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
	public function showLuck(Request $request){
		$req=$request->getContent();
		$json=base64_decode($req);
		$data=json_decode($json,TRUE);
		$redisLuck= Redis::connection('default');
		$now   = new DateTime;
		$date=$now->format( 'Y-m-d h:m:s' );
		$dmy=$now->format( 'Ymd' );
		$loginToday=$redisLuck->HGET('login_data',$dmy.$data['u_id']);
		$loginTodayArr=json_decode($loginToday);
		$access_token=$loginTodayArr->access_token;
		$luckdraw=new Luck_draw_rewardsModel();
		$defindMstModel=new DefindMstModel();
		$result=[];
		if($access_token==$data['access_token'])
		{
			$normalDrawJosn=$redisLuck->HGET('luckdrawfree1',$dmy.$data['u_id']);
			$gemDrawJson=$redisLuck->HGET('luckdrawfree2',$dmy.$data['u_id']);
			$normalDrawData=json_decode($normalDrawJosn,TRUE);
			$gemDrawData=json_decode($gemDrawJson,TRUE);
			$coinLuck=$luckdraw->where('draw_type',1)->first();
			$gemLuck=$luckdraw->where('draw_type',2)->first();
			if($normalDrawData){
				$coinDraw=0;
				$coinTimeUtil=($coinLuck['free_draw_duration']+$normalDrawData['createtime'])-time();
				}
			else {
				$coinDraw=1;
				$coinTimeUtil=$coinLuck['free_draw_duration'];

			}

			if($gemDrawData){
				$gemDraw=0;
				$gemTimeUtil=($gemLuck['free_draw_duration']+$gemDrawData['createtime'])-time();
			}
			else {
				$gemDraw=1;
				$gemTimeUtil=$gemLuck['free_draw_duration'];
			}
			$discount=$defindMstModel->where('defind_id',22)->first();
			$result['coinDraw']=$coinDraw;
			$result['coinSpend']=$coinLuck['draw_spend'];
			$result['MucoinSpend']=$coinLuck['draw_spend']*10*$discount['value1'];
			$result['coinTimeUtil']=$coinTimeUtil;
			$result['coinMaxTime']=$coinLuck['free_draw_duration'];

			$result['gemDraw']=$gemDraw;
			$result['gemSpend']=$gemLuck['draw_spend'];
			$result['MugemSpend']=$gemLuck['draw_spend']*10*$discount['value2'];;

			$result['gemTimeUtil']=$gemTimeUtil;
			$result['gemMaxTime']=$gemLuck['free_draw_duration'];

			$response=json_encode($result,TRUE);
 	    	return base64_encode($response);
		}
	}
 	public function draw(Request $request){
		$req=$request->getContent();
		$json=base64_decode($req);
	 	//dd($json);
		$redisLuck= Redis::connection('default');
		$now   = new DateTime;
		$date=$now->format( 'Y-m-d h:m:s' );
		$dmy=$now->format( 'Ymd' );
		$data=json_decode($json,TRUE);
		$loginToday=$redisLuck->HGET('login_data',$dmy.$data['u_id']);
		$loginTodayArr=json_decode($loginToday);
		$access_token=$loginTodayArr->access_token;

		if(isset($data['u_id'])&&$access_token==$data['access_token'])
		{
			$result=[];
			$drawtype=$data['draw_type'];
			$luckdraw=new Luck_draw_rewardsModel();
			$characterModel=new CharacterModel();
			$defindMstModel=new DefindMstModel();
			$gotToday=$redisLuck->HGET('luckdrawfree'.$drawtype,$dmy.$data['u_id']);
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
		   		$defindData2=$defindMstModel->where('defind_id',4)->first(); 
		   		$rate=rand($defindData2['value1'], $defindData2['value2']);
		   		} 
		   

		   		$drawresult=$luckdraw->where('draw_type',$drawtype)->where('start_date','<=',$date)->where('end_date','>=',$date)->where('user_lv_from','<=',$chardata['ch_lv'])->where('user_lv_to','>=',$chardata['ch_lv'])->where('star_from','<=',$chardata['ch_star'])->where('star_to','>=',$chardata['ch_star'])->where('rate_from','<=',$rate)->where('rate_to','>=',$rate)->first();
		   	 if($drawresult){

		  	 		$draw=$this->chooseBaggage($drawresult,$data,0);
		 			$redisLuck->HSET('luckdrawfree'.$drawtype,$dmy.$data['u_id'],json_encode($draw,TRUE));
		   			$result['luckdraw']=$draw;
					$response=json_encode($result,TRUE);
 	    			return base64_encode($response);
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
		$redisLuck= Redis::connection('default');
		$now   = new DateTime;
		$date=$now->format( 'Y-m-d h:m:s' );
		$dmy=$now->format( 'Ymd' );
		
		$result=[];
		$drawtype=$data['draw_type'];
		$luckdraw=new Luck_draw_rewardsModel();
		$characterModel=new CharacterModel();	
		$defindMstModel=new DefindMstModel();
		$usermodel=new UserModel();
		$loginToday=$redisLuck->HGET('login_data',$dmy.$data['u_id']);
		$loginTodayArr=json_decode($loginToday);
		$access_token=$loginTodayArr->access_token;
		if($access_token==$data['access_token']){
			$userData=$usermodel->where('u_id',$data['u_id'])->first();
		   $chardata=$characterModel->where('u_id',$data['u_id'])->first();	
		   $gotToday=$redisLuck->HGET('luckdrawfree'.$drawtype,$dmy.$data['u_id']);
		   $luckdata=$luckdraw->where('draw_type',$drawtype)->first();
		   if($gotToday){
		   		$todaydraw=json_decode($gotToday,TRUE);
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
						$draw=$this->chooseBaggage($drawresult,$data,1);

		   			if($drawtype==1){
		   				$draw['spent_coin']=$drawresult['draw_spend'];
		   				$userCoin=$userData['u_coin']-$drawresult['draw_spend'];
		   	 			$usermodel->where('u_id',$data['u_id'])->update(["u_coin"=>$userCoin]);
		   			}
		   			else {
		   				$draw['spent_gem']=$drawresult['draw_spend'];
		   				$userGem=$userData['u_gem']-$drawresult['draw_spend'];
		   	 			$usermodel->where('u_id',$data['u_id'])->update(["u_gem"=>$userGem]);
		   			}

		   		$redisLuck->HSET('luckdraw'.$drawtype,$date.$data['u_id'],json_encode($draw,TRUE));
		   		$result['luckdraw']=$draw;
				$result['timeuntil']=($luckdata['free_draw_duration']+$todaydraw['createtime'])-time();
		   		$response=json_encode($result,TRUE);
 	    		return base64_encode($response);
		   			}
		 
				else{
					throw new Exception("sorry, no avaliable prize");
					}

		  	 }
		   		else{

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
		  	 			$draw=$this->chooseBaggage($drawresult,$data,0);
		 				$redisLuck->HSET('luckdrawfree'.$drawtype,$dmy.$data['u_id'],json_encode($draw,TRUE));
		   				$result['luckdraw']=$draw;

						$result['timeuntil']=$luckdata['free_draw_duration'];
						$response=json_encode($result,TRUE);
 	    				return base64_encode($response);
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

 	public function multiDraw(Request $request){
 				$req=$request->getContent();
		$json=base64_decode($req);
	 	//dd($json);
		$data=json_decode($json,TRUE);
		$redisLuck= Redis::connection('default');
		$now   = new DateTime;
		$date=$now->format( 'Y-m-d h:m:s' );
		$dmy=$now->format( 'Ymd' );
		$loginToday=$redisLuck->HGET('login_data',$dmy.$data['u_id']);
		$loginTodayArr=json_decode($loginToday);
		$access_token=$loginTodayArr->access_token;
		if($access_token==$data['access_token']){

 				$drawtype=$data['draw_type'];
 				$luckdraw=new Luck_draw_rewardsModel();
 				$luckdata=$luckdraw->where('draw_type',$drawtype)->first();
 				$characterModel=new CharacterModel();
 				$defindMstModel=new DefindMstModel();
 				$usermodel=new UserModel();
 				$userData=$usermodel->where('u_id',$data['u_id'])->first();
 				$draw_quantity=$defindMstModel->where('defind_id',2)->first();
 				$chardata=$characterModel->where('u_id',$data['u_id'])->first();	
		 		$result=[];
		 		$discount=$defindMstModel->where('defind_id',22)->first();
			 if($drawtype==1&&$userData['u_coin']<$draw_quantity['value1']*$luckdata['draw_spend']){
					throw new Exception("no enough coins");
			 }
			 else if($userData['u_gem']<$draw_quantity['value1']*$luckdata['draw_spend']){
					throw new Exception("no enough gems");
			 }
		   for($i=0;$i<$draw_quantity['value1'];$i++)
		   {
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
		   		
		   			$draw=$this->chooseBaggage($drawresult,$data,1);
		   			$redisLuck->HSET('luckdraw'.$drawtype,$date.$data['u_id'],json_encode($draw,TRUE));
		   			$result[]=$draw;

					} 
				}
				
		   		if($drawtype==1){
		   			$result['spent_coin']=$drawresult['draw_spend']*$draw_quantity['value1'];
		   			$userCoin=$userData['u_coin']-$drawresult['draw_spend']*$draw_quantity['value1']*$discount['value1'];
		   	 		$usermodel->where('u_id',$data['u_id'])->update(["u_coin"=>$userCoin]);
		   		}
		   		else {
		   			$result['spent_gem']=$drawresult['draw_spend']*$draw_quantity['value1']*$discount['value2'];
		   			$userGem=$userData['u_gem']-$drawresult['draw_spend']*$draw_quantity['value1'];
		   	 		$usermodel->where('u_id',$data['u_id'])->update(["u_gem"=>$userGem]);
		   		}

		   		$final['luckdraw']=$result;

		   		$response=json_encode($final,TRUE);
 	    		return base64_encode($response);
 	    	}
 	    	else{
 	    		throw new Exception("there have some error of you access_token");
 	    	}
 	    	

 		}

 		private function chooseBaggage($drawresult,$data,$pay){
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
		   		if($pay==0){
		   		$draw['duration']=$drawresult['free_draw_duration'];
		   	}
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
		   				$baReNew['created_at']=$date;
		   				$baReModel->insert($baReNew);
		   			}
		   		}

		   		else if ($drawresult['item_type']==2){
		   			$equData=$equipmentModel->where('equ_id',$drawresult['item_org_id'])->first();
		   			$draw['item_name']=$equData['equ_name'];
		   			$draw['item_img_path']=$equData['icon_path'];
		   			$draw['description']=$equData['equ_description'];
		   			for($i=0;$i<=$drawresult['item_quantity'];$i++){
		   				$baEqNew['u_id']=$data['u_id'];
		   				$baEqNew['b_equ_id']=$equData['equ_id'];
		   				$baEqNew['b_equ_rarity']=$equData['equ_rarity'];
		   				$baEqNew['b_icon_path']=$equData['icon_path'];
		   				$baEqNew['status']=0;
		   				$baEqNew['updated_at']=$date;
		   				$baEqNew['created_at']=$date;
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
		   				$baScNew['bsc_id']=$scData['sc_id'];
		   				$baScNew['bsc_rarity']=$scData['sc_rarity'];
		   				$baScNew['bsc_icon']=$scData['sc_img_path'];
		   				$baScNew['status']=0;
		   				$baScNew['updated_at']=$date;
		   				$baScNew['created_at']=$date;
		   				$baScModel->insert($baScNew);
		   				}		   			
		 			}
		 		return $draw;

 		}
}
