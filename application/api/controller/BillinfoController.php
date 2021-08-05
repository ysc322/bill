<?php
namespace app\api\controller;
use app\api\controller\base\BaseController;
use mylib\Bill;
use think\facade\Log;
use think\facade\Request;
use think\Exception;
use think\Db;
use app\common\model\UserModel;
use app\common\model\BillinfoModel;
use app\common\model\TakenotesModel;
use app\common\model\CheckvipModel;
use think\facade\Cache;
use Curl\Curl;
use GuzzleHttp\json_encode;

use think\facade\Session;
//use mylib\Sms;
use mylib\oss;
class BillinfoController extends BaseController
{

    //ip代理生成
//    function mkipdl(){
//
//        $key = 'ipdlcache1'.date('YmdHi');
//        if(false !== Cache::get($key)&&!empty(Cache::get($key))) {
//            $res = Cache::get($key);
//        }else{
//            $url = 'http://t.ipjldl.com/index.php/api/entry?method=proxyServer.generate_api_url&packid=1&fa=0&fetch_key=&groupid=0&qty=1&time=1&pro=&city=&port=1&format=txt&ss=1&css=&dt=1&specialTxt=3&specialJson=&usertype=2';
//
//            $urs = new Curl();
//            $res = $urs->get($url);
//            $res = $res->response;
//            Cache::set($key,$res ,60);
//        }
//        return $res;
//    }


	//获取发票添加数据
    public function unthumbs(){
		$ress=$this->chektoken();//检测用户token
		$fapiao=fapiao();
		if(!$ress){
			return json_encode(['code' =>2, 'msg'=>'请登录' ]);
		}
        if($ress['shakynum'] > 0 && time()<'1622476799'){

        }else{
            if($ress['frequency'] <= 0){
                return json_encode(['code'=>4,'msg'=>'您的查验次数已用完！']);
            }
        }
    	$billinfo= new BillinfoModel();
		$request = Request();
    	$data = $request->param();
    	$image = file_get_contents($data['img']);

        //增值税发票
        $result = $fapiao->vatInvoice($image);
        //记录日志
        $assss['test'] = json_encode($result);
        Db::table('fp_test')->insert($assss);
        if (array_key_exists('error_code', $result)) {
            $object = substr($data['img'], 2);
            unlink('/home/wwwroot/mini/public/' . $object);
            return $this->json_error('发票识别失败，请重试');
        }
        if ($result['words_result']['InvoiceNum'] == '' || $result['words_result']['InvoiceCode'] == '' || $result['words_result']['InvoiceDate'] == '') {
            $object = substr($data['img'], 2);
            unlink('/home/wwwroot/mini/public/' . $object);
            return $this->json_error('发票识别失败，请重试');
        } else {
            $daim = $result['words_result']['InvoiceCode'];
            $riqi = $result['words_result']['InvoiceDate'];
            $billcode = $result['words_result']['InvoiceNum'];
        }
        if ($billinfo->getInfo(['uid' => $ress['id'], 'billcode' => $billcode, 'state' => 0])) {
            $object = substr($data['img'], 2);
            unlink('/home/wwwroot/mini/public/' . $object);
            //此发票已存在
            return $this->json_error('此发票已存在');
        }
        //开始查验
        if (strpos($result['words_result']['Remarks'], '红字增值税') !== false) {
            $str = "-";
        } else {
            $str = '';
        }
        $da = [
            'code' => $daim,
            'number' => $billcode,
            'date' => date('Y-m-d', strtotime(str_replace(array('年', '月', '日'), array('-', '-', ''), $riqi))),
            'extaxtotalfee' => $str . $result['words_result']['TotalAmount'],
            'checkcode' => $result['words_result']['CheckCode']
        ];
        $res = $this->chaxun($da, $ress, $data);
        if ($res == '') {
            return $this->json_error('发票识别失败,您可以尝试使用扫码或手动输入查验！');
        }
        $res['photourl'] = IMG_PATH . $res['photourl'];
        return json_encode(['code' => 0, 'msg' => '识别成功', 'result' => $res]);
    }


