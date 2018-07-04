<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use App\Http\Controllers\BattleController;
use Log;
use DateTime;
class BattleBackgroundCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'battle:background';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */

    public function handle()
    {   
        $now   = new DateTime;
        $dmy=$now->format( 'Ymd' );
     
        $redis_user=Redis::connection('battle_user');
        $redis_battle=Redis::connection('battle');
        $u_list='battle_users';
        $users=$redis_user->HKEYS($u_list);
        foreach ($users as $user) {
           $battleEnd=$redis_user->HGET('battle'.$user.$dmy,'end');
           if($battleEnd>0){
            // $redis_user->DEL('battle'.$user);
            // $redis_user->HDEL($u_list,$user);
            // $Keys=$redis_user->HGETALL('battle'.$user.$dmy);
            // foreach ($Keys as $key) {
            //    $redis_battle->DEL($key);
            // }
            // $redis_user->DEL('battle'.$user.$dmy);
           }
           else if ($battleEnd==0||!$battleEnd){

            $multi_key='multi'.$user;
            $exist=$redis_battle->EXISTS($multi_key);
            $current=$this->getMillisecond();
            if($exist>0){
                 $end_time=$redis_battle->HGET($multi_key,'end_time');
                 if($current>=$end_time){
                    Log::info('test runing cron tab'.$dmy);
                    $redis_battle->DEL($multi_key);
                 }
             }
            // $this->checkBattleStatus($user);
           }
        }

    }
    private function getMillisecond() {
        list($t1, $t2) = explode(' ', microtime());     
        return (float)sprintf('%.0f', (floatval($t1) + floatval($t2)) * 1000);  
    }

}
