<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use DateTime;

class CheckUserToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $req=$request->getContent();
        $json=base64_decode($req);
        //dd($json);
        $data=json_decode($json,TRUE);
        $u_id=$data['u_id'];
        $now   = new DateTime;
        $dmy=$now->format( 'Ymd' );
        $datetime=$now->format( 'Y-m-d h:m:s' );
        $access_token=$data['access_token'];
        $redis= Redis::connection('default');
        $loginToday=$redis->HGET('login_data',$u_id);
        $loginTodayArr=json_decode($loginToday,TRUE);
        if(is_null($loginTodayArr)){
             $access_token2=$loginTodayArr->access_token;

            if($access_token==$access_token2){
            return $next($request);
            }
            else{
            throw new Exception("there is something wrong with token");
            }
        }else{
            throw new Exception("there is something login data");

        }

    }
}
