<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use swoole_server;
use App\Http\Controllers\MatchController;

class NotifySocket extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notify:socket';

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
        $serv = new swoole_server("0.0.0.0/0", 6385, SWOOLE_PROCESS, SWOOLE_SOCK_UDP);
        //
    }
}
