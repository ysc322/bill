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

use Alipay\aop\AopClient;
use Alipay\aop\request\AlipayTradeAppPayRequest;
class PayController extends BaseController
{
    //支付并签约
	public function appalipay()
    {
        $info=$this->chektoken();//检测用户token
        if(!$info){
            return json_encode(['code' =>2, 'msg'=>'请登录' ]);
        }
        $request = Request();
        $res = $request->param();
        $receipt    = new ReceiptModel();
        $product    = new ProductModel();
        $User       = new UserModel();
        //获取支付金额等信息
        $duix=$product->getInfo(['id'=>$res['productid']]);
        if (!$duix) {
            return $this->json_error('请选择需要充值的金额');
        }
        //随机订单号orunm
        $tradeNo=md5($res['id'].time());
        //生成订单信息
        $arq=[
            'uid'=>$info['id'],
            'productid'=>$res['productid'],//商品id
            'money'=>$duix['rulprice'],//价格
            'source'=>$res['source'],//来源
            'pattern'=>'支付宝',//支付方式
            'outtradeno'=>$tradeNo,//订单号
            'ctime'=>date('Y-m-d H:i:s',time()),//
            'state'=>0
        ];
        $receipt->getAdd($arq);
        //调用支付接口
        $total_amount=$duix['rulprice'];//现价格
        $aop = new AopClient();
        //print_r($aop);exit;
        $aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";     //网关地址要使用沙箱网关alipaydev
        $aop->appId = "2021001136637135";
        $aop->rsaPrivateKey = 'MIIEvAIBADANBgkqhkiG9w0BAQEFAASCBKYwggSiAgEAAoIBAQDH8TvhDbE/XseLe39v28AjEHRiddlXitdSPbOvT4GSUMkdajHVV//IWkLLxXMJ/28N+zzLNT/nk3TUNIor0LDQLztovzwruGVOIjiiWfM6b3Q9hQZANoB77Ew0f5PYlW/UCScTJ2qnh1FJcxlBkhE79pzZOBGliAsTfhCnvchhF4I/eCoyFKxAl7zgMSzcqgCO+nT5e7cvLI6JzMoOJW8q6syyFJTmusQrdRnU+2Blp2ltyB2GmB5XumfeB+hvAVtlQOjYhZNyBias7Iqic+UPkn/qI0ItUdK0HT26y8nNELyVy+9KiX6hkfqvcU53ThnKZMZYZUooboTOEHhgGKihAgMBAAECggEABQTEGQEL+ug5IAIoj7k3WGn4UeRdYTol73WHBhZ7zR1j/u4EdDNyQygo1w6MtaqIshQlUYBzqy9jVlQ/R5xEY+Vbm2R5cSt+t3habZDObyAPDeBB6Oi1Tub+fq7ZGi2eEfds2Kk7nxrhanOOJbwx3IxzIWyr3LjOdl3KszsrRWyTRCZ7DbYPe3sqASm0TMbYtUQuWMt5qA7NEAHrhE4GPQ1wS1f1EGIlWNbf1z4utNCnbyEtjbL1Ul26gI1Qui5YzNUgOCK7mcpp1bYFQcfiScYCn1gS8BhX9pI/dUdwLHXNijYhbtvV1rUgj8eGc4QsUYvN0Wm8dEBdxwaCeTnAhQKBgQDt4nnf/5fVYmgNFjs6iGUEsPj4KE2ITpRsLUQCnAgcv5JbfNsBlcq2Dswmg44K29HSShm8NQWPuCbmZh9RBeWTZJ2oorRvmGTf+3HOLO0DAz+jgF2XUlm9Imvd4dYFQkQC4ezJ5nIPeLehrBPJT6OaL8mjImOgVmn1GD94abUMYwKBgQDXKxPjxHHCjXkQsPNidWT3fgDs3pd4P5vZtxwSk6UYq9LdFq8B6UKTy2PnURy6sZ4mWKdFU1w9zfGtnydrcpcJpcPscb1C5K1RQZvjjSjgO+Xc+wQHROeUQnKnWiSyCfKKs8RvKHI1pnIxp7CWRFvvOrkduvpeqAcL/fsYl5RcKwKBgCEsKtisuMyd22qvxfSknomn8CAS5rTyWEo5iblfbtYrdMpjP9sZu1nl+FwKjl0/SCQuByaysiLXiD0q0oUm8Fu8dSvV/JlvQ+nkE3uv0iFQa6huNx7p8e+pBCe93W2ATyGjxbRl+VPk/p6B84RoIaVXsqOYIRJz2nS+O+Obwg6HAoGAXSmxuvTbrLlEqg1z1DWwOdi83dKjEtW3zVSTOxfibAQ5kLC5pcIxqXxvDUD5h3xQVZodEs89KSV6dwpqLwO2kd7MLhwxLj2FLaGStbvw7uYAaOXmoJ8dBfyfWAaXzN6xvYyIiPiiOsiOuSE6PFXs2HA8prrGnSVzp6WYitVWz3ECgYAUFmg3QmPRmY1CYAM0a/2HudJsk9JGaZgflqBWYOvzcttHJX1h7puq4dIAXJFBaeKT+3gL0qPYU1Fd9uh1pTPS1Gi3CkNeVJPZbmTRddJTO1agYdYe9SkR3YObi9XqzAER+gdno/HN5wEY0/UCjp8DX97wC6wuhkahIQ6uk3TrQw==';
        $aop->format = "json";
        $aop->postCharset = "UTF-8";
        $aop->signType = "RSA2";
        $aop->alipayrsaPublicKey = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAx/E74Q2xP17Hi3t/b9vAIxB0YnXZV4rXUj2zr0+BklDJHWox1Vf/yFpCy8VzCf9vDfs8yzU/55N01DSKK9Cw0C87aL88K7hlTiI4olnzOm90PYUGQDaAe+xMNH+T2JVv1AknEydqp4dRSXMZQZIRO/ac2TgRpYgLE34Qp73IYReCP3gqMhSsQJe84DEs3KoAjvp0+Xu3LyyOiczKDiVvKurMshSU5rrEK3UZ1PtgZadpbcgdhpgeV7pn3gfobwFbZUDo2IWTcgYmrOyKonPlD5J/6iNCLVHStB09usvJzRC8lcvvSol+oZH6r3FOd04ZymTGWGVKKG6EzhB4YBiooQIDAQAB';
        //实例化具体API对应的request类,类名称和接口名称对应,当前调用接口名称：alipay.trade.app.pay
        $request = new AlipayTradeAppPayRequest();
        //SDK已经封装掉了公共参数，这里只需要传入业务参数，沙箱环境的product_code只能是FAST_INSTANT_TRADE_PAY
        
        $arr['body']                = '发票管家';
        $arr['subject']             = $duix['title'];
        $arr['out_trade_no']        = $tradeNo;
        $arr['timeout_express']     = '30m';
        $arr['total_amount']        = floatval($total_amount);
        $arr['product_code']        = 'QUICK_MSECURITY_PAY';
        $arr['agreement_sign_params ']=[
            'personal_product_code'=>'CYCLE_PAY_AUTH_P',
            'sign_scene'=>'INDUSTRY|DIGITAL_MEDIA',
            'external_agreement_no'=>$tradeNo,//商户签约号，代扣协议中标示用户的唯一签约号（确保在商户系统中唯一）
            'access_params'=>[
                    'channel'=>'ALIPAYAPP'
                ],
            'period_rule_params'=>[
                    'period_type'=>'MONTH',//按月扣款
                    'period'=>$duix['number'],//一个月为单位
                    'execute_time'=>date('Y-m-d',time()),//首次付款的时间
                    'single_amount'=>'1000.00' //单次限额不能超过此额度
                ]
            ];//签约参数。如果希望在sdk中支付并签约，需要在这里传入签约信息

        $info = json_encode($arr,JSON_UNESCAPED_UNICODE);
        $request->setNotifyUrl("http://bill.ganbuguo.com/pay/notify");
        $request->setBizContent($info);
        //这里和普通的接口调用不同，使用的是sdkExecute
        $response = $aop->sdkExecute($request);
        //htmlspecialchars是为了输出到页面时防止被浏览器将关键参数html转义，实际打印到日志以及http传输不会有这个问题
        $aaa=htmlspecialchars($response);//就是orderString 可以直接给客户端请求，无需再做处理。
        //return json_encode($response);
        return json_encode(['code'=>0,'msg'=>'ok','result'=>$aaa]);

    }

