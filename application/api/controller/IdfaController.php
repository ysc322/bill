<?php
namespace app\api\controller;
use app\api\controller\base\BaseController;
use think\facade\Log;
use think\facade\Request;
use think\Exception;
use think\Db;
use think\facade\Cache;
use app\common\model\IdfaModel;
use app\common\model\HidfaModel;
use GuzzleHttp\json_encode;


class IdfaController extends BaseController
{

	//去重
	public function checkidfa(){
		$request = Request();
    	$data = $request->param();
		$idfa= new IdfaModel();
		//查询idfa是否存在
		$aaid=$idfa->getOne(['idfa'=>$data['idfa']]);
		if($aaid){
			//存在
            $res=array($data['idfa']=>1);

		}else{
            $res=array($data['idfa']=>0);
		}
        return json_encode(['code'=>1,'msg'=>'ok','time'=>time(),'data'=>$res]);
	}

	//激活接口
    public function jhidfa(){
        $request = Request();
        $data = $request->param();
        $hidfa= new HidfaModel();
        $arr=['idfa'=>$data['idfa'],'ctime'=>time()];
        $hidfa->adddata($arr);
        return json_encode(['code'=>1,'msg'=>'ok','time'=>time(),'data'=>$res=array($data['idfa']=>1)]);
    }
	

	
}
?>