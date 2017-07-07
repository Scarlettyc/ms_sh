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
			$finalChar=$characterModel->where('u_id',$uid)->first();
			$response['user_data']['character_info']=json_encode($finalChar,TRUE);
			return $response;
		}
		else {
			throw new Exception("char already exist");
		}
	}
	public function passTu(Request $request){
		$req=$request->getContent();
		$json=base64_decode($req);
		$data=json_decode($json,TRUE);
		$uid=$data['u_id'];
		$usermodel=new UserModel();
		$characterModel=new CharacterModel();
		$usermodel->where('u_id',$data['u_id'])->update(["pass_tutorial"=>1]);

		$finalUser=$usermodel->where('u_id',$uid)->first();
		$response['user_data']['user_info']=json_encode($finalUser,TRUE);
		return $response;
	}
 }
