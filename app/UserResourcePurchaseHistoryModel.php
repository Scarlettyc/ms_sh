<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;
class UserResourcePurchaseHistoryModel extends Model
{
	protected $fillable = ['r_pur_id','u_id','r_id','order_id','order_status','updated_at','created_at'];
	protected $connection = 'mysql';
	protected $table = "User_Resource_Purchase_History";
}