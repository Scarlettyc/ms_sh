<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use swoole_websocket_server;
use swoole_server;
use App\Http\Controllers\MatchController;

class NotifyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notify:start';

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
	 $reqs=array(); //保持客户端的长连接在这个数组里
        $serv = new swoole_websocket_server("0.0.0.0",6385,SWOOLE_BASE);
        $serv->set(['worker_num' => 4,'daemonize'   => 0]);
//如下可以设置多端口监听
//$server = new swoole_websocket_server("0.0.0.0", 9501, SWOOLE_BASE);
//$server->addlistener('0.0.0.0', 9502, SWOOLE_SOCK_UDP);
//$server->set(['worker_num' => 4]);

        $serv->on('Open', function($server, $req) {
            global $reqs;
        $reqs[]=$req->fd;
       // $serv->sendto($clientInfo['address'], $clientInfo['port'], "Server ".$result);

        //var_dump(count($reqs));//输出长连接数
    });

        $serv->on('Message', function($server, $frame) {
        global $reqs;
            echo "message: ".$frame->data."\n";
            foreach ($server->connections as $key => $value) {  
                 $matchController=new MatchController();
                 $resultList=$matchController->match($frame->fd,$frame->data);
                if($resultList)
                {  
                    if($frame->fd == $value){  
                        $server->push($value, $resultList['u_id_1']);  
                    }
                    else if ($frame->fd == $resultList['client_id']){

                        $server->push($value, $resultList['u_id_2']);  
                    }


                }
                else {
                    $server->push($value, 'waitting');  
                }  
        }  
    });

        $serv->on('Close', function($server, $fd) {
            echo "connection close: ".$fd."\n";
        });

        $serv->start();
	       
 
    }
}
