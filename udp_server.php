<?php
/**
 * Created by PhpStorm.
 * User: yangyi
 * Date: 2016/12/7
 * Time: 18:02
 */

//创建Server对象，监听 127.0.0.1:9503端口，类型为SWOOLE_SOCK_UDP
$serv = new Swoole\Server("127.0.0.1",6380, SWOOLE_BASE, SWOOLE_SOCK_UDP);
$serv->set([
    'worker_num' => 4, # 4个worker
    'task_worker_num' => 4, # 4个task
    'deamonize' => false,
]);


//监听数据发送事件
$serv->on('Packet', function ($serv, $data, $clientInfo) {
    $serv->sendto($clientInfo['address'], $clientInfo['port'], "Server ".$data);
    var_dump($clientInfo, $data);
    //把任务丢给task
    $serv->task($data);
});

$serv->on('Task', function ($serv, $task_id, $from_id, $data) {
    echo "This Task {$task_id} from Worker {$from_id}\n";
    echo "Data: {$data}\n";
    //模拟慢io查询、
    for($i = 0 ; $i < 2 ; $i ++ ) {
        sleep(1);
        echo "Task {$task_id} Handle {$i} times...\n";
    }
    //return 数据 给 Finish
    return "Task {$task_id}'s result";
});


$serv->on('Finish', function ($serv,$task_id, $data) {
    echo "Task {$task_id} finish\n";
    echo "Result: {$data}\n";
});

//启动服务器
$serv->start();
~                