    //正常下单接口
    public function alipay(){
        $info=$this->chektoken();//检测用户token
        if(!$info){
            return json_encode(['code' =>2, 'msg'=>'请登录' ]);
        }
        $request = Request();
        $res = $request->param();
        $receipt    = new ReceiptModel();
        $product    = new ProductModel();
        $User       = new UserModel();
        //获取支付金额等信息
        $duix=$product->getInfo(['id'=>$res['productid']]);
        if (!$duix) {
            return $this->json_error('请选择需要充值的金额');
        }
        //随机订单号orunm
        $tradeNo=md5($res['id'].time());
        //生成订单信息
        $arq=[
            'uid'=>$info['id'],
            'productid'=>$res['productid'],//商品id
            'money'=>$duix['rulprice'],//价格
            'source'=>$res['source'],//来源
            'pattern'=>'支付宝',//支付方式
            'outtradeno'=>$tradeNo,//订单号
            'ctime'=>date('Y-m-d H:i:s',time()),//
            'state'=>0
        ];
        $receipt->getAdd($arq);
        //调用支付接口
        $total_amount=$duix['rulprice'];//现价格
        $aop = new AopClient();
        //print_r($aop);exit;
        $aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";     //网关地址要使用沙箱网关alipaydev
        $aop->appId = "2021001136637135";
        $aop->rsaPrivateKey = 'MIIEvAIBADANBgkqhkiG9w0BAQEFAASCBKYwggSiAgEAAoIBAQDH8TvhDbE/XseLe39v28AjEHRiddlXitdSPbOvT4GSUMkdajHVV//IWkLLxXMJ/28N+zzLNT/nk3TUNIor0LDQLztovzwruGVOIjiiWfM6b3Q9hQZANoB77Ew0f5PYlW/UCScTJ2qnh1FJcxlBkhE79pzZOBGliAsTfhCnvchhF4I/eCoyFKxAl7zgMSzcqgCO+nT5e7cvLI6JzMoOJW8q6syyFJTmusQrdRnU+2Blp2ltyB2GmB5XumfeB+hvAVtlQOjYhZNyBias7Iqic+UPkn/qI0ItUdK0HT26y8nNELyVy+9KiX6hkfqvcU53ThnKZMZYZUooboTOEHhgGKihAgMBAAECggEABQTEGQEL+ug5IAIoj7k3WGn4UeRdYTol73WHBhZ7zR1j/u4EdDNyQygo1w6MtaqIshQlUYBzqy9jVlQ/R5xEY+Vbm2R5cSt+t3habZDObyAPDeBB6Oi1Tub+fq7ZGi2eEfds2Kk7nxrhanOOJbwx3IxzIWyr3LjOdl3KszsrRWyTRCZ7DbYPe3sqASm0TMbYtUQuWMt5qA7NEAHrhE4GPQ1wS1f1EGIlWNbf1z4utNCnbyEtjbL1Ul26gI1Qui5YzNUgOCK7mcpp1bYFQcfiScYCn1gS8BhX9pI/dUdwLHXNijYhbtvV1rUgj8eGc4QsUYvN0Wm8dEBdxwaCeTnAhQKBgQDt4nnf/5fVYmgNFjs6iGUEsPj4KE2ITpRsLUQCnAgcv5JbfNsBlcq2Dswmg44K29HSShm8NQWPuCbmZh9RBeWTZJ2oorRvmGTf+3HOLO0DAz+jgF2XUlm9Imvd4dYFQkQC4ezJ5nIPeLehrBPJT6OaL8mjImOgVmn1GD94abUMYwKBgQDXKxPjxHHCjXkQsPNidWT3fgDs3pd4P5vZtxwSk6UYq9LdFq8B6UKTy2PnURy6sZ4mWKdFU1w9zfGtnydrcpcJpcPscb1C5K1RQZvjjSjgO+Xc+wQHROeUQnKnWiSyCfKKs8RvKHI1pnIxp7CWRFvvOrkduvpeqAcL/fsYl5RcKwKBgCEsKtisuMyd22qvxfSknomn8CAS5rTyWEo5iblfbtYrdMpjP9sZu1nl+FwKjl0/SCQuByaysiLXiD0q0oUm8Fu8dSvV/JlvQ+nkE3uv0iFQa6huNx7p8e+pBCe93W2ATyGjxbRl+VPk/p6B84RoIaVXsqOYIRJz2nS+O+Obwg6HAoGAXSmxuvTbrLlEqg1z1DWwOdi83dKjEtW3zVSTOxfibAQ5kLC5pcIxqXxvDUD5h3xQVZodEs89KSV6dwpqLwO2kd7MLhwxLj2FLaGStbvw7uYAaOXmoJ8dBfyfWAaXzN6xvYyIiPiiOsiOuSE6PFXs2HA8prrGnSVzp6WYitVWz3ECgYAUFmg3QmPRmY1CYAM0a/2HudJsk9JGaZgflqBWYOvzcttHJX1h7puq4dIAXJFBaeKT+3gL0qPYU1Fd9uh1pTPS1Gi3CkNeVJPZbmTRddJTO1agYdYe9SkR3YObi9XqzAER+gdno/HN5wEY0/UCjp8DX97wC6wuhkahIQ6uk3TrQw==';
        $aop->format = "json";
        $aop->apiVersion = '1.0';
        $aop->postCharset = "UTF-8";
        $aop->signType = "RSA2";
        $aop->alipayrsaPublicKey = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAx/E74Q2xP17Hi3t/b9vAIxB0YnXZV4rXUj2zr0+BklDJHWox1Vf/yFpCy8VzCf9vDfs8yzU/55N01DSKK9Cw0C87aL88K7hlTiI4olnzOm90PYUGQDaAe+xMNH+T2JVv1AknEydqp4dRSXMZQZIRO/ac2TgRpYgLE34Qp73IYReCP3gqMhSsQJe84DEs3KoAjvp0+Xu3LyyOiczKDiVvKurMshSU5rrEK3UZ1PtgZadpbcgdhpgeV7pn3gfobwFbZUDo2IWTcgYmrOyKonPlD5J/6iNCLVHStB09usvJzRC8lcvvSol+oZH6r3FOd04ZymTGWGVKKG6EzhB4YBiooQIDAQAB';
        //实例化具体API对应的request类,类名称和接口名称对应,当前调用接口名称：alipay.trade.app.pay
        $request = new AlipayTradeAppPayRequest();
        $arr['body']                = '发票管家';
        $arr['subject']             = $duix['title'];//
        $arr['out_trade_no']        = $tradeNo;//
        $arr['timeout_express']     = '30m';
        $arr['total_amount']        = floatval($total_amount);//
        $arr['product_code']        = 'QUICK_MSECURITY_PAY';
        $info = json_encode($arr);
        $request->setNotifyUrl("http://bill.ganbuguo.com/pay/notify");
        $request->setBizContent($info);
        $result = $aop->sdkExecute ($request);
        //print_r($result);
        //$responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        //$resultCode = $result->$responseNode->code;
        //$aaa=htmlspecialchars($result);
        //if(!empty($resultCode)&&$resultCode == 10000){
        return json_encode(['code'=>0,'msg'=>'ok','result'=>$result]);
        //} else {
        //    return $this->json_error('购买失败');
        //}

    }

