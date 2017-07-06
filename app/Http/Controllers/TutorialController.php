<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\Article;
use App\UserModel;
use App\CharacterModel;

class TutorialController extends Controller
{
	public function createChar(Request $request){
		$req=$request->getContent();
		$json=base64_decode($req);
	 	//dd($json);
		$data=json_decode($json,TRUE);
		$uid=$data['u_id'];
		if($characterModel->isExist('u_id',$uid)<0)
		{
			$characterModel=new CharacterModel();
			$datetime=$now->format( 'Y-m-d h:m:s' );
			$char['title']=$data['title'];
			$char['createdate']=$datetime;
			$char['u_id']=$datetime;
			$characterModel->insert($char);
		}
		else {
			throw new Exception("char already exist");
		}
	}
	public function updateEq(Request $request){
		$req=$request->getContent();
		$json=base64_decode($req);
		$data=json_decode($json,TRUE);
		$uid=$data['u_id'];
		$usermodel=new UserModel();
		$characterModel=new CharacterModel();
		$update['passTutorial']=1;
		$usermodel->update($update)->where('u_id',$uid);
	}
 }
