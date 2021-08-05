<?php
namespace app\admin\model;

use think\Model;
use think\db;

class MessageModel extends Model
{
    protected $table = 'fp_message';
    
    function getList($where,$order='',$pagesize=20){
    	return Db::name('fp_message')->where($where)->order($order)->paginate($pagesize);
    }
    function getOne($where){
    	//return $this->where($where)->find()->data;
    	return Db::name('fp_message')->where($where)->find();
    }
    function updatedatas($data,$where){
    	return Db::name('fp_message')->where($where)->update($data);
    	
    }
    function adddata($data){
    	return Db::name('fp_message')->insert($data);
    }
 
}