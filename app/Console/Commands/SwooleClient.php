<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use swoole_client;
use App\Http\Controllers\BattleController;
use Log;
class SwooleClient extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'swoole:client';

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
    {   $client = new swoole_client(SWOOLE_SOCK_TCP);
        $client->connect('127.0.0.1', 6385, 1);

        // $test=swoole_timer_tick(300, function ($id) use ($client){
         $client->send("test");
         $message = $client->recv();
            Log::info("Get Message From Server\n".$message);

        // });

        // swoole_timer_after(14000, function () use($client,$test){
        // swoole_timer_clear($test);
        // Log::info("client close\n");
        //  $client->close();
        // });

    }

}
