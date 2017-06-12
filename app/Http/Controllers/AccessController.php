<?php

namespace App\Http\Controllers;
use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\UserModel;
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
        // try { 
                if(isset($data["uuid"]))
                {
                    if($usermodel->isExist('uuid',$data['uuid'])>0){
                        $userData=$usermodel->where('uuid','=',$data['uuid'])->get();
                        $response=json_encode($userData,TRUE);
                        $response=base64_encode($response);
                    }
                    else {
                        $usermodel->createNew($data);
                        $userData=$usermodel->where('uuid','=',$data['uuid'])->first();
                        $response=json_encode($userData,TRUE);
                        $response=base64_encode($response);
                    }
                }
                else {

                    throw new Exception("oppos, you nee Need UUId");

                $response = [
                'status' => 'wrong',
                'error' => "please send uuid",
                    ];
            }

         return  response()->json($response);

        // return view('home');
    }
    // public function update(Request $request)
    // {   
    //     $req=$request->getContent();
    //     $json=base64_decode($req);
    //     $data=json_decode($json,TRUE);
    //     $usermodel=new UserModel();
    //     $usermodel->createNew($data);
    //     $responseData=UserModel::where('u_id',$data['u_id'])->get();

    //     return json_encode($responseData);

    // }
    
}
