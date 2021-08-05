<?php
namespace app\common\model;
use think\Model;
use think\Db;

class DrawuserModel extends Model
{
    protected $table = 'fp_drawuser';
	
	//��ѯ�Ƿ��Ѿ�����
	function getOne($where){
    	return Db::name('fp_drawuser')->where($where)->find();
    }
	//��������
	function updatedatas($data,$where){
    	return Db::name('fp_drawuser')->where($where)->update($data);
    }
	//��������
	function adddata($data){
    	return Db::name('fp_drawuser')->insert($data);
    }
	//�ж��ֻ����Ƿ����
	function checkphone($phone){
    	return Db::name('fp_drawuser')->where(['phone'=>$phone])->find();
    }
	//��ѯ�û�������Ϣ
	function getInfo($where){
    	return Db::name('fp_drawuser')->where($where)->find();
    }
	//��ȡ�û��б�
	function getList($where){
		return Db::name('fp_drawuser')->where($where)->select();
	}
	
	
	
}
?>