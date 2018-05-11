<?php
namespace App\Util;
use App\Http\Requests;
use App\SkillMstModel;
use App\MapStoneRelationMst;
use App\AtkEffectionMst;
use App\BuffEffectionMst;
use App\Util\MapTrapUtil;
use App\DefindMstModel;
use Exception;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\Redis;
use Log;
use App\SkillEffDeatilModel;
use App\EffElementModel;


class AttackHitUtil
{
	public function getSelfEff($skill_id,$user,$enemy,$occurtime){
		$skillModel=new SkillMstModel();
 		$buffEffModel= new BuffEffectionMst();
 		$atkEffModel=new AtkEffectionMst();
		$skillData=$skillModel->where('skill_id',$skill_id)->first();
		$result=[];
		if($skillData['self_buff_eff_id']!=0){
			$mileSecond=$this->getMillisecond();
			$buffEff=$buffEffModel->where('buff_eff_id',$skillData['self_eff_id'])->first();
			if($mileSecond-$occurtime<=$buffEff['eff_skill_dur']){
				$result['selfbuff']=$buffEff;
			}
		}
		return $result;
	}

	public function getatkEff($skill_id,$user,$enemy,$clientID,$enemy_clientId,$user_direction,$enemy_direction){
		$atkEffModel=new AtkEffectionMst();
		$skillModel=new SkillMstModel();
		$tmp=1;
		if($clientID>$enemy_clientId){
			$user['x']=-$user['x'];
			$tmp=-1;
			$user_direction=-$user_direction;
		}

		$skillData=$skillModel->select('atk_eff_id')->where('skill_id',$skill_id)->first();
		$atkEff=$atkEffModel->where('atk_eff_id',$skillData['atk_eff_id'])->first();
		$effXfrom=$enemy['x'];
		$effYfrom=$enemy['y'];

			if(abs($user['x']-$enemy['x'])<$atkEff['eff_skill_hit_width']&&($user['x']-$enemy['x'])*$enemy_direction>0){
				return $atkEff;
			}
 			// if(abs($user_direction*$user['x']-($enemy_direction)*$enemy['x'])<=$atkEff['eff_skill_hit_width']&&abs($user['y']-$enemy['y'])<=$atkEff['eff_skill_hit_henght']){
 		 // 		return $atkEff;
 		 // 	}
 		 	else {
 		 		return null;
 		 	}
	}



	public function getconstantEff($skill_id,$occurtime,$user,$enemy,$clientID,$enemy_clientId,$user_direction,$enemy_direction,$constantEff){
		$atkEffModel=new AtkEffectionMst();
		$skillModel=new SkillMstModel();
		$buffEffModel= new BuffEffectionMst();
		if($clientID>$enemy_clientId){
			$user['x']=-$user['x'];
		}
		if(isset($constantEff['enemy_buff_eff_id'])&&time()-$occurtime<$constantEff['enemy_buff_constant_time']){
			$result['enemy_buff']=$this->buffStatus($constantEff['enemy_buff_eff_id']);

		}

		if(isset($constantEff['atk_eff_id'])){
			$atkEff=$atkEffModel->where('atk_eff_id',$skillData['atk_eff_id'])->first();
			if($atkEff['eff_skill_move_distance']>0){
				if($constantEff['start_x']+$atkEff['eff_skill_move_distance']-$user['x']<=2){
				 if(abs($user_direction*$user['x']-($enemy_direction)*$enemy['x'])<=$atkEff['eff_skill_hit_width']&&abs($user['y']-$enemy['y'])<=$atkEff['eff_skill_hit_width']){
 		 			$result['atkEff']=$atkEff;
				}

			}
			else if(time()-$occurtime<$constantEff['atk_constant_time']) {
				if(abs($user_direction*$user['x']-($enemy_direction)*$enemy['x'])<=$atkEff['eff_skill_hit_width']&&abs($user['y']-$enemy['y'])<=$atkEff['eff_skill_hit_width']){
 		 			$result['atkEff']=$atkEff;
 		 		}
			}
			} 
		}
		return $result;
	}

