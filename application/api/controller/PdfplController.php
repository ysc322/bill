<?php
namespace app\api\controller;
use app\api\controller\base\BaseController;
use think\facade\Log;
use think\facade\Request;
use think\Exception;
use think\Db;
use app\common\model\UserModel;
use app\common\model\TakenotesModel;
use app\common\model\CheckvipModel;
use app\common\model\BillinfoModel;
use app\common\model\ElectronModel;
use app\common\model\FolderModel;
use app\common\model\BatchModel;
use app\common\model\BatchSingleModel;

use think\facade\Cache;
use GuzzleHttp\json_encode;

use mylib\curll;
class PdfplController extends BaseController
{
	
    //处理批量的PDF（暂时不用）
    public function batchlist()
    {
        $batch = new BatchModel();
        $batchsingle = new BatchSingleModel();
        $info = $this->chektoken();//检测用户token
        if (!$info) {
            return json_encode(['code' => 2, 'msg' => '请登录']);
        }
        $request = Request();
        $data = $request->param();
        $imgth = '';
        if (request()->file('file')) {
            $imgth = $this->batch();
            if ($imgth == '') {
                return $this->json_error('请上传图片！');
            }
        }
        //$imgth=$data['imgurl'];//保存的发票pdf的地址
        $batchid = $batch->getAdd(['uid' => $info['id'], 'imgurl' => $imgth]);
        $ph = substr($imgth, 2);//删除'./'
        $pdf = '/home/wwwroot/fapiao/public/' . $ph;
        $photourl = $this->ppng($pdf);//获取转换成png后的图片地址
        if (empty($photourl) != '') {
            foreach ($photourl as $k => $v) {
                $batchsingle->adddata(['batchid' => $batchid, 'batimg' => $v]);
            }
            $batch->updatedatas(['state' => 1], ['id' => $batchid]);//修改处理状态
        } else {
            return $this->json_error('请检查PDF是否符合规则！');
        }
        return $this->json_success('上传成功！');
    }


	
	//pdf批量上传接口
    public function batch(){
        $batch = new BatchModel();
        //$batchsingle = new BatchSingleModel();
        $res = $this->chektoken();//检测用户token
        $clientType = Request::instance()->header('clientType');
        $request = Request();
        $data = $request->param();
        if (!$res) {
            return json_encode(['code' => 2, 'msg' => '请登录']);
        }
        if($res['uvip']!=1){
            return json_encode(['code'=>3,'msg'=>'vip专属功能!','result'=>'']);
        }
        $imgth = $data['file'];
        $fileName=$data['filename'];
        /*if (request()->file('file')) {
            $req=upload();
            $info=json_decode($req,true);
            if($info['success']==1) {
                $imgth='.'.$info['url'];
                $fileName=$info['fileName'];
            }
        }*/
        if ($imgth == '') {
            return $this->json_error('请上传图片！');
        }
        if($clientType=='iOS'){
            $client=1;
        }else{
            $client=0;
        }
        $batchid = $batch->getAdd(['uid' => $res['id'],'client'=>$client,'folderid'=>$data['folderid'], 'imgurl' => $imgth,'title'=>$fileName]);
        if($batchid){
            return $this->json_success('上传成功！');
        }else{
            return $this->json_error('上传失败');
        }
    }

    /**
     * PDF2PNG  将PDF转成png
     * @param $pdf  待处理的PDF文件
     * @param $path 待保存的图片路径
     * @param $page 待导出的页面 -1为全部 0为第一页 1为第二页
     * @return      保存好的图片路径和文件名
     */
    public function ppng($pdf){
        //$pdf='/home/wwwroot/fapiao/public/uploads/file/xlx/as.pdf';
        $path='./uploads/file/pdf';
        $page=-1;

        if(!extension_loaded('imagick')){
            return false;
        }
        if(!file_exists($pdf)){
            return false;
        }
        $im = new \Imagick();
        $im->setResolution(120,120);
        $im->setCompressionQuality(100);
        //print_r($im);exit;
        if($page==-1){
            $im->readImage($pdf);
        }else{
            $im->readImage($pdf."[".$page."]");
        }
        foreach ($im as $Key => $Var){
            $Var->setImageFormat('png');
            $filename = $path."/". md5($Key.time()).'.png';
            if($Var->writeImage($filename) == true){
                $Return[] = $filename;
            }
        }
        //print_r($Return);exit;
        return $Return;
    }

    //获取批量导入的PDF列表
    public function pldaoru(){
        $info = $this->chektoken();//检测用户token
        if (!$info) {
            return json_encode(['code' => 2, 'msg' => '请登录']);
        }
        $batch = new BatchModel();
        $list=$batch->getList(['uid'=>$info['id']]);
        $qq=[];
        if($list) {
            foreach ($list as $k => $v) {
                $v['eronum']=0;
                if($v['state']==1) {
                    $balist = Db::name('fp_batch_single')->where(['batchid' => $v['id'], 'state' => 0])->select();
                    if ($balist) {
                        $v['state'] = 2;//正在进行
                    } else {
                        $baclist = Db::name('fp_batch_single')->where(['batchid' => $v['id'], 'state' => 2])->select();
                        if ($baclist) {
                            $v['state'] = 3;//出现错误
                            $v['eronum']=count($baclist);
                            $v['erolist']=$this->errlist($v['id']);
                        }
                    }
                }else{
                    $v['state'] = 0;//未处理
                }
                $v['foldername']= Db::name('fp_folder')->where(['id' => $v['folderid']])->value('title');
                $v['num']= Db::name('fp_batch_single')->where(['batchid' => $v['id']])->count();
                $qq[] = $v;
            }
        }
        return json_encode(['code'=>0,'msg'=>'ok','result'=>$qq]);
    }

    //获取错误的页数
    public function errlist($batchid){
        $baclist = Db::name('fp_batch_single')->where(['batchid' => $batchid, 'state' => 2])->select();
        $ss=[];
        foreach ($baclist as $v){
            $ss[]=$v['sequence'];
        }
        $res=implode(',',$ss);
       // return json_encode(['code'=>0,'msg'=>'ok','result'=>$res]);
        return $res;
    }


	
}
?>