<?php

namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;

class task2 extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('app\command\task2');
        // 设置参数
        
    }

    protected function execute(Input $input, Output $output)
    {
    	// 指令输出
    	$output->writeln('app\command\task2');
    }
}
