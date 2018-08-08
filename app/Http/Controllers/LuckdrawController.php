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
use App\Util\BaggageUtil;
use App\Lucky_draw_rateModel;
use Exception;
use Illuminate\Support\Facades\Redis;
use App\Http\Controllers\MissionController;
use DB;
use DateTime;

class LuckdrawController extends Controller
{	

	  public function luckdrawList(Request $request){
  	 	$req=$request->getContent();
		$json=base64_decode($req);
		$data=json_decode($json,TRUE);
		$redisLuck= Redis::connection('default');
		$now   = new DateTime;
		$date=$now->format( 'Y-m-d h:m:s' );
		$dmy=$now->format( 'Ymd' );
		$draw_type=$data['draw_type'];
		$luckdraw=new Luck_draw_rewardsModel();
		$defindMst=new DefindMstModel();
		$definData=$defindMst->where('defind_id',28)->first();
		$defindDiscount=$defindMst->where('defind_id',22)->first();
		$defindFree=$defindMst->where('defind_id',33)->first();

		$luckData=$luckdraw-> 
		select(DB::raw('lk_id,item_id,draw_type ,item_quantity,item_name,item_type,if(item_type=3, CONCAT("Scroll_Random_",item_rarity),"") as sc_img_path'))->where('draw_type',$draw_type)->where('start_date','<=',$date)->where('end_date','>=',$date)->get();
		if($draw_type==$defindFree['value1']){
			$freeDraw=$redisLuck->HGET('luckdrawfree',$dmy.$data['u_id']);
			if($freeDraw){
				$result['gemTimeUtil']=$defindFree['value2']+$freeDraw-time();
				$result['free_draw_duration']=$defindFree['value2'];
			}else{
				$result['free_draw_duration']=$defindFree['value2'];
			}
		}
		$result['draw_spend']=$definData['value1'];
		$result['multi_spend']=$definData['value1']*$defindDiscount['value1']*$defindDiscount['value2'];
		$result['reward_list']=$luckData;
		$response=json_encode($result,TRUE);
		return base64_encode($response);
  }

  public function one(Request $request){
  		$req=$request->getContent();
		$json=base64_decode($req);
	 	$usermodel=new UserModel();
		$redisLuck= Redis::connection('default');
		$now   = new DateTime;
		$date=$now->format( 'Y-m-d h:m:s' );
		$dmy=$now->format( 'Ymd' );
		$data=json_decode($json,TRUE);
		$u_id=$data['u_id'];
		$defindMstModel=new DefindMstModel();
		$BaggageUtil=new BaggageUtil();
		$draw_type=$data['draw_type'];


		$result=$this->luckdraw($u_id,$draw_type,1);
		$response=json_encode($result,TRUE);
		return base64_encode($response);

  }
    public function many(Request $request){
  		$req=$request->getContent();
		$json=base64_decode($req);
	 	$usermodel=new UserModel();
		$redisLuck= Redis::connection('default');
		$now   = new DateTime;
		$date=$now->format( 'Y-m-d h:m:s' );
		$dmy=$now->format( 'Ymd' );
		$data=json_decode($json,TRUE);
		$u_id=$data['u_id'];
		$defindMstModel=new DefindMstModel();
		$BaggageUtil=new BaggageUtil();
		$draw_type=$data['draw_type'];

		$result=$this->luckdraw($u_id,$draw_type,10);
		$response=json_encode($result,TRUE);
		return base64_encode($response);
  }



