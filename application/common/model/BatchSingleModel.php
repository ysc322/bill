<?php
namespace app\common\model;
use think\Model;
use think\Db;

class BatchSingleModel extends Model
{
    protected $table = 'fp_batch_single';

    //查询是否已经存在
    function getInfo($where){
        return Db::name('fp_batch_single')->where($where)->find();
    }
    //更新数据
    function updatedatas($data,$where){
        return Db::name('fp_batch_single')->where($where)->update($data);
    }
    //新增数据
    function adddata($data){
        return Db::name('fp_batch_single')->insert($data);
    }
    //获取列表
    function getList($where){
        return Db::name('fp_batch_single')->where($where)->select();
    }
    //新增数据并返回新增ID
    function getAdd($data){
        return Db::name('fp_batch_single')->insertGetId($data);
    }




}