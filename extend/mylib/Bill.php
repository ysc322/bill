<?php

/**
 * Created by PhpStorm.
 * User: liuzhengbin
 * Date: 2018/5/14
 * Time: 18:31
 */
namespace mylib;
use Curl\Curl;
use think\facade\Request;
use think\Exception;
use think\Db;

class Bill {

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
            'invoicetype' => $qq['invoicetype'],//发票类型
            'opendate' => $qq['kprq_zp'],//开票时间
            'billcode' => $qq['fphm_zp'],//发票号码
            'invoicecode' => $qq['fpdm_zp'],//发票代码
            'pretax' => $qq['je_zp'],//税前金额
            'invoicename' => $qq['fpcc_zp'],//发票名称
            'taxamount' => $qq['se_zp'],//税额
            'amount' => $qq['jshjxx_zp'],//金额
            'checkcode' => $qq['jym_zp'],//校验码
            'salename' => $qq['xfmc_zp'],//销售方名称
            'saleduty' => $qq['xfsbh_zp'],//销售方纳税人识别号
            'saleplace' => $qq['xfdzdh_zp'],//销售方地址及电话
            'salebank' => $qq['xfyhzh_zp'],//销售方开户行及账号
            'buyname' => $qq['gfmc_zp'],//购方名称
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
            'invoicetype' => $qq['invoicetype'],//发票种类
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
            'remarks' => $qq['bz_dzfp'],//备注
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
            'invoicetype' => $qq['invoicetype'],//发票种类
            'opendate' => $qq['kprq_jdcfp'],//开票时间
            'billcode' => $qq['fphm_jdcfp'],//发票号码
            'invoicecode' => $qq['fpdm_jdcfp'],//发票代码
            'pretax' => $qq['cjfy_jdcfp'],//税前金额
            'invoicename' => $qq['fpcc_jdcfp'],//发票名称
            'taxamount' => $qq['zzsse_jdcfp'],//税额
            'amount' => $qq['jshjxx_jdcfp'],//金额
            'checkcode' => $qq['jqbm_jdcfp'],//校验码
            'salename' => $qq['xhdwmc_jdcfp'],//销售方名称
            'saleduty' => $qq['nsrsbh_jdcfp'],//销售方纳税人识别号
            'saleplace' => $qq['dz_jdcfp'].$qq['dh_jdcfp'],//销售方地址及电话
            'salebank' => $qq['khyh_jdcfp'].$qq['zh_jdcfp'],//销售方开户行及账号
            'buyname' => $qq['ghdw_jdcfp'],//购方名称
            'buyduty' => $qq['sfzhm_jdcfp'],//购方纳税人识别号
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
            'invoicetype' => $qq['invoicetype'],//发票种类
            'opendate' => $qq['kprq_pp'],//开票时间
            'billcode' => $qq['fphm_pp'],//发票号码
            'invoicecode' => $qq['fpdm_pp'],//发票代码
            'pretax' => $qq['je_pp'],//税前金额
            'invoicename' => $qq['fpcc_pp'],//发票名称
            'taxamount' => $qq['se_pp'],//税额
            'amount' => $qq['jshjxx_pp'],//金额
            'checkcode' => $qq['jym_pp'],//校验码
            'salename' => $qq['xfmc_pp'],//销售方名称
            'saleduty' => $qq['xfsbh_pp'],//销售方纳税人识别号
            'saleplace' => $qq['xfdzdh_pp'],//销售方地址及电话
            'salebank' => $qq['xfyhzh_pp'],//销售方开户行及账号
            'buyname' => $qq['gfmc_pp'],//购方名称
            'buyduty' => $qq['gfsbh_pp'],//购方纳税人识别号
            'buyplace' => $qq['gfdzdh_pp'],//购方地址及电话
            'buybank' => $qq['gfyhzh_pp'],//购方开户行及账号
            'checkbill' => 1,//已验证
            'checktime' => date('Y-m-d H:i:s', time()),//验证时间
            //'remarks' => $qq['bz_pp'] . '机器编码：' . $qq['jqbh_pp'],//备注
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
            'invoicetype' => $qq['invoicetype'],//发票种类
            'opendate' => $qq['kprq_jp'],//开票时间
            'billcode' => $qq['fphm_jp'],//发票号码
            'invoicecode' => $qq['fpdm_jp'],//发票代码
            'pretax' => $qq['je_jp'],//税前金额
            'invoicename' => $qq['fpcc_jp'],//发票名称
            'taxamount' => $qq['se_jp'],//税额
            'amount' => $qq['jshj_jp'],//金额
            'checkcode' => $qq['jym_jp'],//校验码
            'salename' => $qq['xfmc_jp'],//销售方名称
            'saleduty' => $qq['xfsh_jp'],//销售方纳税人识别号
            'saleplace' => $qq['xfdzdh_jp'],//销售方地址及电话
            'salebank' => $qq['xfyhzh_jp'],//销售方开户行及账号
            'buyname' => $qq['gfmc_jp'],//购方名称
            'buyduty' => $qq['gfsh_jp'],//购方纳税人识别号
            'buyplace' => $qq['gfdzdh_jp'],//购方地址及电话
            'buybank' => $qq['gfyhzh_jp'],//购方开户行及账号
            'checkbill' => 1,//已验证
            'checktime' => date('Y-m-d H:i:s', time()),//验证时间
            //'remarks' => $qq['bz_jp'] . '机器编码：' . $qq['fphmjp'],//备注
            'remarks' => $qq['bz_jp'],//备注
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
            'invoicetype' => $qq['invoicetype'],//发票种类
            'opendate' => $qq['kprq_pp'],//开票时间
            'billcode' => $qq['fphm_pp'],//发票号码
            'invoicecode' => $qq['fpdm_pp'],//发票代码
            'pretax' => $qq['je_pp'],//税前金额
            'invoicename' => $qq['fpcc_pp'],//发票名称
            'taxamount' => $qq['se_pp'],//税额
            'amount' => $qq['jshjxx_pp'],//金额
            'checkcode' => $qq['jym_pp'],//校验码
            'salename' => $qq['xfmc_pp'],//销售方名称
            'saleduty' => $qq['xfsbh_pp'],//销售方纳税人识别号(为获取)
            'saleplace' => $qq['xfdzdh_pp'],//销售方地址及电话
            'salebank' => $qq['xfyhzh_pp'],//销售方开户行及账号
            'buyname' => $qq['gfmc_pp'],//购方名称
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
            'taxa'=>$ta,//税率
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



    //ip代理生成
    function zmkipdl(){
        return '';
        $curl = new Curl();
        $url = 'http://www.appgodlike.com//api/mkipdl';
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

        $ipp = $this->zmkipdl();
        Db::table('fp_test')->insert(['test'=>'cd /home/wwwroot/caiji/fapiao/html/ && python fp1.py '.$fpdm.' '.$fphm.' '.$date.' '.$jym.' '.$ipp." 2>&1"]);
        $res = exec('cd /home/wwwroot/caiji/fapiao/html/ && python fp1.py '.$fpdm.' '.$fphm.' '.$date.' '.$jym.' '.$ipp." 2>&1",$out,$ress);
        Db::table('fp_test')->insert(['test'=>$res]);
        $res = str_replace("'", '"', $res);
        if($res){
            return $res;
        }
    }

}