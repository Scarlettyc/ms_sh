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
    {
            $serv = new swoole_server("0.0.0.0/0", 6380, SWOOLE_PROCESS, SWOOLE_SOCK_UDP);
            $serv->set([
                'worker_num' => 4, # 4个worker
                'task_worker_num' => 4, # 4个task
                'heartbeat_check_interval' => 0.005,
                'heartbeat_idle_time' => 0.1,
                'deamonize' => true,
                ]);
            $serv->on('Packet', function ($serv, $data, $clientInfo) {

            $serv->task($data);
            
            var_dump($clientInfo);
        });
        
        $serv->on('Task', function ($serv, $task_id, $from_id, $data) {

        $battle=new BattleController();
        $result=$battle->test($data);
        // $result=$battle->battle($data);
        for($i = 0 ; $i < 2 ; $i ++ ) {
        sleep(0.1);
        }
    //return 数据 给 Finish
            return  $result;
        });

        $serv->on('Finish', function ($serv,$task_id, $data) {
        $serv->sendto($clientInfo['address'], $clientInfo['port'], "Server ".$result);
        });

//log("testtest");
//启动服务器
    $serv->start();   
    }
}
