<?php
namespace app\common\model;
use think\Model;
use think\Db;

class TakenotesModel extends Model
{
    protected $table = 'fp_takenotes';
	
	//查询是否已经存在
	function getOne($where){
    	return Db::name('fp_takenotes')->where($where)->find();
    }
	//更新数据
	function updatedatas($data,$where){
    	return Db::name('fp_takenotes')->where($where)->update($data);
    }
	//新增数据
	function adddata($data){
        return Db::name('fp_takenotes')->insert($data);
    }
	//获取列表
	function getList($where){
		return Db::name('fp_takenotes')->where($where)->select();
	}

	//新增数据并返回id
	function addidfa($data){
        return Db::name('fp_takenotes')->insertGetId($data);
    }

	//新增+1
	function getInc($where,$str){
        return Db::name('fp_takenotes')->where($where)->setInc($str);
    }

	//新增-1
	function getDec($where,$str){
        return Db::name('fp_takenotes')->where($where)->setDec($str);
    }

}
?>