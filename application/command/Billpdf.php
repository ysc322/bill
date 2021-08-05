<?php

namespace app\common\command;

use mylib\apns;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use mylib\oss;

class Billpdf extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('billpdf');
        // 设置参数
        
    }

    protected function execute(Input $input, Output $output)
    {
        $this->batchlist();
		$user = new \app\common\model\UserModel();
		$batch = new \app\common\model\BatchModel();
        $batchsingle = new \app\common\model\BatchSingleModel();

        $list=$batchsingle->getList(['state'=>0]);
        foreach ($list as $k=>$v){
            $info=$batch->getInfo(['id'=>$v['batchid']]);
            $res=$this->billdiscern($info,$v);
            //if($res['state']!=''){
            $batchsingle->updatedatas(['state'=>$res['state'],'remarks'=>$res['remarks']],['id'=>$v['id']]);

           // }
        }
        $tui=$batch->getList(['state'=>1,'client'=>1,'sendout'=>0]);
        foreach ($tui as $kt=>$vt){
            $usinfo=$user->getInfo(['id'=>$vt['uid']]);
            if($usinfo['devicetoken']!=''){
                $content="所有发票已完成处理，请前往发票夹中查看!";
                $aa=apns::push($usinfo['devicetoken'],$content);
                if($aa){
                    $batch->updatedatas(['sendout'=>1],['id'=>$vt['id']]);
                }
            }
        }
    	// 指令输出
    	$output->writeln('billpdf');
    }

    //处理上传上来的PDF
    public function batchlist(){
        $batch = new \app\common\model\BatchModel();
        $batchsingle = new \app\common\model\BatchSingleModel();
        $list=$batch->getList(['state'=>0]);
        if($list){
            foreach ($list as $key=>$val){
                $ph = substr($val['imgurl'], 2);//删除'./'
                $pdf = '/home/wwwroot/fapiao/public/' . $ph;
                //下载PDF
                //oss::donfile($ph,$val['imgurl']);
               $arr=$this->ppng($pdf);
                //print_r($arr);exit;
               if(!empty($arr)){
                   foreach ($arr as $k => $v) {
                       $batchsingle->adddata(['batchid' => $val['id'],'sequence'=>($k+1),'batimg' => $v]);
                       //echo $batchsingle->getLastSql();exit;
                   }
                   $batch->updatedatas(['state'=>1],['id'=>$val['id']]);
               }
            }
        }

    }
    //PDF处理成图片
    public function ppng($pdf){
        //$pdf='/home/wwwroot/fapiao/public/uploads/file/xlx/as.pdf';
        $path='/home/wwwroot/fapiao/public/uploads/file/pdf';
        $yong='./uploads/file/pdf';
        $page=-1;

        if(!extension_loaded('imagick')){
            return false;
        }
        if(!file_exists($pdf)){
            return false;
        }
        $im = new \Imagick();
        $im->setResolution(120,120);
        $im->setCompressionQuality(100);
        //print_r($im);exit;
        if($page==-1){
            $im->readImage($pdf);
        }else{
            $im->readImage($pdf."[".$page."]");
        }
        foreach ($im as $Key => $Var){
            $Var->setImageFormat('png');
            $title= md5($Key.time()).'.png';
            $filename = $path."/".$title;
            if($Var->writeImage($filename) == true){
                $filename=$yong."/". $title;
                $ph = substr($filename, 2);//删除'./'
                $img = '/home/wwwroot/fapiao/public/' . $ph;
                chmod($img,0777);
                $Return[] = $filename;
            }
        }
        //print_r($Return);exit;
        return $Return;
    }


    /*  百度识别
     *  $info PDF总数据
     *  $date 单张图片信息
     *  返回$res=['state'=>''];
     */
    public function billdiscern($info,$date){
        $billinfo= new \app\common\model\BillinfoModel();

       // print_r($date['batimg']);exit;
        $ph = substr($date['batimg'], 2);//删除'./'
        $date['img'] = '/home/wwwroot/fapiao/public/' . $ph;
        $img=file_get_contents($date['img']);
        $fapiao=fapiao();
        $result=$fapiao->vatInvoice($img);
        //print_r($result);exit;
        if (array_key_exists('error_code',$result)) {
            $res['state']=2;
            $res['remarks']='识别失败!';
        }else{
            if ($billinfo->getInfo(['uid'=>$info['uid'],'billcode'=>$result['words_result']['InvoiceNum'],'state'=>0])) {
                $res['state']=2;
                $res['remarks']='此发票已存在!';
            }else{
                $date['folderid']=$info['folderid'];
                $res=$this->zzbill($result['words_result'],$info['uid'],$date);
            }
        }
        return $res;
    }

    //增值税发票添加数据 返回数据详细信息
    public function zzbill($result,$uid,$data){
        $billinfo = new \app\common\model\BillinfoModel();
        $result['InvoiceDate']=date('Y-m-d',strtotime( str_replace( array('年','月','日') , array('-','-','') , $result['InvoiceDate'])));
        $aws=$result['CommodityTaxRate'][0]['word'];
        $sww=substr($aws,0,strrpos($aws,"%"))/100;
        if(strpos($result['Remarks'],'红字增值税') !== false){
            $str="-";
        }else{
            $str='';
        }
        if(isset($data['folderid'])){
            $folderid=$data['folderid'];
        }else{
            $folderid=0;
        }
        if(isset($data['batimg'])){
            $inf = pathinfo($data['batimg'],PATHINFO_DIRNAME);//获取前面部分
            $ext = pathinfo($data['batimg'],PATHINFO_EXTENSION);//获取后缀
            $newname=$inf.'/'.$result['InvoiceNum'].'.'.$ext;
            if($result['InvoiceNum']!=''){
                //chmod($data['batimg'],0777);
                $ph = substr($data['batimg'], 2);//删除'./'
                $new = substr($newname, 2);//删除'./'
                $img = '/home/wwwroot/fapiao/public/' . $ph;
                $imgnew = '/home/wwwroot/fapiao/public/' . $new;
                rename($img,$imgnew);
                //上传oss
                $object=substr($newname,2);
                $content=file_get_contents($imgnew);
                oss::uploadfile($object,$content);
            }else{
                $newname=$data['batimg'];
                //上传oss
                $ph = substr($newname, 2);//删除'./'
                $img = '/home/wwwroot/fapiao/public/' . $ph;
                $object=substr($data['batimg'],2);
                $content=file_get_contents($img);
                oss::uploadfile($object,$content);
            }
        }
        $array=[
            'uid'=>$uid,
            'typeid'=>1,
            'folderid'=>$folderid,
            //'photourl'=>$data['img'],
            'photourl'=>$newname,
            'invoicetype'=>$result['InvoiceType'],//发票种类
            'photodate'=>date('Y-m-d',time()),//拍照时间
            'opendate'=>$result['InvoiceDate'],//开票时间
            'consumetype'=>"服务",//消费类型
            'billcode'=>$result['InvoiceNum'],//发票号码
            'invoicecode'=>$result['InvoiceCode'],//发票代码
            'pretax'=>$str.$result['TotalAmount'],//税前金额
            'invoicename'=>$result['InvoiceTypeOrg'],//发票名称
            'taxamount'=>$str.$result['TotalTax'],//税额
            'amount'=>$str.$result['AmountInFiguers'],//金额
            'checkcode'=>$result['CheckCode'],//校验码
            'salename'=>$result['SellerName'],//销售方名称
            'saleduty'=>$result['SellerRegisterNum'],//销售方纳税人识别号
            'saleplace'=>$result['SellerAddress'],//销售方地址及电话
            'salebank'=>$result['SellerBank'],//销售方开户行及账号
            'buyname'=>$result['PurchaserName'],//购方名称
            'buyduty'=>$result['PurchaserRegisterNum'],//购方纳税人识别号
            'buyplace'=>$result['PurchaserAddress'],//购方地址及电话
            'buybank'=>$result['PurchaserBank'],//购方开户行及账号
            'remarks'=>$result['Remarks'],//备注
            'taxa'=>$sww,
            'ctime'=>time()//创建时间
        ];
        $id=$billinfo->getAdddata($array);
        if($id){
            $array=[
                'state'=>1,
                'remarks'=>''
            ];
        }else{
            $array=[
                'state'=>2,
                'remarks'=>'写入数据失败！'
            ];
        }
        return $array;
    }




















}
