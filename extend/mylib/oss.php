<?php
namespace mylib;

use OSS\OssClient;
use OSS\Core\OssException;
use OSS\Model\CorsConfig;
use OSS\Model\CorsRule;


class oss {
	private static $ossClient;
	private static $bucket = "ihbstatic";
	public static function myconstruct() {
		//oss上传
		$accessKeyId = "LTAI4G6QB7uVc895tmGiZHtH";
		$accessKeySecret = "PjIX8UKHQhOVTwGTs8vvaISpjhCkFw";
		// Endpoint以杭州为例，其它Region请按实际情况填写。
		//$endpoint = "oss-cn-beijing.aliyuncs.com";
		$endpoint = "oss-cn-beijing-internal.aliyuncs.com";

		//您拥有的Bucket的名称。
		//您要创建的Object的名称。
		//$object = 'uploads/goods/'.date('Ym').'/' . $filename;
		//$content = "Hi, OSS.";
		try {
			self::$ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);
			//$ossClient->putObject($bucket, $object, $content);
		
		} catch (OssException $e) {
			//print $e->getMessage();
		}
			
		
	}
	public static function uploadfile($object,$content=''){
		self::myconstruct();
		try {
			self::deletefile($object);
			self::$ossClient->putObject(self::$bucket, $object, $content);
		} catch (OssException $e) {
			print $e->getMessage();
		}
        unlink('/home/wwwroot/bill/public/'.$object);
	}
    public static function donfile($object,$localfile){
        self::myconstruct();
        try {
            //self::deletefile($object);
            $options = array(
                OssClient::OSS_FILE_DOWNLOAD => $localfile
            );
            self::$ossClient->getObject(self::$bucket, $object, $options);
            //self::$ossClient->putObject(self::$bucket, $object, $content);
        } catch (OssException $e) {
            print $e->getMessage();
        }
    }
	public static function uploadfilepath($object,$filePath=''){
		self::myconstruct();
		try {
			$filePath = str_replace(BASEPATH, '', $filePath);
			$filePath = BASEPATH.'/'.$filePath;
    		//var_dump($object);
			self::$ossClient->multiuploadFile(self::$bucket, $object, $filePath);
		} catch (OssException $e) {
			print $e->getMessage();
		}
	}
	public static function doesObjectExist($object){
		
			self::myconstruct();
			try {
				//var_dump($object);
				//http://fthome.oss-cn-zhangjiakou.aliyuncs.com/
				$object = ltrim($object,'/');
				if(strpos($object, IMAGE_OSS_HTTP_REPLACE)!==false){
					$object = str_replace(IMAGE_OSS_HTTP_REPLACE, '', $object);
					$object = substr($object,0,strpos($object, '?'));
				}
				$exist = self::$ossClient->doesObjectExist(self::$bucket, $object);
				if($object == 'uploads/weixin/2018101539676145128.jpeg'){
					//var_dump($exist);
				}
				//var_dump($exist);
				if($exist === true){
					return true;
				}else{
					return false;
				}
				return $exist;
			} catch (OssException $e) {
				//print $e->getMessage();
			}
		
		return false;
	}
	public static function imgresize($object, $width, $height,$m = 'pad'){
		$width = round($width);
		$height = round($height);
			$object = str_replace(BASEPATH, '', $object);
			$url = '';
		if(strpos($object, IMAGE_OSS_HTTP_REPLACE)!==false){
			$object = str_replace(IMAGE_OSS_HTTP_REPLACE, '', $object);
			if(strpos($object, '?')!==false){
				$object = substr($object,0,strpos($object, '?'));
			}
		}
		if(strpos($object, IMAGE_OSS_HTTP)!==false){
			$object = str_replace(IMAGE_OSS_HTTP, '', $object);
			if(strpos($object, '?')!==false){
				$object = substr($object,0,strpos($object, '?'));
			}
		}
		if($object){
			$hz = pathinfo($object)['extension'];
			$url = IMAGE_OSS_HTTP.'/'.$object.'?x-oss-process='."image/resize,m_$m,h_$height,w_$width";
			if($hz == 'png'){
				$url = IMAGE_OSS_HTTP.'/'.$object.'?x-oss-process=image/format,png,x-oss-process='."image/resize,m_$m,h_$height,w_$width";
			}
			return $url;
		}
			return $url;
// 		$timeout = 3600000;
// 		//$options = array(OssClient::OSS_PROCESS => "image/interlace,1/format,jpg,image/resize,m_$m,h_$height,w_$width" );
// 		$options = array(OssClient::OSS_PROCESS => "image/resize,m_$m,h_$height,w_$width" );
// 		$signedUrl = self::$ossClient->signUrl(self::$bucket, $object, $timeout, "GET", $options);
// 		return $signedUrl;
	
	}
	public static function downfile($file,$path){
 		$dir = substr($path,strpos($path, 'uploads'),((strlen($path)-(strlen($path)-strrpos($path,'/'))))-strpos($path, 'uploads'));
 		$darr = explode('/', $dir);
 		if(!is_dir(BASEPATH.'/'.$dir.'/')){
 			@mkdir(BASEPATH.'/'.$dir.'/',0777,true);
 		}
		if($file&& strpos($file, 'http')!==false){
			$str = ftfile_get_contents($file);
			$res = file_put_contents($path, $str);
			unset($str);
		}
		return $path;
		
	}
	public static function urlsafe_b64encode($string) {
	   $data = base64_encode($string);
	   $data = str_replace(array('+','/','='),array('-','_',''),$data);
	   return $data;
	}
	public static function imgwortersize($object, $width, $height,$position='se',$alpha=70){
		// 水印
		self::myconstruct();
		try {
			$syimg = self::urlsafe_b64encode('uploads/goods/201807/goods153110496084679.png');
			if($width<500){
				$syimg = self::urlsafe_b64encode('uploads/atlas/201901/atlas154855797246038644.png');
				
			}

			$url = IMAGE_OSS_HTTP.'/'.$object.'?x-oss-process='."image/resize,w_$width,h_$height/watermark,image_$syimg,x_20,t_$alpha,g_$position,p_10";
			return $url;
			
			
			
// 			$timeout = 360000;
// 			$options = array(OssClient::OSS_PROCESS => "image/resize,w_$width,h_$height/watermark,image_$syimg,x_20,t_$alpha,g_$position,p_10" );
// 			$signedUrl = self::$ossClient->signUrl(self::$bucket, $object, $timeout, "GET", $options);
// 			//var_dump($signedUrl);
// 			return $signedUrl;
		} catch (OssException $e) {
			//print $e->getMessage();
		}
	
	}
	public static function imgworter($object){
		// 水印
		self::myconstruct();
		try {
				
			$syimg = self::urlsafe_b64encode('uploads/goods/201807/goods153110496084679.png');
			$timeout = 360000;
			$options = array(OssClient::OSS_PROCESS => "image/watermark,image_$syimg,t_90,g_se,x_10,y_10" );
			$signedUrl = self::$ossClient->signUrl(self::$bucket, $object, $timeout, "GET", $options);
			//var_dump($signedUrl);
			return $signedUrl;
		} catch (OssException $e) {
			//print $e->getMessage();
		}
	
	}

    public static function deletefile($object){
        self::myconstruct();
        try{
            self::$ossClient->deleteObject(self::$bucket, $object);
        } catch(OssException $e) {
            return;
        }
    }
	
	public static function putBucketCors()
{
        return;
		self::myconstruct();
	$bucket = self::$bucket;
	$ossClient = self::$ossClient;
    $corsConfig = new CorsConfig();
    $rule = new CorsRule();
    $rule->addAllowedHeader("*");
    $rule->addAllowedOrigin("http://p.fthome.com");
    $rule->addAllowedOrigin("http://www.fthome.com");
    $rule->addAllowedOrigin("http://www.fatailife.com");
    $rule->addAllowedOrigin("http://p2.fatailife.com");
    $rule->addAllowedOrigin("http://ldg.p.fthome.com");
    $rule->addAllowedOrigin("http://test.p.fthome.com");
    $rule->addAllowedOrigin("http://hhb.p.fthome.com");
    $rule->addAllowedOrigin("http://zxb.p.fthome.com");
    $rule->addAllowedOrigin("http://hwy.p.fthome.com");
    $rule->addAllowedOrigin("http://hxh.p.fthome.com");
    $rule->addAllowedOrigin("http://zhq.p.fthome.com");
    $rule->addAllowedOrigin("http://wenwen.p.fthome.com");
    $rule->addAllowedMethod("GET");
    $rule->setMaxAgeSeconds(1);
    $corsConfig->addRule($rule);
    try{
        $ossClient->putBucketCors($bucket, $corsConfig);
    } catch(OssException $e) {
        printf(__FUNCTION__ . ": FAILED\n");
        printf($e->getMessage() . "\n");
        return;
    }
}
}
