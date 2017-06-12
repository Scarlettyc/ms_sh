<?php

namespace App\Http\Controllers;
use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\UserModel;
use App\Charmodel;
use App\NationModel;
use App\NationBuildingsModel;
use App\NationResourceModel;
use App\BuildingsMstModel;
use App\ResourceMstModel;
use App\UserLoginHistoryModel;
use Exception;
use App\Exceptions\Handler;
use Illuminate\Http\Response;
class AccessController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public  function login(Request $request)
    // public function login()
    {

        $req=$request->getContent();
        $json=base64_decode($req);
        $data=json_decode($json,TRUE);

        $usermodel=new UserModel();
        $charmodel=new CharModel();
        $nationModel=new NationModel();
        $nationBuModel=new NationBuildingsModel();
        $nationReModel=new NationResourceModel();
        $userLoginHistory=new UserLoginHistoryModel();
        $result=[];

        $buildingMst=BuildingsMstModel::get();
        $resourceMst=ResourceMstModel::get();
        $result['mst_data']['build_mst']=$buildingMst;
        $result['mst_data']['resource_mst']=$resourceMst;


        // try { 
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
                            $userChar=$charmodel->where('u_id','=',$u_id)->first();
                            $userNation=$nationModel->where('u_id','=',$u_id)->first();
                            $n_id=$userNation['n_id'];
                            $nationBuildings=$nationBuModel->where('n_id','=',$n_id)->get();
                            $nationResource=$nationReModel->where('n_id','=',$n_id)->get();
                            $userLoginCount=UserLoginHistoryModel::where('u_id','=',$u_id)->get()->distinct('loginday')->count('loginday');
                            $result['user_data']['login_count']=$userLoginCount+1;
                            $result['user_data']['char_info']=$userChar;
                            $result['user_data']['nation_data']=$userNation;
                            $result['user_data']['nation_buildings']=$nationBuildings;
                       
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

        // return view('home');
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
