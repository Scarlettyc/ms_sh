<?php

$serv = new swoole_server("0.0.0.0/0", 6385, SWOOLE_PROCESS, SWOOLE_SOCK_UDP);
      
       $serv->set(array(
        'worker_num' => 4,
        'daemonize' => true,
   ));

//监听数据接收事件
    $serv->on('Packet', function ($serv, $data, $clientInfo) {
    $battle=new BattleController();
    $result=$battle->battle($data);
    $serv->sendto($clientInfo['address'], $clientInfo['port'], "Server ".$result);
    // var_dump($clientInfo);
});

//启动服务器
$serv->start(); 
    

?>