	public function buffStatus($buff_id){
		$buffEff=$buffEffModel->where('eff_id',$buff_id)->first();
		switch ($buffEff['eff_buff_type']) {
			case 1:
				$eff_ch_stun=$buffEff['eff_value1'];
				$result['eff_ch_stun']=$eff_ch_stun;
			case 3:
				$eff_ch_stun=$buffEff['eff_value1'];
				$result['eff_ch_stun']=$eff_ch_stun;			
			case 2:
				$eff_ch_stun=$buffEff['eff_value1'];;
				$result['eff_ch_stun']=$eff_ch_stun;
			case 7:
				$eff_ch_res_per=$buffEff['eff_value1'];
				$result['eff_ch_res_per']=$eff_ch_res_per;
				break;
			case 9:
				$eff_ch_uncontrollable=$buffEff['eff_value1'];
				$result['eff_ch_uncontrollable']=$eff_ch_uncontrollable;
				break;
			case 6;
			   	$eff_ch_invincible=$buffEff['eff_value1'];
				$result['eff_ch_invincible']=$eff_ch_invincible;
				break;

			}
			return $result;

	} 

	public function checkEffConstant($skill_id,$x){
    	$skillModel=new SkillMstModel();
    	$skill_data=$skillModel->where('skill_id',$skill_id)->first();
    	$result=[];
    	if($skill_data['buff_constant_time']!=0){
    		$result['self_buff_eff_id']=$skill_data['self_buff_eff_id'];
    		$result['buff_constant_time']=$skill_data['buff_constant_time'];
    	}
    	if($skill_data['enemy_buff_constant_time']!=0){
    		$result['enemy_buff_eff_id']=$skill_data['enemy_buff_eff_id'];
    		$result['enemy_buff_constant_time']=$skill_data['enemy_buff_constant_time'];
    	}
    	if($skill_data['atk_constant_time']!=0){
    		$atkEffModel=new AtkEffectionMst();
    		$atkEff=$atkEffModel->where('atk_eff_id',$skill_id)->first();
    		$result['atk_eff_id']=$skill_data['atk_eff_id'];
    		$result['start_x']=$x;
    		$result['atk_constant_time']=$skill_data['atk_constant_time'];
 
    	}
    	return $result;
    }
    
    public function haveEffConstant($constantEff,$skill_occur_time){
    		if(isset($constantEff['buff_constant_time'])&&time()-$skill_occur_time<$constantEff['buff_constant_time']){
    		$result['self_buff_eff_id']=$skill_data['self_buff_eff_id'];
    		$result['buff_constant_time']=$skill_data['buff_constant_time'];
    		$result['buff_last_time']=time()-$skill_occur_time-$skill_data['buff_constant_time'];
    	}
    	if(isset($constantEff['enemy_buff_constant_time'])&&time()-$skill_occur_time<$constantEff['enemy_buff_constant_time']){
    		$result['enemy_buff_eff_id']=$skill_data['enemy_buff_eff_id'];
    		$result['enemy_buff_constant_time']=$skill_data['enemy_buff_constant_time'];
    		$result['enemy_buff_last_time']=time()-$skill_occur_time-$skill_data['enemy_buff_constant_time'];
    	}
    	if(isset($constantEff['atk_constant_time'])&&time()-$skill_occur_time<$constantEff['atk_constant_time']){
    		if(isset($constantEff['start_x'])){
    			$result['start_x']=$constantEff['start_x'];
    		}
    		$result['atk_eff_id']=$skill_data['atk_eff_id'];
    		$result['atk_constant_time']=$skill_data['atk_constant_time'];
    		$result['atk_last_time']=time()-$skill_occur_time-$skill_data['atk_constant_time'];
    	}
    	return $result;
    }

	public function getMillisecond() {
		list($t1, $t2) = explode(' ', microtime());
		return (float)sprintf('%.0f',(floatval($t1)+floatval($t2))*1000);
}

    private function checkSkillInterrput($map_id,$effX,$effY,$effR,$interrput){
    	$mapUtil=new MapTrapUtil();
    	$interrput=0;
    	$end=1;
    		if($interrput==3||$interrput==2){
    			$interrput=$mapUtil->checkEffstone($map_id,$effX,$effY,$effR,$effAngle);
    		}
    		else if($interrput==0){
    			$end=0;
    		}
    	return ['interrput'=>$interrput,'end'=>$end];
    } 

