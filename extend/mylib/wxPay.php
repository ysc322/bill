<?php
namespace mylib;

class wxPay{
    /*
    配置参数
    */
    var $config = array(
        'appid' => "wx7a60314809484430",    /*微信开放平台上的应用id*/
        'mch_id' => "1577882041",   /*微信申请成功之后邮件中的商户id*/
        'api_key' => "Hxk2esij37djdjfd6f4jkds5viwkn7cv",    /*在微信商户平台上自己设定的api密钥 32位*/
        'notify_url' => 'http://bill.ganbuguo.com/wxpay/notify', /*自定义的回调程序地址id*/
        'qian_url' => 'http://bill.ganbuguo.com/wxpay/qian' /*自定义的签约回调程序地址*/
    );

    public function  __construct() {

    }

    //获取预支付订单
    public function getPrePayOrder($body, $out_trade_no, $total_fee,$attach){
        $url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
        $notify_url = $this->config["notify_url"];
        
        $onoce_str = $this->getRandChar(32);
        
        $data["appid"] = $this->config["appid"];
        $data["body"] = $body;
        $data["mch_id"] = $this->config['mch_id'];
        $data["nonce_str"] = $onoce_str;
        $data["notify_url"] = $notify_url;
        $data["out_trade_no"] = $out_trade_no;
        $data["spbill_create_ip"] = $this->get_client_ip();
        $data["total_fee"] = $total_fee;
        $data["trade_type"] = "APP";
        $data["attach"] = $attach;
        $s = $this->getSign($data, false);
        $data["sign"] = $s;
        
        $xml      = $this->arrayToXml($data);
        $response = $this->postXmlCurl($xml, $url);
        
        //将微信返回的结果xml转成数组
        if($response != false){
            return $this->xmlstr_to_array($response);
        }else{
            return false;   
        }
    }
    //支付签约
    public function getPayOrder($body, $out_trade_no, $total_fee,$attach){
        $url = "https://api.mch.weixin.qq.com/pay/contractorder";
        $notify_url = $this->config["notify_url"];
        
        $onoce_str = $this->getRandChar(32);
        $data["appid"] = $this->config["appid"];
        $data["mch_id"] = $this->config['mch_id'];
        $data["contract_mchid"] = $this->config['mch_id'];
        $data["contract_appid"] = $this->config["appid"];
        $data["out_trade_no"] = $out_trade_no;
        $data["nonce_str"] = $onoce_str;
        $data["body"] = $body;
        $data["notify_url"] = $notify_url;
        $data["total_fee"] = $total_fee;
        $data["spbill_create_ip"] = $this->get_client_ip();
        $data["trade_type"] = "APP";
        $data["plan_id"] = 1;//协议模板id
        $data["contract_code"] = $out_trade_no;//签约协议号
        $data["request_serial"] = time();//商户请求签约时的序列号，要求唯一性。序列号主要用于排序，不作为查询条件
        $data["attach"] = $attach;
        $data["contract_display_account"] = "发票管家";//签约用户的名称,用于页面展示
        $data["contract_notify_url"] = $this->config["qian_url"];
        $s = $this->getSign($data, false);
        $data["sign"] = $s;
        
        $xml      = $this->arrayToXml($data);
        $response = $this->postXmlCurl($xml, $url);
        
        //将微信返回的结果xml转成数组
        if($response != false){
            return $this->xmlstr_to_array($response);
        }else{
            return false;   
        }
    }



    //执行第二次签名，才能返回给客户端使用
    public function getOrder($prepayId){
        $data["appid"] = $this->config["appid"];
        $data["noncestr"] = $this->getRandChar(32);;
        $data["package"] = "Sign=WXPay";
        $data["partnerid"] = $this->config['mch_id'];
        $data["prepayid"] = $prepayId;
        $data["timestamp"] = time();
        $s = $this->getSign($data, false);
        $data["sign"] = $s;

        return $data;
    }

    /*
        生成签名
    */
    public function getSign($Obj)
    {
        if(is_array($Obj)){
            foreach($Obj as $k => $v){
                $Parameters[strtolower($k)] = $v;
            }
        }
        
        //签名步骤一：按字典序排序参数
        ksort($Parameters);
        $String = $this->formatBizQueryParaMap($Parameters, false);
        //echo "【string】 =".$String."</br>";
        //签名步骤二：在string后加入KEY
        $String  = $String."&key=".$this->config['api_key'];
        //echo "<textarea style='width: 50%; height: 150px;'>$String</textarea> <br />";
        //签名步骤三：MD5加密
        $result_ = strtoupper(md5($String));
        return $result_;
    }

    //获取指定长度的随机字符串
    public function getRandChar($length){
       $str = null;
       $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
       $max = strlen($strPol)-1;

       for($i=0;$i<$length;$i++){
        $str.=$strPol[rand(0,$max)];//rand($min,$max)生成介于min和max两个数之间的一个随机整数
       }

       return $str;
    }

