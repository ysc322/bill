<?php

namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use mylib\rabbitmq;
class Rabbitmqc extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('runrabbitmqc');
        // 设置参数
        
    }

    protected function execute(Input $input, Output $output)
    {

    	//作为worker执行任务
    	$rabbit = new rabbitmq();
    	$rabbit->runwork();

    	$output->writeln('res');
    }
}
