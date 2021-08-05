<?php
namespace app\common\model;
use think\Model;
use think\Db;

class ElectronModel extends Model
{
    protected $table = 'fp_electron';
	
	//查询是否已经存在
	function getOne($where){
    	return Db::name('fp_electron')->where($where)->find();
    }
	//更新数据
	function updatedatas($data,$where){
    	return Db::name('fp_electron')->where($where)->update($data);
    }
	//新增数据
	function adddata($data){
    	return Db::name('fp_electron')->insertGetId($data);
    }
	
	
}
?>