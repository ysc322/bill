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

class BillController extends BaseController
{
	//发票详情
//    public function billxq(){
//        $ress=$this->chektoken();//检测用户token
//        if(!$ress){
//            return json_encode(['code' =>2, 'msg'=>'请登录' ]);
//        }
//        $billinfo= new BillinfoModel();
//        $request = Request();
//        $data = $request->param();
//        $where=['id'=>$data['id']];
//        $field='id,uid,typeid,photourl,invoicetype,photodate,opendate,consumetype,billcode,invoicecode,
//            pretax,invoicename,taxamount,amount,checkcode,salename,saleduty,saleplace,salebank,buyname,
//            buyduty,buyplace,buybank,remarks,ctime,folderid,taxa,checkbill,checkstate,remind,remindtime,reminddate,checktime';
//        $info=$billinfo->getXq($where,$field);
//        if ($info) {
//            $info['ctime']=date('Y-m-d H:i:s',$info['ctime']);
//            return json_encode(['code'=>0,'msg'=>'ok','result'=>$info]);
//        }else{
//            return $this->json_error('此发票不存在');
//        }
//    }

    //发票详情
    public function billxq(){

        $ress=$this->chektoken();//检测用户token
        if(!$ress){
            return json_encode(['code' =>2, 'msg'=>'请登录' ]);
        }
        $billinfo= new BillinfoModel();
        $folder = new FolderModel();
        $electron = new ElectronModel();
        $request = Request();
        $data = $request->param();
        $data['typeid']=$data['typeid'] ? $data['typeid']:0;
        $where=['id'=>$data['id']];
        if($data['typeid']< 16 ){
            $field='id,uid,typeid,photourl,invoicetype,photodate,opendate,consumetype,billcode,invoicecode,
            pretax,invoicename,taxamount,amount,checkcode,salename,saleduty,saleplace,salebank,buyname,
            buyduty,buyplace,buybank,remarks,ctime,folderid,taxa,checkbill,checkstate,remind,remindtime,reminddate,checktime,soutype';
        }elseif($data['typeid']==16){
            $field='id,uid,typeid,photourl,invoicetype,photodate,opendate,consumetype,billcode,invoicecode,invoicename,
            amount,frompl,topl,ctime,folderid,remind,remindtime,reminddate,soutype';
        }elseif($data['typeid']==17){
            $field='id,uid,typeid,photourl,invoicetype,photodate,opendate,consumetype,billcode,seattype,invoicename,
            amount,frompl,topl,ctime,name,folderid,remind,remindtime,reminddate,soutype,remarks';
        }elseif($data['typeid']==18){
            $field='id,uid,typeid,photourl,invoicetype,photodate,opendate,consumetype,billcode,invoicename,
            amount,frompl,topl,ctime,name,idcard,folderid,remind,remindtime,reminddate,soutype';
        }elseif($data['typeid']==19){
            $field='id,uid,typeid,photourl,invoicetype,invoicecode,photodate,opendate,consumetype,billcode,invoicename,
            amount,ctime,folderid,remind,remindtime,reminddate,soutype';
        }else{
            return $this->json_error('typeid参数错误');
        }
        $info=$billinfo->getXq($where,$field);
        if ($info) {
            //if($info['photourl'] !='' || $info['checkbill']== 1){ //认证过的不让修改
            if($info['photourl'] !=''){
                unset($info['taxa']);
                //$info['photourl']=INLET_PATH . $info['photourl'];
                $sas=substr($info['photourl'],2);
                $info['photourl']=IMG_PATH.$sas;
            }else{
                $info['photourl']='';
            }
            if($info['folderid']==0){
                $info['foldername']='我的发票夹';
            }else{
                $allfolder=$folder->getInfo(['id'=>$info['folderid']]);
                $info['foldername']=$allfolder['title'];
            }
            //获取PDF地址
            $reee=$electron->getOne(['billid'=>$info['id']]);
            if($reee){
                if(strpos($reee['imgth'],'http') === false){
                    //$info['pdf']= INLET_PATH . $reee['imgth'];
                    $reee['imgth']=substr($reee['imgth'],2);
                    $info['pdf']= INLET_PATH . $reee['imgth'];
                }else{
                    $info['pdf']=$reee['imgth'];
                }
            }else{
                $info['pdf']='';
            }
            $info['ctime']=date('Y-m-d H:i:s',$info['ctime']);
            return json_encode(['code'=>0,'msg'=>'ok','result'=>$info]);
        }else{
            return $this->json_error('此发票不存在');
        }

    }

    //修改发票信息
    public function upbill(){
        $ress=$this->chektoken();//检测用户token
        if(!$ress){
            return json_encode(['code' =>2, 'msg'=>'请登录' ]);
        }
        $billinfo= new BillinfoModel();
        $request = Request();
        $data = $request->param();

        $where=['id'=>$data['id'],'uid'=>$ress['id']];
        if(array_key_exists('folderid', $data) && $data['folderid']==='NULL'){
            $data['folderid']=0;
        }
        $info=$billinfo->updatedatas($data,$where);
        if($info === false){
            return $this->json_error('修改失败');
        }else{
            return $this->json_success('修改成功');
        }
    }

    //移动发票夹
    public function movebill(){
        $ress=$this->chektoken();//检测用户token
        if(!$ress){
            return json_encode(['code' =>2, 'msg'=>'请登录' ]);
        }
        $billinfo= new BillinfoModel();
        $request = Request();
        $data = $request->param();
        $idas=explode(",",$data['id']);
        $da=['folderid'=>$data['folderid']];
        $info=$billinfo->updateFolder($da,$idas);
        if($info){
            return $this->json_success('修改成功');
        }else{
            return $this->json_error('修改失败');
        }
    }

    //删除发票
   public function billdel(){
        $ress=$this->chektoken();//检测用户token
        if(!$ress){
            return json_encode(['code' =>2, 'msg'=>'请登录' ]);
        }
        $billinfo= new BillinfoModel();
        $request = Request();
        $data = $request->param();
        $idas=explode(",",$data['id']);
        $da=['state'=>1];
        $info=$billinfo->updateFolder($da,$idas);
        if($info){
            return $this->json_success('删除成功');
        }else{
            return $this->json_error('删除失败');
        }
    }
































	
	
}
?>