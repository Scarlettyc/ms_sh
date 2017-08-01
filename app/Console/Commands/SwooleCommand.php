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

            $serv->on('Packet', function ($serv, $data, $clientInfo) {

            $battle=new BattleController();
            $result=$battle->battle($data);
            $serv->sendto($clientInfo['address'], $clientInfo['port'], "Server ".$result);
            var_dump($clientInfo);
});
//log("testtest");
//启动服务器
    $serv->start();   
    }
}
