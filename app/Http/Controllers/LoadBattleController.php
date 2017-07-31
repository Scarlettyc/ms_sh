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
    	if(isset($data)){
    		$match_id=$data['match_id'];
 	    	$matchList=Redis::HGET('match_list',$match_id);
 	    	$matchArr=json_decode($matchList);
 	    	$u_id=$data['u_id'];
 	    	if($u_id==$matchArr[0]){
 	    		$enmey_uid=$matchArr[1];
 	    	}
 	    	else if($u_id==$matchArr[1]){
				$enmey_uid=$matchArr[0];
 	    	}
 	    	else{
 	    		throw new Exception("wrong match_id");
 	    	}

 	    	$charaM=new CharacterModel();
 	    	$eqModel=new EquipmentMstModel();
 	    	$mapData=$this->chooseMap();
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

    private function chooseMap(){
    	$defindmst=new DefindMstModel();
    	$defindData=$defindmst->where('defind_id',10)->first();
    	$mapID=rand($defindData['value1'],$defindData['value2'] );
    	$map=new MapModel();
    	$mapData=$map->where('map_id',$mapID)->first();
    	return $mapData;

    }
    private function getData($u_id){

 	    $charaM=new CharacterModel();
 	    $eqModel=new EquipmentMstModel();
 	    $skillModel=new SkillMstModel();
    	$charData=$charaM->where('u_id',$u_id)->first();
 	    $weapon_id=$charData['w_id'];
 	    $movement_id=$charData['m_id'];
 	    $ad_id=$charData['ad_id'];
 	    $eqData=$eqModel->whereIn('equ_id',[$weapon_id,$movement_id])->get();
 	    $result=[];
 	    foreach ($eqData as $key => $eqEach) {
 	    	$skillData=$skillModel->where('skill_id',$eqEach['equ_id'])->first();
 	    	$tmp['equipment']=$eqEach;
 	    	$tmp['skill']=$skillData;
 	    	$result[]=$tnp;
 	    }
 	    $final['chardata']=$charData;
 	    $final['eqdata']=$result;
 	    return $final;
 	     }

}
