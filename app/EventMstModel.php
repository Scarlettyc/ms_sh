<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;
class EventMstModel extends Model
{
	protected $fillable = ['event_id','banner_path','web_path','start_date','end_date','createdate','udpated_at'];
	protected $connection='mysql';
	protected $table = "Event_mst";
}