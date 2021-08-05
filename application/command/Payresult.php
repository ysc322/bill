<?php

namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use mylib\qypay;

class Payresult extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('payresult');
        // 设置参数
        
    }
	//定时查询支付中的支付结果，并修改状态
    protected function execute(Input $input, Output $output)
    {

        $UserWithdrawLogModel = new \app\common\model\UserWithdrawLogModel();
        $rows = $UserWithdrawLogModel->getpaying();
        $num = 0;
        if($rows) {
            foreach($rows as $v) {
            	$pay = new qypay();
            	$res = $pay->payment_results($v['order_no']);
				if($res['code'] == '0000'){
	            	$res['pay_message'] = $res['pay_message'];
	            	$res['pay_result'] = $res['pay_status'];
	            	$res['amount'] = $res['amount'];
	            	$res['out_order_no'] = $v['out_order_no'];
	            	$pay->result_check($res);
                    $num++;
				}else{
					if($v['result_check_num']>2){
		            	$resr['pay_message'] = $res['message'];
		            	$resr['pay_result'] = 2;
		            	$resr['status'] = 4;
	            		$resr['out_order_no'] = $v['out_order_no'];
	            		$resr['amount'] = $v['money'];
	            		$UserWithdrawLogModel->where('id='.$v['id'])->update($resr);
        				$pay->balance($v['id']); //回滚余额
					}else{
						$resr['result_check_num'] = $v['result_check_num']+1;
	            		$UserWithdrawLogModel->where('id='.$v['id'])->update($resr);
					}
				}
            	
            }
        }


        // $rows =  $model->autoAbandon(time());
    	// 指令输出
    	$output->writeln('res'. json_encode($res));
    }
}
