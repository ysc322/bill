<?php
namespace app\common\model;
use think\Model;
use think\Db;

class CheckvipModel extends Model
{
    protected $table = 'fp_checkvip';
	
	//查询是否已经存在
	function getOne($where){
    	return Db::name('fp_checkvip')->where($where)->find();
    }
	//更新数据
	function updatedatas($data,$where){
    	return Db::name('fp_checkvip')->where($where)->update($data);
    }
	//新增数据
	function adddata($data){
        return Db::name('fp_checkvip')->insert($data);
    }
	//获取列表
	function getList($where){
		return Db::name('fp_checkvip')->where($where)->select();
	}

	//新增数据并返回id
	function addidfa($data){
        return Db::name('fp_checkvip')->insertGetId($data);
    }
    //获取当月的次数/全部次数
    function getNum($where){
        //return Db::name('fp_checkvip')->where($where)->whereTime('ctime','month')->count();//当月次数
        return Db::name('fp_checkvip')->where($where)->count();//全部次数
    }

}
?>