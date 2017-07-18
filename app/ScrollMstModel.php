<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class ScrollMstModel extends Model
{
	protected $fillable = ['sc_id','sc_name','equ_id','r_id_1','rd1_quantity','r_id_2','rd2_quantity','r_id_3','rd3_quantity','r_id_4','rd4_quantity','r_id_5','rd5_quantity','sc_rarity','description','sc_img_path','udpatedate','createdate'];

	protected $connection='mysql';
	protected $table = "Scroll_mst";

}