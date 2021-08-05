<?php

namespace app\common\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;

class Task1 extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('app\command\task1');
        // 设置参数
        
    }

    protected function execute(Input $input, Output $output)
    {
    	// 指令输出
    	$output->writeln('app\command\task1');
    }
}
