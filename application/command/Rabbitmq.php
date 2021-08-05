<?php

namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;


class Rabbitmq extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('rabbitmq');
        // 设置参数
        
    }

    protected function execute(Input $input, Output $output)
    {
    	//作为任务发布者，
    	$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
    	$channel = $connection->channel();
    	
    	$channel->queue_declare('task_queue', false, true, false, false);
    	$data = "work1";
    	$msg = new AMQPMessage($data,
    			array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT)
    	);
    	
    	$channel->basic_publish($msg, '', 'task_queue');
    	
    	echo " [x] Sent ", $data, "\n";
    	
    	$channel->close();
    	$connection->close();
    }
}