    //查询签约状态
    //https://docs.alipay.com/pre-open/api_pre/alipay.user.agreement.query
    public function qyue(){
        $aop = new AopClient ();
        $aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";     //网关地址要使用沙箱网关alipaydev
        $aop->appId = "2021001136637135";
        $aop->rsaPrivateKey = 'MIIEvAIBADANBgkqhkiG9w0BAQEFAASCBKYwggSiAgEAAoIBAQDH8TvhDbE/XseLe39v28AjEHRiddlXitdSPbOvT4GSUMkdajHVV//IWkLLxXMJ/28N+zzLNT/nk3TUNIor0LDQLztovzwruGVOIjiiWfM6b3Q9hQZANoB77Ew0f5PYlW/UCScTJ2qnh1FJcxlBkhE79pzZOBGliAsTfhCnvchhF4I/eCoyFKxAl7zgMSzcqgCO+nT5e7cvLI6JzMoOJW8q6syyFJTmusQrdRnU+2Blp2ltyB2GmB5XumfeB+hvAVtlQOjYhZNyBias7Iqic+UPkn/qI0ItUdK0HT26y8nNELyVy+9KiX6hkfqvcU53ThnKZMZYZUooboTOEHhgGKihAgMBAAECggEABQTEGQEL+ug5IAIoj7k3WGn4UeRdYTol73WHBhZ7zR1j/u4EdDNyQygo1w6MtaqIshQlUYBzqy9jVlQ/R5xEY+Vbm2R5cSt+t3habZDObyAPDeBB6Oi1Tub+fq7ZGi2eEfds2Kk7nxrhanOOJbwx3IxzIWyr3LjOdl3KszsrRWyTRCZ7DbYPe3sqASm0TMbYtUQuWMt5qA7NEAHrhE4GPQ1wS1f1EGIlWNbf1z4utNCnbyEtjbL1Ul26gI1Qui5YzNUgOCK7mcpp1bYFQcfiScYCn1gS8BhX9pI/dUdwLHXNijYhbtvV1rUgj8eGc4QsUYvN0Wm8dEBdxwaCeTnAhQKBgQDt4nnf/5fVYmgNFjs6iGUEsPj4KE2ITpRsLUQCnAgcv5JbfNsBlcq2Dswmg44K29HSShm8NQWPuCbmZh9RBeWTZJ2oorRvmGTf+3HOLO0DAz+jgF2XUlm9Imvd4dYFQkQC4ezJ5nIPeLehrBPJT6OaL8mjImOgVmn1GD94abUMYwKBgQDXKxPjxHHCjXkQsPNidWT3fgDs3pd4P5vZtxwSk6UYq9LdFq8B6UKTy2PnURy6sZ4mWKdFU1w9zfGtnydrcpcJpcPscb1C5K1RQZvjjSjgO+Xc+wQHROeUQnKnWiSyCfKKs8RvKHI1pnIxp7CWRFvvOrkduvpeqAcL/fsYl5RcKwKBgCEsKtisuMyd22qvxfSknomn8CAS5rTyWEo5iblfbtYrdMpjP9sZu1nl+FwKjl0/SCQuByaysiLXiD0q0oUm8Fu8dSvV/JlvQ+nkE3uv0iFQa6huNx7p8e+pBCe93W2ATyGjxbRl+VPk/p6B84RoIaVXsqOYIRJz2nS+O+Obwg6HAoGAXSmxuvTbrLlEqg1z1DWwOdi83dKjEtW3zVSTOxfibAQ5kLC5pcIxqXxvDUD5h3xQVZodEs89KSV6dwpqLwO2kd7MLhwxLj2FLaGStbvw7uYAaOXmoJ8dBfyfWAaXzN6xvYyIiPiiOsiOuSE6PFXs2HA8prrGnSVzp6WYitVWz3ECgYAUFmg3QmPRmY1CYAM0a/2HudJsk9JGaZgflqBWYOvzcttHJX1h7puq4dIAXJFBaeKT+3gL0qPYU1Fd9uh1pTPS1Gi3CkNeVJPZbmTRddJTO1agYdYe9SkR3YObi9XqzAER+gdno/HN5wEY0/UCjp8DX97wC6wuhkahIQ6uk3TrQw==';
        $aop->format = "json";
        $aop->postCharset = "UTF-8";//GBK
        $aop->signType = "RSA2";
        $aop->alipayrsaPublicKey = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAx/E74Q2xP17Hi3t/b9vAIxB0YnXZV4rXUj2zr0+BklDJHWox1Vf/yFpCy8VzCf9vDfs8yzU/55N01DSKK9Cw0C87aL88K7hlTiI4olnzOm90PYUGQDaAe+xMNH+T2JVv1AknEydqp4dRSXMZQZIRO/ac2TgRpYgLE34Qp73IYReCP3gqMhSsQJe84DEs3KoAjvp0+Xu3LyyOiczKDiVvKurMshSU5rrEK3UZ1PtgZadpbcgdhpgeV7pn3gfobwFbZUDo2IWTcgYmrOyKonPlD5J/6iNCLVHStB09usvJzRC8lcvvSol+oZH6r3FOd04ZymTGWGVKKG6EzhB4YBiooQIDAQAB';
        $aop->apiVersion = '1.0';
        $arr=[];
        $request = new AlipayUserAgreementQueryRequest ();
        $info = json_encode($arr,JSON_UNESCAPED_UNICODE);
        $request->setBizContent($info);
        /*$request->setBizContent("{" .
        "\"personal_product_code\":\"GENERAL_WITHHOLDING_P\"," .
        "\"alipay_user_id\":\"2088101122675263\"," .
        "\"alipay_logon_id\":\"abx@alitest.com\"," .
        "\"sign_scene\":\"INDUSTRY|MEDICAL\"," .
        "\"external_agreement_no\":\"test\"," .
        "\"third_party_type\":\"PARTNER\"," .
        "\"agreement_no\":\"20170322450983769228\"" .
        "  }");
        */
        $result = $aop->execute ( $request); 
       // $request->setNotifyUrl();//设置返回地址
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;//
        if(!empty($resultCode)&&$resultCode == 10000){
            /*
            $responseNode="alipay_user_agreement_query_response"{
                "alipay_user_agreement_query_response": {
                    "code": "10000",
                    "msg": "Success",
                    "valid_time": "2017-05-24 15:00:40",
                    "alipay_logon_id": "test***ali@alipay.net",
                    "invalid_time": "2117-05-24 00:00:00",
                    "pricipal_type": "CARD",
                    "device_id": "RSED235F875932",
                    "principal_id": "2088101122675263",
                    "sign_scene": "INDUSTRY|CARRENTAL",
                    "agreement_no": "20170322450983769228",
                    "third_party_type": "PARTNER",
                    "status": "NORMAL",
                    "sign_time": "2017-05-24 15:00:40",
                    "personal_product_code": "GENERAL_WITHHOLDING_P",
                    "external_agreement_no": "test",
                    "zm_open_id": "268816057852461313538942792",
                    "external_logon_id": "2088101118392209",
                    "credit_auth_mode": "DEDUCT_HUAZHI"
                },
                "sign": "ERITJKEIJKJHKKKKKKKHJEREEEEEEEEEEE"
            }
            记录签约数据用
            */
            echo "成功";
        }else{
            echo "失败";
        }
    }

