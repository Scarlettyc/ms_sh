<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\UserModel;
use App\MatchRangeModel;
use App\CharacterModel;
use Illuminate\Support\Facades\Redis;
use DateTime;
use Exception;
class BattleController extends Controller
{
    public function battle($data)
    {
    	$req=$request->getContent();
		$json=base64_decode($req);
	 	//dd($json);
		$data=json_decode($json,TRUE);
		$u_id=$data['u_id'];		
		$now   = new DateTime;;
		$dmy=$now->format( 'Ymd' );
		$data=json_decode($json,TRUE);
		if(isset($data)){



		}
    	else{
 	    		throw new Exception("there have some error");
 	    }
 	    	
	}
}
