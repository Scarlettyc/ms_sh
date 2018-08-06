<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class ScrollMstModel extends Model
{
	protected $fillable = ['sc_id','sc_name','equ_group','sc_coin','sc_rarity','sc_description','sc_img_path','sc_sale_price','updatedate','createdate'];

	protected $connection='mysql';
	protected $table = "Scroll_mst";

}