 private function luckdraw($u_id,$draw_type,$quantity){
	 	$usermodel=new UserModel();
		$redisLuck= Redis::connection('default');
		$now   = new DateTime;
		$date=$now->format( 'Y-m-d h:m:s' );
		$dmy=$now->format( 'Ymd' );
		$defindMstModel=new DefindMstModel();
		$BaggageUtil=new BaggageUtil();
		$ScrollMstModel=new ScrollMstModel();
		$luckdraw=new Luck_draw_rewardsModel();
		$luck_rate=new Lucky_draw_rateModel();
		$user_data=$usermodel->select('u_gem','u_coin')->where('u_id',$u_id)->first();
		$result=[];
		$defindSpend=$defindMstModel->where('defind_id',28)->first();
		$defindDiscount=$defindMstModel->where('defind_id',22)->first();  
		$defindMission=$defindMstModel->where('defind_id',1)->first(); 
		if($quantity==$defindDiscount['value2']){
			$totalSpend=$defindSpend['value1']*$defindDiscount['value1']*$quantity;
		}
		else{
			$totalSpend=$defindSpend['value1']*$quantity;
		}

			if($draw_type==1){
				$defindData=$defindMstModel->where('defind_id',3)->first(); 
				$defindSpend=$defindMstModel->where('defind_id',28)->first(); 
				if($user_data['u_coin']<$totalSpend){
				throw new Exception("no enough coin!");
				}
				$usermodel->where('u_id',$u_id)->update(['u_coin'=>$user_data['u_coin']-$totalSpend,'updated_at'=>$date]);
				$mission_key='mission_daily_'.$dmy.'_'.$u_id;
				$achiveMission=$redisLuck->HEXIST($mission_key,$defindMission['value1']);
				if(!$achiveMission){
				$redisLuck->HSET($mission_key,$defindMission['value1'],1);
				}

			}
			else if($draw_type==2){
				$mission_key='mission'.'_'.$u_id;
					if($quantity==1){
						$freeData=$redisLuck->HGET('luckdrawfree',$dmy.$u_id);
						if(!$freeData){
						$redisLuck->HSET('luckdrawfree',$dmy.$u_id,time());
						$totalSpend=0;
						$defindData=$defindMstModel->where('defind_id',4)->first(); 
						}
						else{
							$defindData=$defindMstModel->where('defind_id',4)->first(); 
				
							if($user_data['u_gem']<$totalSpend){
							throw new Exception("no enough gems!");
							}
							$usermodel->where('u_id',$u_id)->update(['u_gem'=>$user_data['u_gem']-$totalSpend,'updated_at'=>$date]);

						}
					}
					else{
						$defindData=$defindMstModel->where('defind_id',4)->first(); 
				
						if($user_data['u_gem']<$totalSpend){
						throw new Exception("no enough gems!");
						}
						$usermodel->where('u_id',$u_id)->update(['u_gem'=>$user_data['u_gem']-$totalSpend,'updated_at'=>$date]);
					}

			$achiveMission=$redisLuck->HEXIST($mission_key,$defindMission['value2']);
				if(!$achiveMission){
				$redisLuck->HSET($mission_key,$defindMission['value2'],1);
				}
			}

			$defindSp=$defindMstModel->where('defind_id',34)->first();  
			$user_data_after=$usermodel->select('u_gem','u_coin')->where('u_id',$u_id)->first();
			for($i=0;$i<$quantity;$i++){
				$rate=rand($defindData['value1'], $defindData['value2']);
				$luck_total=$redisLuck->HGET('luck_total_'.$draw_type,$u_id);
				if(is_null($luck_total)){
					$luck_total=0;
				}
			$getLk=$luck_rate->select('lk_id')->where('draw_count',$luck_total+1)->where('rate_from','<=',$rate)->where('rate_to','>=',$rate)->where('draw_type',$draw_type)->first();
			$drawresult=$luckdraw->select(DB::raw('lk_id,item_id,draw_type ,item_quantity,item_name,item_type,item_rarity,if(item_type=3, CONCAT("Scroll_Random_",item_rarity),"") as sc_img_path'))->where('draw_type',$draw_type)->where('start_date','<=',$date)->where('lk_id',$getLk['lk_id'])->first();
			$history_key="luck_draw_history";
				if($getLk['lk_id']==$defindSp['value1']||$getLk['lk_id']==$defindSp['value2']){
				
					$redisLuck->HSET('luck_total_'.$draw_type,$u_id,0);
				}
				else{
					$redisLuck->HSET('luck_total_'.$draw_type,$u_id,$luck_total+1);
				}
				if($drawresult){
					if($drawresult['item_type']==3){
						$scroll_list=$ScrollMstModel->select('sc_id')->where('sc_rarity',$drawresult['item_rarity'])->orderBy(DB::raw('RAND()'))->first();
						$drawresult['item_id']=$scroll_list['sc_id'];
					}
					$drawresult['time']=time();
					$redisLuck->LPUSH('luck_draw_'.$u_id.$draw_type,json_encode($drawresult,TRUE));
					unset($drawresult['time']);
					$result[]=$drawresult;
				}
			}
		$BaggageUtil->insertToBaggage($u_id,$result);
		$final['draw_result']=$result;
		return $final;
  		}
}
	// public function showLuck(Request $request){
	// 	$req=$request->getContent();
	// 	$json=base64_decode($req);
	// 	$data=json_decode($json,TRUE);
	// 	$redisLuck= Redis::connection('default');
	// 	$now   = new DateTime;
	// 	$date=$now->format( 'Y-m-d h:m:s' );
	// 	$dmy=$now->format( 'Ymd' );
	// 	// $CharSkillEffUtil=new CharSkillEffUtil();
	// 	// $access_token=$data['access_token'];
	// 	// $checkToken=$CharSkillEffUtil->($access_token,$u_id);
	// 	$luckdraw=new Luck_draw_rewardsModel();
	// 	$defindMstModel=new DefindMstModel();
	// 	$result=[];
	// 	// if($checkToken)
	// 	// {
	// 		$normalDrawJosn=$redisLuck->HGET('luckdrawfree1',$dmy.$data['u_id']);
	// 		$gemDrawJson=$redisLuck->HGET('luckdrawfree2',$dmy.$data['u_id']);
	// 		$normalDrawData=json_decode($normalDrawJosn,TRUE);
	// 		$gemDrawData=json_decode($gemDrawJson,TRUE);
	// 		$coinLuck=$luckdraw->where('draw_type',1)->first();
	// 		$gemLuck=$luckdraw->where('draw_type',2)->first();
	// 		if($normalDrawData){
	// 			$coinDraw=0;
	// 			$coinTimeUtil=($coinLuck['free_draw_duration']+$normalDrawData['createtime'])-time();
	// 			}
	// 		else {
	// 			$coinDraw=1;
	// 			$coinTimeUtil=$coinLuck['free_draw_duration'];

	// 		}

	// 		if($gemDrawData){
	// 			$gemDraw=0;
	// 			$gemTimeUtil=($gemLuck['free_draw_duration']+$gemDrawData['createtime'])-time();
	// 		}
	// 		else {
	// 			$gemDraw=1;
	// 			$gemTimeUtil=$gemLuck['free_draw_duration'];
	// 		}
	// 		$discount=$defindMstModel->where('defind_id',22)->first();
	// 		$result['coinDraw']=$coinDraw;
	// 		$result['coinSpend']=$coinLuck['draw_spend'];
	// 		$result['MucoinSpend']=$coinLuck['draw_spend']*10*$discount['value1'];
	// 		$result['coinTimeUtil']=$coinTimeUtil;
	// 		$result['coinMaxTime']=$coinLuck['free_draw_duration'];

	// 		$result['gemDraw']=$gemDraw;
	// 		$result['gemSpend']=$gemLuck['draw_spend'];
	// 		$result['MugemSpend']=$gemLuck['draw_spend']*10*$discount['value2'];;

	// 		$result['gemTimeUtil']=$gemTimeUtil;
	// 		$result['gemMaxTime']=$gemLuck['free_draw_duration'];

	// 		$response=json_encode($result,TRUE);
 // 	    	return base64_encode($response);
	// 	// }
	// }
 	// public function draw(Request $request){
	// 	$req=$request->getContent();
	// 	$json=base64_decode($req);
	//  	//dd($json);
	// 	$redisLuck= Redis::connection('default');
	// 	$now   = new DateTime;
	// 	$date=$now->format( 'Y-m-d h:m:s' );
	// 	$dmy=$now->format( 'Ymd' );
	// 	$data=json_decode($json,TRUE);
	// 	// $CharSkillEffUtil=new CharSkillEffUtil();
	// 	// $access_token=$data['access_token'];
	// 	// $checkToken=$CharSkillEffUtil->($access_token,$u_id);

	// 	if(isset($data['u_id']))
	// 	{
	// 		$result=[];
	// 		$drawtype=$data['draw_type'];
	// 		$luckdraw=new Luck_draw_rewardsModel();
	// 		$characterModel=new CharacterModel();
	// 		$defindMstModel=new DefindMstModel();
	// 		$gotToday=$redisLuck->HGET('luckdrawfree'.$drawtype,$dmy.$data['u_id']);
	// 		if($gotToday){
	// 			$todaydraw=json_decode($gotToday,TRUE);
	// 			$luckdata=$luckdraw->where('draw_type',$drawtype)->first();
	// 			$result['luckdrawfree'.$drawtype]['timeuntil']=($luckdata['free_draw_duration']+$todaydraw['createtime'])-time();
	// 			$response=json_encode($result,TRUE);
 // 	    		return $response;
                     
	// 		}
	// 		else {
	// 	   		$chardata=$characterModel->where('u_id',$data['u_id'])->first();	
	// 	  	 if($drawtype==1){
	// 	   		$defindData=$defindMstModel->where('defind_id',3)->first(); 
	// 	  		$rate=rand($defindData['value1'], $defindData['value2']);
	// 			}
	// 	   		else {
	// 	   		$defindData2=$defindMstModel->where('defind_id',4)->first(); 
	// 	   		$rate=rand($defindData2['value1'], $defindData2['value2']);
	// 	   		} 
		   

	// 	   		$drawresult=$luckdraw->where('draw_type',$drawtype)->where('start_date','<=',$date)->where('end_date','>=',$date)->where('user_lv_from','<=',$chardata['ch_lv'])->where('user_lv_to','>=',$chardata['ch_lv'])->where('star_from','<=',$chardata['ch_star'])->where('star_to','>=',$chardata['ch_star'])->where('rate_from','<=',$rate)->where('rate_to','>=',$rate)->first();
	// 	   	 if($drawresult){
	// 	  	 		$draw=$this->chooseBaggage($drawresult,$data,0);
	// 	 			$redisLuck->HSET('luckdrawfree'.$drawtype,$dmy.$data['u_id'],json_encode($draw,TRUE));
	// 	   			$result['luckdraw']=$draw;
	// 				$response=json_encode($result,TRUE);
 // 	    			return base64_encode($response);
 // 					}
	// 			else{
	// 				throw new Exception("sorry, no avaliable prize");
	// 			}

 // 			}
 //    	}
 // }






 	// public function oneDraw(Request $request){
 	// 	$req=$request->getContent();
		// $json=base64_decode($req);
	 // 	//dd($json);
		// $data=json_decode($json,TRUE);
		// $redisLuck= Redis::connection('default');
		// $now   = new DateTime;
		// $date=$now->format( 'Y-m-d h:m:s' );
		// $dmy=$now->format( 'Ymd' );
		
		// $result=[];
		// $drawtype=$data['draw_type'];
		// $luckdraw=new Luck_draw_rewardsModel();
		// $characterModel=new CharacterModel();	
		// $defindMstModel=new DefindMstModel();
		// $usermodel=new UserModel();
		// $BaggageUtil=new BaggageUtil();
		// // $CharSkillEffUtil=new CharSkillEffUtil();
		// // $access_token=$data['access_token'];
		// // $checkToken=$CharSkillEffUtil->($access_token,$u_id);
		// // if($checkToken){
		// 	$userData=$usermodel->where('u_id',$data['u_id'])->first();
		//    $chardata=$characterModel->where('u_id',$data['u_id'])->first();	
		//    $gotToday=$redisLuck->HGET('luckdrawfree'.$drawtype,$dmy.$data['u_id']);
		//    $luckdata=$luckdraw->where('draw_type',$drawtype)->first();
		//    if($gotToday){
		//    		$todaydraw=json_decode($gotToday,TRUE);
		//    	  if($drawtype==1){
		//    		$defindData=$defindMstModel->where('defind_id',3)->first(); 
		//   		 $rate=rand($defindData['value1'], $defindData['value2']);
		//    		$payBy=$userData['u_coin'];
		// 			}
		//    		else {
		//    			$defindData=$defindMstModel->where('defind_id',4)->first(); 
		//    			$rate=rand($defindData['value1'], $defindData['value2']);
		//    	 		$payBy=$userData['u_gem'];
		//   		} 
		   
		//    		$drawresult=$luckdraw->where('draw_type',$drawtype)->where('start_date','<=',$date)->where('end_date','>=',$date)->where('user_lv_from','<=',$chardata['ch_lv'])->where('user_lv_to','>=',$chardata['ch_lv'])->where('star_from','<=',$chardata['ch_star'])->where('star_to','>=',$chardata['ch_star'])->where('rate_from','<=',$rate)->where('rate_to','>=',$rate)->where('draw_spend','<=',$payBy)->first();
		//    		$u_id=$data['u_id'];
		//    		if($drawresult){
		// 				$draw=$this->chooseBaggage($drawresult,$data,1);
		// 				$result['luckdraw'][]=$draw;
		//    			if($drawtype==1){
		//    				$result['spent_coin']=$drawresult['draw_spend'];
		//    				$userCoin=$userData['u_coin']-$drawresult['draw_spend'];
		//    	 			$usermodel->where('u_id',$data['u_id'])->update(["u_coin"=>$userCoin]);
		//    				$BaggageUtil->RecordSpend($data['u_id'],$userCoin,0);
		//    			}
		//    			else {
		//    				$result['spent_gem']=$drawresult['draw_spend'];
		//    				$userGem=$userData['u_gem']-$drawresult['draw_spend'];
		//    	 			$usermodel->where('u_id',$data['u_id'])->update(["u_gem"=>$userGem]);
		//    				$BaggageUtil->RecordSpend($u_id,0,$userGem);
		//    			}

		//    		$redisLuck->HSET('luckdraw'.$drawtype,$date.$data['u_id'],json_encode($draw,TRUE));
		   	
		// 		$result['timeuntil']=($luckdata['free_draw_duration']+$todaydraw['createtime'])-time();
		//    		$response=json_encode($result,TRUE);
 	//     		return base64_encode($response);
		//    			}
		 
		// 		else{
		// 			throw new Exception("sorry, no avaliable prize");
		// 			}

		//   	 }
		//    		else{

		//   	 		if($drawtype==1){
		//    				$defindData=$defindMstModel->where('defind_id',3)->first(); 
		//   				$rate=rand($defindData['value1'], $defindData['value2']);
		// 			}
		//    			else {
		//    			$defindData=$defindMstModel->where('defind_id',4)->first(); 
		//    			$rate=rand($defindData['value1'], $defindData['value2']);
		//    			} 
		   
		//    			$drawresult=$luckdraw->where('draw_type',$drawtype)->where('start_date','<=',$date)->where('end_date','>=',$date)->where('user_lv_from','<=',$chardata['ch_lv'])->where('user_lv_to','>=',$chardata['ch_lv'])->where('star_from','<=',$chardata['ch_star'])->where('star_to','>=',$chardata['ch_star'])->where('rate_from','<=',$rate)->where('rate_to','>=',$rate)->first();
		//    	 		if($drawresult){
		//   	 			$draw=$this->chooseBaggage($drawresult,$data,0);
		//  				$redisLuck->HSET('luckdrawfree'.$drawtype,$dmy.$data['u_id'],json_encode($draw,TRUE));
		//    				$result['luckdraw'][]=$draw;

		// 				$result['timeuntil']=$luckdata['free_draw_duration'];
		// 				$response=json_encode($result,TRUE);
 	//     				return base64_encode($response);
 	// 			// 		}
		// 			// else{
		// 			// 	throw new Exception("sorry, no avaliable prize");
		// 			// }


		//    	}

		 
		// }

 	// }

 	// public function multiDraw(Request $request){
 	// 			$req=$request->getContent();
		// $json=base64_decode($req);
	 // 	//dd($json);
		// $data=json_decode($json,TRUE);
		// $redisLuck= Redis::connection('default');
		// $now   = new DateTime;
		// $date=$now->format( 'Y-m-d h:m:s' );
		// $dmy=$now->format( 'Ymd' );
		// // $CharSkillEffUtil=new CharSkillEffUtil();
		// // $access_token=$data['access_token'];
		// // $checkToken=$CharSkillEffUtil->($access_token,$u_id);
		// // if($checkToken){
 	// 			$drawtype=$data['draw_type'];
 	// 			$luckdraw=new Luck_draw_rewardsModel();
 	// 			$luckdata=$luckdraw->where('draw_type',$drawtype)->first();
 	// 			$characterModel=new CharacterModel();
 	// 			$defindMstModel=new DefindMstModel();
 	// 			$usermodel=new UserModel();
 	// 			$userData=$usermodel->where('u_id',$data['u_id'])->first();
 	// 			$draw_quantity=$defindMstModel->where('defind_id',2)->first();
 	// 			$chardata=$characterModel->where('u_id',$data['u_id'])->first();	
		//  		$result=[];
		//  		$discount=$defindMstModel->where('defind_id',22)->first();
		// 	 if($drawtype==1&&$userData['u_coin']<$draw_quantity['value1']*$luckdata['draw_spend']){
		// 			throw new Exception("no enough coins");
		// 	 }
		// 	 else if($userData['u_gem']<$draw_quantity['value1']*$luckdata['draw_spend']){
		// 			throw new Exception("no enough gems");
		// 	 }
		//    for($i=0;$i<$draw_quantity['value1'];$i++)
		//    {
		//    		if($drawtype==1){
		//    		$defindData=$defindMstModel->where('defind_id',3)->first(); 
		//    		$rate=rand($defindData['value1'], $defindData['value2']);
		// 		}
		//    		else {
		//    		$defindData=$defindMstModel->where('defind_id',4)->first(); 
		//    		$rate=rand($defindData['value1'], $defindData['value2']);

		//   		} 

		//    		$drawresult=$luckdraw->where('draw_type',$drawtype)->where('start_date','<=',$date)->where('end_date','>=',$date)->where('user_lv_from','<=',$chardata['ch_lv'])->where('user_lv_to','>=',$chardata['ch_lv'])->where('star_from','<=',$chardata['ch_star'])->where('star_to','>=',$chardata['ch_star'])->where('rate_from','<=',$rate)->where('rate_to','>=',$rate)->first();
		//    		if($drawresult){
		   		
		//    			$draw=$this->chooseBaggage($drawresult,$data,1);
		//    			$redisLuck->HSET('luckdraw'.$drawtype,$date.$data['u_id'],json_encode($draw,TRUE));
		//    			$result['luckdraw'][]=$draw;

		// 			} 
		// 	}
				
		//    		if($drawtype==1){
		//    			$result['spent_coin']=$drawresult['draw_spend']*$draw_quantity['value1'];
		//    			$userCoin=$userData['u_coin']-$drawresult['draw_spend']*$draw_quantity['value1']*$discount['value1'];
		//    	 		$usermodel->where('u_id',$data['u_id'])->update(["u_coin"=>$userCoin]);
		//    		}
		//    		else {
		//    			$result['spent_gem']=$drawresult['draw_spend']*$draw_quantity['value1']*$discount['value2'];
		//    			$userGem=$userData['u_gem']-$drawresult['draw_spend']*$draw_quantity['value1']*$discount['value2'];
		//    	 		$usermodel->where('u_id',$data['u_id'])->update(["u_gem"=>$userGem]);
		//    		}

		//    		$response=json_encode($result,TRUE);
 	//     		return base64_encode($response);
 	//     	// }
 	// 	}

