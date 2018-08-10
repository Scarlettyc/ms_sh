<?php

namespace App\GM_modles;
use DB;
use Carbon\Carbon;
use DateTime;
use CharacterModel;

use Illuminate\Database\Eloquent\Model;

class GMNoticeRewardsModel extends Model
{
	protected $fillable = ['notice_reward_id', 
  'notice_id', 
  'item_id', 
  'equ_type',
  'item_quantity', 
  'item_rarilty',
  'item_type',
  'createdate',
  'updated_at'];

	protected $connection = 'gm_db';
	protected $table = "GM_notice_rewards";

}