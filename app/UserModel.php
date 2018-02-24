<?php

namespace App;
use DB;
use Carbon\Carbon;
use DateTime;
use Illuminate\Database\Eloquent\Model;

class UserModel extends Model
{
     protected $fillable = ['u_id', 'fb_id', 'ch_id', 'country', 'machine_type','email','password','pass_tutorial','u_vip_lv','profile_img','u_payment','u_gem','u_coin','u_login_count','u_get_reward','os','uuid','friend_id','like_number','createdate','updatedate'];

     protected $connection='mysql';
     protected $table = "User"; 

     public function isExist($key,$uuid){
        $count=$this->where($key,'=',$uuid)->count();
        return $count;

     }
     
     public function createNew($udata){
     	$lastUid=$this->select(DB::raw('MAX(u_id)'))->first();
        $uid=substr($lastUid['MAX(u_id)'],2,11)+1;
        $udata['u_id']=substr($lastUid['MAX(u_id)'],0,2).$uid;
        $udata['createdate']=Carbon::now();
        $udata['updated_at']=Carbon::now();
        $udata['friend_id']=$this->createTOKEN(11);
        $udata['password']=md5($this->createTOKEN(8));
        if( array_key_exists('access_token',$udata)){
            unset($udata['access_token']);
        }
       
        $this->insert($udata);
        return $udata;
     }
    public function createTOKEN($length){
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
    public function updateUserValue($u_id,$key,$value){
        $now   = new DateTime;
        $dmy=$now->format( 'Ymd' );
        $datetime=$now->format( 'Y-m-d h:m:s' );
        $user_value=$this->select($key)->where('u_id',$u_id)->first();
        $update=$user_value[$key]+$value;
        $this->where('u_id',$u_id)->update([$key=>$update,'updated_at'=>$datetime]);

    }
}