    //统一收单支付调用接口
    //https://docs.alipay.com/pre-open/api_pre/alipay.trade.pay
    public function tyi(){
        $aop = new AopClient ();
        $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
        $aop->appId = 'your app_id';
        $aop->rsaPrivateKey = '请填写开发者私钥去头去尾去回车，一行字符串';
        $aop->alipayrsaPublicKey='请填写支付宝公钥，一行字符串';
        $aop->apiVersion = '1.0';
        $aop->signType = 'RSA2';
        $aop->postCharset='GBK';
        $aop->format='json';
        $request = new AlipayTradePayRequest ();
        $arr=[];
        $info = json_encode($arr,JSON_UNESCAPED_UNICODE);
        $request->setBizContent($info);
        /*$request->setBizContent("{" .
        "\"out_trade_no\":\"20150320010101001\"," .
        "\"scene\":\"bar_code\"," .
        "\"auth_code\":\"28763443825664394\"," .
        "\"product_code\":\"FACE_TO_FACE_PAYMENT\"," .
        "\"subject\":\"Iphone6 16G\"," .
        "\"buyer_id\":\"2088202954065786\"," .
        "\"seller_id\":\"2088102146225135\"," .
        "\"total_amount\":88.88," .
        "\"trans_currency\":\"USD\"," .
        "\"settle_currency\":\"USD\"," .
        "\"discountable_amount\":8.88," .
        "\"body\":\"Iphone6 16G\"," .
        "      \"goods_detail\":[{" .
        "        \"goods_id\":\"apple-01\"," .
        "\"goods_name\":\"ipad\"," .
        "\"quantity\":1," .
        "\"price\":2000," .
        "\"goods_category\":\"34543238\"," .
        "\"categories_tree\":\"124868003|126232002|126252004\"," .
        "\"body\":\"特价手机\"," .
        "\"show_url\":\"http://www.alipay.com/xxx.jpg\"" .
        "        }]," .
        "\"operator_id\":\"yx_001\"," .
        "\"store_id\":\"NJ_001\"," .
        "\"terminal_id\":\"NJ_T_001\"," .
        "\"extend_params\":{" .
        "\"sys_service_provider_id\":\"2088511833207846\"," .
        "\"industry_reflux_info\":\"{\\\\\\\"scene_code\\\\\\\":\\\\\\\"metro_tradeorder\\\\\\\",\\\\\\\"channel\\\\\\\":\\\\\\\"xxxx\\\\\\\",\\\\\\\"scene_data\\\\\\\":{\\\\\\\"asset_name\\\\\\\":\\\\\\\"ALIPAY\\\\\\\"}}\"," .
        "\"card_type\":\"S0JP0000\"" .
        "    }," .
        "\"timeout_express\":\"90m\"," .
        "\"auth_confirm_mode\":\"COMPLETE：转交易支付完成结束预授权;NOT_COMPLETE：转交易支付完成不结束预授权\"," .
        "\"terminal_params\":\"{\\\"key\\\":\\\"value\\\"}\"," .
        "\"promo_params\":{" .
        "\"actual_order_time\":\"2018-09-25 22:47:33\"" .
        "    }," .
        "\"advance_payment_type\":\"ENJOY_PAY_V2\"," .
        "      \"query_options\":[" .
        "        \"voucher_detail_list\"" .
        "      ]," .
        "\"is_async_pay\":ASYNC_PAY" .
        "  }");
        */
        $result = $aop->execute ( $request); 

        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        if(!empty($resultCode)&&$resultCode == 10000){
        echo "成功";
        } else {
        echo "失败";
        }
    }
	
