<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class TrapMstModel extends Model
{
	protected $fillable = [  'trap_id',
  'trap_name',
  'destroyable',
  'passable',
  'TL_x_a',
  'TL_y_a',
  'BR_x_a',
  'BR_y_a',
  'CD',
  'DMG',
  'slow_presentage',
  'created_at',
  'updated_at'];

	protected $connection = 'mysql';
	protected $table = "Trap_mst";
}