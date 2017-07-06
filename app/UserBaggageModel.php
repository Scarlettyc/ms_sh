<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class UserBaggageModel extends Model
{
     protected $fillable = ['b_id','u_id', 'item_org_id', 'item_type', 'item_quantity', 'status','createdate'];

     protected $connection='mysql';
     protected $table = "User_Baggage"; 

}
