<?php

namespace App;
use DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class UserBaggageModel extends Model
{
     protected $fillable = ['b_id','u_id', 'item_org_id', 'item_type', 'item_quantity', 'status','createdate'];

     protected $connection='mysql';
     protected $table = "User_Baggage"; 

     public function updatebaggage($u_id,$item_type,$item_org_id,$quantity){
     		 $now   = new DateTime;
			 $date=$now->format( 'Y-m-d h:m:s' );
     	     $item=$this->where('u_id','=',$uuid)->where('item_type',$item_type)->where('item_org_id',$item_org_id)->first();
     	     if($item){
                $this->where('u_id','=',$uuid)->where('item_type',$item_type)->where('item_org_id',$item_org_id)->update(["item_quantity"=>$item['item_quantity']+$quantity]);

     	     }
     	     else{
     	     	$baggage['u_id']=$u_id;
     	     	$baggage['item_type']=$item_type;
     	     	$baggage['item_org_id']=$item_org_id;
     	     	$baggage['item_quantity']=$quantity;
     	     	$baggage['status']=0;
     	     	$baggage['createdate']=$date;
     	     	$this->insert($baggage);
     	     }
       
     	     return true;
     }

}
