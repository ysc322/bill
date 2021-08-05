<?php
namespace app\common\model;
use think\Model;
use think\Db;

class JzContactunitModel extends Model
{
    protected $table = 'fp_jz_contactunit';
	
	//查询是否已经存在
	function getOne($where){
    	return Db::name('fp_jz_contactunit')->where($where)->find();
    }
	//更新数据
	function updatedatas($data,$where){
    	return Db::name('fp_jz_contactunit')->where($where)->update($data);
    }
	//新增数据
	function adddata($data){
        return Db::name('fp_jz_contactunit')->insert($data);
    }
	//获取列表
	function getList($where){
		return Db::name('fp_jz_contactunit')->where($where)->select();
	}

	
	
}
?>