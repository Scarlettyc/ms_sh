<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;

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
        $access_token=$data['access_token'];
        $redisLoad= Redis::connection('default');
        $loginToday=$redisLoad->HGET('login_data',$dmy.$u_id);
        $loginTodayArr=json_decode($loginToday,TRUE);
        $access_token2=$loginTodayArr->access_token;

        if($access_token==$access_token2){
        return $next($request);
        }
        else{
            throw new Exception("there is something wrong with token");
        }

    }
}
