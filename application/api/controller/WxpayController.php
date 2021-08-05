<?php
namespace app\api\controller;
use app\api\controller\base\BaseController;
use think\facade\Log;
use think\facade\Request;
use think\Exception;
use think\Db;
use think\facade\Cache;
use app\common\model\UserModel;
use app\common\model\BillinfoModel;
use app\common\model\ReceiptModel;
use app\common\model\ProductModel;
use app\common\model\ProductHaveModel;
use GuzzleHttp\json_encode;

use mylib\wxPay;
class WxpayController extends BaseController
{

    //生成不同的订单号
    public function oustr($tr){
        $receipt    = new ReceiptModel();
        if($receipt->getInfo(['outtradeno'=>$tr])){
            $tr=date('YmdHis',time()).time().rand(1000000,9999999).rand(10000,99999);
        }
        return $tr;
    }




    //微信支付
	public function wxpay()
    {
        $info=$this->chektoken();//检测用户token
        if(!$info){
            return json_encode(['code' =>2, 'msg'=>'请登录' ]);
        }
        $request = Request();
        $res = $request->param();
        $receipt    = new ReceiptModel();
        $product    = new ProductModel();
        //$User       = new UserModel();
        //获取支付金额等信息
        $duix=$product->getInfo(['id'=>$res['productid']]);
        if (!$duix) {
            return $this->json_error('请选择需要充值的金额');
        }
        //随机订单号orunm
        $oru=$res['id'].date('YmdHis',time());
        $oruaa=time().rand(1000,9999).rand(1000,9999);
        $orun=$oru.$oruaa;
        $orunm=substr($orun,0,32);
        $tradeNo=$this->oustr($orunm);
//print_r($tradeNo);exit;
        $wxPay     = new wxPay();
        $orderBody="发票管家";
        $money=$duix['rulprice']*100;
        $attach="发票管家";
        //$response  = $wxPay->getPayOrder($orderBody, $tradeNo, $money,$attach);
        $response  = $wxPay->getPrePayOrder($orderBody, $tradeNo, $money,$attach);
        
        //print_r($response);exit;
        if($response['result_code']=='SUCCESS'){
            $arr=[
                'uid'=>$info['id'],
                'productid'=>$res['productid'],//商品id
                'money'=>$duix['rulprice'],//价格
                'source'=>$res['source'],//来源
                'pattern'=>'微信',//支付方式
                'outtradeno'=>$tradeNo,//订单号
                'ctime'=>date('Y-m-d H:i:s',time()),//
                'state'=>0
            ];
            $receipt->getAdd($arr);
        }
        $x = $wxPay->getOrder($response['prepay_id']);
        print_r(json_encode($x));
    }




	
    /**
     *  支付回调
     */
    public function jnotify(){
        $receipt    = new ReceiptModel();
        $product    = new ProductModel();
        $User       = new UserModel();
        //存储微信的回调
        $xml = file_get_contents('php://input');
        $ssss     = new wxPay();
        $res=$ssss->xmlstr_to_array($xml);
        $timetxt = date(" Y-m-d H:i:s ").":". json_encode($res)."\r\n";
        // 写日志
        $bruce=fopen("/home/wwwroot/fapiao/public/uploads/file/" .date("y-m-d") . ".txt","ab+"); //打开日志文件
        if(!$bruce)
        {
           echo'文件不存在';
           exit;
        }
        fwrite($bruce,$timetxt);
        fclose($bruce);
        $aa=$ssss->verifyNotify($res);
        //验证签名，并回应微信。
        //对后台通知交互时，如果微信收到商户的应答不是成功或超时，微信认为通知失败，
        //微信会通过一定的策略（如30分钟共8次）定期重新发起通知，
        //尽可能提高通知的成功率，但微信不保证通知最终能成功。
       if($aa == FALSE){
            return $this->json_error('FAIL签名失败');
        }else{
            $order_id         = $res['out_trade_no'];
            //$sign           =$res['sign'];
            $ress=$receipt->getInfo(['outtradeno'=>$order_id]);
            if($ress && $ress['state']==0 && $aa['return_code']=='SUCCESS' && $aa['result_code']=='SUCCESS'){//支付为完成时走正常程序
                $receipt->updatedatas(['state'=>1,'comment'=>json_encode($res),'etime'=>date('Y-m-d H:i:s',time())],['outtradeno'=>$order_id]);
                //获取
                $dsa=$product->getInfo(['id'=>$ress['productid']]);
                $info=$User->getOne(['id'=>$ress['uid']]);
                if($info['uvip']==0){
                    //不是会员，修改会员时间和vip状态
                    $start_time=time();
                }else{
                    $start_time=strtotime($info['totime']);
                }
                $time=date('Y-m-d H:i:s',strtotime("+".$dsa['number']." month",$start_time ));
                $User->updatedatas(['uvip'=>1,'totime'=>$time],['id'=>$ress['uid']]);//修改用户vip信息
                //return json_encode(['return_code'=>'SUCCESS','return_msg'=>'OK']);
                $str='<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
                echo $str;exit;
            }else{
                $str='<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[参数格式校验错误]]></return_msg></xml>';
                echo $str;exit;
            }
        }
    }

    //签约回调地址
    public function qian(){
            $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
            $ssss     = new wxPay();
            $res=$ssss->xmlstr_to_array($xml);
            $data['test']=json_encode($res);
            Db::table('fp_test')->insertGetId($data);
    }