    /**
     *  支付回调
     */
    public function jnotify(){
        $request = Request();
        $res = $request->param();
        $receipt    = new ReceiptModel();
        $product    = new ProductModel();
        $User       = new UserModel();
       /* $timetxt = date(" Y-m-d").":". json_encode($res) ."\r\n";
        // 写日志
        $bruce=fopen("/home/wwwroot/fapiao/public/uploads/file/" .date("m-d") . ".txt","ab+"); //打开日志文件
        if(!$bruce)
        {
            echo'文件不存在';
            exit;
        }
        fwrite($bruce,$timetxt);
        fclose($bruce);*/
        // 订单号
            $out_trade_no = $res['out_trade_no'];
            // 交易状态
            $trade_status =  $res['trade_status'];
            // 支付宝交易号
            $trade_no = $res['trade_no'];
            if ($trade_status == 'TRADE_FINISHED' || $trade_status == 'TRADE_SUCCESS') {
                    //获取订单信息
                    $ress=$receipt->getInfo(['outtradeno'=>$out_trade_no]);
                   // 这里进行逻辑判断，如果支付成功，可以改变支付状态，可按照具体情况具体分析
                   if ($ress && $ress['state']==0) {
                       $receipt->updatedatas(['state'=>1,'comment'=>json_encode($res),'etime'=>$res['gmt_payment']],['outtradeno'=>$out_trade_no]);
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
                       echo 'success';
                    }else{
                       echo '订单已完成或不存在';
                    }
            }else{
                echo 'fail';
            }
    }






