<?php
namespace app\api\controller;
use app\api\controller\base\BaseController;
use think\facade\Log;
use think\facade\Request;
use think\Exception;
use think\Db;
use app\common\model\UserModel;
use app\common\model\BillinfoModel;
use app\common\model\ElectronModel;
use app\common\model\FolderModel;
use think\facade\Cache;
use GuzzleHttp\json_encode;

use mylib\curll;
use mylib\oss;
class ScanController extends BaseController
{
	//二维码扫描
	public function checkbill(){
        $info=$this->chektoken();//检测用户token
        if(!$info){
            return json_encode(['code' =>2, 'msg'=>'请登录' ]);
        }
		$billinfo= new BillinfoModel();
        $folder = new FolderModel();
		$request = Request();
    	$data = $request->param();
    	//$str="01,04,012001800104,42247092,9433.96,20190710,56605089491318728361,F73F,";
    	$res=explode(',', $data['strcode']);
    	if(count($res)<2){
    		return $this->json_error('识别失败,暂不支持此类型发票');
    	}
        $where=['uid'=>$info['id'],'billcode'=>$res[3],'state'=>0];
        $field='id,uid,typeid,photourl,invoicetype,photodate,opendate,consumetype,billcode,invoicecode,
			pretax,invoicename,taxamount,amount,checkcode,salename,saleduty,saleplace,salebank,buyname,
			buyduty,buyplace,buybank,remarks,ctime,folderid';
        $qs=$billinfo->getXq($where,$field);
        if($qs){
            if($qs['folderid']==0){
                $qs['foldername']='我的发票夹';
            }else{
                $allfolder=$folder->getInfo(['id'=>$qs['folderid']]);
                $qs['foldername']=$allfolder['title'];
            }
            //$qs['photourl']=INLET_PATH . $qs['photourl'];
            $qs['photourl']=IMG_PATH . $qs['photourl'];
            return json_encode(['code'=>0,'msg'=>'ok','result'=>$qs]);
        }else{
            return json_encode(['code'=>1,'msg'=>'此发票不存在','result'=>'']);
        }
	}





	
}
?>