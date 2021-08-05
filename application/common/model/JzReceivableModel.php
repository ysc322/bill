<?php
namespace app\common\model;
use think\Model;
use think\Db;

class JzReceivableModel extends Model
{
    protected $table = 'fp_jz_receivable';
	
	//查询是否已经存在
	function getOne($where){
    	return Db::name('fp_jz_receivable')->where($where)->find();
    }
	//更新数据
	function updatedatas($data,$where){
    	return Db::name('fp_jz_receivable')->where($where)->update($data);
    }
	//新增数据
	function adddata($data){
        return Db::name('fp_jz_receivable')->insertGetId($data);
    }
	//获取列表
	function getList($where){
		return Db::name('fp_jz_receivable')->where($where)->select();
	}
    //删除数据
    function getdel($where){
        return Db::name('fp_jz_receivable')->where($where)->delete();
    }
    //获取总和
    function getNum($where){
        return Db::name('fp_jz_receivable')->where($where)->sum('surplus');
    }
	
	
}
?>