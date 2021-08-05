<?php

namespace app\common\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;

class Bill extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('bill');
        // 设置参数
        
    }

    protected function execute(Input $input, Output $output)
    {
		$DcwCityModel = new \app\common\model\ExportModel();
        $star=date("Y-m-d",strtotime("-1 day"))." 00:00:00";
        $end=date("Y-m-d",strtotime("-1 day"))." 23:59:59";
        //print_r($star);exit;
		$res=$DcwCityModel->where('ctime', 'between', [$star, $end])->select();
		//echo $DcwCityModel->getLastSql();exit;
		for ($i=0;$i<count($res);$i++){
            $jh=str_replace("./","/home/wwwroot/fapiao/public/",$res[$i]['exceljh']);
            $xx=str_replace("./","/home/wwwroot/fapiao/public/",$res[$i]['excelxx']);
            $ss=str_replace("./","/home/wwwroot/fapiao/public/",$res[$i]['compress']);
		    //var_dump($jh);exit;
		    if(file_exists($jh)){
                unlink($jh);
            }
            if(file_exists($xx)){
                unlink($xx);
            }
            if(file_exists($ss)){
                unlink($ss);
            }
        }
    	// 指令输出
    	$output->writeln('bill');
    }
}
