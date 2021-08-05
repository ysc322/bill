<?php
namespace app\common\model;
use think\Model;
use think\Db;

class BatchModel extends Model
{
    protected $table = 'fp_batch';

    //查询是否已经存在
    function getInfo($where){
        return Db::name('fp_batch')->where($where)->find();
    }
    //更新数据
    function updatedatas($data,$where){
        return Db::name('fp_batch')->where($where)->update($data);
    }
    //新增数据
    function adddata($data){
        return Db::name('fp_batch')->insert($data);
    }
    //获取列表
    function getList($where){
        return Db::name('fp_batch')->where($where)->select();
    }
    //新增数据并返回新增ID
    function getAdd($data){
        return Db::name('fp_batch')->insertGetId($data);
    }




}