    //生成订单
    public function orderlist(){
        $info=$this->chektoken();//检测用户token
        if(!$info){
            return json_encode(['code' =>2, 'msg'=>'请登录' ]);
        }
        $request = Request();
        $data = $request->param();
        $receipt    = new ReceiptModel();
        $product    = new ProductModel();
        //随机订单号orunm
        $oru=$info['id'].date('YmdHis',time());
        $oruaa=time().rand(1000,9999).rand(1000,9999);
        $orun=$oru.$oruaa;
        $orunm=substr($orun,0,32);
        $tradeNo=$this->oustr($orunm);
        //获取商品价格信息
        $rqq=$product->getInfo(['id'=>$data['productid']]);
        $arr=[
            'uid'=>$info['id'],
            'productid'=>$data['productid'],//商品id
            'money'=>$rqq['rulprice'],//价格
            'pattern'=>'ios内购',
            'source'=>$data['source'],//来源
            'outtradeno'=>$tradeNo,//订单号
            'ctime'=>date('Y-m-d H:i:s',time())
        ];
        //创建
        $id=$receipt->getAdd($arr);
        if($id){
            $res=[
                'product_id'=>$id,//订单id
                'transaction_id'=>$arr['outtradeno'] //订单号
            ];
            return json_encode(['code'=>0,'msg'=>'ok','result'=>$res]);
        }else {
            return $this->json_error('订单生成失败');
        }
    }


