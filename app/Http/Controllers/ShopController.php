<?php

namespace App\Http\Controllers;
use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\UserResourcePurchaseHistoryModel;
use App\ResourceMstModel;
use App\UserModel;
use App\UserBaggageResModel;
use App\StoreReRewardModel;
use App\StoreGemToCoinMstModel;
use App\InAppPurchaseModel;
use App\DefindMstModel;
use App\GemPurchaseBundleMst;
use Exception;
use App\ScrollMstModel;
use App\Exceptions\Handler;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Redis;
use App\Util\BaggageUtil;
use Carbon\Carbon;
use DateTime;
use App\Http\Controllers\MissionController;
use App\Util\CharSkillEffUtil;

class ShopController extends Controller
{
	public function shopCoin(Request $request){
		$req=$request->getContent();
		$json=base64_decode($req);
		$data=json_decode($json,TRUE);
		$now=new DateTime;
		$datetime=$now->format( 'Y-m-d h:m:s' );
		$dmy=$now->format( 'Ymd' );
		$redisShop= Redis::connection('default');
		// $CharSkillEffUtil=new CharSkillEffUtil();
		// $access_token=$data['access_token'];
		// $checkToken=$CharSkillEffUtil->($access_token,$u_id);
		$inAppModel=new InAppPurchaseModel();
		// if($access_token==$data['access_token']){
		$resourceShop=$inAppModel
				->join('Rescource_mst','Rescource_mst.r_id','=','Store_purchase_mst.item_id')
				->select('Store_purchase_mst.item_id','Store_purchase_mst.item_type','Store_purchase_mst.item_min_quantity','Store_purchase_mst.item_max_times','Rescource_mst.r_rarity as pay_type','Rescource_mst.r_price * Store_purchase_mst.item_min_quantity as item_spend')
				->where('Store_purchase_mst.start_date','<=',$datetime)
				->where('Store_purchase_mst.end_date','>=',$datetime)
				->where('Store_purchase_mst.item_type',1)
				->get();
		$result['shop_list']=$resourceShop;
		$response=json_encode($result,TRUE);
 	    return base64_encode($response);
 		// }
 		// else {
 		// 	throw new Exception("there is something wrong with token");
 		// }
	}

	public function buyResouce(Request $request){
		$req=$request->getContent();
		$json=base64_decode($req);
		$data=json_decode($json,TRUE);
		$now=new DateTime;
		$datetime=$now->format( 'Y-m-d h:m:s' );
		$dmy=$now->format( 'Ymd' );
		$redisShop= Redis::connection('default');
		$resourceModle=new ResourceMstModel();
		$UserModel=new UserModel;
		$inAppModel=new InAppPurchaseModel();
		$BaggageUtil=new BaggageUtil();
		$u_id=$data['u_id'];
		$item_id=$data['item_id'];
		$item_type=$data['item_type'];
		$times=$data['item_times'];
		$pay_type=$data['pay_type'];
		$shopData=$inAppModel->select('item_min_quantity')->where('item_id',$item_id)->where('pay_type',$pay_type)->where('item_type',$item_type)->where('start_date','<=',$datetime)->where('end_date','>=',$datetime)->first();
		$resourceData=$resourceModle->select('r_price')->where('item_id',$item_id)->first();
		$totalSpend=$times*$shopData['item_min_quantity']*$resourceData['r_price'];
		if($pay_type==1){
			$userData=$UserModel->select('u_coin')->where('u_id',$u_id)->first();
				if($userData['u_coin']<$totalSpend){
				return base64_encode("no enough coin");
				}
				else{	
					$coin=$userData['u_coin']-$totalSpend;
					$UserModel->where('u_id',$u_id)->update(['u_coin'=>$coin,'updated_at'=>$datetime]);
					$BaggageUtil->RecordSpend($u_id,$totalSpend,0);
					
				}
			}
			else if($pay_type==2){
				$userData=$UserModel->select('u_gem')->where('u_id',$u_id)->first();
				if($userData['u_gem']<$totalSpend){
				return base64_encode("no enough coin");
				}
				else{
					$gem=$userData['u_gem']-$totalSpend;
					$UserModel->where('u_gem',$u_id)->update(['u_gem'=>$gem,'updated_at'=>$datetime]);
					$BaggageUtil->RecordSpend($u_id,0,$totalSpend);
				}
			}
				$BaggageUtil->updateBaggageResource($u_id,$item_id,$item_type,$shopData['item_min_quantity']*$times);
					$boughtData['u_id']=$u_id;
					$boughtData['item_type']=$item_type;
					$boughtData['item_id']=$item_id;
					$boughtData['item_quantity']=($shopData['item_min_quantity']*$times);
					$boughtData['spent']=$totalSpend;
					$boughtData['pay_type']=$pay_type;
					$boughtData['datetime']=time(); 
					$boughtJson=json_encode($boughtData,TRUE);
					$redisShop->LPUSH('buy_resource_'.$u_id,$boughtJson);
					return base64_encode($boughtJson);
	}

