<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;
class ScrollResourceModel extends Model
{
	protected $fillable = ['sc_re_id' ,
  'sc_id',
  'r_id',
  'r_quantity',
  'updated_at' ,
  'created_at'];

	protected $connection='mysql';
	protected $table = "Scroll_Resource_mst";
}