<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\Article;
use App\UserModel;

class TutorialController extends Controller
{
	public function passTutorial(Request $request){
		$req=$request->getContent();
		$json=base64_decode($req);
	 	//dd($json);
		$data=json_decode($json,TRUE);
		$update['passTutorial']=1;
		$update['']
		$uid=$data['u_id'];

		$userModel->update($update)->where('u_id',$uid);
	}
 }
