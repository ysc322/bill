<?php
namespace app\common\model;
use think\Model;
use think\Db;

class UserModel extends Model
{
    protected $table = 'fp_user';
	
	//获取一条信息
	function getOne($where){
    	return Db::name('fp_user')->where($where)->find();
    }
	//修改信息
	function updatedatas($data,$where){
    	return Db::name('fp_user')->where($where)->update($data);
    }
	//新增一条信息
	function adddata($data){
    	return Db::name('fp_user')->insert($data);
    }
	//监测手机号是否存在
	function checkphone($phone){
    	return Db::name('fp_user')->where(['phone'=>$phone])->find();
    }
	//获取用户信息
	function getInfo($where){
    	return Db::name('fp_user')->where($where)->find();
    }
	//获取用户列表
	function getList($where){
		return Db::name('fp_user')->where($where)->select();
	}
	
	
	
}
?>