	public function rareResourceList (Request $request){
		$req=$request->getContent();
		$json=base64_decode($req);
		$data=json_decode($json,TRUE);
		$now=new DateTime;
		$datetime=$now->format( 'Y-m-d h:m:s' );
		$dmy=$now->format( 'Ymd' );
		$redisShop=Redis::connection('default');
		$storeReModel=new StoreReRewardModel();
		$defindMst=new DefindMstModel();
		$rate=$defindMst->where('defind_id',23)->first();
		$refresh=$defindMst->where('defind_id',25)->first();
		$refreshDuration=$defindMst->where('defind_id',26)->first();
		$scrollModel=new ScrollMstModel();
		// $CharSkillEffUtil=new CharSkillEffUtil();
		// $access_token=$data['access_token'];
		// $checkToken=$CharSkillEffUtil->($access_token,$u_id);
		if($data){
				$u_id=$data['u_id'];
				$listCount=0;
				$times=$redisShop->HGET('refresh_times',$dmy.$u_id);
			if($times>0){
				$key='store_rare_'.$u_id.'_'.$dmy.'_'.$times;
				$listCount=$redisShop->LLEN($key);
				}
			else{
				$key='store_rare_'.$u_id.'_'.$dmy.'_0';
				$listCount=$redisShop->LLEN($key);	
				$redisShop->HSET('refresh_times',$dmy.$u_id,0);
			}
			$rewardList=[];
			$tempList=[];
			$idList=[];
		
			if($listCount>0){
				$rewardList=$redisShop->LRANGE($key,0,$listCount);
				$rewardList=array_reverse($rewardList);
				foreach($rewardList as $each){
					$tempList[]=json_decode($each,TRUE);
				}
				$result['reward']=$tempList;
				$result['times']=$times;

				if($times>0){
				$result['spend_gem']=$refresh['value2']*($times+1);
				$result['next_gem']=$refresh['value2']*($times+2);
				}
				else{
				$result['spend_gem']=$refresh['value1'];
				$result['next_gem']=$refresh['value2'];
				}

				$result['refresh_time']=strtotime(date("Y-m-d 5:0:0",strtotime("+1 day")))-time();
				$rewardJson=json_encode($result,TRUE);
				return base64_encode($rewardJson);
			}
			else{	
				for($i=1;$i<=6;$i++){
					$reward=array();
					$number=rand($rate['value1'],$rate['value2']);
					$reward=$storeReModel->select('store_reward_id','item_id','item_type','item_quantity','gem_spend')->where('rate_from','<=',$number)->where('rate_to','>=',$number)->wherenotIn('store_reward_id',$idList)->first();

					if($reward['item_type']==3){
						$scro_img=$scrollModel->select('sc_img_path')->where('sc_id',$reward['item_id'])->first();
						$reward['sc_img_path']=$scro_img['sc_img_path'];	
					}
					$idList[]=$reward['store_reward_id'];
					$reward['status']=0;
					$result['reward'][]=$reward;
					$redisShop->LPUSH($key,$reward);
				}
				$result['times']=0;
				$result['spend_gem']=$refresh['value1'];
				$result['next_gem']=$refresh['value2'];
				$result['refresh_time']=strtotime(date("Y-m-d 5:0:0",strtotime("+1 day")))-time();
				$data=json_encode($result,TRUE);
				return base64_encode($data);
				}
			}
	}

