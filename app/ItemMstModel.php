<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class ItemMstModel extends Model
{
	protected $fillable = ['Item_type','Item_name','updatedate','createdate'];
	protected $connection = 'mysql';
	protected $table = "Item_mst";
}