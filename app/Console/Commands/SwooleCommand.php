<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use swoole_server;
use App\Http\Controllers\BattleController;
class SwooleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'swoole:start';

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
    {  $serv = new swoole_server("0.0.0.0/0", 6380, SWOOLE_PROCESS, SWOOLE_SOCK_UDP);
        $serv->set(array(
            'worker_num'  => 8,
            'daemonize'   => 0, //是否作为守护进程,此配置一般配合log_file使用
            'max_request' => 1000,
            'dispatch_mode' => 2,
            'debug_mode' => 1,
            'log_file'    => './storage/logs/swoole.log',
            'heartbeat_check_interval' => 60,
            'heartbeat_idle_time' => 600, 
        ));
        $serv->on('Packet', function ($serv, $data, $clientInfo) {
             $battle=new BattleController();
             $result=$battle->test($data);
             $serv->sendto($clientInfo['address'], $clientInfo['port'], "Server ".$result);
             });
        $serv->start(); 

    }

}
