<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use App\Http\Controllers\BattleController;
use swoole_websocket_server;
use swoole_server;
use Log;
use DateTime;
class BattleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'battle:start';

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
        $serv = new swoole_websocket_server("0.0.0.0",6390,SWOOLE_BASE);
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
       // $serv->sendto($clientInfo['address'], $clientInfo['port'], "Server ".$result);      //var_dump(count($reqs));//输出长连接数
    });

        $serv->on('Message', function($server, $frame) {
        global $reqs;
            // foreach ($server->connections as $key => $value) {  
                 $BattleController=new BattleController();
                 $string=$frame->data;
                 $tag=substr($string,0,2);
                 $now   = new DateTime;
                 $dmy=$now->format( 'Ymd' );
                 Log::info($string);
                 if($tag==42){
                    $ustring=substr($string,2);
                    $uslist= json_decode($ustring);
                    if($uslist[0]!="Boop"){
                        $u_id=$uslist[1]->u_id;
                        $access_token=$uslist[1]->access_token;
                        Log::info($string);
                    // $server->tick(600, function() {
                    //         Log::info("test tick");
                    //    });     
                         if(isset($uslist[1]->battle_data)){
                            $battle_data=$uslist[1]->battle_data;
                        }
                        $frame_id=$uslist[1]->frame_id;
                        $redis_battle=Redis::connection('battle');
                        $battleKey='battle_status'.$u_id.$dmy;
                        $match_id=$redis_battle->HGET($battleKey,'match_id');
                     if($uslist[0]=="BattleStart"){
                        $server->tick(600, function() use ($match_id,$frame_id,$frame) {
                            Log::info("test tick 666");
                            // $resultList=$BattleController->battleReturn($match_id,$frame_id);
                            //   $server->push($resultList['client_id_2'], $resultList['battle_data']); 
                            // $server->push($resultList['client_id'], $resultList['battle_data']);
                          });
 
                     }
                     if($uslist[0]=="BattleRecieve"){
                         $resultList=$BattleController->battleTestNew($frame->fd,$u_id,$battle_data,$frame_id,$match_id);

                     }
                    if($uslist[0]=="BattleClose"){
                        // $u_id=$uslist[1]->u_id;
                        // $access_token=$uslist[1]->access_token;
                        // $matchController->closeMatch($u_id,$access_token);
                        // $result1=$tag.'["CloseBattle",{"Battle canceled"}]"';
                        // $server->push($value, $result1);  
                        $server->close();
                    }
             }
         } 
    });


        $serv->on('Close', function($server, $fd) {
            echo "connection close: ".$fd."\n";
        });
        $serv->start();
        $serv->tick(1000, function(){ Log::info(date("Y-m-d H:i:s") . "\n"); });
    }

}
