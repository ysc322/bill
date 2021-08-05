<?php
namespace app\common\model;
use think\Model;
use think\Db;

class JzBookkeepingModel extends Model
{
    protected $table = 'fp_jz_bookkeeping';
	
	//查询是否已经存在
	function getOne($where){
    	return Db::name('fp_jz_bookkeeping')->where($where)->find();
    }
	//更新数据
	function updatedatas($data,$where){
    	return Db::name('fp_jz_bookkeeping')->where($where)->update($data);
    }
	//新增数据
	function adddata($data){
        return Db::name('fp_jz_bookkeeping')->insert($data);
    }
	//获取列表
	function getList($where){
		return Db::name('fp_jz_bookkeeping')->where($where)->select();
	}

	
	
}
?>