<?php
/**
 * Created by PhpStorm.
 * User: ysc
 * Date: 2019/10/29
 * Time: 19:09
 */
namespace mylib;

class curll {

	/**
	 * 使用：   
	 * echo curlOpen('http://www.baidu.com');   
	 *   
	 * POST数据   
	 * $post = array('aa'=>'ddd','ee'=>'d')   
	 * 或   
	 * $post = 'aa=ddd&ee=d';   
	 * echo curlOpen('http://www.baidu.com',array('post'=>$post));   
	 * @param string $url
	 * @param array $config
	 */
	public static function curlOpen($url, $config = array())
	{
		$arr = array('post' => false,'referer' => $url,'cookie' => '', 'useragent' => 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/4.0; SLCC1; .NET CLR 2.0.50727; .NET CLR 3.0.04506; customie8)', 'timeout' => 20, 'return' => true, 'proxy' => '', 'userpwd' => '', 'nobody' => false,'header'=>array(),'gzip'=>true,'ssl'=>false,'isupfile'=>false);
		$arr = array_merge($arr, $config);
		$ch = curl_init();
		
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, $arr['return']);
		curl_setopt($ch, CURLOPT_NOBODY, $arr['nobody']);  
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_USERAGENT, $arr['useragent']);
		curl_setopt($ch, CURLOPT_REFERER, $arr['referer']);
		curl_setopt($ch, CURLOPT_TIMEOUT, $arr['timeout']);
		//curl_setopt($ch, CURLOPT_HEADER, true);//获取header
		if($arr['gzip']) curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');
		if($arr['ssl'])
		{
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		}
		if(!empty($arr['cookie']))
		{
			curl_setopt($ch, CURLOPT_COOKIEJAR, $arr['cookie']);
			curl_setopt($ch, CURLOPT_COOKIEFILE, $arr['cookie']); 
		} 
		
		if(!empty($arr['proxy']))
		{
			//curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);  
			curl_setopt ($ch, CURLOPT_PROXY, $arr['proxy']);
			if(!empty($arr['userpwd']))
			{            
				curl_setopt($ch,CURLOPT_PROXYUSERPWD,$arr['userpwd']);
			}        
		}    
		
		//ip比较特殊，用键值表示
		if(!empty($arr['header']['ip']))
		{
			array_push($arr['header'],'X-FORWARDED-FOR:'.$arr['header']['ip'],'CLIENT-IP:'.$arr['header']['ip']);
			unset($arr['header']['ip']);
		}   
		$arr['header'] = array_filter($arr['header']);
		
		if(!empty($arr['header']))
		{
			curl_setopt($ch, CURLOPT_HTTPHEADER, $arr['header']); 
		}

		if ($arr['post'] != false)
		{
			curl_setopt($ch, CURLOPT_POST, true);
			if(is_array($arr['post']) && $arr['isupfile'] === false)
			{
				$post = http_build_query($arr['post']);            
			} 
			else
			{
				$post = $arr['post'];
			}
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		}    
		$result = curl_exec($ch);
		//var_dump(curl_getinfo($ch));
		curl_close($ch);

		return $result;
	}
	
	/**
	 * curl post方法
	 * @param string $url 请求地址
	 * @param array|string $param post参数
	 */
	public static function http_post($url,$param){
		$oCurl 			= curl_init();
		
		if(stripos($url,"https://")!==FALSE){
			curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
		}
		
		if (is_string($param)) {
			$strPOST 	= $param;
		} else {
			$aPOST 		= array();
			foreach($param as $key=>$val){
				$aPOST[]= $key."=".urlencode($val);
			}
			$strPOST 	=  join("&", $aPOST);
		}
		
		curl_setopt($oCurl, CURLOPT_URL, $url);
		curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt($oCurl, CURLOPT_POST,true);
		curl_setopt($oCurl, CURLOPT_POSTFIELDS,$strPOST);
		
		$sContent 		= curl_exec($oCurl);
		$aStatus 		= curl_getinfo($oCurl);
		
		curl_close($oCurl);
		
		if(intval($aStatus["http_code"])==200){
			return $sContent;
		}else{
			return false;
		}	
	}
	
	/*
	* 获取限行城市列表
	* $appkey为appkey
	*/
	public static function city($appkey){
		$url = "https://api.jisuapi.com/vehiclelimit/city?appkey=$appkey";
		$result = self::curlOpen($url, ['ssl'=>true]);
		$jsonarr = json_decode($result, true);
		//exit(var_dump($jsonarr));
		if($jsonarr['status'] != 0)
		{
			echo $jsonarr['msg'];
			exit();
		}
		$result = $jsonarr['result'];
		return $result;
	}
	
	/*
	*获取城市限行详情
	* $appkey为appkey
	* $city为城市名称全拼
	*/
	public static function cityxq($appkey,$city){
			$date = date("Y-m-d");//时间'2015-12-02';
			$url = "https://api.jisuapi.com/vehiclelimit/query?appkey=$appkey&city=$city&date=$date";
			$result = self::curlOpen($url, ['ssl'=>true]);
			$jsonarr = json_decode($result, true);
			//exit(var_dump($jsonarr));
			if($jsonarr['status'] != 0)
			{
				echo $jsonarr['msg'];
				exit();
			}
			$result = $jsonarr['result'];
			return $result;
	}
	
	
	
	/*
	* 获取所有城市的信息（天气）
	* $appkey为appkey
	*/
	public static function allCity($appkey){
		$url = "https://api.jisuapi.com/weather/city?appkey=$appkey";
		$result = self::curlOpen($url, ['ssl'=>true]);
		$jsonarr = json_decode($result, true);
		//exit(var_dump($jsonarr));
		if($jsonarr['status'] != 0)
		{
			echo $jsonarr['msg'];
			exit();
		}
		$result = $jsonarr['result'];
		return $result;
	}
	
	
	/*
	* 获取所有城市的天气预报详情
	* $appkey为appkey
	* $city为城市中文名称
	*/
	public static function citySky($appkey,$city){
		//$city = '安顺';//utf8
		//$cityid='111';//任选
		//$citycode='101260301';//任选
		$url = "https://api.jisuapi.com/weather/query?appkey=$appkey&city=$city";
		$result = self::curlOpen($url, ['ssl'=>true]);
		$jsonarr = json_decode($result, true);
		//exit(var_dump($jsonarr));
		if($jsonarr['status'] != 0)
		{
			echo $jsonarr['msg'];
			exit();
		}
		$result = $jsonarr['result'];
		return $result;
	}
	
	
	/*
	* 发送验证码
	* $appkey为appkey
	* $mobile为手机号$code为验证码
	*/
	public static function sendmsg($mobile,$content){
		//header("Content-Type:text/html;charset=utf-8");
		$appkey = '3e6e9f3444a51496';//你的appkey
		$url = "https://api.jisuapi.com/sms/send?appkey=$appkey&mobile=$mobile&content=$content";
		//print_r($url);exit;
		$result = self::curlOpen($url, ['ssl'=>true]);
		//print_r($result);exit;
		$jsonarr = json_decode($result, true);
		//exit(var_dump($jsonarr));
		if($jsonarr['status'] != 0)
		{
			echo $jsonarr['msg'];
			exit();
		}
		$result = $jsonarr['result'];
		return $result;
	}
	
	/*
	* 发票查验
	* $appkey为appkey
	* $number,$date,$extaxtotalfee,$checkcode,$code
	*/
	public static function checkbill($number,$date,$extaxtotalfee,$checkcode,$code){
		$appkey = '3e6e9f3444a51496';//你的appkey
		$url = "https://api.jisuapi.com/invoiceverify/verify?appkey=$appkey&code=$code&number=$number&date=$date&extaxtotalfee=$extaxtotalfee&checkcode=$checkcode";
		$result = self::curlOpen($url, ['ssl'=>true]);
		$jsonarr = json_decode($result, true);
		 
		if($jsonarr['status'] != 0)
		{
		    //echo $jsonarr['msg'];
		    return json_encode(['code'=>1,'msg'=>$jsonarr['msg']]);
		    exit();
		}
		$invoiceverify = $jsonarr['result'];
		return $invoiceverify;
		//echo $invoiceverify['number'].' '.$invoiceverify['code'].' '.$invoiceverify['seller'].' '.$invoiceverify['sellercreditno'].' '.$invoiceverify['sellerbank'].' '.$invoiceverify['selleraddress'].' '.$invoiceverify['buyer'].' '.$invoiceverify['buyercreditno'].' '.$invoiceverify['buyerbank'].' '.$invoiceverify['buyeraddress'].' '.$invoiceverify['totalfeecn'].' '.$invoiceverify['extaxtotalfee'].' '.$invoiceverify['date'].' '.$invoiceverify['totaltax'].' '.$invoiceverify['type'].' '.$invoiceverify['remark'].' '.$invoiceverify['district'].' '.$invoiceverify['type'].' '.$invoiceverify['machinecode'].' '.$invoiceverify['checkcode'].' '.$invoiceverify['state'].'<br>';
		//foreach($result['itemlist'] as $val)
		//{
		  //  echo $val['name'].' '.$val['type'].' '.$val['unit'].' '.$val['num'].' '.$val['price'].' '.$val['totalfee'].' '.$val['taxrate'].' '.$val['taxfee'].'<br>';
		//}
	}
	
	

}

?>