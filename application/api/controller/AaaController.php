<?php
namespace app\api\controller;
use app\api\controller\base\BaseController;
use think\facade\Log;
use think\facade\Request;
use think\Exception;
use think\Db;
use app\common\model\UserModel;
use app\common\model\BillinfoModel;
use app\common\model\ElectronModel;
use app\common\model\FolderModel;
use think\facade\Cache;
use GuzzleHttp\json_encode;

use mylib\img;
class AaaController extends BaseController
{

	//发票验证阿里云
	public function test(){
        $request = Request();
        $data = $request->param();
        if($data['type']==1){
            //扫码验证
            $res=explode(',', $data['strcode']);
            if(count($res)<2){
                return $this->json_error('识别失败,暂不支持此类型发票');
            }
            $res[5]=substr($res[5],0,4).'-'.substr($res[5],4,2).'-'.substr($res[5],6);
            $number=$res[3];
            $date=$res[5];
            $extaxtotalfee=$res[4];
            $checkcode=$res[6];
            $code=$res[2];
        }else{
            //手动
            $number=$data['number'];
            $date=$data['date'];
            $extaxtotalfee=$data['extaxtotalfee'];
            $checkcode=$data['checkcode'];
            $code=$data['code'];
        }
        $rq=$this->alicheck($number,$date,$extaxtotalfee,$checkcode,$code);
        print_r(json_decode($rq,true));exit;
        $re=json_decode($rq,true);
        if($re['message']['status']== -1){
            return $this->json_error('识别失败');
        }else{
            $se=$re['invoice'][0]['veritem'];
            //print_r($res);exit;
            $res=[];
            foreach ($se as $k => $v){
                $res[$v['name']]=$v['content'];
            }
            if($res['invoiceType']=='04'){
                $res['invoiceType']="增值税普通发票";
            }elseif($res['invoiceType']=='01'){
                $res['invoiceType']="增值税专用发票";
            }elseif($res['invoiceType']=='10'){
                $res['invoiceType']="增值税电子普通发票";
            }
            $arr=[
                'uid'=>$info['id'],
                'typeid'=>1,
                'photourl'=>'',
                'folderid'=>0,
                'invoicetype'=>$res['invoiceType'],//发票种类
                'photodate'=>date('Y-m-d',time()),//拍照时间
                'opendate'=>$res['billingDate'],//开票时间
                'consumetype'=>"服务",//消费类型
                'billcode'=>$res['invoiceNumber'],//发票号码
                'invoicecode'=>$res['invoiceCode'],//发票代码
                'pretax'=>$res['totalAmount'],//税前金额
                'invoicename'=>$res['administrativeDivisionName'].$res['invoiceType'],//发票名称
                'taxamount'=>$res['totalTax'],//税额
                'amount'=>$res['amountTax'],//金额
                'checkcode'=>$res['checkCode'],//校验码
                'salename'=>$res['salesName'],//销售方名称
                'saleduty'=>$res['salesTaxNo'],//销售方纳税人识别号
                'saleplace'=>$res['salesAddressPhone'],//销售方地址及电话
                'salebank'=>$res['salesBank'],//销售方开户行及账号
                'buyname'=>$res['purchaserName'],//购方名称
                'buyduty'=>$res['purchaserTaxNo'],//购方纳税人识别号
                'buyplace'=>$res['purchaserAddressPhone'],//购方地址及电话
                'buybank'=>$res['purchaserBank'],//购方开户行及账号
                'checkbill'=>1,//已验证
                'checktime'=>date('Y-m-d H:i:s',time()),//验证时间
                'remarks'=>$res['remarks'].'------'.$res['machineCode'].':状态:'.$res['state'],//备注
                'ctime'=>time()//创建时间
            ];



        }
	}

//发票验证阿里云
	public function alicheck($number,$date,$extaxtotalfee,$checkcode,$code){
		$host = "http://verinvoice.sinosecu.com.cn";
	    $path = "/verapi/verInvoice.do";
	    $method = "POST";
	    $appcode = "3f825fb50584460687f6b1fb359d8570";
	    $headers = array();
	    array_push($headers, "Authorization:APPCODE " . $appcode);
	    //根据API的要求，定义相对应的Content-Type
	    array_push($headers, "Content-Type".":"."application/x-www-form-urlencoded; charset=UTF-8");
	    $querys = "";
	    $bodys = "billingDate=".$date."&checkCode=".$checkcode."&invoiceCode=".$code."&invoiceNumber=".$number."&totalAmount=".$extaxtotalfee;
	    $url = $host . $path;

	    $curl = curl_init();
	    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
	    curl_setopt($curl, CURLOPT_URL, $url);
	    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
	    curl_setopt($curl, CURLOPT_FAILONERROR, false);
	    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($curl, CURLOPT_HEADER, true);
	    if (1 == strpos("$".$host, "https://"))
	    {
	        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
	    }
	    curl_setopt($curl, CURLOPT_POSTFIELDS, $bodys);
	     //return curl_exec($curl);
         $response = curl_exec($curl); // 已经获取到内容，没有输出到页面上。
		 curl_close($curl);
		 $response=substr($response,strripos($response,"Path=/")+6);
		 return $response;exit;
	}

    public function tuyas(){
        $src='http://bill.ganbuguo.com/uploads/file/20191119/11.png';
	    $img=new img($src,1);

	    $imgss=$img->compressImg('1');
	    print_r($imgss);exit;
        $image='http://bill.ganbuguo.com/uploads/file/20191119/11.png';
        $src = @imagecreatefromjpeg($image);
        list($width,$height) = getimagesize($image); //获取图片的高度
        $newwidth = $width;   //宽高可以设置, 楼主是想让它的宽高不变才没赋值
        $newheight = $height;
        $tmp = imagecreatetruecolor($newwidth,$newheight); //生成新的宽高
        imagecopyresampled($tmp, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height); //缩放图像
        $output = imagejpeg($tmp, $image, 50);
        print_r('1111');
        print_r($output);exit;
    }








}
?>