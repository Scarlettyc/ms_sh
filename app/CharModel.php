<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class CharModel extends Model
{
     protected $fillable = ['ch_id','u_id', 'race_id', 'ch_lv', 'ch_talentpoint', 'ch_strength','ch_stamina','ch_intellect','ch_fre','createdate','u_vip_lv','u_payment','u_gem'];

     protected $connection='mysql';
     protected $table = "character"; 

}
