<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class UserPaymentHistory extends Model
{
	protected $fillable = ['upayment_id','u_id','bundle_id','paydate','payment','recept_key','pay_status','gem_quantity','createdate'];

	protected $connection = 'mysql';
	protected $table = "User_Payment_History";
}