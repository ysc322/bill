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
class IosmerpayController extends BaseController
{
	/**
      * 21000 App Store不能读取你提供的JSON对象
      * 21002 receipt-data域的数据有问题
      * 21003 receipt无法通过验证
      * 21004 提供的shared secret不匹配你账号中的shared secret
      * 21005 receipt服务器当前不可用
      * 21006 receipt合法，但是订阅已过期。服务器接收到这个状态码时，receipt数据仍然会解码并一起发送
      * 21007 receipt是Sandbox receipt，但却发送至生产系统的验证服务
      * 21008 receipt是生产receipt，但却发送至Sandbox环境的验证服务
     */
    public function acurl($receipt_data, $sandbox=0){

        //小票信息
        //$POSTFIELDS = array("receipt-data" => $receipt_data);
        //$POSTFIELDS = json_encode($POSTFIELDS);
        $POSTFIELDS=$receipt_data;
//print_r($POSTFIELDS);exit;
        //正式购买地址 沙盒购买地址
        $url_buy     = "https://buy.itunes.apple.com/verifyReceipt";    
        $url_sandbox = "https://sandbox.itunes.apple.com/verifyReceipt";
        $url = $sandbox ? $url_sandbox : $url_buy;
        //简单的curl
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $POSTFIELDS);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        curl_close($ch);
        //print_r($result);exit;
        return $result;
    }


    
    


    //ios充值验证
    public function shaxiang(){
        $info=$this->chektoken();//检测用户token
        if(!$info){
            return json_encode(['code' =>2, 'msg'=>'请登录' ]);
        }
        $request = Request();
        $res = $request->param();
        $receipt    = new ReceiptModel();
        $product    = new ProductModel();
        $User       = new UserModel();
        $receipt_data = $res['receipt_data'];//64位编码
        $product_id = $res['product_id'];//订单id
        $transaction_id = $res['transaction_id'];//订单号
        $assss['test']=json_encode($res);
        Db::table('fp_test')->insert($assss);
        //查询此此订单是否已使用
        $ss=$receipt->getInfo(['outtradeno'=>$transaction_id]);
        if($ss){
            $assss['test']="购买失败此订单已支付完成或过期";
            Db::table('fp_test')->insert($assss);
            return $this->json_error('购买失败此订单已支付完成或过期');
        }else{
            $rqq=$product->getInfo(['appleid'=>$product_id]);
            $arr=[
                'uid'=>$info['id'],
                'productid'=>$rqq['id'],//商品id
                'money'=>$rqq['rulprice'],//价格
                'pattern'=>'ios内购',
                'source'=>'ios',//来源
                'outtradeno'=>$transaction_id,//订单号
                'receipt'=>$receipt_data,
                'ctime'=>date('Y-m-d H:i:s',time()),
                'type'=>0
            ];
            //写入订单表
            $receipt->getAdd($arr);
        }
        //验证参数
        if (strlen($receipt_data)<20){
            return $this->json_error('参数非法');
        }
        //请求验证
        $html = $this->acurl($receipt_data,$sandbox = false);
        $data = json_decode($html,1);

        if($data['status']=='21005'){
            return $this->json_error('receipt服务器当前不可用');
        }
        //如果是沙盒数据 则验证沙盒模式
        if($data['status']=='21007'){
            //请求验证
            $html = $this->acurl($receipt_data, $sandbox=1);
            $data = json_decode($html,1);
            $data['sandbox'] = '1';
        }
        if (isset($_GET['debug'])) {
            exit(json_encode($data));
        }
        if($data['status']==0){
            $receipt->updatedatas(['state'=>1,'etime'=>date('Y-m-d H:i:s',time()),'comment'=>json_encode($data),'checkproof'=>1],['outtradeno'=>$transaction_id]);
            if($rqq){
                //获取支付的订单的商品id，给用户添加会员周期
                if($info['member']==0){
                    //不是会员，修改会员时间和vip状态
                    $start_time=time();
                }else{
                    $start_time=strtotime($info['metotime']);
                }
                $time=date('Y-m-d H:i:s',strtotime("+1 month",$start_time ));
                $num=$info['frequency']+$rqq['frequency'];
                $zong=$info['totalnum']+$rqq['frequency'];
                $User->updatedatas(['member'=>1,'metotime'=>$time,'frequency' => $num, 'totalnum' => $zong],['id'=>$info['id']]);//修改用户vip信息
            }
            $assss['test']=$transaction_id."购买成功";
            Db::table('fp_test')->insert($assss);
            return $this->json_success('购买成功');
        }else{
            return $this->json_error('购买失败3');
        }
    }


    //会员商品列表
    public function xgoodlist(){
        $product    = new ProductModel();
        $where=['state'=>0];
        $res=$product->getList($where);
        return json_encode(['code'=>0,'msg'=>'ok','result'=>$res]);
    }






















	
	
}
?>