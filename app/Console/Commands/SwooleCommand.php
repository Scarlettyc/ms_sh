<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
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
            // 'task_worker_num' => 8, 
            // 'task_ipc_mode' => 3,
            'log_file'    => './storage/logs/swoole.log',
            'heartbeat_check_interval' => 60,
            'heartbeat_idle_time' => 600, 
        ));
        // $this->serv->on('Task', array($this, 'onTask'));

        // $this->serv->on('Finish', array($this, 'onFinish'));

        // $this->serv->start();

//     }
//     public function onReceive(swoole_server $serv, $fd, $from_id, $data) {

// //echo "Get Message From Client {$fd}:{$data}n";

// // send a task to task worker.

//         $serv->task($data);

//     }

//     public function onTask($serv, $task_id, $from_id, $data) {
//         $battle=new BattleController();
//         $result=$battle->getData($data);
//           if($result){
//             $i=0
//             do {
//                 $key='match_history'.$data['match_id'].'_'.$result;
//                 $count=$redis_battle->LLEN($key);
//                 sleep(2);
//                 $i++;
//             //检查数据池中的数据，如果数据池中数据少于配置值，则向数据池中补充数据
//                 if ($count>0) {
//                      $serv->sendto($clientInfo['address'], $clientInfo['port'], "Server ".$result);
//                 } else if($i>=20){
//                     break;
//                 }
//                 } while (true);
//             }
//         }

// }

//     public function onFinish($serv, $task_id, $data) {

//         echo "Task {$task_id} finishn";

//         echo "Result: {$data}n";

// }

        $serv->on('Packet', function ($serv, $data, $clientInfo) {
             $battle=new BattleController();
             $arr=json_decode($data,TRUE);
             $result=$battle->getData($arr);
             $redis_battle=Redis::connection('battle');
             if($result){
                $key='match_history'.$arr['match_id'].'_'.$result;
                // $count=$redis_battle->LLEN($key);
                // if($count>0){
                    $result=$battle->battle($result['u_id_1'],$result['u_id_2'],$data);
                // } 
             }

             $serv->sendto($clientInfo['address'], $clientInfo['port'], "Server ".$result);
             });
        $serv->start(); 

    }

}
