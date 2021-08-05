<?php

namespace app\common\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use mylib\oss;

class Photo extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('photo');
        // 设置参数
        
    }

    protected function execute(Input $input, Output $output)
    {
		//$bill = new \app\common\model\BillinfoModel();
		$bill = new \app\common\model\ElectronModel();

        //$list=$bill->where(['state'=>0])->where("id > 29533 and photourl !='' ")->select();
        $list=$bill->select();
        foreach ($list as $k=>$v){
            if(strpos($v['imgth'],'uploads/file/') !== false){
                //$object=substr($v['photourl'],2);
                $object=substr($v['imgth'],2);
                $img= '/home/wwwroot/fapiao/public/' . $object;
                $content=file_get_contents($img);
                oss::uploadfile($object,$content);
            }

        }
    	// 指令输出
    	$output->writeln('photo');
    }



















}