//  		private function chooseBaggage($drawresult,$data,$pay){
//  			$baReModel=new UserBaggageResModel();
// 			$baEqModel=new UserBaggageEqModel();
// 			$baScModel=new UserBaggageScrollModel();

// 			$rescourceModel=new ResourceMstModel();
// 			$scrollModel=new ScrollMstModel();
// 			$equipmentModel=new EquipmentMstModel();
// 			$now   = new DateTime;
// 			$date=$now->format( 'Y-m-d h:m:s' );

// 		   		$draw['u_id']=$data['u_id'];
// 		   		$draw['item_id']=$drawresult['item_id'];
// 		   		$draw['item_quantity']=$drawresult['item_quantity'];
// 		   		$draw['item_type']=$drawresult['item_type'];
// 		   		$draw['createtime']=time();
// 		   		if($pay==0){
// 		   		$draw['duration']=$drawresult['free_draw_duration'];
// 		   	}
// 		   		$draw['draw_type']=$data['draw_type'];

//  			if($drawresult['item_type']==1){
// 		   			$rescourceData=$rescourceModel->where('r_id',$drawresult['item_id'])->first();
// 		   			$draw['item_name']=$rescourceData['r_name'];
// 		   			$draw['item_img_path']=$rescourceData['r_img_path'];
// 		   			$draw['description']=$rescourceData['r_description'];
// 		   			$baRedata=$baReModel->where('u_id',$data['u_id'])->where('r_id',$drawresult['item_id'])->first();
// 		   			if(isset($baRedata)){
// 		   				$br_quanitty=$baRedata['quantity']+$drawresult['item_quantity'];
// 		   				$baReModel->where('u_id',$data['u_id'])->where('r_id',$drawresult['item_id'])->update(['quantity'=>$br_quanitty,'updated_at'=>$date]);
// 		   			}
// 		   			else{
// 		   				$baReNew['u_id']=$data['u_id'];
// 		   				$baReNew['r_id']=$drawresult['item_id'];
// 		   				$baReNew['br_icon']=$rescourceData['r_img_path'];
// 		   				$baReNew['r_rarity']=$rescourceData['r_rarity'];
// 		   				$baReNew['r_type']=$rescourceData['r_type'];
// 		   				$baReNew['quantity']=$drawresult['item_quantity'];
// 		   				$baReNew['status']=0;
// 		   				$baReNew['updated_at']=$date;
// 		   				$baReNew['created_at']=$date;
// 		   				$baReModel->insert($baReNew);
// 		   			}
// 		   		}