 //  private function getEffElement($skill_id){
 //  	$skillModel=new SkillMstModel();
 //  	$skillEffModel=new SkillEffModel();
	// $effElementModel=new EffElementModel();
	// $buffEffectionMst=new BuffEffectionMst();
	// $skillData=$skillModel->select('skill_id','self_buff_eff_id','buff_constant_time','enemy_buff_eff_id','enemy_buff_constant_time','atk_eff_id','atk_constant_time')->Where('skill_id',$skill_id)->first();
	// $effData=$skillEffModel->select('eff_id','eff_element_id','eff_value','eff_type')->wherein('eff_id',[$skillData['atk_eff_id'],$skillData['self_buff_eff_id'],$skillData['enemy_buff_eff_id']])->get();
	// return $effData;

 //  }
/*
/*2018.04.27 edition*/
	public function checkSkillHit($enemySkill,$x,$y,$enemyX,$enemyY,$direction,$enemy_direction,$match_id,$enemy_uid){

		$skillModel=new SkillMstModel();
		$SkillEffDeatilModel=new SkillEffDeatilModel();
    $redis_battle=Redis::connection('battle');
		$skill_id=$enemySkill['skill_id'];
		$skill_group=$enemySkill['skill_group'];
		$skill_damage=$enemySkill['skill_damage'];
		$skill_prepare_time=$enemySkill['skill_prepare_time'];
		$skill_atk_time=$enemySkill['skill_atk_time'];
		$skillEffs=$SkillEffDeatilModel->where('skill_id',$skill_id)->get();
		$effs=$this->findEffFunciton($skillEffs);
		$defindMst=new DefindMstModel();
		$current=$this->getMillisecond();
		
		$defindFront=$defindMst->select('value1','value2')->where('defind_id',9)->first();
		$defindBack=$defindMst->select('value1','value2')->where('defind_id',11)->first();

		$x_front=$x+$defindFront['value1']*$enemy_direction;
		$x_back=$x+$defindBack['value1']*$enemy_direction;
		$y_font=$y+$defindFront['value2'];
		$y_back=$y+$defindBack['value2'];
    $hit=false;

		// if($skill_damage==1){
			// if(isset($effs['TL_x'])){
				$enemyX_from=$enemyX+$effs['TL_x']*$enemy_direction;
			// $enemyX_from=$enemyX+$effs['TL_x'];
				$enemyY_from=$enemyY+$effs['BR_y'];
			//$enemyX_to=$enemyX+$effs['BR_x']*$enemy_direction;
				$enemyX_to=$enemyX+$effs['BR_x']*$enemy_direction;
				$enemyY_to=$enemyY+$effs['TL_y'];
      //   }
      // }
       if($skill_damage==2){
          $fly_tools_key='battle_flytools'.$match_id.$enemy_uid;
          $fly_toolsJson=$redis_battle->HGET($fly_tools_key,$skill_id);
          $fly_toolsData=json_decode($fly_toolsJson,TRUE);
          $occur_time=$fly_toolsData['occur_time'];
          $start_x=$fly_toolsData['start_x'];
          $start_y=$fly_toolsData['start_y'];
          $start_direction=$fly_toolsData['start_direction'];
        if(isset($effs['TL_x'])&&$current-$occurtime<$effs['eff_duration']){
          if($current-$occurtime>0){
            $start_x=$start_x+$effs['eff_spead']*($current-$occurtime)*$start_direction;
            }
            $enemyX_from=$start_x+$effs['TL_x']*$start_direction;
            $enemyY_from=$start_y+$effs['BR_y'];
            $enemyX_to=$start_x+$effs['BR_x']*$start_direction;
            $enemyY_to=$start_y+$effs['TL_y'];
      }
    }
				if($enemyX_from<$enemyX_to&&$enemyY_from<$enemyY_to){
					if(($x_front>=$enemyX_from&&$x_front<=$enemyX_to&&$y_font>=$enemyY_from&&$y_font<=$enemyY_to)||($x_back>=$enemyX_from&&$x_back<=$enemyX_to&&$y_back>=$enemyY_from&&$y_back<=$enemyY_to)){
            $hit=true;
					}
				}
				else if($enemyX_from<$enemyX_to&&$enemyY_from>$enemyY_to){
					if(($x_front>=$enemyX_from&&$x_front<=$enemyX_to&&$y_font<=$enemyY_from&&$y_font>=$enemyY_to)||($x_back>=$enemyX_from&&$x_back<=$enemyX_to&&$y_back<=$enemyY_from&&$y_back>=$enemyY_to)){
              $hit=true;
						}
					}
				else if($enemyX_from>$enemyX_to&&$enemyY_from<$enemyY_to){
					if(($x_front<=$enemyX_from&&$x_front>=$enemyY_from&&$y_font>=$enemyY_from&&$y_font<=$enemyY_to)||($x_back<=$enemyX_from&&$x_back>=$enemyX_to&&$y_back>=$enemyY_from&&$y_back<=$enemyY_to)){
              $hit=true;
					}
				}
				else if($enemyX_from<$enemyX_to&&$enemyY_from<$enemyY_to){
					if(($x_front<=$enemyX_from&&$x_front>=$enemyY_from&&$y_font>=$enemyY_from&&$y_font<=$enemyY_to)||($x_back<=$enemyX_from&&$x_back>=$enemyX_to&&$y_back<=$enemyY_from&&$y_back>=$enemyY_to)){
						$hit=true;
						}
					}

          if($hit&&$skill_damage==2){
            $redis_battle->HDEL($fly_tools_key,$skill_id);
            // Log::info('not hit skill_id'.$skill_id.' enemyX'.$enemyX.' enemyY'.$enemyY.' enemyskillXfrom'.$enemyX_from.' enemyskillXto'.$enemyX_to.' enemyskillYfrom'.$enemyY_from.' enemyskillYto'.$enemyY_to.' enemy_direction'.$enemy_direction.' userxfront'.$x_front.' useryfront'.$y_font.' user_xBack'.$x_back.' user_yBack'.$y_back.' userDirection'.$direction);	
			   }
         else if(!$hit&&$skill_damage==2&&$current-$occurtime>$effs['eff_duration']){
           $redis_battle->HDEL($fly_tools_key,$skill_id);
         }
         else {
          return $hit;
		}
	}

