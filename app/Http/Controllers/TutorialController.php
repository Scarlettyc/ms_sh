<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\Article;
use App\UserModel;
use App\CharacterModel;
use Exception;
use DateTime;
class TutorialController extends Controller
{
	public function createChar(Request $request){
		$req=$request->getContent();
		$json=base64_decode($req);
	 	//dd($json);
		$data=json_decode($json,TRUE);
		$uid=$data['u_id'];
		$characterModel=new CharacterModel();
		//dd($characterModel->isExist('u_id',$uid));
		if($characterModel->isExist('u_id',$uid)==0)
		{
 			$now   = new DateTime;

			$datetime=$now->format( 'Y-m-d h:m:s' );
			$char['ch_title']=$data['title'];
			$char['createdate']=$datetime;
			$char['u_id']=$uid;
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
