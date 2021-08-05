<?php
namespace app\api\controller;
use app\api\controller\base\BaseController;
use think\facade\Log;
use think\facade\Request;
use think\Exception;
use think\Db;
use think\facade\Cache;
use app\common\model\UserModel;
use app\common\model\ElectronModel;
use app\common\model\BillinfoModel;
use app\common\model\FolderModel;
use GuzzleHttp\json_encode;
use mylib\oss;

class EnterController extends BaseController
{

    //手动录入
    public function houstment(){

        $ress=$this->chektoken();//检测用户token
        if(!$ress){
            return json_encode(['code' =>2, 'msg'=>'请登录' ]);
        }
        $billinfo= new BillinfoModel();

        $request = Request();
        $data = $request->param();
        $data['typeid']=$data['typeid'] ? $data['typeid']:1;
        if(isset($data['folderid'])){
            $folderid=$data['folderid'];
        }else{
            $folderid=0;
        }
        if(isset($data['img'])){
            $img=$data['img'];
        }else{
            $img='';
        }
        if(isset($data['amount'])){
            $amount=$data['amount'];
        }else{
            $amount='';
        }
        $array=[
            'uid'=>$ress['id'],
            'folderid'=>$folderid,
            'photourl'=>$img,
            'photodate'=>date('Y-m-d',time()),//拍照时间
            'opendate'=>$data['opendate'],//开票时间
            'billcode'=>$data['billcode'],//发票号码
            'amount'=>$amount,//金额
            'soutype'=>1,//拍照识别来的数据
            'ctime'=>time()//创建时间
        ];
        if($data['typeid']== 1 ){

        }elseif($data['typeid']==16){
            $array=[
                'typeid'=>2,
                'consumetype'=>"交通",//消费类型
                'invoicecode'=>$data['invoicecode'],//发票代码
                'invoicetype'=>"出租车发票",//发票种类
                'invoicename'=>"出租车发票",//发票名称
                'frompl'=>$data['frompl'],//起始
                'topl'=>$data['topl'],//结束
                'ctime'=>time()//创建时间
            ];
            if($array['amount']==''){
                return $this->json_error('获取数据失败！');
            }
            $id=$billinfo->getAdddata($array);
        }elseif($data['typeid']==17){
            $field='id,uid,typeid,photourl,invoicetype,photodate,opendate,consumetype,billcode,seattype,invoicename,
            amount,frompl,topl,ctime,name,folderid,remind,remindtime,reminddate,soutype';
        }elseif($data['typeid']==18){
            $field='id,uid,typeid,photourl,invoicetype,photodate,opendate,consumetype,billcode,invoicename,
            amount,frompl,topl,ctime,name,idcard,folderid,remind,remindtime,reminddate,soutype';
        }elseif($data['typeid']==19){
            $field='id,uid,typeid,photourl,invoicetype,invoicecode,photodate,opendate,consumetype,billcode,invoicename,
            amount,ctime,folderid,remind,remindtime,reminddate,soutype';
        }else{
            return $this->json_error('参数错误');
        }
    }



































	
	
}
?>