	public function getEffValue($skill_id){
  		$skillModel=new SkillMstModel();
  		$SkillEffDeatilModel=new SkillEffDeatilModel();
		$skillEffs=$SkillEffDeatilModel->select('eff_element_id','eff_value','eff_type')->where('skill_id',$skill_id)->get();
		// $result=$this->findEffFunciton($skillEffs);
		return $skillEffs;
  }
  	public function findEffFunciton($skill_eff){
  		$result=[];
  		foreach ($skill_eff as $key => $each_eff) {
  		switch ($each_eff->eff_element_id) {
  			case 1:
  				$result['TL_x']=$each_eff->eff_value;
  				break;
  			case 2:
  				$result['TL_y']=$each_eff->eff_value;
  				break;
  			case 3:
  				$result['BR_x']=$each_eff->eff_value;
  				break;
  			case 4:
  				$result['BR_y']=$each_eff->eff_value;
  				break;
  			case 5:
  				$result['hit recover']=$each_eff->eff_values;
  				break;
			case 6:
  			$result['knockdown']=$each_eff->eff_value;
  				break;
  			case 7:
  			$result['stun']=$each_eff->eff_value;
  			case 8:
  			$result['execute']=$each_eff->eff_value;
  				break;
  			case 9:
  			$result['snipe']=$each_eff->eff_value;
  				break;
  			case 11:
  			$result['dash']=$each_eff->eff_value;
  				break;
  			case 12:
  			$result['immune control']=$each_eff->eff_value;
  				break;
  			case 13:
  			$result['displacement']=$each_eff->eff_value;
  				break;
  			case 14:
  			$result['spread']=$each_eff->eff_value;
  				break;
  			case 15:
  			$result['block']=$each_eff->eff_value;
  				break;
   			case 16:
  			$result['damage reduction']=$each_eff->eff_value;
  				break;
  			case 17:
  			$result['strike back']=$each_eff->eff_value;
  				break;
  			case 18:
  			$result['crash']=$each_eff->eff_value;
  				break;
  			case 19:
  			$result['shadowstep']=$each_eff->eff_value;
  				break;
  			case 20:
  			$result['crit']=$each_eff->eff_value;
  				break;
  			case 21:
  			$result['omnislash']=$each_eff->eff_value;
  				break;
  			case 22:
  			$result['avatar']=$each_eff->eff_value;
  				break;
  			case 23:
  			$result['eff_skill_atk_point']=$each_eff->eff_value;
  				break;
  			case 24:
  			$result['crash_hitpeole']=$each_eff->eff_value;
  				break;
  			case 25:
  			$result['movable']=$each_eff->eff_value;
  				break;
  			case 26:
  			$result['quick_time']=$each_eff->eff_value;
  				break;
  			case 27:          
  			$result['dash_time']=$each_eff->eff_value;
               				break;
            case 28:
             $result['dash_direction']=$each_eff->eff_value;
              				break;
            case 29:
             $result['displacement_direction']=$each_eff->eff_value;
              				break;
            case 30:
             $result['displacement_distance']=$each_eff->eff_value;
              				break;
            case 31:
             $result['spread_distance']=$each_eff->eff_value;
              				break;
            case 32:
             $result['spread_frequency']=$each_eff->eff_value;
              				break;
            case 33:
             $result['block_percentage']=$each_eff->eff_value;
              				break;
            case 34:
             $result['damage_reduction_percentage']=$each_eff->eff_value;
              				break;
            case 35:
             $result['slow_percentage']=$each_eff->eff_value;
              				break;
            case 36:
             $result['strike back_percentage']=$each_eff->eff_value;
              				break;
            case 37:
             $result['quick_percentage']=$each_eff->eff_value;
              				break;
            case 38:
             $result['crit_percentage']=$each_eff->eff_value;
              				break;
            case 39:
             $result['execute_damage']=$each_eff->eff_value;
              				break;
            case 40:
             $result['avatar_damage_precent']=$each_eff->eff_value;
              				break;
            case 41:
             $result['avatar_defince_precent']=$each_eff->eff_value;
              				break;
            case 42:
             $result['avatar_atk_range_precent']=$each_eff->eff_value;
              				break;
            case 43:
             $result['eff_duration']=$each_eff->eff_value;
              				break;
            case 44:
             $result['eff_spead']=$each_eff->eff_value;
              				break;
            case 45:
             $result['eff_interval']=$each_eff->eff_value;
              				break;

  			default:
  				# code...
  				break;

  		}
  		# code...
  	}
  	return $result;

  }