// 		   		else if ($drawresult['item_type']==2){
// 		   			$equData=$equipmentModel->where('equ_id',$drawresult['item_id'])->first();
// 		   			$draw['item_name']=$equData['equ_name'];
// 		   			$draw['item_img_path']=$equData['icon_path'];
// 		   			$draw['description']=$equData['equ_description'];
// 		   			$now2   = new DateTime;
// 					$date2=$now2->format( 'Y-m-d h:m:s' );
// 		   			for($i=0;$i<$drawresult['item_quantity'];$i++){
// 		   				$baEqNew['u_id']=$data['u_id'];
// 		   				$baEqNew['equ_id']=$equData['equ_id'];
// 		   				$baEqNew['equ_rarity']=$equData['equ_rarity'];
// 		   				$baEqNew['equ_type']=$equData['equ_type'];
// 		   				$baEqNew['icon_path']=$equData['icon_path'];
// 		   				$baEqNew['status']=0;
// 		   				$baEqNew['updated_at']=$date2;
// 		   				$baEqNew['created_at']=$date2;
// 		   				$baEqModel->insert($baEqNew);
// 		   				}
// 		   			}
// 		   		else if ($drawresult['item_type']==3){
// 		   			$scData=$scrollModel->where('sc_id',$drawresult['item_id'])->first();
// 		   			$draw['item_name']=$scData['sc_name'];
// 		   			$draw['item_img_path']=$scData['sc_img_path'];
// 		   			$draw['description']=$scData['sc_description'];
// 					for($i=0;$i<$drawresult['item_quantity'];$i++){
// 		   				$baScNew['u_id']=$data['u_id'];
// 		   				$baScNew['sc_id']=$scData['sc_id'];
// 		   				$baScNew['sc_rarity']=$scData['sc_rarity'];
// 		   				$baScNew['sc_icon']=$scData['sc_img_path'];
// 		   				$baScNew['status']=0;
// 		   				$baScNew['updated_at']=$date;
// 		   				$baScNew['created_at']=$date;
// 		   				$baScModel->insert($baScNew);
// 		   				}		   			
// 		 			}
// 		 		return $draw;

// //  		}
// }
