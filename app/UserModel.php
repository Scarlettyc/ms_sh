<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class UserModel extends Model
{
     protected $fillable = ['u_id', 'fb_id', 'ch_id', 'country', 'machine_type','email','password','u_name','os','uuid','createdate','u_vip_lv','u_payment','u_gem'];

     protected $connection='mysql';
     protected $table = "user"; 

     public function isExist($key,$uuid){

     	return $this->where($key,'=',$uuid)->count();

     }
     
     public function createNew($udata){
     	$lastUid=$this->select(DB::raw('MAX(u_id)'))->first();
        $uid=substr($lastUid['MAX(u_id)'],2,11)+1;
        $udata['u_id']=substr($lastUid['MAX(u_id)'],0,2).$uid;
        $udata['createdate']=Carbon::now();
        $this->insert($udata);

     }
     // public function update($udata){
     // 	$uid=$udata['u_id'];
     // 	unset($udata['u_id']);
     // 	$this->where('u_id','=',$uid)->update($udata);
     // }
}
