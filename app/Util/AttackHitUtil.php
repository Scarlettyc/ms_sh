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
		return 
                  $result;
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
	public function checkSkillHit($enemySkill,$x,$y,$enemyX,$enemyY,$direction,$enemy_direction,$match_id,$enemy_uid,$listKey=0){

		$skillModel=new SkillMstModel();
		$SkillEffDeatilModel=new SkillEffDeatilModel();
    $redis_battle=Redis::connection('battle');
	   //	$skill_id=$enemySkill['skill_id'];
		//$skill_group=$enemySkill['skill_group'];

    $skill_id=$enemySkill['skill_id'];
    $skill_damage=$enemySkill['skill_damage'];
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
		$y_font=$y+$defindFront['value2'];
		$y_back=$y+$defindBack['value2'];


    $enmeyX_front=$enemyX+$defindFront['value1']*$enemy_direction;
    $enmeyX_back=$enemyX+$defindBack['value1']*$enemy_direction;
    $enmeyY_font=$enemyY+$defindFront['value2'];
    $enmeyY_back=$enemyY+$defindBack['value2'];
    $hit=false;
    $startDamage=0;
    // if(isset($enemySkill['skill_prepare_time'])){
    //     if($enemySkill['skill_prepare_time']!=0&&$enemySkill['occur_time']+$enemySkill['skill_prepare_time']<=$current){
    //         $startDamage=1;
    //     }
    //      else if($enemySkill['skill_prepare_time']==0){
    //         $startDamage=1;
    //     }
    //   }
    //   else if (!isset($enemySkill['skill_prepare_time'])){
    //         $startDamage=1;
    //   }
      // LOG::info( "startDamage ".$startDamage);

			if(isset($effs['TL_x_a']))
      {
				$enemyX_from=$enemyX+$effs['TL_x_a']*$enemy_direction;
				$enemyY_from=$enemyY+$effs['BR_y_a'];
				$enemyX_to=$enemyX+$effs['BR_x_a']*$enemy_direction;
				$enemyY_to=$enemyY+$effs['TL_y_a'];
        $hit=$this->hitvalues($enemyX_from,$enemyX_to,$enemyY_from,$enemyY_to,$x_front,$x_back,$y_font,$y_back);
        $fly_tools_key='battle_flytools'.$match_id.$enemy_uid;
        $displacement_key='displacement'.$match_id.$enemy_uid;
        $multi_key='multi'.$match_id.$enemy_uid;

        if($skill_damage==6||isset($enemySkill['displacement_distance'])){
          $battleData=json_encode($enemySkill,TRUE);
          $occur_time=$enemySkill['occur_time'];
          //$occur_time=$current;
          $start_x=-($enemySkill['start_x']);
          $start_y=($enemySkill['start_y']);
          $start_direction=-$enemySkill['start_direction'];
          if(!isset($effs['eff_duration'])){
            $effs['eff_duration']=0;
          }
          if(isset($effs['TL_x_a'])&&$current-$occur_time==$effs['eff_duration']){
            $battleData=json_encode($enemySkill,TRUE);
            $occur_time=$enemySkill['occur_time'];
          //$occur_time=$current;
            $start_x=-($enemySkill['start_x']);
            $start_y=($enemySkill['start_y']);
            $start_direction=-$enemySkill['start_direction'];
            $enemyX_from=$start_x+$effs['TL_x_a']*$start_direction;
          }
        }
        if($skill_damage==3){
            $battleData=json_encode($enemySkill,TRUE);
            $occur_time=$enemySkill['occur_time'];
            $start_x=-($enemySkill['start_x']);
            $start_y=($enemySkill['start_y']);
            $start_direction=-$enemySkill['start_direction'];
            $multi_interval_key='multi_interval'.$match_id.$enemy_uid.'_'.$skill_id;

            if(!isset($effs['eff_duration'])){
            $effs['eff_duration']=0;
            }
            if(!isset($effs['eff_interval'])){
              $effs['eff_interval']=1;
            }
            $count=$redis_battle->HLEN($multi_interval_key);
            // Log::info('count'.$count);
            if($count<=round($effs['eff_duration']/$effs['eff_interval'])&&$current-$occur_time<=$effs['eff_duration'])
            { 
              $lastInterval=$redis_battle->HGET($multi_interval_key,$count);
              $value=$current-$lastInterval;
              if(round($value/$effs['eff_interval'],1)>=0.9)
             { 
                $enemyX_from=$enmeyX_front+$effs['TL_x_a']*$start_direction;
                $enemyY_from=$enmeyY_font+$effs['BR_y_a'];
                $enemyX_to=$enemyX_to+$effs['BR_x_a']*$start_direction;
                $enemyY_to=$enemyY_to+$effs['TL_y_a'];
           
                  if($y_font<$y_back){
                    if($enemyX_from<=$x_back&&$enemyX_from>=$x_front&&$y_back>=$enemyY_to||$enemyX_from>=$x_back&&$enemyX_from<=$x_front&&$y_back>=$enemyY_to){
                    $hit=true;
                  }
                  else if($y_font>$y_back){
                     if($enemyX_from<=$x_back&&$enemyX_from>=$x_front&&$y_font>=$enemyY_to||$enemyX_from>=$x_back&&$enemyX_from<=$x_front&&$y_font>=$enemyY_to){
                          $hit=true;
                      }
                    }
                   $hit=$this->hitvalues($enemyX_from,$enemyX_to,$enemyY_from,$enemyY_to,$x_front,$x_back,$y_font,$y_back);
                    }
                    Log::info('damage 3 skill_id'.$skill_id.' enemyX'.$enemyX.' enemyY'.$enemyY.' enemyskillXfrom'.$enemyX_from.' enemyskillXto'.$enemyX_to.' enemyskillYfrom'.$enemyY_from.' enemyskillYto'.$enemyY_to.' enemy_direction'.$enemy_direction.' userxfront'.$x_front.' useryfront'.$y_font.' user_xBack'.$x_back.' user_yBack'.$y_back.' userDirection'.$direction);  
                    $redis_battle->HSET($multi_interval_key,$count+1,$current);
              }
              else {
                $hit=false;
              }
            }
            else {
                    $hit=false;
            }
          if($count==round($effs['eff_duration']/$effs['eff_interval'])||$current-$occur_time>$effs['eff_duration']){
             $redis_battle->DEL($multi_interval_key);
            }
        }
        if($skill_damage==4){
            $battleData=json_encode($enemySkill,TRUE);
            $occur_time=$enemySkill['occur_time'];
            $start_x=-($enemySkill['start_x']);
            $start_y=($enemySkill['start_y']);
            $start_direction=-$enemySkill['start_direction'];
            $multi_interval_key='multi_interval'.$match_id.$enemy_uid.'_'.$skill_id;

            if(!isset($effs['eff_duration'])){
            $effs['eff_duration']=0;
            }
            if(!isset($effs['eff_interval'])){
              $effs['eff_interval']=1;
            }
            $count=$redis_battle->HLEN($multi_interval_key);
            
            if($count<=round($effs['eff_duration']/$effs['eff_interval'])&&$current-$occur_time<=$effs['eff_duration'])
            { 
              $lastInterval=$redis_battle->HGET($multi_interval_key,$count);
              $value=$current-$lastInterval;
              Log::info('lastInterval: '.$lastInterval.'value/eff_interval:'.round($value/$effs['eff_interval'],1).' value: '.$value.' eff_interval:'.$effs['eff_interval']);
              if(round($value/$effs['eff_interval'],1)>=0.9)
             { 
                $enemyX_from=$start_x+$effs['TL_x_a']*$start_direction;
                $enemyY_from=$start_y+$effs['BR_y_a'];
                $enemyX_to=$start_x+$effs['BR_x_a']*$start_direction;
                $enemyY_to=$start_y+$effs['TL_y_a'];
           
                  if($y_font<$y_back){
                    if($enemyX_from<=$x_back&&$enemyX_from>=$x_front&&$y_back>=$enemyY_to||$enemyX_from>=$x_back&&$enemyX_from<=$x_front&&$y_back>=$enemyY_to){
                    $hit=true;
                  }
                  else if($y_font>$y_back){
                     if($enemyX_from<=$x_back&&$enemyX_from>=$x_front&&$y_font>=$enemyY_to||$enemyX_from>=$x_back&&$enemyX_from<=$x_front&&$y_font>=$enemyY_to){
                          $hit=true;
                      }
                    }
                   $hit=$this->hitvalues($enemyX_from,$enemyX_to,$enemyY_from,$enemyY_to,$x_front,$x_back,$y_font,$y_back);
                    }
                    Log::info('damage 4 skill_id'.$skill_id.' enemyX'.$enemyX.' enemyY'.$enemyY.' enemyskillXfrom'.$enemyX_from.' enemyskillXto'.$enemyX_to.' enemyskillYfrom'.$enemyY_from.' enemyskillYto'.$enemyY_to.' enemy_direction'.$enemy_direction.' userxfront'.$x_front.' useryfront'.$y_font.' user_xBack'.$x_back.' user_yBack'.$y_back.' userDirection'.$direction);  
                    $redis_battle->HSET($multi_interval_key,$count+1,$current);
              }
              else {
                $hit=false;
              }
            }
            else {
                    $hit=false;
            }
            if($count==round($effs['eff_duration']/$effs['eff_interval'])||$current-$occur_time>$effs['eff_duration']){
             $redis_battle->DEL($multi_interval_key);
            }
        }

        if($skill_damage==2&&isset($enemySkill['occur_time']))
        {
          $battleData=json_encode($enemySkill,TRUE);
          $occur_time=$enemySkill['occur_time'];
          //$occur_time=$current;
          $start_x=-($enemySkill['start_x']);
          $start_y=($enemySkill['start_y']);
          $start_direction=-$enemySkill['start_direction'];
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
            $hit=$this->hitvalues($enemyX_from,$enemyX_to,$enemyY_from,$enemyY_to,$x_front,$x_back,$y_font,$y_back);
          if($y_font<$y_back){
            if($enemyX_from<=$x_back&&$enemyX_from>=$x_front&&$y_back>=$enemyY_to||$enemyX_from>=$x_back&&$enemyX_from<=$x_front&&$y_back>=$enemyY_to){
            $hit=true;
           }
          else if($y_font>$y_back){
             if($enemyX_from<=$x_back&&$enemyX_from>=$x_front&&$y_font>=$enemyY_to||$enemyX_from>=$x_back&&$enemyX_from<=$x_front&&$y_font>=$enemyY_to){
                    $hit=true;
                }

              }
            }
          }

          if($hit&&$skill_damage==6){
          $redis_battle->HDEL($displacement_key,$skill_id);
          }
          // if($skill_damage==3&&$current-$occur_time>$effs['eff_duration']){
          //    $redis_battle->HDEL($displacement_key,$skill_id);
          // }
          if($hit&&$skill_damage==2){
            $redis_battle->HDEL($fly_tools_key.'_'.$skill_id,$occur_time);
            // Log::info('damage 2 skill_id'.$skill_id.' enemyX'.$enemyX.' enemyY'.$enemyY.' enemyskillXfrom'.$enemyX_from.' enemyskillXto'.$enemyX_to.' enemyskillYfrom'.$enemyY_from.' enemyskillYto'.$enemyY_to.' enemy_direction'.$enemy_direction.' userxfront'.$x_front.' useryfront'.$y_font.' user_xBack'.$x_back.' user_yBack'.$y_back.' userDirection'.$direction);	
			   }
         else if(!$hit&&$skill_damage==2&&$current-$occur_time>$effs['eff_duration']){
            $redis_battle->HDEL($fly_tools_key.'_'.$skill_id,$occur_time);
            // Log::info('out of time hdel skill_id'.$skill_id.' enemyX'.$enemyX.' enemyY'.$enemyY.' enemyskillXfrom'.$enemyX_from.' enemyskillXto'.$enemyX_to.' enemyskillYfrom'.$enemyY_from.' enemyskillYto'.$enemyY_to.' enemy_direction'.$enemy_direction.' userxfront'.$x_front.' useryfront'.$y_font.' user_xBack'.$x_back.' user_yBack'.$y_back.' userDirection'.$direction.'$current-$occur_time'.($current-$occur_time).'eff duration'.$effs['eff_duration']); 
         }
      return $hit;
    }
	}

  private function hitvalues($enemyX_from,$enemyX_to,$enemyY_from,$enemyY_to,$x_front,$x_back,$y_font,$y_back){
        $hit=false;
        if($enemyX_from<$enemyX_to&&$enemyY_from<$enemyY_to){
          if(($x_front>=$enemyX_from&&$x_front<=$enemyX_to&&$y_font>=$enemyY_from&&$y_font<=$enemyY_to)||($x_back>=$enemyX_from&&$x_back<=$enemyX_to&&$y_font>=$enemyY_from&&$y_back<=$enemyY_to)){
            $hit=true;
           }
          }
        else if($enemyX_from<$enemyX_to&&$enemyY_from>$enemyY_to){
          if(($x_front>=$enemyX_from&&$x_front<=$enemyX_to&&$y_font<=$enemyY_from&&$y_font>=$enemyY_to)||($x_back>=$enemyX_from&&$x_back<=$enemyX_to&&$y_font<=$enemyY_from&&$y_back>=$enemyY_to)){
              $hit=true;
            }
          }
        else if($enemyX_from>$enemyX_to&&$enemyY_from<$enemyY_to){
          if(($x_front<=$enemyX_from&&$x_front>=$enemyY_from&&$y_font>=$enemyY_from&&$y_font<=$enemyY_to)||($x_back<=$enemyX_from&&$x_back>=$enemyX_to&&$y_font>=$enemyY_from&&$y_back<=$enemyY_to)){
              $hit=true;
          }
        }
        else if($enemyX_from<$enemyX_to&&$enemyY_from<$enemyY_to){
          if(($x_front<=$enemyX_from&&$x_front>=$enemyY_from&&$y_font>=$enemyY_from&&$y_font<=$enemyY_to)||($x_back<=$enemyX_from&&$x_back>=$enemyX_to&&$y_font<=$enemyY_from&&$y_back>=$enemyY_to)){
            $hit=true;
            }
          }
          return $hit;
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
  public function calculateCharValue($chardata,$enemyData,$skillatkEff){
  		$randCrit=rand(1,100);
  		$critBool=1;
		if($randCrit<=$enemyData['ch_crit']){
		$critBool=2;
		}
		$user_def=($chardata['ch_armor']*1.1)/(15*$chardata['ch_lv']+$chardata['ch_armor']+40);
		$enemy_res=$enemyData['ch_res'];
		$hpMax=$chardata['ch_hp_max'];
  		if($enemyData['skill']['skill_group']=1||$enemyData['skill']['skill_group']=5||$enemyData['skill']['skill_group']=6){
      if(!isset($skillatkEff['eff_skill_atk_point'])){
        $skillatkEff['eff_skill_atk_point']=3;
      }
			$enemy_atk=$enemyData['ch_atk']*$skillatkEff['eff_skill_atk_point']*$enemy_res;
			$enemyDMG=($enemy_atk*$critBool)*(1-$user_def);
      Log::info('enmey damage'.$enemyDMG);
			$hpMax=$chardata['ch_hp_max'];
			$chardata['ch_hp_max']=round($hpMax-$enemyDMG);
			if($chardata['ch_hp_max']<0){
				$chardata['ch_hp_max']=0;
			}
  		}
  		else if ($enemyData['skill']['skill_group']=2){
  			$enemy_atk=$enemyData['ch_atk']*$skillatkEff['eff_skill_atk_point']+pow($enemy_charData['ch_lv'],2)*2;
 	 		$enemyDMG=($enemy_atk*$critBool)*(1-$user_def);
 	 		$hpMax=$chardata['ch_hp_max'];
			$chardata['ch_hp_max']=round($hpMax-$enemy_atk);
  	}
  	return $chardata;

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
  public function checkFlyTools($match_id,$u_id){
      $redis_battle=Redis::connection('battle');
      $current=$this->getMillisecond();
      $CharacterModel=new CharacterModel();
      $EquipmentMstModel= new EquipmentMstModel();
      $skillModel=new SkillMstModel();
      $skill_keys='battle_user_skills_'.$u_id;
      $all_skills=$redis_battle->HKEYS($skill_keys);
      // $all_skills=[224,225,226,227];
      $result=[];
      if(isset($all_skills)){
        foreach ($all_skills as $key => $skill) {
          $fly_tools_key='battle_flytools'.$match_id.$u_id;
          $fly_tools_key_sp=$fly_tools_key.'_'.$skill;
          $speical_skills=$redis_battle->HVALS($fly_tools_key_sp);
          $result[]= $speical_skills;
          # code...
        }
      return $result;
    }
  }
  public function checkDisplament($match_id,$u_id){
       $redis_battle=Redis::connection('battle');
       $displacement_key='displacement'.$match_id.$u_id;
       $displacement_skills=$redis_battle->HVALS($displacement_key);
       return $displacement_skills;

  }

  public function checkMulti($match_id,$u_id){
       $redis_battle=Redis::connection('battle');
       $multi_key='multi'.$match_id.$u_id;
       $skills=$redis_battle->HVALS($multi_key);
       return $skills;
  }
}
