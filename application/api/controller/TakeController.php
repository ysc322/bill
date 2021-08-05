<?php
namespace app\api\controller;
use app\api\controller\base\BaseController;
use think\facade\Log;
use think\facade\Request;
use think\Exception;
use think\Db;
use app\common\model\TakeModel;
use think\facade\Cache;
use GuzzleHttp\json_encode;

use mylib\img;
class TakeController extends BaseController
{
    //新增记录
	public function takeadd(){
        $info=$this->chektoken();//检测用户token
        if(!$info){
            return json_encode(['code' =>2, 'msg'=>'请登录' ]);
        }
        $request = Request();
        $data = $request->param();
        $take=new TakeModel();
        $data['uid']=$info['id'];
        $data['ctime']=date('Y-m-d H:i:s',time());
        $ss=$take->adddata($data);
        if($ss){
            return $this->json_success('添加成功');
        }else{
            return $this->json_error('添加失败');
        }
    }


    //读取列表
    public function takelist(){
        $info=$this->chektoken();//检测用户token
        if(!$info){
            return json_encode(['code' =>2, 'msg'=>'请登录' ]);
        }
        $take=new TakeModel();
        $ss=$take->getList(['uid'=>$info['id'],'state'=>0]);
        if(!$ss){
            $ss=[];
        }
        return json_encode(['code'=>0,'msg'=>'ok','result'=>$ss]);
    }
    //修改记录信息
    public function takeup(){
        $info=$this->chektoken();//检测用户token
        if(!$info){
            return json_encode(['code' =>2, 'msg'=>'请登录' ]);
        }
        $request = Request();
        $data = $request->param();
        $take=new TakeModel();
        $ss=$take->updatedatas($data,['uid'=>$info['id'],'id'=>$data['id']]);
        if($ss===false){
            return $this->json_error('修改失败');
        }else{
            return $this->json_success('修改成功');
        }
    }
    //删除记录信息
    public function takedel(){
        $info=$this->chektoken();//检测用户token
        if(!$info){
            return json_encode(['code' =>2, 'msg'=>'请登录' ]);
        }
        $request = Request();
        $data = $request->param();
        $take=new TakeModel();
        $ss=$take->updatedatas(['state'=>1],['uid'=>$info['id'],'id'=>$data['id']]);
        if($ss){
            return $this->json_success('删除成功');
        }else{
            return $this->json_error('删除失败');
        }
    }
	
	
}
?>