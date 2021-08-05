<?php
namespace app\common\model;
use think\Model;
use think\Db;

class ExportModel extends Model
{
    protected $table = 'fp_export';
	
	//查询
	function getOne($where){
    	return Db::name('fp_export')->where($where)->find();
    }
	//修改
	function updatedatas($data,$where){
    	return Db::name('fp_export')->where($where)->update($data);
    }
	//新增
	function adddata($data){
    	return Db::name('fp_export')->insert($data);
    }
    //新增
    function gitdata($data){
        return Db::name('fp_export')->insertGetId($data);
    }
	//检测
	function checkphone($where){
    	return Db::name('fp_export')->where($where)->find();
    }
	/*//
	function getInfo($where , $page = 0 , $pageSize = 8){
		$start =  intval($page) > 0 ? intval($page) - 1 : 0;
    	return Db::name('fp_export')->where($where)->order('id desc')->limit($start * $pageSize, $pageSize)->select();
    }*/
	
	//获取列表
	function getInfo($where){
    	return Db::name('fp_export')->where($where)->select();
    }
	
	//计算个数
	function getCount($where){
    	return Db::name('db_event')->where($where)->count();
    }
	
	//查询当月信息
	function getYueRi($where,$syue,$tyue){
    	return Db::name('db_event')->field('date')->where($where)->whereTime('date','between',[$syue,$tyue])->group('date')->select();
    }
	
	
}
?>