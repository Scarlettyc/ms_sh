<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;
<<<<<<< HEAD

class ScrollMstModel extends Model
{
	protected $fillable = ['sc_id','sc_name','r_id_1','rd1_quantity','r_id_2','rd2_quantity','r_id_3','rd3_quantity','r_id_4','rd4_quantity','r_id_5','rd5_quantity','sc_rarity','description','sc_img_path','updatedate','createdate'];
	
	protected $connection='mysql';
    protected $table = "Scroll_mst"; 

=======
class ScrollMstModel extends Model
{
	protected $fillable = ['ch_star','star_level','star_path','udpatedate','createdate'];

	protected $connection='mysql';
	protected $table = "Scroll_mst";
>>>>>>> ea25a64a84bd97ce8f0672334ceaa1df6e2210d6
}