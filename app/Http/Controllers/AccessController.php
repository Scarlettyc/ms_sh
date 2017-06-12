<?php

namespace App\Http\Controllers;
use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\UserModel;
use Exception;
use App\Exceptions\Handler;
use Response;
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
        try { 
                if(array_key_exists("uuid",$data))
                {
                    if($usermodel->isExist('uuid',$data['uuid'])>0){
                        $userData=UserModel::where('uuid','=',$data['uuid'])->first();
                        $response=json_encode($userData,TRUE)；
                    }
                    else {
                        $usermodel->createNew($data);
                        $userData=UserModel::where('uuid','=',$data['uuid'])->first();
                        $response=json_encode($userData,true)；
                        $response=base64_enode($response);
                    }
                }
                else {

                    throw new Exception("oppos, you nee Need UUId");
                }

            $response = [
                'status' => 0,
                'error' => "please send uuid",
            ];
        }
        catch (Exception $e) {

            Handler::exceptionHandle($e);
        } 

         return Response::json($response);

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
