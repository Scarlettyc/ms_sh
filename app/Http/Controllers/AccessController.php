<?php

namespace App\Http\Controllers;
use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\UserModel;
class AccessController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    // public function login()
    {
        $req=$request->getContent();
        //print_r($req);
        $json=base64_decode($req);
        $data=json_decode($json,TRUE);
        $usermodel=new UserModel();
        // if($usermodel->isExist('uuid',$data['uuid'])>0){
        //   $userData=UserModel::where('uuid','=',$data['uuid'])->first();
        // }
        // else {
           $usermodel->createNew($data);
           $userData=UserModel::where('uuid','=',$data['uuid'])->first();
        // }
            $res=json_decode($userData,TRUE);
            $json=base64_encode($res);
            print_r($res);
        // }

        return $res ;

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
