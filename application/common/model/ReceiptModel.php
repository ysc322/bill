<?php
namespace app\common\model;
use think\Model;
use think\Db;

class ReceiptModel extends Model
{
    protected $table = 'fp_recharge';

    //查询是否已经存在
    function getInfo($where){
        return Db::name('fp_recharge')->where($where)->find();
    }
    //更新数据
    function updatedatas($data,$where){
        return Db::name('fp_recharge')->where($where)->update($data);
    }
    //新增数据
    function adddata($data){
        return Db::name('fp_recharge')->insert($data);
    }
    //获取列表
    function getList($where){
        return Db::name('fp_recharge')->where($where)->select();
    }
    //新增数据返回id
    function getAdd($data){
        return Db::name('fp_recharge')->insertGetId($data);
    }
    //获取各个支付方式支付的总和
    function getNum($where){
        return Db::name('fp_recharge')->where($where)->sum('money');
    }


}