    //购买交易记录
    public function orderlist(){
        $info=$this->chektoken();//检测用户token
        if(!$info){
            return json_encode(['code' =>2, 'msg'=>'请登录' ]);
        }
        $receipt    = new ReceiptModel();
        $res=$receipt->getList(['uid'=>$info['id'],'state'=>1,'type'=>1]);
        if($res){
            for($i=0;$i<count($res);$i++){ 
                $qq=Db::table('fp_product_number')->where(['id'=>$res[$i]['productid']])->find();
                $arr[$i]['title']=$qq['title'];
                $arr[$i]['etime']=$res[$i]['etime'];
                $arr[$i]['money']=$res[$i]['money'];
                $arr[$i]['state']="成功";
            }
        }else{
            $arr=[];
        }
        return json_encode(['code'=>0,'msg'=>'ok','result'=>$arr]);
    }





    //查询订单信息
    public function cha(){
        $ssss     = new wxPay();
        $request = Request();
        $res = $request->param();
        $aa=$ssss->searchOrder($res['out_trade_no']);
        print_r($aa);exit;
    }


    public function aq(){
        $ssss     = new wxPay();
        print_r($ssss);exit;
    }


    //购买次数接口
    public function wxpaycs()
    {
        $info=$this->chektoken();//检测用户token
        if(!$info){
            return json_encode(['code' =>2, 'msg'=>'请登录' ]);
        }
        $request = Request();
        $res = $request->param();
        $receipt    = new ReceiptModel();
        $product    = new ProductHaveModel();
        //$User       = new UserModel();
        //获取支付金额等信息
        $duix=$product->getInfo(['id'=>$res['productid']]);
        if (!$duix) {
            return $this->json_error('请选择需要充值的次数');
        }
        //随机订单号orunm
        $oru=$res['id'].date('YmdHis',time());
        $oruaa=time().rand(1000,9999).rand(1000,9999);
        $orun=$oru.$oruaa;
        $orunm=substr($orun,0,32);
        $tradeNo=$this->oustr($orunm);
//print_r($tradeNo);exit;
        $wxPay     = new wxPay();
        $orderBody="发票全能王";
        $money=$duix['rulprice']*100;
        $attach="发票全能王";
        //$response  = $wxPay->getPayOrder($orderBody, $tradeNo, $money,$attach);
        $response  = $wxPay->getPrePayOrder($orderBody, $tradeNo, $money,$attach);

        //print_r($response);exit;
        if($response['result_code']=='SUCCESS'){
            $arr=[
                'uid'=>$info['id'],
                'productid'=>$res['productid'],//商品id
                'money'=>$duix['rulprice'],//价格
                'source'=>$res['source'],//来源
                'pattern'=>'微信',//支付方式
                'outtradeno'=>$tradeNo,//订单号
                'ctime'=>date('Y-m-d H:i:s',time()),//
                'state'=>0,
                'type'=>1
            ];
            $receipt->getAdd($arr);
        }
        $x = $wxPay->getOrder($response['prepay_id']);
        print_r(json_encode($x));
    }

    //新的回调接口
    /**
     *  支付回调
     */
    public function notify(){
        $receipt    = new ReceiptModel();
        $producth    = new ProductHaveModel();
        $product    = new ProductModel();
        $User       = new UserModel();
        //存储微信的回调
        $xml = file_get_contents('php://input');
        $ssss     = new wxPay();
        $res=$ssss->xmlstr_to_array($xml);
        $aa=$ssss->verifyNotify($res);
        //验证签名，并回应微信。
        //对后台通知交互时，如果微信收到商户的应答不是成功或超时，微信认为通知失败，
        //微信会通过一定的策略（如30分钟共8次）定期重新发起通知，
        //尽可能提高通知的成功率，但微信不保证通知最终能成功。
        if($aa == FALSE){
            return $this->json_error('FAIL签名失败');
        }else{
            $order_id         = $res['out_trade_no'];
            $ress=$receipt->getInfo(['outtradeno'=>$order_id]);
            if($ress && $ress['state']==0 && $aa['return_code']=='SUCCESS' && $aa['result_code']=='SUCCESS'){//支付为完成时走正常程序
                $receipt->updatedatas(['state'=>1,'comment'=>json_encode($res),'etime'=>date('Y-m-d H:i:s',time())],['outtradeno'=>$order_id]);
                //获取
                $info=$User->getOne(['id'=>$ress['uid']]);
                if($ress['type']==1){
                    $dsa=$producth->getInfo(['id'=>$ress['productid']]);//冲次数
                    $num=$info['frequency']+$dsa['number'];
                    $zong=$info['totalnum']+$dsa['number'];
                    $User->updatedatas(['frequency' => $num, 'totalnum' => $zong], ['id' => $ress['uid']]);//修改用户次数
                }else {
                    $dsa=$product->getInfo(['id'=>$ress['productid']]);//送次数
                    if ($info['uvip'] == 0) {
                        //不是会员，修改会员时间和vip状态
                        $start_time = time();
                    } else {
                        $start_time = strtotime($info['totime']);
                    }
                    $time = date('Y-m-d H:i:s', strtotime("+" . $dsa['number'] . " month", $start_time));
                    $num=$info['frequency']+$dsa['frequency'];
                    $zong=$info['totalnum']+$dsa['frequency'];
                    $User->updatedatas(['uvip' => 1, 'totime' => $time,'frequency' => $num, 'totalnum' => $zong], ['id' => $ress['uid']]);//修改用户vip信息
                }
                $str = '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
                echo $str;exit;
            }else{
                $str='<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[参数格式校验错误]]></return_msg></xml>';
                echo $str;exit;
            }
        }
    }












	
	
}
?>