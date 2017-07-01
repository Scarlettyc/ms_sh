<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class GemPurchaseBundleMst extends Model
{
	protected $fillable = ['bundle_id','u_payment','os','gem_quantity','createdate'];

	protected $connection = 'mysql';
	protected $table = "Gem_Purchase_Bundle_mst";
}