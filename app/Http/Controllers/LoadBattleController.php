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
use App\Util\MapTrapUtil;
use Illuminate\Support\Facades\Redis;
use App\SkillEffDeatilModel;
use App\Util\AttackHitUtil;
use DateTime;
use Exception;
use DB;
// use App\Util\CharSkillEffUtil;
class LoadBattleController extends Controller
{
    public function loadingGame(Request $request)
    {
    	$req=$request->getContent();
		$json=base64_decode($req);
	 	//dd($json);
        $now   = new DateTime;
        $dmy=$now->format( 'Ymd' );
		$data=json_decode($json,TRUE);
        $u_id=$data['u_id'];
        $redis_battle=Redis::connection('battle');
        // $CharSkillEffUtil=new CharSkillEffUtil();
        // $access_token=$data['access_token'];
        // $checkToken=$CharSkillEffUtil->($access_token,$u_id);
    	// if($checkToken){
    		$match_id=$data['match_id'];
            $battleKey='battle_status'.$dmy;
 	    	$matchList=$redis_battle->HGET($battleKey,$u_id);

 	    	$matchArr=json_decode($matchList,TRUE);
 	    	
            if($matchArr['match_id']!=$match_id){
 	    		throw new Exception("wrong match_id");
 	    	}
            $enemy_uid=$matchArr['enemy_uid'];

 	    	$charaM=new CharacterModel();
 	    	$eqModel=new EquipmentMstModel();
 	    	$userData=$this->getData($u_id);
 	    	$enemyData=$this->getData($enemy_uid);
 	    	$result['user_data']=$userData;
 	    	$result['enemy_data']=$enemyData;
 	    	$response=json_encode($result,TRUE);
			return  base64_encode($response);
		// }
		// else {
		// 	throw new Exception("wrong data");
		// }
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
        $charRe['ch_lv']=$charData['ch_lv'];
 	    $weapon_id=$charData['w_id'];
 	    $movement_id=$charData['m_id'];
 	    $core_id=$charData['core_id'];
 	    $eqData=$eqModel->select('equ_group')->where('equ_id',$weapon_id)->first();
        // $coreData=$eqModel->select('special_skill_id')->where('equ_id',$core_id)->first();
        // $moveData=$eqModel->select('special_skill_id')->where('equ_id',$movement_id)->first();
 	    $result=[];
        $normal_skills=$skillModel->select('skill_id','skill_group', 'skill_damage','skill_name','skill_icon','skill_cd','skill_info')->where('equ_group',$eqData['equ_group'])->where('equ_id',0)->get();
        $special_skill=$skillModel->select('skill_id','skill_group', 'skill_damage','skill_name','skill_icon','skill_cd','skill_info')->where('equ_group',$eqData['equ_group'])->where('equ_id',$weapon_id)->first();
        $core_skill=$skillModel->select('skill_id','skill_group','skill_damage', 'skill_name','skill_icon','skill_cd','skill_info')->where('equ_id',$core_id)->first();
        $movement_skill=$skillModel->select('skill_id','skill_group','skill_damage', 'skill_name','skill_icon','skill_cd','skill_info')->where('equ_id',$movement_id)->first();
         foreach ($normal_skills as $key =>$eachSkill){
            $tmp['skills']=$eachSkill;
            $tmp['skill_effs']=$this->getEffs($eachSkill);
            $result['normal_skills'][]=$tmp;
         }
        $special_effs=$this->getEffs($special_skill);
        $core_effs=$this->getEffs($core_skill);
        $move_effs=$this->getEffs($movement_skill);
        $special_skill['skill_effs']=$move_effs;
        $result['special_skill']=$special_skill;
        $core_skill['skill_effs']=$move_effs;
        $result['core_skill']=$core_skill;
        $movement_skill['skill_effs']=$move_effs; 
        $result['movement_skill']=$movement_skill;
        $final['chardata']=$charRe;
 	    $final['skillData']=$result;
 	    return $final;
 	  }

    public function loadMap(Request $request){
        $req=$request->getContent();
        $json=base64_decode($req);
        $now   = new DateTime;
        $dmy=$now->format( 'Ymd' );
        $data=json_decode($json,TRUE);
        $mapTrapUtil=new MapTrapUtil();
        $data=json_decode($json,TRUE);
        $redisLoad= Redis::connection('default');
        $u_id=$data['u_id'];
        // $CharSkillEffUtil=new CharSkillEffUtil();
        // $access_token=$data['access_token'];
        // $checkToken=$CharSkillEffUtil->($access_token,$u_id);
        $redis_battle=Redis::connection('battle');
        $match_id=$data['match_id'];
        $battleKey='battle_status'.$dmy;
        if(isset($data)){
            $matchList=$redis_battle->HGET($battleKey,$u_id);
            $matchArr=json_decode($matchList,TRUE);
            if($match_id==$matchArr['match_id']){
                $mapId=$matchArr['map_id'];
                $mapData=$mapTrapUtil->getMapData($mapId);
                $result["map_data"]=$mapData;
                $response=json_encode($result,TRUE);
             return  base64_encode($response);
            } 
        }
    }

    private function getEffs($skill){
        $attackHitUtil=new AttackHitUtil();
        $result=[];
        $effs= $attackHitUtil->getEffValue($skill->skill_id);
        return $effs;
    }
}
