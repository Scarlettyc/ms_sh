<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;
class MessageMstModel extends Model
{
  
	protected $fillable = ['message_id','message_title','message_info','start_date','end_date','os','country','created_at','updated_at'];
	protected $connection='mysql';
	protected $table = "Message_List_mst";
}