    //数组转xml
    public function arrayToXml($arr)
    {
        $xml = "<xml>";
        foreach ($arr as $key=>$val)
        {
             if (is_numeric($val))
             {
                $xml.="<".$key.">".$val."</".$key.">"; 

             }
             else
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";  
        }
        $xml.="</xml>";
        return $xml; 
    }

    //post https请求，CURLOPT_POSTFIELDS xml格式
    public function postXmlCurl($xml,$url,$second=30)
    {       
        //初始化curl        
        $ch = curl_init();
        //超时时间
        curl_setopt($ch,CURLOPT_TIMEOUT,$second);
        //这里设置代理，如果有的话
        //curl_setopt($ch,CURLOPT_PROXY, '8.8.8.8');
        //curl_setopt($ch,CURLOPT_PROXYPORT, 8080);
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        //运行curl
        $data = curl_exec($ch);
        //返回结果
        if($data)
        {
            curl_close($ch);
            return $data;
        }
        else 
        { 
            $error = curl_errno($ch);
            echo "curl出错，错误码:$error"."<br>";
            echo "<a href='http://curl.haxx.se/libcurl/c/libcurl-errors.html'>错误原因查询</a></br>";
            curl_close($ch);
            return false;
        }
    }

    /*
        获取当前服务器的IP
    */
    public function get_client_ip()
    {
        if ($_SERVER['REMOTE_ADDR']) {
        $cip = $_SERVER['REMOTE_ADDR'];
        } elseif (getenv("REMOTE_ADDR")) {
        $cip = getenv("REMOTE_ADDR");
        } elseif (getenv("HTTP_CLIENT_IP")) {
        $cip = getenv("HTTP_CLIENT_IP");
        } else {
        $cip = "unknown";
        }
        return $cip;
    }

    //将数组转成uri字符串
    public function formatBizQueryParaMap($paraMap, $urlencode)
    {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v)
        {
            if($urlencode)
            {
               $v = urlencode($v);
            }
            $buff .= strtolower($k) . "=" . $v . "&";
        }
        $reqPar;
        if (strlen($buff) > 0) 
        {
            $reqPar = substr($buff, 0, strlen($buff)-1);
        }
        return $reqPar;
    }

    /**
    *xml转成数组
    */
    public function xmlstr_to_array($xmlstr) {
        $array_data = json_decode(json_encode(simplexml_load_string($xmlstr, 'SimpleXMLElement', LIBXML_NOCDATA)), true);       
        return $array_data;
        //$doc = new DOMDocument();
        //$doc->loadXML($xmlstr);
        //return $this->domnode_to_array($doc->documentElement);
    }
    public function domnode_to_array($node) {
        $output = array();
        switch ($node->nodeType) {
            case XML_CDATA_SECTION_NODE:
            case XML_TEXT_NODE:
                $output = trim($node->textContent);
                break;
            case XML_ELEMENT_NODE:
                for ($i=0, $m=$node->childNodes->length; $i<$m; $i++) {
                    $child = $node->childNodes->item($i);
                    $v = $this->domnode_to_array($child);
                    if(isset($child->tagName)) {
                        $t = $child->tagName;
                        if(!isset($output[$t])) {
                            $output[$t] = array();
                        }
                        $output[$t][] = $v;
                    }elseif($v) {
                        $output = (string) $v;
                    }
                }
                if(is_array($output)) {
                    if($node->attributes->length) {
                        $a = array();
                        foreach($node->attributes as $attrName => $attrNode) {
                            $a[$attrName] = (string) $attrNode->value;
                        }
                        $output['@attributes'] = $a;
                    }
                    foreach ($output as $t => $v) {
                        if(is_array($v) && count($v)==1 && $t!='@attributes') {
                            $output[$t] = $v[0];
                        }
                    }
                }
                break;
        //switch结束
        }
        return $output;
    }
    
    
    //获取预支付订单
    public function searchOrder($out_trade_no){
        $url = "https://api.mch.weixin.qq.com/pay/orderquery";
        $onoce_str = $this->getRandChar(32);
        
        $data["appid"] = $this->config["appid"];
        $data["mch_id"] = $this->config['mch_id'];
        $data["nonce_str"] = $onoce_str;
        //$data["transaction_id"] = $out_trade_no;
        $data["out_trade_no"] = $out_trade_no;
        $s = $this->getSign($data, false);
        $data["sign"] = $s;
        
        $xml      = $this->arrayToXml($data);
        $response = $this->postXmlCurl($xml, $url);
        //将微信返回的结果xml转成数组
        if($response != false){
            return $this->xmlstr_to_array($response);
        }else{
            return false;   
        }
    }


    /**
     * Wechat::verifyNotify()
     * 验证服务器通知
     * @param array $data
     * @return array
     */
    public function verifyNotify($xml) {
        $AppSignature = $xml['sign'];    
        unset($xml['sign']);
        $sign = $this->getSign($xml);
        if ($AppSignature != $sign) {
            return false;
        } elseif (strtolower($xml['return_code']) != 'success') {
            return false;
        }
        return $xml;
    }
    
}


?>