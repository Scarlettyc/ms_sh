<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class MatchRangeModel extends Model
{
     protected $fillable = ['match_id', 'rank_from','rank_to', 'createdate'];

	protected $connection = 'mysql';
	protected $table = "Match_Range_mst";
}