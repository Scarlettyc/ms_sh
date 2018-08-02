<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class InAppPurchaseModel extends Model
{
	protected $fillable = ['inapp_id', 'item_type', 'item_min_quantity', 'item_max_times','start_date','end_date','createdate'];
	protected $connection='mysql';
	protected $table = "Store_purchase_mst";
}