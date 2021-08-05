<?php
namespace app\common\model;
use think\Model;
use think\Db;

class HistoryModel extends Model
{
    protected $table = 'fp_history';
	
	//查询是否已经存在
	function getOne($where){
    	return Db::name('fp_history')->where($where)->find();
    }
	//更新数据
	function updatedatas($data,$where){
    	return Db::name('fp_history')->where($where)->update($data);
    }
	//新增数据
	function adddata($data){
        return Db::name('fp_history')->insert($data);
    }
	//获取列表
	function getList($where){
		return Db::name('fp_history')->where($where)->select();
	}

	//查询会员状态，返回结果
    function getCheck($uid){
	    $res=$this->getOne(['uid'=>$uid]);
	    if($res){
	        $time=date('Y-m-d H:i:s',time());
	        if($res['totime']<$time){
	            //将用户移除此表
                Db::name('fp_history')->where(['uid'=>$uid])->delete();
                $check=0;
            }else{
                $check=1;//暂时有效会员
            }
        }else{
	        $check=0;//不是之前会员
        }
	    return $check;
    }
	
	
}
?>