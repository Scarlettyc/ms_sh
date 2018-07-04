<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use App\Http\Controllers\BattleController;
use Log;
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
        Log::info('test cron tab'.$dmy);
        $redis_user=Redis::connection('battle_user');
        $u_list='battle_users';
        $users=$redis_user->HKEYS($u_list);
        foreach ($users as $user) {
           $battleEnd=$redis_user->HGET('battle'.$user.$dmy,'end');
           if($battleEnd>0){
            $redis_user->DEL('battle'.$user.$dmy);
            $redis_user->HDEL($users, $user);
           }
           // else{
           //  // $this->checkBattleStatus($user);
           // }
        }

    }
    // private function checkBattleStatus(){

    // }

}
