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
use Exception;
use App\Exceptions\Handler;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Redis;
use App\Util\BaggageUtil;
use Carbon\Carbon;
use DateTime;

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
		$loginToday=$redisShop->HGET('login_data',$dmy.$data['u_id']);
		$loginTodayArr=json_decode($loginToday);
		$access_token=$loginTodayArr->access_token;
		$inAppModel=new InAppPurchaseModel();
		if($access_token==$data['access_token']){
		$resourceShop=$inAppModel->select('item_id','item_type','item_min_quantity','item_max_times','item_spend')->where('start_date','<=',$datetime)->where('end_date','>=',$datetime)->get();
		$result['shop_list']=$resourceShop;
		$response=json_encode($result,TRUE);
 	    return base64_encode($response);
 		}
 		else {
 			return base64_encode("there is something wrong with token");
 		}
	}

	public function buyResouceBYCoin(Request $request){
		$req=$request->getContent();
		$json=base64_decode($req);
		$data=json_decode($json,TRUE);
		$now=new DateTime;
		$datetime=$now->format( 'Y-m-d h:m:s' );
		$dmy=$now->format( 'Ymd' );
		$redisShop= Redis::connection('default');
		
		$UserModel=new UserModel;
		$inAppModel=new InAppPurchaseModel();
		$BaggageUtil=new BaggageUtil();
		$redis_shop=Redis::connection('default');

		$u_id=$data['u_id'];
		$item_id=$data['item_id'];
		$item_type=$data['item_type'];
		$times=$data['item_times'];
		$shopData=$inAppModel->select('item_spend','item_min_quantity')->where('item_id',$item_id)->where('item_type',$item_type)->where('start_date','<=',$datetime)->where('end_date','>=',$datetime)->first();
		$totalSpend=$times*$shopData['item_spend'];
		$userData=$UserModel->select('u_coin')->where('u_id',$u_id)->first();
		if($userData['u_coin']<$totalSpend){
			return base64_encode("no enough coin");
		}
		else{	
				$coin=$userData['u_coin']-$totalSpend;
				$UserModel->where('u_id',$u_id)->update(['u_coin'=>$coin,'updated_at'=>$datetime]);
				$BaggageUtil->updateBaggageResource($u_id,$item_id,$item_type,$shopData['item_min_quantity']*$times);
				$boughtData['u_id']=$u_id;
				$boughtData['item_type']=$item_type;
				$boughtData['item_id']=$item_id;
				$boughtData['item_quantity']=($shopData['item_min_quantity']*$times);
				$boughtData['spent']=$totalSpend;
				$boughtJson=json_encode($boughtData,TRUE);
				$redisShop->LPUSH('buy_resource',$boughtJson);
				return base64_encode($boughtJson);
		}
	}

	// public function buyResource(Request $request)
	// {	
	// 	$req=$request->getContent();
	// 	$data=json_decode($req,TRUE);
	// 	$now=new DateTime;
	// 	$datetime=$now->format( 'Y-m-d h:m:s' );
	// 	$dmy=$now->format( 'Ymd' );

	// 	$UserModel=new UserModel;
	// 	$UserResHistory=new UserResourcePurchaseHistoryModel;
	// 	$ResourceMstModel=new ResourceMstModel;
	// 	$UserBaggageResModel=new UserBaggageResModel;

	// 	$u_id=$data['u_id'];
	// 	$item_id=$data['item_id'];
	// 	$item_type=$data['item_type'];
	// 	$quantity=$data['quantity'];
	// 	$resInfo=$ResourceMstModel->where('r_id',$r_id)->first();
	// 	$UserInfo=$UserModel->where('u_id',$u_id)->first();
	// 	$userGem=$UserInfo['u_gem'];
	// 		if($r_id<=5)
	// 		{
	// 			$currency=$data['currency'];
	// 			if($currency == 1)
	// 			{
	// 				$usedGem=$resInfo['r_gem_price']*$quantity;
	// 				$updateGem=$userGem-$usedGem;
	// 				if($updateGem>=0)
	// 				{
	// 					$UserModel->update(['u_gem'=>$updateGem,'updated_at'=>$datetime]);
	// 				}else{
	// 				throw new Exception("Don't have enough Gem!");
	// 				}
	// 			}else if($currency == 2)
	// 			{
	// 				$userCoin=$UserModel->where('u_id',$u_id)->pluck('u_coin');
	// 				$usedCoin=$resInfo['r_coin_price']*$quantity;
	// 				$updateCoin=$userCoin-$usedCoin;
	// 				if($updateCoin>=0)
	// 			{
	// 				$UserModel->update(['u_coin'=>$updateCoin,'updated_at'=>$datetime]);
	// 			}else{
	// 				throw new Exception("Don't have enough Coin!");
	// 			}
	// 		}
	// 	}else if($r_id>=6)
	// 	{
	// 		$order_id=$data['order_id'];
	// 		$resStoreInfo=$UserResHistory->where('u_id',$u_id)->where('order_id',$order_id)->where('order_status',0)->first();

	// 		if (isset($resStoreInfo)) 
	// 		{
	// 			$r_id=$resStoreInfo['r_id'];
	// 			$updateGem=$userGem-$resInfo['r_gem_price'];
	// 			if($updateGem>=0)
	// 			{
	// 				$UserModel->update(['u_gem'=>$updateGem,'updated_at'=>$datetime]);
	// 				$UserResHistory->where('u_id',$u_id)->where('order_id',$order_id)->where('order_status',0)->update(['order_status'=>1,'updated_at'=>$datetime]);
	// 			}else{
	// 				throw new Exception("Don't have enough Gem!");
	// 			}
	// 		}else{
	// 			throw new Exception("Already sold out!");
	// 		}
	// 	}

	// 	$userBagRes=$UserBaggageResModel->where('u_id',$u_id)->where('br_id',$r_id)->first();
	// 	if(isset($userBagRes))
	// 	{
	// 		if($userBagRes['status']==0)
	// 		{
	// 			$updateQuantity=$userBagRes['br_quantity']+$quantity;
	// 			$UserBaggageResModel->where('u_id',$u_id)->where('br_id',$r_id)->update(['br_quantity'=>$updateQuantity,'updated_at'=>$datetime]);
	// 		}else if($userBagRes['status']==1)
	// 		{
	// 			$UserBaggageResModel->where('u_id',$u_id)->where('br_id',$r_id)->update(['br_quantity'=>$quantity,'status'=>0,'updated_at'=>$datetime]);
	// 		}
	// 	}else{
	// 		$UserBaggageResModel->insert(['u_id'=>$u_id,'br_id'=>$r_id,'br_icon'=>$resInfo['r_img_path'],'br_rarity'=>$resInfo['r_rarity'],'br_type'=>$resInfo['r_type'],'br_quantity'=>$quantity,'status'=>0,'updated_at'=>$datetime,'created_at'=>$datetime]);
	// 	}
	// 	$response='Buy successfully';
	// 	return $response;
	// }

	public function rareResourceList (Request $request){
		$req=$request->getContent();
		$data=json_decode($req,TRUE);
		$now=new DateTime;
		$datetime=$now->format( 'Y-m-d h:m:s' );
		$dmy=$now->format( 'Ymd' );
		$redis_shop=Redis::connection('default');
		$storeReModel=new StoreReRewardModel();
		$defindMst=new DefindMstModel();
		$rate=$defindMst->where('defind_id',23)->first();

		if($data){

		$key='store_rare';
		$rewardJson=$redis_shop->HGET($key,$dmy.'_'.$u_id);
		$rewardList=[];
		$u_id=$data['u_id'];
			if($rewardJson){
				return base64_encode($rewardJson);
			}
			else{	for($i=0;$i<=6;$i++){
					$number=rand($rate['value1',$rate['value2']]);
					$reward=$storeReModel->select('item_id','item_type','item_quantity','gem')->where('rate_from','<=',$number)->where('rate_to','>=',$number)->where('start_datetime','<=',$datetime)->where('end_datetime','>=',$datetime)->first();
					$rewardList[]=$reward;
				}
				$rewardList['times']=0;
				return base64_encode($rewardList);
			}
		}
	}

	public function refreashRareResource(Request $request)
	{
		$req=$request->getContent();
		$data=json_decode($req,TRUE);
		$now=new DateTime;
		$datetime=$now->format( 'Y-m-d h:m:s' );
		$dmy=$now->format( 'Ymd' );
		$redis_shop=Redis::connection('default');

		$UserResHistory=new UserResourcePurchaseHistoryModel;
		$ResourceMstModel=new ResourceMstModel;
		$UserModel=new UserModel;
		$StoreGemRefreashMstModel=new StoreGemRefreashMstModel;
		$resource=[];
		$resourceList=[];

		$u_id = $data['u_id'];
		$shopkey='shop'.$u_id.$dmy;
		$UserInfo=$UserModel->where('u_id',$u_id)->first();
		$ref_times=$redis_shop->LRANGE($shopkey,0,0);
		$time=$ref_times['0'];
		if(isset($ref_times))
		{
			if($time<=5)
			{
				$gem=$StoreGemRefreashMstModel->where('id_ref',$time)->first();
				$spend_gem=$gem['gem'];
			}else{
				$spend_gem=100;
			}
			$updateRef=$time+1;
			$redis_shop->LPUSH($shopkey,$updateRef);
		}else{
			$spend_gem=0;
			$redis_shop->LPUSH($shopkey,1);
		}
		
		$updateGem=$UserInfo['u_gem']-$spend_gem;
		$UserModel->where('u_id',$u_id)->update(['u_gem'=>$updateGem,'updated_at'=>$datetime]);

		$resStoreInfo=$UserResHistory->where('u_id',$u_id)->get();

		if(isset($resStoreInfo))
		{
			foreach($resStoreInfo as $obj)
			{
				$id=$obj['r_pur_id'];
				$status=$obj['order_status'];
				if($status == 0)
				{
					$UserResHistory->where('r_pur_id',$id)->update(['order_status'=>2,'updated_at'=>$datetime]);
				}else if($status == 1)
				{
					$UserResHistory->where('r_pur_id',$id)->update(['order_status'=>3,'updated_at'=>$datetime]);
				}
			}

			for($x=1;$x<=5;$x++)
			{
				$r_id = rand(6,10);
				$order_id = $x;
				$UserResHistory->insert(['u_id'=>$u_id,'r_id'=>$r_id,'order_id'=>$order_id,'order_status'=>0,'updated_at'=>$datetime,'created_at'=>$datetime]);
				$resourceInfo=$ResourceMstModel->where('r_id',$r_id)->first();
				$resource['r_id']=$r_id;
				$resource['r_name']=$resourceInfo['r_name'];
				$resource['r_price']=$resourceInfo['r_gem_price'];
				$resource['r_img_path']=$resourceInfo['r_img_path'];
				$resource['r_position']=$order_id;
				$resourceList[]=$resource;
			}
		}else{
			throw new Exception("there have some error");
			$response=[
			'status' => 'Wrong',
			'error' => "please check UserResourcePurchaseHistory table",
			];
		}
		return $resourceList;
	}

	public function buyCoin(Request $request)
	{	
		$req=$request->getContent();
		$data=json_decode($req,TRUE);

		$u_id=$data['u_id'];
		$id=$data['id'];

		$UserModel=new UserModel;
		$StoreGemToCoinMstModel=new StoreGemToCoinMstModel;
		$buyType=$StoreGemToCoinMstModel->where('u_id',$u_id)->first();
		$UserInfo=$UserModel->where('u_id',$u_id)->first();

		$spend_gem=$buyType['gem'];
		$get_coin=$buyType['coin'];

		$updateGem=$UserInfo['u_gem']-$spend_gem;
		$updateCoin=$UserInfo['u_coin']+$get_coin;

		$UserModel->where('u_id',$u_id)->update(['u_gem'=>$updateGem,'u_coin'=>$updateCoin]);

		$response='Buy successfully';
		return $response;		
	}
}