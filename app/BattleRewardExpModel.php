<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;
class BattleRewardExpModel extends Model
{
	protected $fillable = ['be_id',
 'map_id',
 'lv',
 'ranking',
 'win',
 'exp',
 'coin',
 'loots_normal',
 'loots_special',
 'start_date',
 'end_date',
 'updated_at',
 'createdate'];

	protected $connection='mysql';
	protected $table = "Battle_reward_exp";
}