<?php
namespace app\api\controller;
use app\api\controller\base\BaseController;
use think\facade\Log;
use think\facade\Request;
use think\Exception;
use think\Db;
use app\common\model\UserModel;
use app\common\model\TakenotesModel;
use think\facade\Cache;
use app\common\model\UserActiveDayModel;
use app\common\model\BillinfoModel;
use app\common\model\TakeModel;
use app\common\model\CommonNoticeModel;
use GuzzleHttp\json_encode;
use thiagoalessio\TesseractOCR\TesseractOCR;

class IndexController extends BaseController
{

    //官网
	function index(){
		//echo 'aab';
        return $this->fetch('union/index');
	}
    function test(){
        echo (new TesseractOCR('2.jpg'))
        ->lang('chi_sim')
            ->run();
    }
    //分享文件夹的邀请码
	public function foldergx(){
        $request = Request();
        $data = $request->param();
        $this->assign('data',$data['incode']);
        return $this->fetch('union/folder');
    }
    //分享发票抬头
    public function taitou(){
        $request = Request();
        $data = $request->param();
        $take=new TakeModel();
        $res=$take->getOne(['id'=>$data['id']]);
        $this->assign('res',$res);
        return $this->fetch('union/taitou');
    }
	

    function dcwmsg(){
    	$request = Request();
    	$data = $request->param();
    	$dataa['phone'] = $data['phone'];
    	$dataa['content'] = $data['content'];
    	$dataa['addtime'] = time();
    	$res=Db::table('fp_message')->insert($dataa);
		if($res){
			return $this->json_success('发送成功');
		}else{
			return $this->json_error('发送失败');
		}
    }


    function runques(){
        $cat = Db::table('question_cate')->where('pid!=0')->select();
        foreach ($cat as $k=>$v){
            $num = rand(10,40);
            $tab = Db::table('question')->where('cid=0 ')->limit($num)->order('id asc')->select();
            $arr = array_column($tab, 'id');
            $data['cid'] = $v['id'];
           // var_dump(implode(',', $arr));exit;
            Db::table('question')->where('id in('.implode(',', $arr).')')->update($data);
        }
        
    }

    function getqcate(){
        
        $request = Request();
        $data = $request->param();
        $id = $data['cid']+0;
        $tab = Db::table('question_cate')->where('pid= '.$id)->order('id asc')->select();
        
        
        echo json_encode($tab);
    }
    function getq(){
        $request = Request();
        $data = $request->param();
        $id = $data['cid']+0;
        $tabs = Db::table('question')->where('cid='.$id)->order('id asc')->select();
        foreach ($tabs as $k=>$tab){
            if(empty($tab['answer'])&&$tab){
                $arr = [1=>'A','B','C','D','E','F'];
                $taba['content'] = ltrim($tab['content'],',');
                //var_dump($taba['content']);
                $tab['content'] = explode(',', $taba['content']);
                $rand = rand(1,count($tab['content']));
                $taba['answer'] = $arr[$rand];
                $tab['answer'] = $arr[$rand];
                //var_dump($taba);
                //var_dump('id='.$tab['id']);
                Db::table('question')
                ->where('id='.$tab['id'])->update($taba);
            }else{
                $tab['content'] = explode(',', $tab['content']);
            }
            $tabs[$k] = $tab;
        }
        echo json_encode($tabs);
    }

    public function del(){
        $billinfo=new  BillinfoModel();
        $res=$billinfo->where("id>2 and state=1 and photourl !=''")->find();
        //print_r($res);exit;
        if(!$res){
            echo "111";exit;
        }
        unlink($res['photourl']);//删除图片
        $billinfo->where(['id'=>$res['id']])->delete();
    }

    public function shan(){
	    $num=100;
	    for($i=0;$i<$num;$i++){
	        $this->del();
	       // $i++;
        }
    }

}
