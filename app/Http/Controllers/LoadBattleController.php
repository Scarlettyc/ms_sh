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
use App\MatchRangeModel;
use Log;
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
        $redis_user=Redis::connection('battle_user');
        // $CharSkillEffUtil=new CharSkillEffUtil();
        // $access_token=$data['access_token'];
        // $checkToken=$CharSkillEffUtil->($access_token,$u_id);
    	// if($checkToken){
    		$match_id=$data['match_id'];
            $battleKey='battle_status'.$u_id.$dmy;
 	    	$matchID=$redis_battle->HGET($battleKey,'match_id');
 	    	$enemy_uid=$redis_battle->HGET($battleKey,'enemy_uid');
            $client_id=$redis_battle->HGET($battleKey,'client');

            $key_list='battle'.$u_id.$dmy;
            $key_count=$redis_user->HLEN($key_list);
            $redis_user->HSET($key_list,'match_id',$matchID);
            $redis_user->HSET($key_list,'battle_status',$battleKey);
            $enemy_battle_key='battle_status'.$enemy_uid.$dmy;
            $enemy_client=$redis_battle->HGET($enemy_battle_key,'client');

            if($matchID!=$match_id){
 	    		throw new Exception("wrong match_id");
 	    	}

 	    	$charaM=new CharacterModel();
 	    	$eqModel=new EquipmentMstModel();
 	    	$userData=$this->getData($u_id,$match_id);
            $userData['client_id']=$client_id;
 	    	$enemyData=$this->getData($enemy_uid,$match_id);
            $enemyData['client_id']=$enemy_client;
 	    	$result['user_data']=$userData;
 	    	$result['enemy_data']=$enemyData;
 	    	$response=json_encode($result,TRUE);
			return  base64_encode($response);
		// }
		// else {
		// 	throw new Exception("wrong data");
		// }
    }

    private function getData($u_id,$match_id){
        $now   = new DateTime;
        $dmy=$now->format( 'Ymd' );
 	    $charaM=new CharacterModel();
 	    $eqModel=new EquipmentMstModel();
 	    $skillModel=new SkillMstModel();
        $UserBaggageEqModel=new UserBaggageEqModel();
    	$charData=$charaM->where('u_id',$u_id)->first();
        $redis_battle=Redis::connection('battle');
        $redis_user=Redis::connection('battle_user');
        $battle_status_key='battle'.$u_id;
        // $exist=$redis_user->EXISTS($battle_status_key);
        // if($exist==1){
        //     $redis_user->DEL($battle_status_key);
        // }

        $charRe['u_id']=$charData['u_id'];
        $charRe['ch_id']=$charData['ch_id'];
        $charRe['ch_title']=$charData['ch_title'];
        $charRe['ch_hp_max']=$charData['ch_hp_max'];
        $charRe['ch_img']=$charData['ch_img'];
        $charRe['ch_ranking']=$charData['ch_ranking'];
        $charRe['ch_lv']=$charData['ch_lv'];
 	    $weapon_id=$charData['w_id'];
 	    $movement_id=$charData['m_id'];
 	    $core_id=$charData['core_id'];
        $user_def=($charData['ch_armor']*1.1)/(15*$charData['ch_lv']+$charData['ch_armor']+40);
        $redis_user->HSET($battle_status_key,'ch_def',(round($user_def,3)));
        $redis_user->HSET($battle_status_key,'ch_hp_max',$charData['ch_hp_max']);
        $redis_user->HSET($battle_status_key,'ch_crit',$charData['ch_crit']);
        $redis_user->HSET($battle_status_key,'ch_res',$charData['ch_res']);
        $redis_user->HSET($battle_status_key,'ch_atk',$charData['ch_atk']);
        $redis_user->HSET($battle_status_key,'ch_lv',$charData['ch_lv']);
        $redis_user->HSET($battle_status_key,'x',-1000);
        $redis_user->HSET($battle_status_key,'x2',-1000);
        $redis_user->HSET($battle_status_key,'y',-290);
        $redis_user->HSET($battle_status_key,'y2',-290);
        $redis_user->HSET($battle_status_key,'status',1);
        $redis_user->HSET($battle_status_key,'direction',1);
 	    $eqData=$eqModel->select('equ_group')->where('equ_id',$weapon_id)->first();
        $key_list='battle'.$u_id.$dmy;
        $redis_user->HSET($key_list,'user_status',$battle_status_key);
        $u_list='battle_users';
        $redis_user->HSET($u_list,$u_id,time());
        // $coreData=$eqModel->select('special_skill_id')->where('equ_id',$core_id)->first();
        // $moveData=$eqModel->select('special_skill_id')->where('equ_id',$movement_id)->first();
 	    $result=[];
        $normal_skills=$skillModel->select('skill_id','skill_group', 'skill_damage','skill_name','skill_icon','skill_cd','skill_info')->where('equ_group',$eqData['equ_group'])->where('equ_id',0)->get();
        $special_skill=$skillModel->select('skill_id','skill_group', 'skill_damage','skill_name','skill_icon','skill_cd','skill_info')->where('equ_group',$eqData['equ_group'])->where('equ_id',$weapon_id)->first();
        $core_skill=$skillModel->select('skill_id','skill_group','skill_damage', 'skill_name','skill_icon','skill_cd','skill_info')->where('equ_id',$core_id)->first();
        $movement_skill=$skillModel->select('skill_id','skill_group','skill_damage', 'skill_name','skill_icon','skill_cd','skill_info')->where('equ_id',$movement_id)->first();
        $skill_keys='battle_user_skills_'.$u_id;
         foreach ($normal_skills as $key =>$eachSkill){
            $eachSkill['skill_effs']=$this->getEffs($eachSkill);
            $result['normal_skills'][]=$eachSkill;
            // $redis_battle->HSET($skill_keys,$eachSkill['skill_id'],time());
         }
        // $redis_battle->HSET($skill_keys,$special_skill['skill_id'],time());
        $special_effs=$this->getEffs($special_skill);
        $core_effs=$this->getEffs($core_skill);
        $move_effs=$this->getEffs($movement_skill);
        $special_skill['skill_effs']=$special_effs;
        $result['special_skill']=$special_skill;
        $core_skill['skill_effs']=$core_effs;
        $result['core_skill']=$core_skill;
        $movement_skill['skill_effs']=$move_effs; 
        $move_effsJson=json_encode($move_effs,TRUE);
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
        $redis_battle=Redis::connection('battle');
        $match_id=$data['match_id'];
        $battleKey='battle_status'.$u_id.$dmy;
        if(isset($data)){
            $hveMatchID=$redis_battle->HGET($battleKey,'match_id');
            if($match_id==$hveMatchID){
                $mapId=$redis_battle->HGET($battleKey,'map_id');
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
        $effs= $attackHitUtil->getEffValueBytype($skill['skill_id']);
        return $effs;
    }
}
