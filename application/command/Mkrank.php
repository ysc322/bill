<?php

namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;

class Mkrank extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('mkrank');
        // 设置参数
        
    }

    //每日统计
    protected function execute(Input $input, Output $output)
    {
    	$datetime = date('Y-m-d',strtotime('-1 day'));
    	$km = new \app\common\model\CommonTaskKeywordModel();
    	$list = $km->alias("tk")->field('p.appstoreid,tk.keyword,tk.id')
    	->join('CommonTask t', 'tk.task_id = t.id', 'LEFT')
    	->join('CommonProduct p', 't.appid = p.id', 'LEFT')
    	->where('tk.start_time >='.strtotime($datetime.'00:00:00').' and tk.end_time<='.strtotime($datetime.'23:59:59'))
    	->select();
    	if($list){
    		foreach ($list as $k=>$v){
	    			$rank = getrank($v['appstoreid'],$v['keyword']);
	    			//var_dump($rank);
	    			$d['newrank'] = $rank[0]['rank']+0;
	    			$d['newindexs'] = $rank[0]['priority']+0;
	    			$km->where('id='.$v['id'])->update($d);
    		}
    	}
    	$km = new \app\common\model\CommonOutTaskKeywordModel();
    	$list = $km->alias("tk")->field('p.appstoreid,tk.keyword,tk.id')
    	->join('CommonOutTask t', 'tk.task_id = t.id', 'LEFT')
    	->join('CommonProduct p', 't.appid = p.id', 'LEFT')
    	->where('tk.start_time >='.strtotime($datetime.'00:00:00').' and tk.end_time<='.strtotime($datetime.'23:59:59'))
    	->select();
    	if($list){
    		foreach ($list as $k=>$v){
	    			$rank = getrank($v['appstoreid'],$v['keyword']);
	    			//var_dump($rank);
	    			$d['newrank'] = $rank[0]['rank']+0;
	    			$d['newindexs'] = $rank[0]['priority']+0;
	    			$km->where('id='.$v['id'])->update($d);
    		}
    	}
    	$km = new \app\common\model\CommonOperateModel();
    	$kmk = new \app\common\model\CommonOperateKeywordModel();
    	$list = $km->alias("tk")->field('tk.id,p.appstoreid')
    	->join('CommonProduct p', 'tk.appid = p.id', 'LEFT')
    	->where(['tk.dates'=>$datetime])
    	->select();
    		foreach ($list as $k=>$v){
    			$lists = $kmk
    			->where(['opid'=>$v['id']])
    			->select();
    			foreach ($lists as $ks=>$vs){
	    			$rank = getrank($v['appstoreid'],$vs['keyword']);
	    			//var_dump($rank);
	    			$d['newrank'] = $rank[0]['rank'];
	    			$d['newindexs'] = $rank[0]['priority'];
	    			$kmk->where('id='.$vs['id'])->update($d);
    			}
    		}
    	
    	

        // $rows =  $model->autoAbandon(time());
    	// 指令输出
    	$output->writeln('1');
    }
}
