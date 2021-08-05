<?php

namespace mylib;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use GuzzleHttp\json_decode;
use GuzzleHttp\json_encode;
use think\Db;
use app\common\model\CommonOutTaskModel;
use app\common\model\CommonOutTaskKeywordModel;
use app\common\model\CommonTaskKeywordModel;
//消息队列
class rabbitmq{
	//存储任务$data 字符串
	function savetask($data){
		//先不用消息队列
		$this->dowork($data);
		return;
		if(is_test()){
			$this->dowork($data);
			return ;
			exit;
		}
		//作为任务发布者，
		$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
		
		
		$channel = $connection->channel();
		 
		$channel->queue_declare('task_queue_log', false, true, false, false);
		//$data = "work1";
		$data = json_encode($data); 
		$msg = new AMQPMessage($data,
				array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT)
		);
		 
		$channel->basic_publish($msg, '', 'task_queue_log');
		 
		//echo " [x] Sent ", $data, "\n";
		 
		$channel->close();
		$connection->close();
	}
	//执行任务
	function runwork(){

		//作为worker执行任务
		$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
		$channel = $connection->channel();
		 
		$channel->queue_declare('task_queue_log', false, true, false, false);
		 
		echo ' [*] Waiting for messages. To exit press CTRL+C', "\n";
		 
		$callback = function($msg){
    		$this->dowork($msg);
    		//$msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
    	};
		$channel->basic_qos(null, 1, null);
		$channel->basic_consume('task_queue_log', '', false, false, false, false, $callback);
		 
		while(count($channel->callbacks)) {
			$channel->wait();
		}
		 
		$channel->close();
		$connection->close();
	}
	function dowork($msg){
			//$data = json_decode($msg->body,true);
			$data = $msg;
			/*if(!is_test()){
				echo " [x] Received ", $msg->body, "\n";
			}else{
				$data = $msg;
			}*/

			//if($msg->body!='"[\"abc\",\"ddd\"]"'){
			//sleep(substr_count($msg->body, '.'));
			//处理程序
			if(is_array($data)){
			switch ($data['type']){
				case 'union_repeat':
					$this->union_repeat($data['data']);
					break;
				case 'union_active':
					$this->union_active($data);
					break;
				case 'union_click':
					$this->union_click($data);
					break;
				case 'task_active':
					$this->task_active($data);
					break;
				case 'task_click':
					$this->task_click($data);
					break;
				case 'task_repeat':
					$this->task_repeat($data);
					break;
				
			}
			}
			//}
			if(!is_test()){
			//echo " [x] Done", "\n";
			//$msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
			}
	}
    public function new_db($db_config) {
		
        return Db::connect($db_config);

    }
    function task_click($data){
    	$param = $data['param'];
    	$postData = $data['postData'];
    	//点击日志数据组成
    	$clickData = ['appid' => $param['appid'],
    	'ad_user_id' => $param['ad_user_id'], 'idfa' => $param['idfa'],
    	'keyword' => $param['keyword'],
    	'url' => $postData['url'],
    	'result' => $postData['result'],
    	'create_at' => time()
    	];
		$postData['keyword'] = $data['keyword'];
    	
    	$this->new_db('db_log_check')->table('click_log')->insertGetId($clickData);
    	
    	$table = $this->new_db('db_log_check')->table(mk_get_table_name($data['bundleid']));
    	
    	$postDatanew = $postData;
    	unset($postDatanew['ip']);
    	unset($postDatanew['os']);
    	unset($postDatanew['device']);
    	$id = $table->insertGetId($postDatanew);
    	 
    }
    private function _insert_mbund_repeat_idfa($bundleid, $data) {
        
        $bundleid = mk_get_table_name($bundleid);
        $bundleTable =  $this->new_db('db_bunds')->table($bundleid);
        return $bundleTable->insertGetId($data);

    }
    function task_repeat($data){

    	$param = $data['param'];
    	$postData = $data['postData'];
    	$bundleid = $data['bundleid'];
		$postData['keyword'] = $data['keyword'];
    	//查询去重日志记录数据
    	$repeat_log = [
    	'appid' => $param['appid'],
    	'ad_user_id' => ($param['ad_user_id']+0),
    	'idfa' => $param['idfa'],
    	'keyword' => $param['keyword'],
    	'url' => $postData['url'],
    	'result' => $postData['result'],
    	'create_at' => time()
    	];
    	
    	$res = $this->new_db('db_log_check')->table('repeat_log')->insert($repeat_log);

    	
    	$table = $this->new_db('db_log_check')->table(mk_get_table_name($bundleid));
    	
    	$postDatanew = $postData;
    	unset($postDatanew['ip']);
    	unset($postDatanew['os']);
    	unset($postDatanew['device']);
    	$postDatanew['ad_user_id'] = ($postDatanew['ad_user_id']+0);
    	$id = $table->insertGetId($postDatanew);
    	
    	if($id) {
    	
    		
    		$res = $this->_insert_mbund_repeat_idfa($bundleid . '_repeat',
    				['uid' => $param['uid'],
    				'idfa' => $param['idfa'],
    				'repeat_id' => $id,
    				'task_id' => $param['task_id'],
    				'create_at' => time()
    				]
    		);
    	
    		
    	
    	}
    }
    function task_active($data){

    	$param = $data['param'];
    	$postData = $data['postData'];
    	$bundleid = $data['bundleid'];
    	$taskInfo = $data['taskInfo'];
    	$postData['keyword'] = $data['keyword'];
    	$activeLog = $postData;
    	unset($activeLog['udid']);
    	unset($activeLog['uid']);
    	unset($activeLog['task_id']);
    	unset($activeLog['stype']);
    	$activeLog['appid'] = $data['appid'];
    	if($data['app']['type']!=2){
	    	//快速任务 修改任务完成数量
	    	$km = new CommonTaskKeywordModel();
	    	//$keywor = $km->where(['id' => $taskInfo['keyword_id']])
	    	//->field('id,com_num')->find();
	    	//if($keywor){
	    		//$d['com_num'] = $keywor['com_num']+1;
	    		//$km->where(['id' => $taskInfo['keyword_id']])->update($d);
	    	//}
    	}
    	$active_log_id = db('active_log', 'db_log_check')->insertGetId($activeLog);
    	$table = $this->new_db('db_log_check')->table(mk_get_table_name($bundleid));
    	$postDatanew = $postData;
    	$id = $table->insertGetId($postDatanew);

    	if($id) {
    	
    		$data = ['idfa' => $param['idfa'],
    		'uid' => $param['uid'],
    		'task_id' => $param['task_id'],
    		'auser_id' => ($postData['ad_user_id']+0),
    		'activate_id' => $id,
    		'keyword_id' => $taskInfo['keyword_id'],
    		'keyword' => $taskInfo['keyword'],
    		'status' => 1,
    		'create_at' => time(),
    		'ip' => $postData['ip'],
    		'os' => $postData['os'],
    		'device' => $postData['device']
    		];
    		$ishave = $this->new_db('db_bunds')->table(mk_get_table_name($bundleid) . '_active' )->where(['idfa'=>$param['idfa']])->find();

    		if(!$ishave){
    			$this->new_db('db_bunds')->table(mk_get_table_name($bundleid) . '_active')->insert($data);
    		}
    	}
    }
	function union_repeat($data){
		$postData = $data['postData'];
		$appinfo = $data['appinfo'];
		$zq_callback = $data['zq_callback'];
		$appid = $data['appid'];
		$idfa = $postData['idfa'];
		$keyword = $data['keyword'];
		$outer_user_id = (int)$postData['ad_user_id'];
		
		$postData['keyword'] = $keyword;
		$clickData = [
		'appid' => $appid,
		'ad_user_id' => $appinfo['ad_user_admin_id'],
		'idfa' => $idfa,
		'keyword' => $keyword,
		'client_url' => $postData['client_url'],
		'client_result' => $postData['client_result'],
		'url' => $postData['url'],
		'result' => $postData['result'],
		'create_at' => time()
		];
		
		$logid = $this->new_db('db_log_check')->table('repeat_log')->insertGetId($clickData);
		
		$table = $this->new_db('db_log_check')->table(mk_get_table_name($appinfo['bundleid']));
		$postDatanew = $postData;
		$baoid = $table->insertGetId($postDatanew);
		 
		$arr['repeat_log'] = $logid;
		$arr[mk_get_table_name($appinfo['bundleid'])] = $baoid;
		return $arr;
		
		
	}
	function union_active($data){
		$postData = $data['postData'];
		$appinfo = $data['appinfo'];
		$postData['task_id'] = $postData['task_id']+0;
		$clickData = [
		'appid' => $data['appid'],
		'ad_user_id' => $data['appinfo']['ad_user_admin_id'],
		'idfa' => $postData['idfa'],
		'keyword' => $data['keyword'],
		'client_url' => $postData['client_url'],
		'client_result' => $postData['client_result'],
		'url' => $postData['url'],
		'result' => $postData['result'],
		'create_at' => time()
		];
		$postData['keyword'] = $data['keyword'];
		$rid = $this->new_db('db_log_check')->table('active_log')->insertGetId($clickData);
		
		$table = $this->new_db('db_log_check')->table(mk_get_table_name($appinfo['bundleid']));
		
		$postDatanew = $postData;
		$id = $table->insertGetId($postDatanew);

	
		$arr['active_log'] = $rid;
		$arr[mk_get_table_name($appinfo['bundleid'])] = $id;
		return $arr;
	}
	function union_click($data){
		$postData = $data['postData'];
		$appinfo = $data['appinfo'];
		$zq_callback = $data['zq_callback'];
		$appid = $data['appid'];
		$idfa = $postData['idfa'];
		$keyword = $data['keyword'];
		$outer_user_id = (int)$postData['ad_user_id'];

		$postData['keyword'] = $keyword;
		$clickData = [
		'appid' => $appid,
		'ad_user_id' => $appinfo['ad_user_admin_id'],
		'idfa' => $idfa,
		'keyword' => $keyword,
		'client_url' => $postData['client_url'],
		'client_result' => $postData['client_result'],
		'url' => $postData['url'],
		'result' => $postData['result'],
		'create_at' => time()
		];
		if($zq_callback&&$appinfo['type']==2){
			$clickData['callback'] = $zq_callback;
			//如果是回调类型任务
			//存日志
			$dataout['appid'] = $appid;
			$dataout['idfa'] = $idfa;
			$dataout['url'] = urldecode($clickData['callback']);
			$dataout['out_id'] = $outer_user_id;
			$dataout['form_is_back'] = 0;
			$dataout['out_is_back'] = 0;
			$dataout['click_time'] = time();
			$dataout['keyword'] = $keyword;
			$this->new_db('db_log_check')->table('callback_out_log')->insertGetId($dataout);
        	$log_table = mk_get_table_name($appinfo['bundleid']);
			$this->new_db('db_log_check')->table($log_table.'_callback_out_log')->insertGetId($dataout);
			 
		}
		// var_dump($clickData);
		$logid = $this->new_db('db_log_check')->table('click_log')->insertGetId($clickData);
		
		$table = $this->new_db('db_log_check')->table(mk_get_table_name($appinfo['bundleid']));
    	$postDatanew = $postData;
    	$baoid = $table->insertGetId($postDatanew);
    	
    	$arr['click_log'] = $logid;
    	$arr[mk_get_table_name($appinfo['bundleid'])] = $baoid;
		return $arr;
	}
}