    //查验发票
    public function chaxun($data,$info,$shuju){
        $img=$shuju['img'];
        $billinfo= new BillinfoModel();
        $take= new TakenotesModel();
        $checkvip= new CheckvipModel();
        $user= new UserModel();
        $bill= new Bill();
        $lwjym=substr($data['checkcode'],-6);
        //手动
        if (preg_match('/^\d{7}(1|5)\d{2}$/',$data['code'])){
            $code=$data['extaxtotalfee'];
        }else{
            $code=$lwjym;
        }
        if(empty($code)){
            $code=$data['extaxtotalfee'];
        }
        $data['date']=date('Ymd',strtotime($data['date']));
        $contents=[
            "fpdm"=>$data['code'],   // 发票代码
            "fphm"=>$data['number'],       // 发票号码
            "date"=>$data['date'],       // 发票日期
            "code"=>$code // 校验码后6位/税前金额
        ];
        $sqje=$data['extaxtotalfee'];
        $ycm = rand(100000, 500000);
        usleep($ycm);
        $list=$bill->getfpinfohaoxin($contents['fpdm'],$contents['fphm'],$contents['date'],$contents['code']);
        $qq = json_decode($list, true);
        if($qq['code']==1) {
            if ($contents['code'] == $data['extaxtotalfee']) {
                $contents['code'] = $lwjym;
            } else {
                $contents['code'] = $data['extaxtotalfee'];
            }
            $list = $bill->getfpinfohaoxin($contents['fpdm'], $contents['fphm'], $contents['date'], $contents['code']);
            $qq = json_decode($list, true);
        }
        if($qq['code']==0){
            $jian=$bill->untrue($qq);
            if($jian !=''){
                return $this->json_error($jian);
            }
            if($qq['type']=='01'){
                $arr=$bill->zp($qq);
            }elseif($qq['type']=='03'){
                $arr=$bill->jdc($qq);
            }elseif($qq['type']=='04'){
                $arr=$bill->pp($qq);
            }elseif($qq['type']=='08'){
                $arr=$bill->zz($qq);
            }elseif($qq['type']=='10'){
                $arr=$bill->dzfp($qq);
            }elseif($qq['type']=='11'){
                $arr=$bill->jsfp($qq,$sqje);
            }elseif($qq['type']=='14'){
                $arr=$bill->abc($qq,$contents['date']);
            }else{
                return false;
            }
            if(strlen($arr['checkstate'])>2){
                $arr['checkstate']=2;
            }
            if($arr['checkstate']==0){
                $arr['checkstate']=1;
            }else{
                if(strpos($arr['remarks'],'红字增值税') !== false) {
                    $arr['checkstate']=1;
                    $arr['pretax']='-'.$arr['pretax'];
                    $arr['taxamount']='-'.$arr['taxamount'];
                    $arr['amount']='-'.$arr['amount'];
                }else{
                    $arr['checkstate']=2;
                    $arr['remarks']= $arr['remarks'].'--作废--';
                }
            }

            $arr['uid']=$info['id'];
            $arr['photodate']=date('Y-m-d',time());
            $arr['ctime']=time();
            //处理图片问题
            if(isset($img)){
                $inf = pathinfo($img,PATHINFO_DIRNAME);//获取前面部分
                $ext = pathinfo($img,PATHINFO_EXTENSION);//获取后缀
                $newname=$inf.'/'.$data['number'].'.'.$ext;
                rename($img,$newname);
                //上传oss
                $object=substr($newname,2);
                $content=file_get_contents($newname);
                oss::uploadfile($object,$content);
                $arr['photourl']=$newname;
            }
            $check=$billinfo->getInfo(['uid' => $info['id'], 'billcode' => $arr['billcode'], 'state' => 0]);
            if(!$check){
                $id = $billinfo->getAdddata($arr);
                $take->getInc(['uid'=>$info['id']],'checknumber');
                $checkvip->adddata(['uid'=>$info['id'],'type'=>1,'ctime'=>date('Y-m-d H:i:s',time())]);
                if($info['uvip']==0){
                    if($info['shakynum'] > 0 && time()<'1622476799'){
                        $user->where(['id' => $info['id']])->setDec('shakynum');//验证次数减1
                    }else{
                        $user->where(['id' => $info['id']])->setDec('frequency');//验证次数减1
                    }
                }
            }else{
                $id =$check['id'];
            }
            $where = ['id' => $id];
            $field = 'id,uid,typeid,photourl,invoicetype,photodate,opendate,consumetype,billcode,invoicecode,
                    pretax,invoicename,taxamount,amount,checkcode,salename,saleduty,saleplace,salebank,buyname,
                    buyduty,buyplace,buybank,remarks,ctime,folderid,taxa,checkbill,checkstate,remind,remindtime,reminddate,checktime';
            $resq = $billinfo->getXq($where, $field);
            return  $resq;
        }else{
            return '';
        }
    }



