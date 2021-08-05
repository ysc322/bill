<?php
namespace app\common\model;
use think\Model;
use think\Db;

class HidfaModel extends Model
{
    protected $table = 'fp_hidfa';
	
	//查询是否已经存在
	function getOne($where){
    	return Db::name('fp_hidfa')->where($where)->find();
    }
	//更新数据
	function updatedatas($data,$where){
    	return Db::name('fp_hidfa')->where($where)->update($data);
    }
	//新增数据
	function adddata($data){
        return Db::name('fp_hidfa')->insert($data);
    }
	//获取列表
	function getList($where){
		return Db::name('fp_hidfa')->where($where)->select();
	}

	
	
}
?>