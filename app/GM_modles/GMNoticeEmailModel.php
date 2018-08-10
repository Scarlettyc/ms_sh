<?php

namespace App\GM_modles;
use DB;
use Carbon\Carbon;
use DateTime;
use CharacterModel;

use Illuminate\Database\Eloquent\Model;

class GMNoticeEmailModel extends Model
{
	protected $fillable = [ 'notice_id',
  'user_group',
  'user_lv_from',
  'user_lv_to',
  'user_countr',
  'send_message',
  'send_time',
  'status'];

	protected $connection = 'gm_db';
	protected $table = "GM_notice_email";

}