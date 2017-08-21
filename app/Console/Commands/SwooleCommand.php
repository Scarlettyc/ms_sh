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
//             $serv = new swoole_server("0.0.0.0/0", 6380, SWOOLE_PROCESS, SWOOLE_SOCK_UDP);
//             $serv->set([
//                 'worker_num' => 4, # 4个worker
//                 'task_worker_num' => 4, # 4个task
//                 'heartbeat_check_interval' => 0.005,
//                 'heartbeat_idle_time' => 0.1,
//                 'deamonize' => true,
//                 ]);
//             $serv->on('Packet', function ($serv, $data, $clientInfo) {

//             //$serv->task($data);
//             $battle=new BattleController();
//             $result=$battle->test($data);
            
//             var_dump($clientInfo);
//               });


// //启动服务器
//             $serv->start();
//     //     $serv->on('Task', function ($serv, $task_id, $from_id, $data) {

//     //     $battle=new BattleController();
//     //     $result=$battle->test($data);
//     //     // $result=$battle->battle($data);
//     //     for($i = 0 ; $i < 2 ; $i ++ )

       $serv = new swoole_server("0.0.0.0/0", 6380, SWOOLE_PROCESS, SWOOLE_SOCK_UDP);
        $serv->set(array(
            'worker_num'  => 8,
            'daemonize'   => 1, //是否作为守护进程,此配置一般配合log_file使用
            'max_request' => 1000,
            'dispatch_mode' => 2,
            'debug_mode' => 1,
            'log_file'    => './storage/logs/swoole.log',
            'heartbeat_check_interval' => 5,
            'heartbeat_idle_time' => 10, //默认是heartbeat_check_interval的2倍,超过此设置客户端没有回应则强制断开链接
        ));

//监听数据接收事件
           $serv->on('Packet', function ($serv, $data, $clientInfo) {
    $battle=new BattleController();
    $result=$battle->test($data);
    $serv->sendto($clientInfo['address'], $clientInfo['port'], "Server ".$result);
	});
        //开启
        $serv->start();

}

}
