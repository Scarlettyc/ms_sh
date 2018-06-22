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
use App\CharacterModel;
use App\EquipmentMstModel;


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
			return 
                  $result;

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
    	return 
                  $result;
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
    	return 
                  $result;
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

/*2018.04.27 edition*/

	public function checkSkillHit($enemySkill,$x,$y,$direction,$match_id,$enemy_uid,$u_id){
		$skillModel=new SkillMstModel();
		$SkillEffDeatilModel=new SkillEffDeatilModel();
    $redis_battle=Redis::connection('battle');
	   //	$skill_id=$enemySkill['skill_id'];
		//$skill_group=$enemySkill['skill_group'];

    $skill_id=$enemySkill['skill_id'];
    $skill_damage=$enemySkill['skill_damage'];
    $enemyX=$enemySkill['x'];
    $enemyY=$enemySkill['y'];
    $enemy_direction=$enemySkill['direction'];
    //$this->clearOutOftime($match_id,$enemy_uid,$skill_id);
		// $skill_prepare_time=$enemySkill['skill_prepare_time'];
		// $skill_atk_time=$enemySkill['skill_atk_time'];
		$skillEffs=$SkillEffDeatilModel->where('skill_id',$skill_id)->get();
		$effs=$this->findEffFunciton($skillEffs);
		$defindMst=new DefindMstModel();
		$current=$this->getMillisecond();

		
		$defindFront=$defindMst->select('value1','value2')->where('defind_id',9)->first();
		$defindBack=$defindMst->select('value1','value2')->where('defind_id',11)->first();

		$x_front=$x+$defindFront['value1']*$direction;
		$x_back=$x+$defindBack['value1']*$direction;
		$y_front=$y+$defindFront['value2'];
		$y_back=$y+$defindBack['value2'];


    $enmeyX_front=$enemyX+$defindFront['value1']*$enemy_direction;
    $enmeyX_back=$enemyX+$defindBack['value1']*$enemy_direction;
    $enmeyY_front=$enemyY+$defindFront['value2'];
    $enmeyY_back=$enemyY+$defindBack['value2'];
    $hit=false;
    $startDamage=0;
      // $checkMyBuffs=$this->checkBuffs($match_id,$u_id,1);
      // $checkDebuffs=$this->checkBuffs($match_id,$u_id,2);
      $stun=0;
      // if($skill_id>=67&&$skill_id<=72){
      //       $battleData=json_encode($enemySkill,TRUE);
      //       $occur_time=$enemySkill['occur_time'];
      //       $start_x=-($enemySkill['start_x']);
      //       $start_y=($enemySkill['start_y']);
      //       $start_direction=-$enemySkill['start_direction'];
      //       $multi_interval_key='multi_interval'.$match_id.$enemy_uid.'_'.$skill_id;
      //     $count=$redis_battle->HLEN($multi_interval_key);
      //     $lastInterval=$redis_battle->HGET($multi_interval_key,$count);
      //     $interval=500;
      //     $code='B04_a';
      //     if($count==1){
      //       $code='B04_a';
      //     }
      //     else if($count==2){
      //       $code='B04_b';
      //     }
      //     else if($count==3){
      //       $code='B04_c'; 

      //     }
      //       $interval=$defindMst->select('value2')->where('comment', 'like',$code)->where('value1',45)->first();
      //       $TL_x_a=$defindMst->select('value2')->where('comment', 'like',$code)->where('value1',1)->first();
      //       $TL_y_a=$defindMst->select('value2')->where('comment', 'like',$code)->where('value1',2)->first();
      //       $BR_x_a=$defindMst->select('value2')->where('comment', 'like',$code)->where('value1',3)->first();
      //       $BR_y_a=$defindMst->select('value2')->where('comment', 'like',$code)->where('value1',4)->first();

      //       $value=$current-$lastInterval;
      //         if(round($value/$interval['value2'],1)>=0.9&&$count<=3&&round($value/$interval['value2']<=1.5,1)){
      //           $enemyX_from=$enmeyX_front+$TL_x_a['value2']*$start_direction;
      //           $enemyY_from=$enmeyY_front+$BR_y_a['value2'];
      //           $enemyX_to=$enmeyX_back+$BR_x_a['value2']*$start_direction;
      //           $enemyY_to=$enmeyY_back+$TL_y_a['value2'];
      //           $hit=$this->hitvalues($enemyX_from,$enemyX_to,$enemyY_from,$enemyY_to,$x_front,$x_back,$y_front,$y_back,$hit);
      //           if($y_front<$y_back){
      //               if($enemyX_from<=$x_back&&$enemyX_from>=$x_front&&$y_back>=$enemyY_to||$enemyX_from>=$x_back&&$enemyX_from<=$x_front&&$y_back>=$enemyY_to){
      //               $hit=true;
      //             }
      //           }
      //             else if($y_front>$y_back){
      //                if($enemyX_from<=$x_back&&$enemyX_from>=$x_front&&$y_front>=$enemyY_to||$enemyX_from>=$x_back&&$enemyX_from<=$x_front&&$y_front>=$enemyY_to){
      //                     $hit=true;
      //                 }
      //               }
      //        // Log::info('test B04 skill_id'.$skill_id.' enemyX'.$enemyX.' enemyY'.$enemyY.' enemyskillXfrom'.$enemyX_from.' enemyskillXto'.$enemyX_to.' enemyskillYfrom'.$enemyY_from.' enemyskillYto'.$enemyY_to.' enemy_direction'.$enemy_direction.' userxfront'.$x_front.' useryfront'.$y_front.' user_xBack'.$x_back.' user_yBack'.$y_back.' userDirection'.$direction);  
      //            $redis_battle->HSET($multi_interval_key,$count+1,$current);
      //         }
      //       if($count>3){
      //           $redis_battle->DEL($multi_interval_key);
      //       }
      // }
			 if(isset($effs['TL_x_a']))
      {
				$enemyX_from=$enemyX+$effs['TL_x_a']*$enemy_direction;
				$enemyY_from=$enemyY+$effs['BR_y_a'];
				$enemyX_to=$enemyX+$effs['BR_x_a']*$enemy_direction;
				$enemyY_to=$enemyY+$effs['TL_y_a'];

        $fly_tools_key='battle_flytools'.$match_id.$enemy_uid;
        $displacement_key='displacement'.$match_id.$enemy_uid;
        $multi_key='multi'.$match_id.$enemy_uid;
        if($skill_damage==1){
        $hit=$this->hitvalues($enemyX_from,$enemyX_to,$enemyY_from,$enemyY_to,$x_front,$x_back,$y_front,$y_back,$hit);
        // Log::info('damage 1 skill_id'.$skill_id.' enemyX'.$enemyX.' enemyY'.$enemyY.' enemyskillXfrom'.$enemyX_from.' enemyskillXto'.$enemyX_to.' enemyskillYfrom'.$enemyY_from.' enemyskillYto'.$enemyY_to.' enemy_direction'.$enemy_direction.' userxfront'.$x_front.' useryfront'.$y_front.' user_xBack'.$x_back.' user_yBack'.$y_back.' userDirection'.$direction);  
        }
         if($skill_damage==6||isset($enemySkill['displacement_distance'])){
          $battleData=json_encode($enemySkill,TRUE);
          $occur_time=$enemySkill['occur_time'];
          $start_x=-($enemyX);
          $start_y=($enemyY);
          $start_direction=-$enemySkill['direction'];
          if(!isset($effs['eff_duration'])){
            $effs['eff_duration']=0;
          }
          if(isset($effs['TL_x_a'])&&$current-$occur_time==$effs['eff_duration']){
            $battleData=json_encode($enemySkill,TRUE);
            $occur_time=$enemySkill['occur_time'];
          //$occur_time=$current;
            $enemyX_from=$start_x+$effs['TL_x_a']*$start_direction;
          }
           $hit=$this->hitvalues($enemyX_from,$enemyX_to,$enemyY_from,$enemyY_to,$x_front,$x_back,$y_front,$y_back,$hit);
        }

           if($skill_damage==3||$skill_damage==4){
            // $battleData=json_encode($enemySkill,TRUE);
            // $occur_time=$enemySkill['occur_time'];
            // $start_x=-($enemyX);
            // $start_y=($enemyY);
            // $start_direction=-$enemySkill['direction'];
            // if(!isset($effs['eff_duration'])){
            // $effs['eff_duration']=0;
            // }
            // if(!isset($effs['eff_interval'])){
            //   $effs['eff_interval']=1;
            // }
            // if(!isset($effs['movable_time'])){
            //     $enemyX_from=$start_x+$effs['TL_x_a']*$start_direction;
            //     $enemyY_from=$start_y+$effs['BR_y_a'];
            //     $enemyX_to=$start_x+$effs['BR_x_a']*$start_direction;
            //     $enemyY_to=$start_y+$effs['TL_y_a'];
            //     $hit=$this->hitvalues($enemyX_from,$enemyX_to,$enemyY_from,$enemyY_to,$x_front,$x_back,$y_front,$y_back,$hit);
            //     $interval_key=$match_id.$u_id.$skill_id;


            // }
               $hit=$this->multiHit($match_id,$u_id,$x,$y,$direction,$enemy_uid,$skill_id);
          }

           if($skill_damage==2&&isset($enemySkill['occur_time']))
           {
             $battleData=json_encode($enemySkill,TRUE);
             $occur_time=$enemySkill['occur_time'];
             //$occur_time=$current;
             $start_x=-($enemyX);
             $start_y=($enemyY);
             $start_direction=-$enemySkill['direction'];
             if(!isset($effs['eff_duration'])){
               $effs['eff_duration']=0;
             }
            if(isset($effs['TL_x_a'])&&$current-$occur_time<=$effs['eff_duration']){
              if($current-$occur_time>0){
                $start_x=$start_x+$effs['eff_speed']*($current-$occur_time)*$start_direction;
               }
                $enemyX_from=$start_x+$effs['TL_x_a']*$start_direction;
                $enemyY_from=$start_y+$effs['BR_y_a'];
                $enemyX_to=$start_x+$effs['BR_x_a']*$start_direction;
                $enemyY_to=$start_y+$effs['TL_y_a'];
               }
              
             if($y_front<$y_back){
               if($enemyX_from<=$x_back&&$enemyX_from>=$x_front&&$y_back>=$enemyY_to||$enemyX_from>=$x_back&&$enemyX_from<=$x_front&&$y_back>=$enemyY_to){
               $hit=true;
                }
              }  
              else if($y_front>$y_back){
                if($enemyX_from<=$x_back&&$enemyX_from>=$x_front&&$y_front>=$enemyY_to||$enemyX_from>=$x_back&&$enemyX_from<=$x_front&&$y_front>=$enemyY_to){
                       $hit=true;
                   }   

                }
               $hit=$this->hitvalues($enemyX_from,$enemyX_to,$enemyY_from,$enemyY_to,$x_front,$x_back,$y_front,$y_back,$hit);
             }   

            // if($hit&&$skill_damage==6){
            //  $redis_battle->HDEL($displacement_key,$skill_id);
            //  }
             // if($skill_damage==3&&$current-$occur_time>$effs['eff_duration']){
             //    $redis_battle->HDEL($displacement_key,$skill_id);
             // }
             if($hit&&$skill_damage==2){
               $redis_battle->HDEL($fly_tools_key,$skill_id);
             }
               // Log::info('damage 2 skill_id'.$skill_id.' enemyX'.$enemyX.' enemyY'.$enemyY.' enemyskillXfrom'.$enemyX_from.' enemyskillXto'.$enemyX_to.' enemyskillYfrom'.$enemyY_from.' enemyskillYto'.$enemyY_to.' enemy_direction'.$enemy_direction.' userxfront'.$x_front.' useryfront'.$y_front.' user_xBack'.$x_back.' user_yBack'.$y_back.' userDirection'.$direction);	
    			   
            else if(!$hit&&$skill_damage==2&&$current-$occur_time>$effs['eff_duration']){
               $redis_battle->HDEL($fly_tools_key,$skill_id);
               // Log::info('out of time hdel skill_id'.$skill_id.' enemyX'.$enemyX.' enemyY'.$enemyY.' enemyskillXfrom'.$enemyX_from.' enemyskillXto'.$enemyX_to.' enemyskillYfrom'.$enemyY_from.' enemyskillYto'.$enemyY_to.' enemy_direction'.$enemy_direction.' userxfront'.$x_front.' useryfront'.$y_front.' user_xBack'.$x_back.' user_yBack'.$y_back.' userDirection'.$direction.'$current-$occur_time'.($current-$occur_time).'eff duration'.$effs['eff_duration']); 
            }
          return $hit;
        }
      }
  public function checkInterval($skill_id,$x,$y,$direction,$time,$skill_group,$skill_damage,$match_id,$u_id){
    $SkillEffDeatilModel=new SkillEffDeatilModel();
    $redis_battle_history=Redis::connection('battle');
    $multi_key='multi'.$match_id.$u_id;
    $TL_x_a=$SkillEffDeatilModel->select('eff_value')->where('eff_element_id',1)->where('skill_id',$skill_id)->first();
    $BR_y_a=$SkillEffDeatilModel->select('eff_value')->where('eff_element_id',4)->where('skill_id',$skill_id)->first();
    $BR_x_a=$SkillEffDeatilModel->select('eff_value')->where('eff_element_id',3)->where('skill_id',$skill_id)->first();
    $TL_y_a=$SkillEffDeatilModel->select('eff_value')->where('eff_element_id',2)->where('skill_id',$skill_id)->first();
    $interval=$SkillEffDeatilModel->select('eff_value')->where('eff_element_id',45)->where('skill_id',$skill_id)->first();
    $duration=$SkillEffDeatilModel->select('eff_value')->where('eff_element_id',43)->where('skill_id',$skill_id)->first();
    $X_from=$x+$TL_x_a['eff_value']*$direction;
    Log::info('x'.$x.'x_from'.$X_from.'TL_x_a'.$TL_x_a['eff_value']);
    $Y_from=$y+$BR_y_a['eff_value'];
    $X_to=$x+$BR_x_a['eff_value']*$direction;
    $Y_to=$y+$TL_y_a['eff_value'];
    $redis_battle_history->HSET($multi_key,'skill_id',$skill_id);
    $redis_battle_history->HSET($multi_key,'occur_time',$time);
    $redis_battle_history->HSET($multi_key,'x_from',$X_from);
    $redis_battle_history->HSET($multi_key,'y_from',$Y_from);
    $redis_battle_history->HSET($multi_key,'x_to',$X_to);
    $redis_battle_history->HSET($multi_key,'y_to',$Y_to);
    $redis_battle_history->HSET($multi_key,'skill_group',$skill_group);
    $redis_battle_history->HSET($multi_key,'skill_damage',$skill_damage);
    $redis_battle_history->HSET($multi_key,'direction',$direction);
    $redis_battle_history->HSET($multi_key,'interval',$interval['eff_value']);
    $redis_battle_history->HSET($multi_key,'duration',$duration['eff_value']);
    $round=round($duration['eff_value']/$interval['eff_value']);
    $multi_interval_key='multi'.$match_id.$u_id.$skill_id;
    for($i=0;$i<$round;$i++){
      $redis_battle_history->HSET($multi_interval_key,$i,$i*$interval['eff_value']+$time);
    }
  }

  public function multiHit($match_id,$u_id,$x,$y,$direction,$enemy_uid,$skill_id){
    $redis_battle_history=Redis::connection('battle');
    $multi_key='multi'.$match_id.$enemy_uid;
    $defindMst=new DefindMstModel();
    $defindFront=$defindMst->select('value1','value2')->where('defind_id',9)->first();
    $defindBack=$defindMst->select('value1','value2')->where('defind_id',11)->first();

    $x_front=$x+$defindFront['value1']*$direction;
    $x_back=$x+$defindBack['value1']*$direction;
    $y_front=$y+$defindFront['value2'];
    $y_back=$y+$defindBack['value2'];

    $enemyX_from=-($redis_battle_history->HGET($multi_key,'x_from'));
    $enemyY_from=$redis_battle_history->HGET($multi_key,'y_from');
    $enemyX_to=-($redis_battle_history->HGET($multi_key,'x_to'));
    $enemyY_to=$redis_battle_history->HGET($multi_key,'y_to');
    $hit=false;
    $hit=$this->hitvalues($enemyX_from,$enemyX_to,$enemyY_from,$enemyY_to,$x_front,$x_back,$y_front,$y_back,$hit);
    $current=$this->getMillisecond();
    $multi_interval_key='multi'.$match_id.$enemy_uid.$skill_id;
    $hitTime=$redis_battle_history->HGET($multi_key,'enmey_hit_interval');
    $count=$redis_battle_history->HLEN($multi_interval_key);
    $first_time=$redis_battle_history->HGET($multi_interval_key,1);
    $mild_time=$redis_battle_history->HGET($multi_interval_key,ceil($count/2));
    $end_time=$redis_battle_history->HGET($multi_interval_key,$count-1);
    if($hit&&!$current<$end_time){
         if(!$hitTime){     
           if($current<=$mild_time&&$current>=$first_time){
             $hitTime=$count-2;
             $last_hit=$current;
           }
           else if($current>=$mild_time&&$current<=$end_time){
             $hitTime=$count-$mild_time;
             $last_hit=$current;
           }
           else {
             $hit=false;
           }
           $redis_battle_history->HSET($multi_key,'enmey_hit_interval',$hitTime);
           $redis_battle_history->HSET($multi_key,'enmey_hit_last_time',$last_hit);
         }
         else if($hitTime&&$hitTime!=0){
           $last_hit_time=$redis_battle_history->HGET($multi_key,'enmey_hit_last_time');
           $interval=$redis_battle_history->HGET($multi_key,'interval'); 
           if($current+$interva-$last_hit_time<=30){
              $redis_battle_history->HSET($multi_key,'enmey_hit_interval',$hitTime-1);
              $redis_battle_history->HSET($multi_key,'enmey_hit_last_time',$current);
           }
           else{
              $hit=false;
           }

         }
         else {
          $hit=false;
         }
    }
    else{
       $hit=false;
    }
    return $hit;

  }

  private function hitvalues($enemyX_from,$enemyX_to,$enemyY_from,$enemyY_to,$x_front,$x_back,$y_front,$y_back,$hit){
        if($enemyX_from<$enemyX_to&&$enemyY_from<$enemyY_to){
          if(($x_front>=$enemyX_from&&$x_front<=$enemyX_to&&$y_front>=$enemyY_from&&$y_front<=$enemyY_to)||($x_back>=$enemyX_from&&$x_back<=$enemyX_to&&$y_front>=$enemyY_from&&$y_back<=$enemyY_to)){
            $hit=true;
           }
          }
        else if($enemyX_from<$enemyX_to&&$enemyY_from>$enemyY_to){
          if(($x_front>=$enemyX_from&&$x_front<=$enemyX_to&&$y_front<=$enemyY_from&&$y_front>=$enemyY_to)||($x_back>=$enemyX_from&&$x_back<=$enemyX_to&&$y_front<=$enemyY_from&&$y_back>=$enemyY_to)){
              $hit=true;
            }
          }
        else if($enemyX_from>$enemyX_to&&$enemyY_from<$enemyY_to){
          if(($x_front<=$enemyX_from&&$x_front>=$enemyY_from&&$y_front>=$enemyY_from&&$y_front<=$enemyY_to)||($x_back<=$enemyX_from&&$x_back>=$enemyX_to&&$y_front>=$enemyY_from&&$y_back<=$enemyY_to)){
              $hit=true;
          }
        }
        else if($enemyX_from<$enemyX_to&&$enemyY_from<$enemyY_to){
          if(($x_front<=$enemyX_from&&$x_front>=$enemyY_from&&$y_front>=$enemyY_from&&$y_front<=$enemyY_to)||($x_back<=$enemyX_from&&$x_back>=$enemyX_to&&$y_front<=$enemyY_from&&$y_back>=$enemyY_to)){
            $hit=true;
            }
          }
          return $hit;
  }

    public function mapingBuffs($u_id,$match_id,$buff){
      $SkillEffDeatilModel=new SkillEffDeatilModel();
      $myBuffKey='mybuff'.$match_id.'_'.$u_id;
      if($buff==2){
      $myBuffKey='debuff'.$match_id.'_'.$u_id;
      }
      $redis_battle=Redis::connection('battle');
      $buffData=$redis_battle->HGETALL($myBuffKey);
      $result=[];
      $current=$this->getMillisecond();
      foreach ($buffData as $myBuffKey => $time) {
          $keys=explode('_',$myBuffKey);
          $pre_skill=$keys[0];
          $element_type=$keys[1];
          $elementPercent=0;
          $elementTime=$SkillEffDeatilModel->select('skill_id','eff_value','eff_element_id')->where('skill_id',$pre_skill)->where('eff_type',$element_type)->where('eff_name','like','%time%')->first();
          if($element_type==5){
            $elementPercent=$SkillEffDeatilModel->select('skill_id','eff_value','eff_element_id')->where('skill_id',$pre_skill)->where('eff_type',5)->where('eff_name','like','%percent%')->first();
          }
          $exist_time=($time+$elementTime['eff_value'])-$current;
          if($exist_time>0){
          $result[]=['element_type'=>$element_type,'time'=>$exist_time];
            if($elementPercent!=0){
              $result[]=['element_type'=>$element_type,'time'=>$exist_time,'precent'=>$elementPercent];
            }
        }
      }
      return $result;
    }
    public function checkBuffs($u_id,$match_id,$buff){
      $myBuffKey='mybuff'.$match_id.'_'.$u_id;
      if($buff==2){
        $myBuffKey='debuff'.$match_id.'_'.$u_id;
      }
      $redis_battle=Redis::connection('battle');
      $buffData=$redis_battle->HGETALL($myBuffKey);
      $damage_reduction_percentage=0;
      $elementPrence=[];
      $elementTime=[];
      $current=$this->getMillisecond();
      if(isset($buffData)){
        foreach ($buffData as $myBuffKey=> $time) {
          $keys=strpos($myBuffKey,'_');
          $pre_skill=$keys[0];
          $element_type=$keys[1];
            $elementTime=$SkillEffDeatilModel->select('skill_id','eff_value','eff_element_id')->where('skill_id',$pre_skill)->where('eff_type',$element_type)->where('eff_name','like','%time%')->first();
            if($time+$elementTime['eff_value']<$current){
              if($element_type==24){
               $result['invincible']=1;
              }
              else {
                $elementPrence=$SkillEffDeatilModel->select('skill_id','eff_value','eff_element_id')->where('skill_id',$pre_skill)->where('eff_type',$element_type)->where('eff_name','not like','%precent%')->get();
                foreach ($elementPrence as $key => $value) {
                  switch ($value['eff_element_id']) {
                    case 33:
                    case 34:
                      $result['damage_reduction']=$value['eff_value'];
                      break;
                    case 40:
                    case 70:
                      $result['damage_enemy']=$value['eff_value'];
                       break;
                    case 38:
                      $result['crit']=$value['eff_value'];
                       break;
                    case 61:
                      $result['atk_increase']=$value['eff_value'];
                       break;
                    case 42:
                      $result['atk_range']=$value['eff_value'];
                        break;
                    case 41:
                      $result['defence']=$value['eff_value'];
                      break;
                    case 62:
                      $result['damge_absorb']=$value['eff_value'];
                      break;
                    case 67:
                      $result['enhance_defence']=$value['eff_value'];
                      break;
                    case 37:
                      $result['quick']=$value['eff_value'];
                      break;
                    case 68:
                      $result['recover_hp']=$value['eff_value'];
                      break;
                    case 70:
                      $result['reflect_dame']=$value['eff_value'];
                      break;
                    case 35:
                      $result['slow']=$value['eff_value'];
                      break;
                    default:
                      # code...
                      break;
                  }
                }
                return $result;
              }
          }
          else {
            $redis_battle->HDEL($debuffkey,$deffKey);
          }
        }
      }
  }

  public function getEffValueBytype($skill_id){
      $skillModel=new SkillMstModel();
      $SkillEffDeatilModel=new SkillEffDeatilModel();
      $skillEffs=$SkillEffDeatilModel->select('eff_element_id','eff_value','eff_type')->where('skill_id',$skill_id)->get();
      $result=[];
      foreach ($skillEffs as $key => $each_eff) {
        $result[$each_eff['eff_type']][]=['eff_element_id'=>$each_eff['eff_element_id'],'eff_value'=>$each_eff['eff_value']];
      }
      $result2=[];
      foreach ($result as $key => $value) {
        $tmp['eff_type']=$key;
        $tmp['effs']=$value;
        $result2[]=$tmp;
      }
      return $result2;

  }
	public function getEffValue($skill_id){
  		$skillModel=new SkillMstModel();
  		$SkillEffDeatilModel=new SkillEffDeatilModel();
		  $skillEffs=$SkillEffDeatilModel->select('eff_element_id','eff_value','eff_type')->where('skill_id',$skill_id)->get();

		// result=$this->findEffFunciton($skillEffs);
		return $skillEffs;
  }
  	public function findEffFunciton($skill_eff){
  		$result=[];
  		foreach ($skill_eff as $key => $each_eff) {
  		switch ($each_eff->eff_element_id) {
           case 1: $result['TL_x_a']=$each_eff->eff_value; 
                                 break;
           case 2: $result['TL_y_a']=$each_eff->eff_value; 
                                 break;
           case 3: $result['BR_x_a']=$each_eff->eff_value; 
                                 break;
           case 4: $result['BR_y_a']=$each_eff->eff_value; 
                                 break;
           case 5: $result['hit_recover_time']=$each_eff->eff_value; 
                                 break;
           case 6: $result['knockdown_time']=$each_eff->eff_value; 
                                 break;
           case 7: $result['stun_time']=$each_eff->eff_value; 
                                 break;
           case 8: $result['execute_hp_precentage']=$each_eff->eff_value; 
                                 break;
           case 9: $result['slow_time']=$each_eff->eff_value; 
                                 break;
           case 10: $result['snipe_time']=$each_eff->eff_value; 
                                 break;
           case 11: $result['dash_distance']=$each_eff->eff_value; 
                                 break;
           case 12: $result['immune_control']=$each_eff->eff_value; 
                                 break;
           case 13: $result['displacement_time']=$each_eff->eff_value; 
                                 break;
           case 14: $result['spread_interval']=$each_eff->eff_value; 
                                 break;
           case 15: $result['block_time']=$each_eff->eff_value; 
                                 break;
           case 16: $result['damage_reduction_time']=$each_eff->eff_value; 
                                 break;
           case 17: $result['strike_back']=$each_eff->eff_value; 
                                 break;
           case 18: $result['crash_hititem']=$each_eff->eff_value; 
                                 break;
           case 19: $result['shadowstep']=$each_eff->eff_value; 
                                 break;
           case 20: $result['crit_time']=$each_eff->eff_value; 
                                 break;
           case 21: $result['omnislash_time']=$each_eff->eff_value; 
                                 break;
           case 22: $result['avatar_time']=$each_eff->eff_value; 
                                 break;
           case 23: $result['eff_skill_atk_point']=$each_eff->eff_value; 
                                 break;
           case 24: $result['crash_hitpeole']=$each_eff->eff_value; 
                                 break;
           case 25: $result['movable_time']=$each_eff->eff_value; 
                                 break;
           case 26: $result['quick_time']=$each_eff->eff_value; 
                                 break;
           case 27: $result['dash_time']=$each_eff->eff_value; 
                                 break;
           case 28: $result['dash_direction']=$each_eff->eff_value; 
                                 break;
           case 29: $result['displacement_direction']=$each_eff->eff_value; 
                                 break;
           case 30: $result['displacement_distance']=$each_eff->eff_value; 
                                 break;
           case 31: $result['spread_distance']=$each_eff->eff_value; 
                                 break;
           case 32: $result['spread_frequency']=$each_eff->eff_value; 
                                 break;
           case 33: $result['block_percentage']=$each_eff->eff_value; 
                                 break;
           case 34: $result['damage_reduction_percentage']=$each_eff->eff_value; 
                                 break;
           case 35: $result['slow_percentage']=$each_eff->eff_value; 
                                 break;
           case 36: $result['strike_back_times']=$each_eff->eff_value; 
                                 break;
           case 37: $result['quick_percentage']=$each_eff->eff_value; 
                                 break;
           case 38: $result['crit_percentage']=$each_eff->eff_value; 
                                 break;
           case 39: $result['execute_time']=$each_eff->eff_value; 
                                 break;
           case 40: $result['avatar_damage_precent']=$each_eff->eff_value; 
                                 break;
           case 41: $result['avatar_defince_precent']=$each_eff->eff_value; 
                                 break;
           case 42: $result['avatar_atk_range_precent']=$each_eff->eff_value; 
                                 break;
           case 43: $result['eff_duration']=$each_eff->eff_value; 
                                 break;
           case 44: $result['eff_speed']=$each_eff->eff_value; 
                                 break;
           case 45: $result['eff_interval']=$each_eff->eff_value; 
                                 break;
           case 47: $result['TL_x_b']=$each_eff->eff_value; 
                                 break;
           case 48: $result['TL_y_b']=$each_eff->eff_value; 
                                 break;
           case 49: $result['BR_x_b']=$each_eff->eff_value; 
                                 break;
           case 50: $result['BR_y_b']=$each_eff->eff_value; 
                                 break;
           case 51: $result['TL_x_c']=$each_eff->eff_value; 
                                 break;
           case 52: $result['TL_y_c']=$each_eff->eff_value; 
                                 break;
           case 53: $result['BR_x_c']=$each_eff->eff_value; 
                                 break;
           case 54: $result['BR_y_c']=$each_eff->eff_value; 
                                 break;
           case 55: $result['TL_x_d']=$each_eff->eff_value; 
                                 break;
           case 56: $result['TL_y_d']=$each_eff->eff_value; 
                                 break;
           case 57: $result['BR_x_d']=$each_eff->eff_value; 
                                 break;
           case 58: $result['BR_y_d']=$each_eff->eff_value; 
                                 break;
  			default:
  				# code...
            				             break;

  		}
  		# code...
  	}
  	return 
                  $result;

  }

 /*
  code edition from 2018.04.10
*/
  public function calculateCharValue($chardata,$enemyData,$skillatkEff,$skill_group,$u_id,$enemy_uid,$match_id){
  		$SkillEffDeatilModel=new SkillEffDeatilModel();
      $randCrit=rand(1,100);
      $current=$this->getMillisecond();
      $battle_status_key='battle'.$u_id;
      $redis_user=Redis::connection('battle_user');
      $redis_battle=Redis::connection('battle');
     // $checkEnmeyBuffs=$this->checkBuffs($match_id,$enemy_uid,1);
      $damage_reduction=0;
      $crit=0;
      $atk_increase=0;
      if(isset($checkEnmeyBuffs)&&$checkEnmeyBuffs){
        foreach ($checkEnmeyBuffs as $key => $value) {
          if(isset($checkEnmeyBuffs['crit'])){
            $crit=0;
          }
          if(isset($checkEnmeyBuffs['atk_increase'])){
            $atk_increase=0;
          }
      }
    }
      $critBool=1;
      if($randCrit<=$enemyData['ch_crit']||!$crit=0){
        $critBool=2;
      }
		 // $user_def=($chardata['ch_armor']*1.1)/(15*$chardata['ch_lv']+$chardata['ch_armor']+40);
		  $user_def=$chardata['ch_def'];
      $enemy_res=$enemyData['ch_res'];
		  $hpMax=$chardata['ch_hp_max'];
      $execute_hp_precentage=0;
  		if($skill_group==1||$skill_group==5||$skill_group==6){
        if(!isset($skillatkEff['eff_skill_atk_point'])){
           $skillatkEff['eff_skill_atk_point']=3;
        }
      if(isset($elementPrence['eff_value'])){
        $execute_hp_precentage=$elementPrence['eff_value'];
      }
			$enemy_atk=$enemyData['ch_atk']*$skillatkEff['eff_skill_atk_point']*$enemy_res*(1+$atk_increase);
			$enemyDMG=($enemy_atk*$critBool)*(1-$user_def);
      // Log::info('enmey damage'.$enemyDMG);
			$hpMax=$chardata['ch_hp_max'];
			$chardata['ch_hp_max']=round($hpMax*(1-$execute_hp_precentage)-$enemyDMG);
			if($chardata['ch_hp_max']<0){
				$chardata['ch_hp_max']=0;
			}
  		}
  		else if ($skill_group==2){
  		$enemy_atk=$enemyData['ch_atk']*$skillatkEff['eff_skill_atk_point']+pow($enemyData['ch_lv'],2)*2;
 	 		$enemyDMG=($enemy_atk*$critBool)*(1-$user_def);
 	 		$hpMax=$chardata['ch_hp_max'];
			$chardata['ch_hp_max']=round($hpMax*(1-$execute_hp_precentage)-$enemyDMG);
      Log::info('enmey  speical damage'.$enemyDMG);
  	}

    $redis_user->HSET($battle_status_key,'ch_hp_max',$chardata['ch_hp_max']);
  	return $chardata;
  }

  public function addBuff($skill_id,$u_id,$match_id,$enemy_uid){
    $redis_battle=Redis::connection('battle');
    $current=$this->getMillisecond();
    $result=[];
    $debuffkey='debuff'.$match_id.'_'.$enemy_uid;
    $SkillEffDeatilModel=new SkillEffDeatilModel();
    $myBuffKey='mybuff'.$match_id.'_'.$u_id;
    $effData=$SkillEffDeatilModel->select('eff_element_id','eff_type','eff_value')->where('skill_id',$skill_id)->where('eff_type','!=',1)->get();
    foreach ($effData as $key => $buff) {
      if($buff['eff_type']!=8){
      if($buff['eff_type']<=7||$buff['eff_type']==26||$buff['eff_type']==27){
        $redis_battle->HSET($debuffkey,$skill_id.'_'.$buff['eff_type'],$current);  
      }
      else{
        $redis_battle->HSET($myBuffKey,$skill_id.'_'.$buff['eff_type'],$current);
        }
      }
    }
  }

  public function clearOutOftime($match_id,$u_id,$skill_id){
     $redis_battle=Redis::connection('battle');
     $EquipmentMstModel= new EquipmentMstModel();
     $SkillEffDeatilModel=new SkillEffDeatilModel();
     $current=$this->getMillisecond();
     $skillEffs=$SkillEffDeatilModel->select('eff_value')->where('skill_id',$skill_id)->where('eff_element_id',43)->first();
     $eff_duration=$skillEffs['eff_value'];
 
     $fly_tools_key='battle_flytools'.$match_id.$u_id;
     $ocurrTimeList=$redis_battle->HKEYS($fly_tools_key.$skill_id);
     if(isset($ocurrTimeList)){
     foreach ( $ocurrTimeList as $occurtime){
        if($current-$occurtime>$eff_duration){
           $redis_battle->HDEL($fly_tools_key.$skill_id,$occurtime);
        }
     }
   }
  }
  public function checkSkillRecord($match_id,$u_id,$key){
      $redis_battle=Redis::connection('battle');
      $fly_tools_key=$key.$match_id.$u_id;
      $fly_skills=$redis_battle->HVALS($fly_tools_key);
      return $fly_skills;
  }
  // public function checkDisplament($match_id,$u_id){
  //      $redis_battle=Redis::connection('battle');
  //      $displacement_key='displacement'.$match_id.$u_id;
  //      $displacement_skills=$redis_battle->HVALS($displacement_key);
  //      return $displacement_skills;

  // }

  public function checkMulti($match_id,$u_id,$key){
       $redis_battle=Redis::connection('battle');
       $multi_key=$key.$match_id.$u_id;
       $skills=$redis_battle->HGETALL($multi_key);
       $battleData=json_encode($skills,TRUE);
       return $skills;
  }
}
