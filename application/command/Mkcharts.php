<?php

namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use mylib\charts;

class Mkcharts extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('mkcharts');
        // 设置参数
        
    }

    //每日统计
    protected function execute(Input $input, Output $output)
    {

    	$Model = new \app\common\model\UserChartModel();
    	$charts = new charts();
    	$day = date('Y-m-d',strtotime('-1 day'));
    	$data['day'] = $day;
    	$data['task'] = $charts->task($day);
    	$data['finish_task'] = $charts->taskf($day);
    	$data['withdraw'] = $charts->withdraw($day);
    	$data['withdraw_true'] = $charts->withdraw_true($day);
    	$data['income'] = $charts->income($day);
    	$data['taskpay'] = $charts->taskpay($day);
    	$data['user_add'] = $charts->useradd($day);
    	$data['user_active'] = $charts->day($day);
    	$res = $Model->where(['day'=>$day])->find();
    	if(!$res){
    		$Model->insert($data);
    	}
    	

        // $rows =  $model->autoAbandon(time());
    	// 指令输出
    	$output->writeln('1');
    }
}
