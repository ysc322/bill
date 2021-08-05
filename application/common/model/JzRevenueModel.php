<?php
namespace app\common\model;
use think\Model;
use think\Db;

class JzRevenueModel extends Model
{
    protected $table = 'fp_jz_revenue';
	
	//查询是否已经存在
	function getOne($where){
    	return Db::name('fp_jz_revenue')->where($where)->find();
    }
	//更新数据
	function updatedatas($data,$where){
    	return Db::name('fp_jz_revenue')->where($where)->update($data);
    }
	//新增数据
	function adddata($data){
        return Db::name('fp_jz_revenue')->insert($data);
    }
	//获取列表
	function getList($where){
		return Db::name('fp_jz_revenue')->where($where)->select();
	}
	//删除数据
    function getdel($where){
        return Db::name('fp_jz_revenue')->where($where)->delete();
    }

	
	
}
?>