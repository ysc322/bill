<?php
namespace app\common\model;
use think\Model;
use think\Db;

class ProductModel extends Model
{
    protected $table = 'fp_product';

    //查询是否已经存在
    function getInfo($where){
        return Db::name('fp_product')->where($where)->find();
    }
    //更新数据
    function updatedatas($data,$where){
        return Db::name('fp_product')->where($where)->update($data);
    }
    //新增数据
    function adddata($data){
        return Db::name('fp_product')->insert($data);
    }
    //获取列表
    function getList($where){
        return Db::name('fp_product')->where($where)->select();
    }




}