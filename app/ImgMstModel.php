<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;
class ImgMstModel extends Model
{
	protected $fillable = ['img_id','w_id','m_id','core_id','img_path','updatedate','createdate'];

	protected $connection = 'mysql';
	protected $table = "Img_mst";
}