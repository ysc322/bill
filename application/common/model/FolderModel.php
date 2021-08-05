<?php
namespace app\common\model;
use think\Model;
use think\Db;

class FolderModel extends Model
{
    protected $table = 'fp_folder';

    //查询是否已经存在
    function getInfo($where){
        return Db::name('fp_folder')->where($where)->find();
    }
    //更新数据
    function updatedatas($data,$where){
        return Db::name('fp_folder')->where($where)->update($data);
    }
    //新增数据
    function adddata($data){
        return Db::name('fp_folder')->insert($data);
    }
    //获取列表
    function getList($where){
        return Db::name('fp_folder')->where($where)->select();
    }
    //新增数据并返回新增ID
    function getAdd($data){
        return Db::name('fp_folder')->insertGetId($data);
    }




}