		public function buyFromRefreshList(Request $request){
			$req=$request->getContent();
			$json=base64_decode($req);
			$data=json_decode($json,TRUE);
			$now=new DateTime;
			$datetime=$now->format( 'Y-m-d h:m:s' );
			$dmy=$now->format( 'Ymd' );
			$position=$data['number'];
			$u_id=$data['u_id'];
			$userModel=new UserModel();
			$redisShop=Redis::connection('default');
			$BaggageUtil=new BaggageUtil();
			$times=$redisShop->HGET('refresh_times',$dmy.$u_id);
			$key='store_rare_'.$u_id.'_'.$dmy.'_'.$times;
			$listCount=$redisShop->LLEN($key);
			$rewardData=$redisShop->LRANGE($key,$listCount-$position,$listCount-$position);
			$reward=json_decode($rewardData[0],True);
			$gem_spend=$reward['gem_spend'];
			$user_gem=$userModel->select('u_gem')->where('u_id',$u_id)->first();
			// $CharSkillEffUtil=new CharSkillEffUtil();
			// $access_token=$data['access_token'];
			// $checkToken=$CharSkillEffUtil->($access_token,$u_id);
			// if($checkToken){
				if($user_gem['u_gem']<$gem_spend){
				return base64_encode("no enough gems");
				}else{
					$BaggageUtil->updateBaggageResource($u_id,$reward['item_id'],$reward['item_type'],$reward['item_quantity']);
					$user_gem=$user_gem['u_gem']-$gem_spend;
					$userModel->where('u_id',$u_id)->update(['u_gem'=>$user_gem]);
					$reward['status']=1;
					$reward=json_encode($reward,TRUE);
					$redisShop->LSET($key,$listCount-$position,$reward);
					$BaggageUtil->RecordSpend($u_id,0,$gem_spend);
					return base64_encode('successfully');
					}
				// }
		}

		public function refreshResource(Request $request){
			$req=$request->getContent();
			$json=base64_decode($req);
			$data=json_decode($json,TRUE);
			$now=new DateTime;
			$datetime=$now->format( 'Y-m-d h:m:s' );
			$dmy=$now->format( 'Ymd' );
			$redisShop=Redis::connection('default');
			$u_id=$data['u_id'];
			$times=$redisShop->HGET('refresh_times',$dmy.$u_id);
			// $CharSkillEffUtil=new CharSkillEffUtil();
			// $access_token=$data['access_token'];
			// $checkToken=$CharSkillEffUtil->($access_token,$u_id);
			// if($checkToken){

				if($times==5){
					 throw new Exception("you only have five times chance!");
				}
				else {
					$defindMst=new DefindMstModel();
					$refresh=$defindMst->where('defind_id',25)->first();
					$spend=($times+1)*$refresh['value2'];
					$redisShop->HSET('refresh_times',$dmy.$u_id,$times+1);
					$result['times']=$times+1;
					$result['spend']=$spend;
					$response=json_encode($result,TRUE);
					return base64_encode($response);
				}
			// }
		}

