<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;
class ScrollMstModel extends Model
{
	protected $fillable = ['ch_star','star_level','star_path','udpatedate','createdate'];

	protected $connection='mysql';
	protected $table = "Scroll_mst";
}