 /*
  code edition from 2018.04.10
*/
  public function calculateCharValue($chardata,$enemyData,$skillatkEff){
  		$randCrit=rand(1,100);
  		$critBool=1;
		if($randCrit<=$enemyData['ch_crit']){
		$critBool=2;
		}
		$user_def=($chardata['ch_armor']*1.1)/(15*$chardata['ch_lv']+$chardata['ch_armor']+40);
		$enemy_res=$enemyData['ch_res'];
		$hpMax=$chardata['ch_hp_max'];
  		if($enemyData['skill']['skill_group']==1||$enemyData['skill']['skill_group']==5||$enemyData['skill']['skill_group']==6){
			$enemy_atk=$enemyData['ch_atk']*$skillatkEff['eff_skill_atk_point']*$enemy_res;
			$enemyDMG=($enemy_atk*$critBool)*(1-$user_def);
			Log::info('enemyDMG'.$enemyDMG);
			$hpMax=$chardata['ch_hp_max'];
			$chardata['ch_hp_max']=round($hpMax-$enemyDMG);
			if($chardata['ch_hp_max']<0){
				$chardata['ch_hp_max']=0;
			}
  		}
  		else if ($enemyData['skill']['skill_group']==2){
  			$enemy_atk=$enemyData['ch_atk']*$atkeff['eff_skill_atk_point']+pow($enemy_charData['ch_lv'],2)*2;
 	 		$enemyDMG=($enemy_atk*$critBool)*(1-$user_def);
 	 		$hpMax=$chardata['ch_hp_max'];
			$chardata['ch_hp_max']=round($hpMax-$enemy_atk);
  	}
  	return $chardata;

  }

}
