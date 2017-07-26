<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SwooleCommond extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:name';

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

    public function fire()
    {   $arg=$this->argumnt("action");
        switch ($arg) {
            case 'start':
                $this->info("swoole observer started");
                $this->start();
                break;

            case 'stop':
                $this->info("stopped");
                break;
            case 'restart':
                $this->info('restarted');
                break; 
        }

    }

    private function start()
    {   
        $this->serv=new swoole_server("0.0.0.0",9502);
        $this->serv->set(array(
            'worker_num'=>8,
            'daemonize'=>false,
            'max_request'=>10000,
            'dispatch_mode'=>2,
            'debug_mode'=>1
            ))
        $handler=App::make('handlers\SwooleHandler');
        $this->serv->on('Start',array($handler,'onStart'));
        $this->serv->on('Connect',array($handler,'onConnect'));
        $this->serv->on('Receive',array($handler,'onReceive'));
        $this->serv->on('Close',array($handler,'onClose'));

        $this->serv->start();

    }
    protected function getArguments(){
        return array(
                array('action', InputArgument::REQUIRED, 'start|stop|restart' ),
                );
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //
    }

}
