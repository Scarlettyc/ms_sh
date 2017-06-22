<?php

namespace App\Http\Controllers;
use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\UserModel;
use App\CharacterModel;
use App\UserLoginHistoryModel;
use Exception;
use App\Exceptions\Handler;
use Illuminate\Http\Response;
class AccessController extends Controller
{
	public function login(Request $request)
	{
		$req=$request->getContent();
		$json=base64_decode($req);
		$data=json_decode($json,TRUE);

		$usermodel=new UserModel();
		$characterModel=new CharacterModel();
		$userLoginHistoryModel=new UserLoginHistoryModel();
		$result=[];

		$usrGem=UserModel::get();
		$result['mst_data']['user_gem']=$userGem;

		if(isset($data["uuid"]))
		{  
			$userData=$data;
			if($usermodel->isExist('uuid',$data['uuid'])>0)
			{
				$userData=$usermodel->where('uuid','=',$data['uuid'])->first();
				$result['user_data']['user_info']=$userData;
				if($usermodel['pass_tutorial'])
				{
					$u_id=$userData['u_id'];
					$userChar=$charactermodel->where('u_id','=',$u_id)->first();
                            //$userNation=$nationModel->where('u_id','=',$u_id)->first();
                            //$n_id=$userNation['n_id'];
                            //$nationBuildings=$nationBuModel->where('n_id','=',$n_id)->get();
                            //$nationResource=$nationReModel->where('n_id','=',$n_id)->get();
					$userLoginCount=UserLoginHistoryModel::where('u_id','=',$u_id)->get()->distinct('loginday')->count('loginday');
					$result['user_data']['login_count']=$userLoginCount+1;
					$result['user_data']['character_info']=$userCharacter;
                            //$result['user_data']['nation_data']=$userNation;
                            //$result['user_data']['nation_buildings']=$nationBuildings;
				}
			}
			else {
				$usermodel->createNew($data);
				$userData=$usermodel->where('uuid','=',$data['uuid'])->first();
				$result['user_data']['user_info']=$userData;

			}
			$userLoginHistory->createNew($userData);

			$response=json_encode($result,TRUE);
			$response=base64_encode($response);
		}
		else {

			throw new Exception("oppos, you nee Need UUId");
			$response = [
			'status' => 'wrong',
			'error' => "please send uuid",
			];
		}
		return  $response;
	}

	public function update(Request $request)
	{
		$req=$request->getContent();
        $json=base64_decode($req);
        $data=json_decode($json,TRUE);
        $usermodel=new UserModel();
        $usermodel->createNew($data);
        $responseData=UserModel::where('u_id',$data['u_id'])->get();

        return json_encode($responseData);
	}
}