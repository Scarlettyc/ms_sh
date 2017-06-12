<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class UserLoginHistoryModel extends Model
{
     protected $fillable = ['u_login_id', 'u_id', 'os', 'machine_type','uuid','date','createdate'];

     protected $connection='mysql';
     protected $table = "user_login_history"; 

     public function isExist($key,$uuid){
        return $this->where('uuid','=',$uuid)->count();
     }
     
     public function createNew($udata){
     	$lastULogin=$this->select(DB::raw('MAX(u_login_id)'))->first();
        $uLoginid=substr($lastULogin['MAX(u_login_id)'],2,11)+1;
        $ulogin=[];
        $ulogin['u_login_id']=substr($lastULogin['MAX(u_login_id)'],0,2).$uLoginid;
        $ulogin['u_id']=$udata['u_id'];
        $ulogin['os']=$udata['os'];
        $ulogin['machine_type']=$udata['machine_type'];
        $ulogin['uuid']=$udata['uuid'];
        $ulogin['country']=$udata['country'];
        $ulogin['loginday']=Carbon::now();
        $ulogin['createdate']=Carbon::now();
        $this->insert($ulogin);

     }
     // public function update($udata){
     // 	$uid=$udata['u_id'];
     // 	unset($udata['u_id']);
     // 	$this->where('u_id','=',$uid)->update($udata);
     // }
}
