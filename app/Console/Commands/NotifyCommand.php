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
        $serv->set(['worker_num' => 2,'daemonize'   => 0,
            'log_file'=> './storage/logs/websocket.log',
            'heartbeat_check_interval' => 600,
            'heartbeat_idle_time' => 6000
    ]);
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
            foreach ($server->connections as $key => $value) {  
                 $matchController=new MatchController();
                 $string=$frame->data;
                 $array=explode('BattleMatch',$string); 
                 $tag = str_replace('["', '',$array[0]);
                 if(count($array)>1&&$tag==42){
                    $ustring = str_replace('",', '',$array[1]);
                    $ustring = str_replace(']', '',$ustring );
                    $uslist=json_decode($ustring,TRUE);
                    $u_id=$uslist["u_id"];
                    $resultList=$matchController->match($frame->fd,$uslist);

                        if(isset($resultList))
                        { 
                            Log::info($resultList);
                            if($frame->fd == $resultList['client_id_2']){ 

                                $uData1=$matchController->finalMatchResult($resultList['u_id_1'],$resultList['u_id_2'],$resultList['match_id'],$resultList['map_id']);
                                $uData2=$matchController->finalMatchResult($resultList['u_id_2'],$resultList['u_id_1'],$resultList['match_id'],$resultList['map_id']);
                                $result1=$tag.'["Message",'.$uData1.']';

                                $result2=$tag.'["Message",'.$uData2.']';                             
                                $server->push($resultList['client_id_2'], $result1); 
                                $server->push($resultList['client_id'], $result2);

                                $break;   
                            }
                        }

                        else {
                                $result1=$tag.'["Message",{"waitting"}]"';
                                $server->push($value, $result1);  
                        }  
                }
                else {
                            $server->push($value, $tag);  
                }
        }  
    });

        $serv->on('Close', function($server, $fd) {
            echo "connection close: ".$fd."\n";
        });
        $serv->start();
    }
}
