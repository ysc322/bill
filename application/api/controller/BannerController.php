<?php
namespace app\api\controller;
use app\api\controller\base\BaseController;
use think\facade\Request;
use think\Exception;
use think\Db;
use app\common\model\BannerModel;
use think\facade\Cache;
use GuzzleHttp\json_encode;

class BannerController extends BaseController
{
	//获取banner图列表
	 public function list(){
    	$list=Db::table('fp_banner')->where(['state'=>0,'type'=>0])->select();
		if(!$list){
			$list=[];
		}
		return json_encode(['code'=>0,'msg'=>'ok','result'=>$list]);
    }

    //获取弹窗
    public function popup(){
        $list=Db::table('fp_banner')->where(['state'=>0,'type'=>1])->select();
        if(!$list){
            $list=[];
        }
        return json_encode(['code'=>0,'msg'=>'ok','result'=>$list]);
    }





















}
?>