    //购买次数
    public function alipaycs(){
        $info=$this->chektoken();//检测用户token
        if(!$info){
            return json_encode(['code' =>2, 'msg'=>'请登录' ]);
        }
        $request = Request();
        $res = $request->param();
        $receipt    = new ReceiptModel();
        $product    = new ProductHaveModel();
        //获取支付金额等信息
        $duix=$product->getInfo(['id'=>$res['productid']]);
        if (!$duix) {
            return $this->json_error('请选择需要充值的次数');
        }
        //随机订单号orunm
        $tradeNo=md5($res['id'].time());
        //生成订单信息
        $arq=[
            'uid'=>$info['id'],
            'productid'=>$res['productid'],//商品id
            'money'=>$duix['rulprice'],//价格
            'source'=>$res['source'],//来源
            'pattern'=>'支付宝',//支付方式
            'outtradeno'=>$tradeNo,//订单号
            'ctime'=>date('Y-m-d H:i:s',time()),//
            'state'=>0,
            'type'=>1
        ];
        $receipt->getAdd($arq);
        //调用支付接口
        $total_amount=$duix['rulprice'];//现价格
        $aop = new AopClient();
        //print_r($aop);exit;
        $aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";     //网关地址要使用沙箱网关alipaydev
        $aop->appId = "2021001136637135";
        $aop->rsaPrivateKey = 'MIIEvAIBADANBgkqhkiG9w0BAQEFAASCBKYwggSiAgEAAoIBAQDH8TvhDbE/XseLe39v28AjEHRiddlXitdSPbOvT4GSUMkdajHVV//IWkLLxXMJ/28N+zzLNT/nk3TUNIor0LDQLztovzwruGVOIjiiWfM6b3Q9hQZANoB77Ew0f5PYlW/UCScTJ2qnh1FJcxlBkhE79pzZOBGliAsTfhCnvchhF4I/eCoyFKxAl7zgMSzcqgCO+nT5e7cvLI6JzMoOJW8q6syyFJTmusQrdRnU+2Blp2ltyB2GmB5XumfeB+hvAVtlQOjYhZNyBias7Iqic+UPkn/qI0ItUdK0HT26y8nNELyVy+9KiX6hkfqvcU53ThnKZMZYZUooboTOEHhgGKihAgMBAAECggEABQTEGQEL+ug5IAIoj7k3WGn4UeRdYTol73WHBhZ7zR1j/u4EdDNyQygo1w6MtaqIshQlUYBzqy9jVlQ/R5xEY+Vbm2R5cSt+t3habZDObyAPDeBB6Oi1Tub+fq7ZGi2eEfds2Kk7nxrhanOOJbwx3IxzIWyr3LjOdl3KszsrRWyTRCZ7DbYPe3sqASm0TMbYtUQuWMt5qA7NEAHrhE4GPQ1wS1f1EGIlWNbf1z4utNCnbyEtjbL1Ul26gI1Qui5YzNUgOCK7mcpp1bYFQcfiScYCn1gS8BhX9pI/dUdwLHXNijYhbtvV1rUgj8eGc4QsUYvN0Wm8dEBdxwaCeTnAhQKBgQDt4nnf/5fVYmgNFjs6iGUEsPj4KE2ITpRsLUQCnAgcv5JbfNsBlcq2Dswmg44K29HSShm8NQWPuCbmZh9RBeWTZJ2oorRvmGTf+3HOLO0DAz+jgF2XUlm9Imvd4dYFQkQC4ezJ5nIPeLehrBPJT6OaL8mjImOgVmn1GD94abUMYwKBgQDXKxPjxHHCjXkQsPNidWT3fgDs3pd4P5vZtxwSk6UYq9LdFq8B6UKTy2PnURy6sZ4mWKdFU1w9zfGtnydrcpcJpcPscb1C5K1RQZvjjSjgO+Xc+wQHROeUQnKnWiSyCfKKs8RvKHI1pnIxp7CWRFvvOrkduvpeqAcL/fsYl5RcKwKBgCEsKtisuMyd22qvxfSknomn8CAS5rTyWEo5iblfbtYrdMpjP9sZu1nl+FwKjl0/SCQuByaysiLXiD0q0oUm8Fu8dSvV/JlvQ+nkE3uv0iFQa6huNx7p8e+pBCe93W2ATyGjxbRl+VPk/p6B84RoIaVXsqOYIRJz2nS+O+Obwg6HAoGAXSmxuvTbrLlEqg1z1DWwOdi83dKjEtW3zVSTOxfibAQ5kLC5pcIxqXxvDUD5h3xQVZodEs89KSV6dwpqLwO2kd7MLhwxLj2FLaGStbvw7uYAaOXmoJ8dBfyfWAaXzN6xvYyIiPiiOsiOuSE6PFXs2HA8prrGnSVzp6WYitVWz3ECgYAUFmg3QmPRmY1CYAM0a/2HudJsk9JGaZgflqBWYOvzcttHJX1h7puq4dIAXJFBaeKT+3gL0qPYU1Fd9uh1pTPS1Gi3CkNeVJPZbmTRddJTO1agYdYe9SkR3YObi9XqzAER+gdno/HN5wEY0/UCjp8DX97wC6wuhkahIQ6uk3TrQw==';
        $aop->format = "json";
        $aop->apiVersion = '1.0';
        $aop->postCharset = "UTF-8";
        $aop->signType = "RSA2";
        $aop->alipayrsaPublicKey = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAx/E74Q2xP17Hi3t/b9vAIxB0YnXZV4rXUj2zr0+BklDJHWox1Vf/yFpCy8VzCf9vDfs8yzU/55N01DSKK9Cw0C87aL88K7hlTiI4olnzOm90PYUGQDaAe+xMNH+T2JVv1AknEydqp4dRSXMZQZIRO/ac2TgRpYgLE34Qp73IYReCP3gqMhSsQJe84DEs3KoAjvp0+Xu3LyyOiczKDiVvKurMshSU5rrEK3UZ1PtgZadpbcgdhpgeV7pn3gfobwFbZUDo2IWTcgYmrOyKonPlD5J/6iNCLVHStB09usvJzRC8lcvvSol+oZH6r3FOd04ZymTGWGVKKG6EzhB4YBiooQIDAQAB';
        //实例化具体API对应的request类,类名称和接口名称对应,当前调用接口名称：alipay.trade.app.pay
        $request = new AlipayTradeAppPayRequest();
        $arr['body']                = '发票全能王';
        $arr['subject']             = $duix['title'];//
        $arr['out_trade_no']        = $tradeNo;//
        $arr['timeout_express']     = '30m';
        $arr['total_amount']        = floatval($total_amount);//
        $arr['product_code']        = 'QUICK_MSECURITY_PAY';
        $info = json_encode($arr);
        $request->setNotifyUrl("http://bill.ganbuguo.com/pay/notify");
        $request->setBizContent($info);
        $result = $aop->sdkExecute ($request);
        return json_encode(['code'=>0,'msg'=>'ok','result'=>$result]);

    }


    //新的回调接口
    /**
     *  支付回调
     */
    public function notify(){
        $request = Request();
        $res = $request->param();
        $receipt    = new ReceiptModel();
        $product    = new ProductModel();
        $producth    = new ProductHaveModel();
        $User       = new UserModel();
        // 订单号
        $out_trade_no = $res['out_trade_no'];
        // 交易状态
        $trade_status =  $res['trade_status'];
        // 支付宝交易号
        $trade_no = $res['trade_no'];
        if ($trade_status == 'TRADE_FINISHED' || $trade_status == 'TRADE_SUCCESS') {
            //获取订单信息
            $ress=$receipt->getInfo(['outtradeno'=>$out_trade_no]);
            // 这里进行逻辑判断，如果支付成功，可以改变支付状态，可按照具体情况具体分析
            if ($ress && $ress['state']==0) {
                $receipt->updatedatas(['state'=>1,'comment'=>json_encode($res),'etime'=>$res['gmt_payment']],['outtradeno'=>$out_trade_no]);
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
                echo 'success';
            }else{
                echo '订单已完成或不存在';
            }
        }else{
            echo 'fail';
        }
    }













}
?>