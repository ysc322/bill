<?php
namespace app\api\controller;
use app\api\controller\base\BaseController;
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
    //ip代理生成
    function mkipdl(){
        $curl = new Curl();
        $url = 'http://ggg.ynsjapp.info/api/mkipdl';
        $urs = new Curl();
        $res = $urs->get($url);
        if($res->response&&$res->http_status_code==200){

            $res = $res->response;
            return $res;
        }else{
            return '';
        }

    }
    function getfpinfohaoxin($fpdm='',$fphm='',$date='',$jym=''){

        $ipp = $this->mkipdl();
        Db::table('fp_test')->insert(['test'=>'cd /home/wwwroot/caiji/fapiao/html/ && python fp1.py '.$fpdm.' '.$fphm.' '.$date.' '.$jym.' '.$ipp." 2>&1"]);
        $res = exec('cd /home/wwwroot/caiji/fapiao/html/ && python fp1.py '.$fpdm.' '.$fphm.' '.$date.' '.$jym.' '.$ipp." 2>&1",$out,$ress);
        Db::table('fp_test')->insert(['test'=>$res]);
        $res = str_replace("'", '"', $res);
        if($res){
            return $res;
        }
    }

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
        $list=$this->getfpinfohaoxin($contents['fpdm'],$contents['fphm'],$contents['date'],$contents['code']);
        $qq = json_decode($list, true);
        if($qq['code']==1) {
            if ($contents['code'] == $data['extaxtotalfee']) {
                $contents['code'] = $lwjym;
            } else {
                $contents['code'] = $data['extaxtotalfee'];
            }
            $list = $this->getfpinfohaoxin($contents['fpdm'], $contents['fphm'], $contents['date'], $contents['code']);
            $qq = json_decode($list, true);
        }
        if($qq['code']==0){
            $jian=$this->untrue($qq);
            if($jian !=''){
                return $this->json_error($jian);
            }
            if($qq['type']=='01'){
                $arr=$this->zp($qq);
            }elseif($qq['type']=='03'){
                $arr=$this->jdc($qq);
            }elseif($qq['type']=='04'){
                $arr=$this->pp($qq);
            }elseif($qq['type']=='08'){
                $arr=$this->zz($qq);
            }elseif($qq['type']=='10'){
                $arr=$this->dzfp($qq);
            }elseif($qq['type']=='11'){
                $arr=$this->jsfp($qq,$sqje);
            }elseif($qq['type']=='14'){
                $arr=$this->abc($qq,$contents['date']);
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
            $id = $billinfo->getAdddata($arr);
            if ($id) {
                $where = ['id' => $id];
                $field = 'id,uid,typeid,photourl,invoicetype,photodate,opendate,consumetype,billcode,invoicecode,
                        pretax,invoicename,taxamount,amount,checkcode,salename,saleduty,saleplace,salebank,buyname,
                        buyduty,buyplace,buybank,remarks,ctime,folderid,taxa,checkbill,checkstate,remind,remindtime,reminddate,checktime';
                $resq = $billinfo->getXq($where, $field);
                $take->getInc(['uid'=>$info['id']],'checknumber');
                $checkvip->adddata(['uid'=>$info['id'],'type'=>1,'ctime'=>date('Y-m-d H:i:s',time())]);
                if($info['uvip']==0){
                    if($info['shakynum'] > 0 && time()<'1622476799'){
                        $user->where(['id' => $info['id']])->setDec('shakynum');//验证次数减1
                    }else{
                        $user->where(['id' => $info['id']])->setDec('frequency');//验证次数减1
                    }
//                    $user->where(['id' => $info['id']])->setDec('frequency');//验证次数减1
                }
                return  $resq;
            } else {
                return '';
            }
        }else{
            return '';
        }
    }

    //增值税专用发票01
    public function zp($qq){
        $qq['zfbz']=$qq['zfbz']?$qq['zfbz']:0;
        $ta='';
        if($qq['summarys'][0]['taxRate']>=1){
            $ta=$qq['summarys'][0]['taxRate']."%";
        }
        $qq['kprq_zp']=date('Y-m-d',strtotime( str_replace( array('年','月','日') , array('-','-','') , $qq['kprq_zp'])));
        $arr = [
            'typeid' => 1,
            'invoicetype' => $qq['typeName'],//发票类型
            'opendate' => $qq['kprq_zp'],//开票时间
            'billcode' => $qq['fphm_zp'],//发票号码
            'invoicecode' => $qq['fpdm_zp'],//发票代码
            'pretax' => $qq['je_zp'],//税前金额
            'invoicename' => $qq['fpcc_zp'],//发票名称
            'taxamount' => $qq['se_zp'],//税额
            'amount' => $qq['jshj_zp'],//金额
            'checkcode' => $qq['jym_zp'],//校验码
            'salename' => $qq['fpmx_xfmc'],//销售方名称
            'saleduty' => $qq['xfsbh_zp'],//销售方纳税人识别号
            'saleplace' => $qq['xfdzdh_zp'],//销售方地址及电话
            'salebank' => $qq['xfyhzh_zp'],//销售方开户行及账号
            'buyname' => $qq['fpmx_gfmc'],//购方名称
            'buyduty' => $qq['gfsbh_zp'],//购方纳税人识别号
            'buyplace' => $qq['gfdzdh_zp'],//购方地址及电话
            'buybank' => $qq['gfyhzh_zp'],//购方开户行及账号
            'checkbill' => 1,//已验证
            'checktime' => date('Y-m-d H:i:s', time()),//验证时间
            //'remarks' => $qq['bz_zp'] . '机器编码：' . $qq['sbbh_dzfp'],//备注
            'remarks' => $qq['bz_zp'],//备注
            'checkstate'=>$qq['zfbz'],//0代表正常 Y或者2代表作废 3是红冲

            'consumetype'=>$qq['summarys'][0]['name'],//消费类型名称
            'specif'=>$qq['summarys'][0]['type'],//规格型号
            'unit'=>$qq['summarys'][0]['unit'],//单位
            'number'=>$qq['summarys'][0]['amount'],//数量
            'unitprice'=>$qq['summarys'][0]['priceUnit'],//单价
            'unittotal'=>$qq['summarys'][0]['priceSum'],//单品金额总和
            //'taxa'=>($qq['summarys'][0]['taxRate'] * 100)."%",//税率
            'taxa'=>$ta,//税率
            'unittax'=>$qq['summarys'][0]['taxSum'],//单品税额
            'whole'=>json_encode($qq)//返回的完整的值
        ];
        return $arr;
    }

    //增值税电子普通发票10
    public function dzfp($qq){
        $qq['zfbz']=$qq['zfbz']?$qq['zfbz']:0;
        $ta='';
        if(intval($qq['summarys'][0]['taxRate'])>=1){
            $ta=$qq['summarys'][0]['taxRate']."%";
        }
        $qq['kprq_dzfp']=date('Y-m-d',strtotime( str_replace( array('年','月','日') , array('-','-','') , $qq['kprq_dzfp'])));
        $arr = [
            'typeid' => 10,
            'invoicetype' => $qq['typeName'],//发票种类
            'opendate' => $qq['kprq_dzfp'],//开票时间
            'billcode' => $qq['fphm_dzfp'],//发票号码
            'invoicecode' => $qq['fpdm_dzfp'],//发票代码
            'pretax' => $qq['je_dzfp'],//税前金额
            'invoicename' => $qq['fpcc_dzfp'],//发票名称
            'taxamount' => $qq['se_dzfp'],//税额
            'amount' => $qq['jshjxx_dzfp'],//金额
            'checkcode' => $qq['jym_dzfp'],//校验码
            'salename' => $qq['xfmc_dzfp'],//销售方名称
            'saleduty' => $qq['xfsbh_dzfp'],//销售方纳税人识别号
            'saleplace' => $qq['xfdzdh_dzfp'],//销售方地址及电话
            'salebank' => $qq['xfyhzh_dzfp'],//销售方开户行及账号
            'buyname' => $qq['gfmc_dzfp'],//购方名称
            'buyduty' => $qq['gfsbh_dzfp'],//购方纳税人识别号
            'buyplace' => $qq['gfdzdh_dzfp'],//购方地址及电话
            'buybank' => $qq['gfyhzh_dzfp'],//购方开户行及账号
            'checkbill' => 1,//已验证
            'checktime' => date('Y-m-d H:i:s', time()),//验证时间
            //'remarks' => $qq['bz_dzfp'] . '机器编码：' . $qq['sbbh_dzfp'],//备注
            'remarks' => $qq['remarks'],//备注
            'checkstate'=>$qq['zfbz'],//0代表正常 Y或者2代表作废 3是红冲

            'consumetype'=>$qq['summarys'][0]['name'],//消费类型名称
            'specif'=>$qq['summarys'][0]['type'],//规格型号
            'unit'=>$qq['summarys'][0]['unit'],//单位
            'number'=>$qq['summarys'][0]['amount'],//数量
            'unitprice'=>$qq['summarys'][0]['priceUnit'],//单价
            'unittotal'=>$qq['summarys'][0]['priceSum'],//单品金额总和
            //'taxa'=>($qq['summarys'][0]['taxRate'] * 100)."%",//税率
            'taxa'=>$ta,//税率
            'unittax'=>$qq['summarys'][0]['taxSum'],//单品税额
            'whole'=>json_encode($qq)//返回的完整的值
        ];
        return $arr;
    }

    //机动车销售统一发票03
    public function jdc($qq){
        $qq['zfbz']=$qq['zfbz']?$qq['zfbz']:0;
        $qq['kprq_jdcfp']=date('Y-m-d',strtotime( str_replace( array('年','月','日') , array('-','-','') , $qq['kprq_jdcfp'])));
        $arr = [
            'typeid' => 3,
            'invoicetype' => $qq['typeName'],//发票种类
            'opendate' => $qq['kprq_jdcfp'],//开票时间
            'billcode' => $qq['fphm_jdcfp'],//发票号码
            'invoicecode' => $qq['fpdm_jdcfp'],//发票代码
            'pretax' => $qq['je_jdcfp'],//税前金额
            'invoicename' => $qq['fpcc_jdcfp'],//发票名称
            'taxamount' => $qq['se_jdcfp'],//税额
            'amount' => $qq['jshjxx_jdcfp'],//金额
            'checkcode' => $qq['jym_jdcfp'],//校验码
            'salename' => $qq['fpmx_xfmc'],//销售方名称
            'saleduty' => $qq['xfsbh_jdcfp'],//销售方纳税人识别号
            'saleplace' => $qq['xfdzdh_jdcfp'],//销售方地址及电话
            'salebank' => $qq['xfyhzh_jdcfp'],//销售方开户行及账号
            'buyname' => $qq['fpmx_gfmc'],//购方名称
            'buyduty' => $qq['gfsbh_jdcfp'],//购方纳税人识别号
            'buyplace' => $qq['gfdzdh_jdcfp'],//购方地址及电话
            'buybank' => $qq['gfyhzh_jdcfp'],//购方开户行及账号
            'checkbill' => 1,//已验证
            'checktime' => date('Y-m-d H:i:s', time()),//验证时间
            //'remarks' => $qq['bz_dzfp'] . '机器编码：' . $qq['jqbm_jdcfp'],//备注
            'remarks' => $qq['remarks'],//备注
            'checkstate'=>$qq['zfbz'],//0代表正常 Y或者2代表作废 3是红冲

            'whole'=>json_encode($qq)//返回的完整的值
        ];
        return $arr;
    }

    //增值税普通发票04
    public function pp($qq){
        $qq['zfbz']=$qq['zfbz']?$qq['zfbz']:0;
        $ta='';
        if(intval($qq['summarys'][0]['taxRate'])>=1){
            $ta=$qq['summarys'][0]['taxRate']."%";
        }
        $qq['kprq_pp']=date('Y-m-d',strtotime( str_replace( array('年','月','日') , array('-','-','') , $qq['kprq_pp'])));
        $arr = [
            'typeid' => 4,
            'invoicetype' => $qq['typeName'],//发票种类
            'opendate' => $qq['kprq_pp'],//开票时间
            'billcode' => $qq['fphm_pp'],//发票号码
            'invoicecode' => $qq['fpdm_pp'],//发票代码
            'pretax' => $qq['je_pp'],//税前金额
            'invoicename' => $qq['fpcc_pp'],//发票名称
            'taxamount' => $qq['se_pp'],//税额
            'amount' => $qq['jshj_pp'],//金额
            'checkcode' => $qq['jym_pp'],//校验码
            'salename' => $qq['fpmx_xfmc'],//销售方名称
            'saleduty' => $qq['xfsbh_pp'],//销售方纳税人识别号
            'saleplace' => $qq['xfdzdh_pp'],//销售方地址及电话
            'salebank' => $qq['xfyhzh_pp'],//销售方开户行及账号
            'buyname' => $qq['fpmx_gfmc'],//购方名称
            'buyduty' => $qq['gfsbh_pp'],//购方纳税人识别号
            'buyplace' => $qq['gfdzdh_pp'],//购方地址及电话
            'buybank' => $qq['gfyhzh_pp'],//购方开户行及账号
            'checkbill' => 1,//已验证
            'checktime' => date('Y-m-d H:i:s', time()),//验证时间
            //'remarks' => $qq['bz_pp'] . '机器编码：' . $qq['jqbh_pp'],//备注
            'remarks' => $qq['remarks'],//备注
            'checkstate'=>$qq['zfbz'],//0代表正常 Y或者2代表作废 3是红冲

            'consumetype'=>$qq['summarys'][0]['name'],//消费类型名称
            'specif'=>$qq['summarys'][0]['type'],//规格型号
            'unit'=>$qq['summarys'][0]['unit'],//单位
            'number'=>$qq['summarys'][0]['amount'],//数量
            'unitprice'=>$qq['summarys'][0]['priceUnit'],//单价
            'unittotal'=>$qq['summarys'][0]['priceSum'],//单品金额总和
            //'taxa'=>($qq['summarys'][0]['taxRate'] * 100)."%",//税率
            'taxa'=>$ta,//税率
            'unittax'=>$qq['summarys'][0]['taxSum'],//单品税额
            'whole'=>json_encode($qq)//返回的完整的值
        ];
        return $arr;
    }

    //增值税电子专用发票08
    public function zz($qq){
        $qq['zfbz']=$qq['zfbz']?$qq['zfbz']:0;
        $ta='';
        if(intval($qq['summarys'][0]['taxRate'])>=1){
            $ta=$qq['summarys'][0]['taxRate']."%";
        }
        $qq['kprq_zp']=date('Y-m-d',strtotime( str_replace( array('年','月','日') , array('-','-','') , $qq['kprq_zp'])));
        $arr = [
            'typeid' => 8,
            'invoicetype' => $qq['typeName'],//发票种类
            'opendate' => $qq['kprq_zp'],//开票时间
            'billcode' => $qq['fphm_zp'],//发票号码
            'invoicecode' => $qq['fpdm_zp'],//发票代码
            'pretax' => $qq['je_zp'],//税前金额
            'invoicename' => $qq['fpcc_zp'],//发票名称
            'taxamount' => $qq['se_zp'],//税额
            'amount' => $qq['jshj_zp'],//金额
            'checkcode' => $qq['jym_zp'],//校验码
            'salename' => $qq['fpmx_xfmc'],//销售方名称
            'saleduty' => $qq['xfsbh_zp'],//销售方纳税人识别号
            'saleplace' => $qq['xfdzdh_zp'],//销售方地址及电话
            'salebank' => $qq['xfyhzh_zp'],//销售方开户行及账号
            'buyname' => $qq['fpmx_gfmc'],//购方名称
            'buyduty' => $qq['gfsbh_zp'],//购方纳税人识别号
            'buyplace' => $qq['gfdzdh_zp'],//购方地址及电话
            'buybank' => $qq['gfyhzh_zp'],//购方开户行及账号
            'checkbill' => 1,//已验证
            'checktime' => date('Y-m-d H:i:s', time()),//验证时间
            //'remarks' => $qq['bz_zp'] . '机器编码：' . $qq['jqbh_zp'],//备注
            'remarks' => $qq['remarks'],//备注
            'checkstate'=>$qq['zfbz'],//0代表正常 Y或者2代表作废 3是红冲

            'consumetype'=>$qq['summarys'][0]['name'],//消费类型名称
            'specif'=>$qq['summarys'][0]['type'],//规格型号
            'unit'=>$qq['summarys'][0]['unit'],//单位
            'number'=>$qq['summarys'][0]['amount'],//数量
            'unitprice'=>$qq['summarys'][0]['priceUnit'],//单价
            'unittotal'=>$qq['summarys'][0]['priceSum'],//单品金额总和
            //'taxa'=>($qq['summarys'][0]['taxRate'] * 100)."%",//税率
            'taxa'=>$ta,//税率
            'unittax'=>$qq['summarys'][0]['taxSum'],//单品税额
            'whole'=>json_encode($qq)//返回的完整的值
        ];
        return $arr;
    }

    //增值税普通发票（卷票)11
    public function jsfp($qq,$sqje){
        $qq['zfbz']=$qq['zfbz']?$qq['zfbz']:0;
        $ta='';
        if(intval($qq['summarys'][0]['taxRate'])>=1){
            $ta=$qq['summarys'][0]['taxRate']."%";
        }
        $qq['kprq_jp']=date('Y-m-d',strtotime( str_replace( array('年','月','日') , array('-','-','') , $qq['kprq_jp'])));
        $arr = [
            'typeid' => 11,
            'invoicetype' => $qq['typeName'],//发票种类
            'opendate' => $qq['kprq_jp'],//开票时间
            'billcode' => $qq['fphm_jp'],//发票号码
            'invoicecode' => $qq['fpdm_jp'],//发票代码
            'pretax' => $qq['je_jp'],//税前金额
            'invoicename' => $qq['fpcc_jp'],//发票名称
            'taxamount' => $qq['se_jp'],//税额
            'amount' => $qq['jshj_jp'],//金额
            'checkcode' => $qq['jym_jp'],//校验码
            'salename' => $qq['fpmx_xfmc'],//销售方名称
            'saleduty' => $qq['xfsbh_jp'],//销售方纳税人识别号
            'saleplace' => $qq['xfdzdh_jp'],//销售方地址及电话
            'salebank' => $qq['xfyhzh_jp'],//销售方开户行及账号
            'buyname' => $qq['fpmx_gfmc'],//购方名称
            'buyduty' => $qq['gfsbh_jp'],//购方纳税人识别号
            'buyplace' => $qq['gfdzdh_jp'],//购方地址及电话
            'buybank' => $qq['gfyhzh_jp'],//购方开户行及账号
            'checkbill' => 1,//已验证
            'checktime' => date('Y-m-d H:i:s', time()),//验证时间
            //'remarks' => $qq['bz_jp'] . '机器编码：' . $qq['fphmjp'],//备注
            'remarks' => $qq['remarks'],//备注
            'checkstate'=>$qq['zfbz'],//0代表正常 Y或者2代表作废 3是红冲

            'consumetype'=>$qq['summarys'][0]['name'],//消费类型名称
            'specif'=>$qq['summarys'][0]['type'],//规格型号
            'unit'=>$qq['summarys'][0]['unit'],//单位
            'number'=>$qq['summarys'][0]['amount'],//数量
            'unitprice'=>$qq['summarys'][0]['priceUnit'],//单价
            'unittotal'=>$qq['summarys'][0]['priceSum'],//单品金额总和
            //'taxa'=>($qq['summarys'][0]['taxRate'] * 100)."%",//税率
            'taxa'=>$ta,//税率
            'unittax'=>$qq['summarys'][0]['taxSum'],//单品税额
            'whole'=>json_encode($qq)//返回的完整的值
        ];
        return $arr;
    }

    //通行费xxx增值税电子普通发票14
    public function abc($qq,$ktime){
        $qq['zfbz']=$qq['zfbz']?$qq['zfbz']:0;
        $ta='';
        if(intval($qq['summarys'][0]['taxRate'])>=1){
            $ta=$qq['summarys'][0]['taxRate']."%";
        }
        $qq['kprq_pp']=date('Y-m-d',strtotime($ktime));
        $arr = [
            'typeid' => 14,
            'invoicetype' => $qq['typeName'],//发票种类
            'opendate' => $qq['kprq_pp'],//开票时间
            'billcode' => $qq['fphm_pp'],//发票号码
            'invoicecode' => $qq['fpdm_pp'],//发票代码
            'pretax' => $qq['je_pp'],//税前金额
            'invoicename' => $qq['fpcc_pp'],//发票名称
            'taxamount' => $qq['se_pp'],//税额
            'amount' => $qq['jshjxx_pp'],//金额
            'checkcode' => $qq['jym_pp'],//校验码
            'salename' => $qq['fpmx_xfmc'],//销售方名称
            'saleduty' => $qq['xfsbh_pp'],//销售方纳税人识别号(为获取)
            'saleplace' => $qq['xfdzdh_pp'],//销售方地址及电话
            'salebank' => $qq['xfyhzh_pp'],//销售方开户行及账号
            'buyname' => $qq['fpmx_gfmc'],//购方名称
            'buyduty' => $qq['gfsbh_pp'],//购方纳税人识别号
            'buyplace' => $qq['gfdzdh_pp'],//购方地址及电话
            'buybank' => $qq['gfyhzh_pp'],//购方开户行及账号
            'checkbill' => 1,//已验证
            'checktime' => date('Y-m-d H:i:s', time()),//验证时间
            'remarks' => $qq['remarks'],//备注
            'checkstate'=>$qq['zfbz'],//0代表正常 Y或者2代表作废 3是红冲

            'consumetype'=>$qq['summarys'][0]['name'],//消费类型名称
            'specif'=>$qq['summarys'][0]['type'],//规格型号
            'unit'=>$qq['summarys'][0]['unit'],//单位
            'number'=>$qq['summarys'][0]['amount'],//数量
            'unitprice'=>$qq['summarys'][0]['priceUnit'],//单价
            'unittotal'=>$qq['summarys'][0]['priceSum'],//单品金额总和
            //'taxa'=>($qq['summarys'][0]['taxRate'] * 100)."%",//税率
            'taxa'=>$ta,//税率
            'unittax'=>$qq['summarys'][0]['taxSum'],//单品税额
            'whole'=>json_encode($qq)//返回的完整的值

        ];
        return $arr;
    }

    //二手车销售统一发票15
    public function escfp($qq){
        $qq['zfbz']=$qq['zfbz']?$qq['zfbz']:0;
        $ta='';
        if(intval($qq['summarys'][0]['taxRate'])>=1){
            $ta=$qq['summarys'][0]['taxRate']."%";
        }
        $qq['kprq_escfp']=date('Y-m-d',strtotime( str_replace( array('年','月','日') , array('-','-','') , $qq['kprq_escfp'])));
        $arr = [
            'typeid' => 15,
            'invoicetype' => $qq['typeName'],//发票种类
            'opendate' => $qq['kprq_escfp'],//开票时间
            'billcode' => $qq['fphm_escfp'],//发票号码
            'invoicecode' => $qq['fpdm_escfp'],//发票代码
            'pretax' => '0.00',//税前金额
            'invoicename' => $qq['fpcc_escfp'],//发票名称
            'taxamount' => '0.00',//税额
            'amount' => $qq['cjhjxx_escfp'],//金额
            'checkcode' => $qq['jqbm_escfp'],//校验码
            'salename' => $qq['xfmc_escfp'],//销售方名称
            'saleduty' => $qq['xfdm_escfp'],//销售方纳税人识别号
            'saleplace' => $qq['xfdz_escfp'].$qq['xfdh_escfp'],//销售方地址及电话
            'salebank' => $qq['escscyh_escfp'],//销售方开户行及账号
            'buyname' => $qq['mfmc_escfp'],//购方名称
            'buyduty' => $qq['mfdm_escfp'],//购方纳税人识别号
            'buyplace' => $qq['mfdz_escfp'].$qq['mfdh_escfp'],//购方地址及电话
            'buybank' => '',//购方开户行及账号
            'checkbill' => 1,//已验证
            'checktime' => date('Y-m-d H:i:s', time()),//验证时间
            //'remarks' => $qq['bz_escfp'] . '机器编码：' . $qq['jqbm_escfp'],//备注
            'remarks' => $qq['bz_escfp'],//备注
            'checkstate'=>$qq['zfbz']//0代表正常 Y或者2代表作废 3是红冲
        ];
        return $arr;
    }

    //错误信息处理
    /*
    * message.put("002","超过该张发票当日查验次数(请于次日再次查验)!");
    * message.put("003","发票查验请求太频繁，请稍后再试！");
    * message.put("004","超过服务器最大请求数，请稍后访问!");
    * message.put("005","请求不合法!");
    * message.put("006","不一致");
    * message.put("007","验证码失效!");
    * message.put("008","验证码错误!");
    * message.put("009","查无此票");
    * message.put("010","查无此票");
    * message.put("010_","系统异常，请重试！(05)");
    * message.put("015","系统异常，请重试！(015)");
    * message.put("016","服务器接收的请求太频繁，请稍后再试！");
    * message.put("020","由于查验行为异常，涉嫌违规，当前无法使用查验服务！");
    * message.put("rqerr","当日开具发票可于次日进行查验！");
     */
    public function untrue($qq){
        if($qq['key1']=='002'||$qq['key1']=='003'||$qq['key1']=='004'||$qq['key1']=='005'||$qq['key1']=='006'||$qq['key1']=='007'||$qq['key1']=='008'||$qq['key1']=='009'||$qq['key1']=='010'||$qq['key1']=='010_'||$qq['key1']=='015'||$qq['key1']=='016'||$qq['key1']=='020'||$qq['key1']=='rqerr'){
            $fds=$qq['message'];
        }else{
            $fds='';
        }
        return $fds;
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
        $where=" `uid`=".$res['id']." and `state`=0 ";
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