<?php
namespace app\common\model;
use think\Model;
use think\Db;

class ElectronModel extends Model
{
    protected $table = 'fp_electron';
	
	//��ѯ�Ƿ��Ѿ�����
	function getOne($where){
    	return Db::name('fp_electron')->where($where)->find();
    }
	//��������
	function updatedatas($data,$where){
    	return Db::name('fp_electron')->where($where)->update($data);
    }
	//��������
	function adddata($data){
    	return Db::name('fp_electron')->insertGetId($data);
    }
	
	
}
?>