    /**
	 * 检查指定字符串是否为日期格式 年-月-日
	 * @param $date  日期字符串
	 * @return bool  true 是日期格式     false 不是日期格式
	 */
	public function valid_date($date){
	    //匹配日期格式
	    if (preg_match ("/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/", $date, $parts)){
	        //检测是否为日期,checkdate为月日年
	        if(checkdate($parts[2],$parts[3],$parts[1])){
	            return true;
	        }else{
	            return false;
	        }
	    }else{
	    	return false;
	    }
	}




    //搜索功能
    public function billss(){
        $res=$this->chektoken();//检测用户token
        if(!$res){
            return json_encode(['code' =>2, 'msg'=>'请登录' ]);
        }
        $request = Request();
        $data = $request->param();
        if($data['key']==''){
            return $this->json_error('请输入要搜索的内容！');
        }
       // $start=$data['page'] ? $data['page'] : 0;
        $billinfo= new BillinfoModel();
        $where=['uid'=>$res['id'],'state'=>0];
        $list=$billinfo->field('id,buyname,billcode,amount,opendate,checkstate,checktime')->where($where)
            ->where("`amount` LIKE '%".$data['key']."%' OR `billcode` LIKE '%".$data['key']."%' OR  `salename` LIKE '%".$data['key']."%'  OR `buyname` LIKE '%".$data['key']."%'")
           // ->order('id desc')->limit($start * 10, 10)->select();
            ->order('id desc')->select();
        if(!$list){
            $list=[];
        }
        return json_encode(['code'=>0,'msg'=>'OK','result'=>$list]);
    }






    //获取用户自己的所有的查验时间
    public function checktime(){
        $res=$this->chektoken();//检测用户token
        if(!$res){
            return json_encode(['code' =>2, 'msg'=>'请登录' ]);
        }
        $request = Request();
        $data = $request->param();
        $billinfo= new BillinfoModel();
        $data['type']=$data['type']?$data['type']:0;//默认查验发票
        $li=[];
        if($data['type']==1){
            $list=$billinfo->getOpentime($res['id'],$data['start'],$data['end']);
            //echo $billinfo->getLastSql();exit;
        }else{
            $list=$billinfo->getChektime($res['id'],$data['start'],$data['end']);
        }
        if($list){
            $lis = array_unique(array_column($list,'opendate'));
            foreach ($lis as $k=>$v){
                $li[]=$v;
            }
        }
        return json_encode(['code'=>0,'msg'=>'OK','result'=>$li]);
    }


    //获取某一天的发票列表
    public function billdaylist(){
        $res=$this->chektoken();//检测用户token
        if(!$res){
            return json_encode(['code' =>2, 'msg'=>'请登录' ]);
        }
        $request = Request();
        $data = $request->param();
        $billinfo= new BillinfoModel();
        $type= $data['type'] ? $data['type'] : 0;
        $where=" `uid`=".$res['id']." and `state`=0 and `checkbill`=1 and soutype=0 ";
        $field="id,buyname,billcode,amount,opendate,checkstate,checktime";
        if ($type==1) {
            //按开票时间排序
            $where.=" and `opendate`='".$data['opendate']."' ";
            $info=$billinfo->getBillxsort($field,$where);
        }else{
            //按照查验时间排序
            $where.=" and DATE_FORMAT(`checktime`,'%Y-%m-%d') ='".$data['opendate']."' ";
            $info=$billinfo->getBillxcheck($field,$where);
            //echo $billinfo->getLastSql();exit;
        }
        if(!$info){
            $info=[];
        }
        return json_encode(['code'=>0,'msg'=>'ok','result'=>$info]);
    }






}
?>