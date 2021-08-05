<?php
namespace app\common\model;
use think\Model;
use think\Db;

class UfolderModel extends Model
{
    protected $table = 'fp_ufolder';

    //查询是否已经存在
    function getInfo($where){
        return Db::name('fp_ufolder')->where($where)->find();
    }
    //更新数据
    function updatedatas($data,$where){
        return Db::name('fp_ufolder')->where($where)->update($data);
    }
    //新增数据
    function adddata($data){
        return Db::name('fp_ufolder')->insert($data);
    }
    //获取列表
    function getList($where){
        return Db::name('fp_ufolder')->where($where)->select();
    }
    //删除对应信息
    function getDel($where){
        return Db::name('fp_ufolder')->where($where)->delete();
    }

    //获取列表
    function getXlist($where){
        return Db::name('fp_ufolder')->where($where)->order('sort asc')->select();
    }


    }