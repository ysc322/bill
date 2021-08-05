<?php
namespace app\common\model;
use think\Model;
use think\Db;

class MessageModel extends Model
{
    protected $table = 'db_message';
	
	//��ѯ�Ƿ��Ѿ�����
	function getOne($where){
    	return Db::name('db_message')->field('id,utel,uname,weixin_openid,uhead,vehicle,token')->where($where)->find();
    }
	//��������
	function updatedatas($data,$where){
    	return Db::name('db_message')->where($where)->update($data);
    }
	//��������
	function adddata($data){
    	return Db::name('db_message')->insert($data);
    }
	//�ж���֤��ʱ������
	function checkcode($phone){
    	$time=Db::name('db_message')->where(['phone'=>$phone])->order('ctime desc')->find();
		$res=time() - strtotime($time['ctime']);
		if($res >600){
			return false;
		}else{
			return true;
		}
    }
	//��ѯ�û�������Ϣ
	function getInfo($where){
    	return Db::name('db_message')->where($where)->find();
    }
}
?>