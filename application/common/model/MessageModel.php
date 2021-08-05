<?php
namespace app\common\model;
use think\Model;
use think\Db;

class MessageModel extends Model
{
    protected $table = 'db_message';
	
	//查询是否已经存在
	function getOne($where){
    	return Db::name('db_message')->field('id,utel,uname,weixin_openid,uhead,vehicle,token')->where($where)->find();
    }
	//更新数据
	function updatedatas($data,$where){
    	return Db::name('db_message')->where($where)->update($data);
    }
	//新增数据
	function adddata($data){
    	return Db::name('db_message')->insert($data);
    }
	//判断验证码时间周期
	function checkcode($phone){
    	$time=Db::name('db_message')->where(['phone'=>$phone])->order('ctime desc')->find();
		$res=time() - strtotime($time['ctime']);
		if($res >600){
			return false;
		}else{
			return true;
		}
    }
	//查询用户所有信息
	function getInfo($where){
    	return Db::name('db_message')->where($where)->find();
    }
}
?>