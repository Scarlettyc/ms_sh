<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class BaggageModel extends Model
{
	protected $fillable = ['b_id','equ_id','equ_quantity','r_id','r_quantity','creatdate'];

	protected $connection = 'mysql';
	protected $table = "Baggage";
}