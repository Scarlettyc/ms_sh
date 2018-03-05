<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;
class EffElementModel extends Model
{
	protected $fillable = [ 'eff_element_id','eff_name','eff_type','eff_description','updatedate','creatdate'];
	protected $connection='mysql';
	protected $table = "Effections_element_mst";
}