<?php

/**
 * Created by PhpStorm.
 * User: liuzhengbin
 * Date: 2018/5/14
 * Time: 18:31
 */
namespace mylib;

class apns{

    public static  function push($deviceToken,$content){
        //推送目标设备号(测试环境和正式环境不一样)
        //$deviceToken = '4f707f4eb373dfbad83188ae1c71ce3dd9eba983b8234b777a24157df20915f4';

        //证书路径
        $pem = '/home/wwwroot/fapiao/public/xieyi/invoice.pem';
        //$pem = '/home/wwwroot/fapiao/public/xieyi/developer.pem';//测试地址
        //测试服务器
        $apnsHost = 'ssl://gateway.sandbox.push.apple.com:2195';

        //正式服务器
        //$apnsHost = 'ssl://gateway.push.apple.com:2195';
        //$content = '测试推送内容';
        $body = array("aps" => array("alert" => $content, "badge" => 1, "sound" => 'default'), 'url' => '');
        $ctx = stream_context_create();
        stream_context_set_option($ctx, "ssl", "local_cert", $pem);

        $pass = "haoxin123";     //如果有密码的话
        stream_context_set_option($ctx, 'ssl', 'passphrase', $pass);
        //$errstr='';
        //$err=0;
        $fp = stream_socket_client($apnsHost, $err, $errstr, 60, STREAM_CLIENT_CONNECT, $ctx);
        //var_dump($fp);exit;
        //print_r($fp);exit;
        if (!$fp)
        {
           // echo "Failed to connect $err $errstr";
           // return;
            return false;
        }
        //print "Connection OK\n";
        $payload = json_encode($body);
        $msg = chr(0) . pack("n", 32) . pack("H*", str_replace(' ', '', $deviceToken)) . pack("n", strlen($payload)) . $payload;
       // echo "sending message :" . $payload . "\n";
        fwrite($fp, $msg);
        fclose($fp);
        return true;
    }


   /* public static function c_push(){
        //if(defined('CURL_HTTP_VERSION_2_0')){

            $device_token   = 'ea3c467ccb7f5208cf987d8bbe8eb65fc8510d4c457728ed2fad90b75430c5b4';
            //$pem_file     = 'path to your pem file';
            $pem_file       = "/home/wwwroot/fapiao/public/xieyi/developer.pem";
            //$pem_secret   = 'your pem secret';
            $pem_secret     = '';
            //$apns_topic   = 'your apns topic. Can be your app bundle ID';
            $apns_topic     = 'com.yiyou.invoice';

            $data = array(
                "aps"=>array(
                    'alert'=>'这是推送标题',
                    "sound"=>"default",
                    "badge"=>0,
                ),
                'app'=>array(
                    "title"=>"这是展示标题内容",
                    "content"=>"这是自定义内容",
                ),
            );
            $url = "https://api.development.push.apple.com/3/device/$device_token";

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $j = json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "apns-topic: $apns_topic",
            ));
            curl_setopt($ch, CURLOPT_SSLCERT, $pem_file);
            curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');
            curl_setopt($ch, CURLOPT_SSLCERTPASSWD, $pem_secret);
            $response = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if($httpcode == 200){
                echo "push ok";
            }
       // }else{

       //     echo "error http2!";
        //}
    }

    */



















}