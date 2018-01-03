<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\UserModel;
use App\EquipmentMstModel; 
use App\CharacterModel;
use App\DefindMstModel;
use App\MapModel;
use App\SkillMstModel;
use App\UserBaggageEqModel;
use Illuminate\Support\Facades\Redis;
use DateTime;
use Exception;
class LoadBattleController extends Controller
{
    public function loadingGame(Request $request)
    {
    	$req=$request->getContent();
		$json=base64_decode($req);
	 	//dd($json);
		$data=json_decode($json,TRUE);
        $redis_battle=Redis::connection('battle');
    	if(isset($data)){
    		$match_id=$data['match_id'];
 	    	$matchList=$redis_battle->HGET('match_list',$match_id);

 	    	$matchArr=json_decode($matchList,TRUE);
 	    	$u_id=$data['u_id'];
 	    	if($u_id==$matchArr['u_id']){
 	    		$enmey_uid=$matchArr['enemy_uid'];
 	    	}
 	    	else if($u_id==$matchArr['enemy_uid']){
				$enmey_uid=$matchArr['u_id'];
 	    	}
 	    	else{
 	    		throw new Exception("wrong match_id");
 	    	}

 	    	$charaM=new CharacterModel();
 	    	$eqModel=new EquipmentMstModel();
 	    	$userData=$this->getData($u_id);
 	    	$enemyData=$this->getData($enmey_uid);
 	    	$result['user_data']=$userData;
 	    	$result['enemy_data']=$enemyData;
 	    	$response=json_encode($result,TRUE);
			return  base64_encode($response);
		}
		else {
			throw new Exception("wrong data");
		}
    }

    private function getData($u_id){

 	    $charaM=new CharacterModel();
 	    $eqModel=new EquipmentMstModel();
 	    $skillModel=new SkillMstModel();
        $UserBaggageEqModel=new UserBaggageEqModel();
    	$charData=$charaM->where('u_id',$u_id)->first();
        $charRe['u_id']=$charData['u_id'];
        $charRe['ch_id']=$charData['ch_id'];
        $charRe['ch_title']=$charData['ch_title'];
        $charRe['ch_hp_max']=$charData['ch_hp_max'];
        $charRe['ch_img']=$charData['ch_img'];
 	    $weapon_id=$charData['w_id'];
 	    $movement_id=$charData['m_id'];
 	    $core_id=$charData['core_id'];
 	    $eqData=$eqModel->whereIn('equ_id',[$weapon_id,$movement_id,$core_id])->where()->get();
 	    $result=[];
 	    foreach ($eqData as $key => $eqEach) {
 	    	$skillData=$skillModel->select('skill_id', 'skill_name','skill_icon','skill_chartlet','skill_info')->where('special_skill_id',$eqEach['skill_id'])->first();
 	    	$result[]=$skillData;;
 	    }
 	    $final['chardata']=$charRe;
 	    $final['skillData']=$result;
 	    return $final;
 	     }

}
