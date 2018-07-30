<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use swoole_websocket_server;
use swoole_server;
use App\Http\Controllers\MatchController;
use Illuminate\Support\Facades\Redis;
use App\Http\Controllers\BattleController;
use Log;
use DateTime;


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
                 $tag=substr($string,0,2);
                 $now   = new DateTime;
                 $dmy=$now->format( 'Ymd' );
                 if($tag==42){
                    $ustring=substr($string,2);
                    $uslist= json_decode($ustring);
                  
                    if($uslist[0]=="BattleMatch"){
                        $u_id=$uslist[1]->u_id;
                        $access_token=$uslist[1]->access_token;
                        $redis_battle=Redis::connection('battle');
                       // $matchController->validateMatch($u_id);
                        $battleKey='battle_status'.$dmy;
                        $inBattle=$redis_battle->HGET($battleKey,$u_id);
                    //    $match_uid=$redis_battle->HKEYS($matchKey);
                            $resultList=$matchController->match($frame->fd,$u_id,$access_token);
                     // Log::info($resultList);
                            if(isset($resultList))
                            { 
                                 if($frame->fd == $resultList['client_id_2']){ 

                                $uData1=$matchController->finalMatchResult($resultList['u_id_1'],$resultList['u_id_2'],$resultList['match_id'],$resultList['map_id']);
                                $uData2=$matchController->finalMatchResult($resultList['u_id_2'],$resultList['u_id_1'],$resultList['match_id'],$resultList['map_id']);
                                $result1=$tag.'["BattleMatch",'.$uData1.']';

                                $result2=$tag.'["BattleMatch",'.$uData2.']';
                                $server->tick(600, function()use($u_id,$server,$frame,$resultList,$result2,$result1) {
                                    Log::info("test tick 667");
                                // $resultList=$BattleController->battleReturn($u_id,$match_id,$frame_id);
                                    // $response=json_encode($resultList['battle_data'],TRUE);
                                    Log::info("test response ");
                                     $server->push($resultList['client_id_2'], $result1); 
                                 $server->push($resultList['client_id'], $result2);
                          });
                             
                                // $server->push($resultList['client_id_2'], $result1); 
                                // $server->push($resultList['client_id'], $result2);
                                $break;   
                            }
                        }
 
                     }
                     if($uslist[0]=="BattleStart"){
                        $redis_battle=Redis::connection('battle');
                        $u_id=$uslist[1]->u_id;
                        $frame_id=1;
                        $BattleController=new BattleController();
                        $battleKey='battle_status'.$u_id.$dmy;
                        $match_id=$redis_battle->HGET($battleKey,'match_id');

                        $server->tick(600, function()use($u_id, $match_id,$frame_id,$BattleController,$server,$frame) {
                            Log::info("test tick 667");
                            $resultList=$BattleController->battleReturn($u_id,$match_id,$frame_id);
                                    $response=json_encode($resultList['battle_data'],TRUE);
                                    Log::info("test response ".$response);
                                    $server->push($frame->fd, "testtests"); 
                          });

                     }
                    if($uslist[0]=="CloseMatch"){
                        $u_id=$uslist[1]->u_id;
                        $access_token=$uslist[1]->access_token;
                        $matchController->closeMatch($u_id,$access_token);
                        $result1=$tag.'["CloseMatch",{"match canceled"}]"';
                        $server->push($value, $result1);  
                    }
            }
        } 
    });


        $serv->on('Close', function($server, $fd) {
            echo "connection close: ".$fd."\n";
        });
        $serv->start();
    }
}