		public function getCoinList(Request $request){
			$req=$request->getContent();
			$json=base64_decode($req);
			$data=json_decode($json,TRUE);
			$now=new DateTime;
			$datetime=$now->format('Y-m-d h:m:s');
			$dmy=$now->format( 'Ymd' );
			$redisShop=Redis::connection('default');
			$u_id=$data['u_id'];
			$UserModel=new UserModel;
			
			$StoreGemToCoinMstModel=new StoreGemToCoinMstModel;
				$coinList=$StoreGemToCoinMstModel->select('id','coin','gem')->where('start_date','<=',$datetime)->where('end_date','>=',$datetime)->get();
				$result['store_coin_list']=$coinList;
				$response=json_encode($result,TRUE);
				return base64_encode($response);
		}

		public function getGemList(Request $request){
			$req=$request->getContent();
			$json=base64_decode($req);
			$data=json_decode($json,TRUE);
			$now=new DateTime;
			$datetime=$now->format( 'Y-m-d h:m:s' );
			$dmy=$now->format( 'Ymd' );
			$redisShop=Redis::connection('default');
			// $CharSkillEffUtil=new CharSkillEffUtil();
			// $access_token=$data['access_token'];
			// $checkToken=$CharSkillEffUtil->($access_token,$u_id);
			$u_id=$data['u_id'];
			$UserModel=new UserModel;
			$GemPurchaseBundleMst=new GemPurchaseBundleMst;
			// if($checkToken){
				$userData=$UserModel->select('country','os')->where('u_id',$u_id)->first();
				$gemList=$GemPurchaseBundleMst->select('bundle_id','u_payment','gem_quantity')->where('start_date','<=',$datetime)->where('end_date','>=',$datetime)->where('os',$userData['os'])->where('country',$userData['country'])->orderby('gem_quantity')->get();
				$result['store_gem_list']=$gemList;
				$response=json_encode($result,TRUE);
				return base64_encode($response);
			// }
		}

	public function buyCoin(Request $request)
	{	
		$req=$request->getContent();
		$json=base64_decode($req);
		$data=json_decode($json,TRUE);
		$now=new DateTime;
		$datetime=$now->format( 'Y-m-d h:m:s' );
		$dmy=$now->format( 'Ymd' );
		$redisShop=Redis::connection('default');
		$BaggageUtil=new BaggageUtil();
		// $CharSkillEffUtil=new CharSkillEffUtil();
		// $access_token=$data['access_token'];
		// $checkToken=$CharSkillEffUtil->($access_token,$u_id);
		$u_id=$data['u_id'];
		$coin=$data['coin'];
		$UserModel=new UserModel;
		$StoreGemToCoinMstModel=new StoreGemToCoinMstModel;
		$buyType=$StoreGemToCoinMstModel->where('coin',$coin)->where('start_date','<=',$datetime)->where('end_date','>=',$datetime)->first();
		$UserInfo=$UserModel->select('u_gem','u_coin')->where('u_id',$u_id)->first();
		$spend_gem=$buyType['gem'];
		$get_coin=$buyType['coin'];
		$key="store_buy_coin_".$u_id;
		// if($checkToken){
			if($UserInfo['u_gem']-$spend_gem>0){
				$updateGem=$UserInfo['u_gem']-$spend_gem;
				$updateCoin=$UserInfo['u_coin']+$get_coin;

				$UserModel->where('u_id',$u_id)->update(['u_gem'=>$updateGem,'u_coin'=>$updateCoin]);
				$buyData['u_id']=$u_id;
				$buyData['datetime']=time();
				$buyData['spend_gem']=$spend_gem;
				$buyData['bought_coin']=$get_coin;
				$buyData['gem_before']=$UserInfo['u_gem'];
				$buyData['coin_before']=$UserInfo['u_coin'];
				$data=json_encode($buyData,TRUE);
				$redisShop->LPUSH($key,$data);
				$BaggageUtil->RecordSpend($u_id,0,$spend_gem);
				return base64_encode('successfully');
				}
				else{
					return base64_encode("no enough gems");	
				}
		// }
		// else {
 	// 		throw new Exception("there is something wrong with token");
 	// 		}
 		}

}