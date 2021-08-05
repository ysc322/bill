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

use mylib\curll;
use mylib\oss;
class PdfController extends BaseController
{

    //批量上传PDF文件
    public function uppdf($dir='',$isoldname=false){

        $file = request()->file('file');

        $path = '/uploads/file/';
        $pathr = '/uploads/file/';
        if (!is_dir(BASEPATH . '/uploads/file/')) {
            //				mkdir(BASEPATH.'/uploads/'.$dir.'/',true);
            mkdir(BASEPATH . '/uploads/file/');
        }
        if ($dir) {

            if (!is_dir(BASEPATH . '/uploads/file/' . $dir . '/')) {
                //				mkdir(BASEPATH.'/uploads/'.$dir.'/',true);
                mkdir(BASEPATH . '/uploads/file/' . $dir . '/', 0777, true);
                chmod(BASEPATH . '/uploads/file/' . $dir . '/', 0777);

            }
            $path = $path . $dir . '/';
            $pathr = $pathr . $dir . '/';
        }
        // 移动到框架应用根目录/public/uploads/ 目录下

        if ($file) {
            for ($i=0;$i<count($file);$i++){
                $info = $file[$i]->move(BASEPATH . $path);
                if ($info) {
                    // 成功上传后 获取上传信息
                    // 输出 jpg
                    $uploadFilename[] = $pathr . $info->getSaveName();;
                } else {
                    // 上传失败获取错误信息
                    return $file[$i]->getError();
                }
            }
            return json_encode($uploadFilename);
        }else{
            return $this->json_error('请上传图片');
        }

    }




    //上传PDF文件
    public function pdfadd(){
        $billinfo= new BillinfoModel();
        $electron = new ElectronModel();
        $folder = new FolderModel();
        $info=$this->chektoken();//检测用户token
        if(!$info){
            return json_encode(['code' =>2, 'msg'=>'请登录' ]);
        }
        $request = Request();
        $data = $request->param();
       // $imgurl=explode(',',$data['img']);
        //$dir='xlx/'.$info['id'].'/';
       // $url=$this->uppdf($info['id']);
       // print_r($url);exit;
        //$imgurl=json_decode($url,true);
       // for ($i=0;$i<count($imgurl);$i++) {
            $array = [
                'uid' => $info['id'],
                'typeid' => 1,
                //'photourl'=>'',
                'invoicetype' => "电子发票",//发票种类
                'photodate' => date('Y-m-d', time()),//拍照时间
                'consumetype' => "服务",//消费类型
                'remarks' => '',//备注
                'ctime' => time()//创建时间
            ];
            //$imgth = $imgurl[$i];//保存的发票pdf的地址
            $imgth = $data['img'];//保存的发票pdf的地址
            $ph = substr($imgth, 1);//删除'./'
            //print_r($ph);exit;
            $resqq = readPDF($ph);
            //print_r($resqq);exit;
            if ($resqq == 0) {
                return $this->json_error('请检查发票链接能否正常打开！');
            }
            $rest = array_filter($resqq);
            for ($i = 0; $i < count($rest); $i++) {
                $array['folderid']=$data['folderid'];
                if (strpos($rest[$i], '发票代码:') !== false) {
                    $array['invoicecode'] = substr(trim($rest[$i]), strripos(trim($rest[$i]), ":") + 1);

                }

                if (strpos($rest[$i], "发票号码:") !== false) {
                    $array['billcode'] = substr(trim($rest[$i]), strripos(trim($rest[$i]), ":") + 1);

                }
                // print_r($array['billcode']);exit;
                if (strpos($rest[$i], '值税电子') !== false) {
                    $array['invoicename'] = trim($rest[$i]);
                }
                if (strpos($rest[$i], '开票日期:') !== false) {
                    $da = str_replace(' ', '', substr(trim($rest[$i]), strripos(trim($rest[$i]), ":") + 1));
                    //print_r($da);exit;
                    $array['opendate'] = date('Y-m-d', strtotime(str_replace(array('年', '月', '日'), array('-', '-', ''), $da)));
                }
                if (strpos($rest[$i], '校 验 码:') !== false) {
                    $array['checkcode'] = str_replace(' ', '', substr(trim($rest[$i]), strripos(trim($rest[$i]), ":") + 1));
                }

                if (strpos($rest[$i], '小写') !== false) {
                    //print_r("111");
                    $q = substr(trim($rest[$i]), strripos(trim($rest[$i]), ")") + 1);
                    $array['amount'] = preg_replace('%[^0-9.]%', '', str_replace('¥', '', $q));
                }
                if (strpos($rest[$i], '%') !== false) {
                    if (strpos(trim($rest[$i]), ' ') !== false) {
                        $sl = str_replace('%', '', substr(trim($rest[$i]), strripos(trim($rest[$i]), " ") + 1));
                        $array['taxa'] = $sl / 100;
                        $array['pretax'] = substr(trim($rest[$i]), 0, strripos(trim($rest[$i]), " "));
                    } else {
                        $sl = str_replace('%', '', trim($rest[$i]));
                        $array['taxa'] = $sl / 100;
                        $array['pretax'] = sprintf("%.2f", round($array['amount'] / (1 + $array['taxa']), 2));
                    }
                }
                $array['taxamount'] = $array['amount'] - $array['pretax'];
                if ($i < 50) {
                    //销售方信心
                    if (strpos($rest[$i], '称:') !== false) {
                        $array['salename'] = substr(trim($rest[$i]), strripos(trim($rest[$i]), ":") + 1);
                    }
                    if (strpos($rest[$i], '识别号: ') !== false) {
                        $array['saleduty'] = substr(trim($rest[$i]), strripos(trim($rest[$i]), ":") + 1);
                    }
                    if (strpos($rest[$i], '电 话: ') !== false) {
                        $array['saleplace'] = substr(trim($rest[$i]), strripos(trim($rest[$i]), ":") + 1);
                    }
                    if (strpos($rest[$i], '开户行及账号: ') !== false) {
                        $array['salebank'] = substr(trim($rest[$i]), strripos(trim($rest[$i]), ":") + 1);
                    }
                }
                if ($i > 50) {
                    //购买方信息
                    if (strpos($rest[$i], '称:') !== false) {
                        $array['buyname'] = substr(trim($rest[$i]), strripos(trim($rest[$i]), ":") + 1);
                    }
                    if (strpos($rest[$i], '识别号: ') !== false) {
                        $array['buyduty'] = substr(trim($rest[$i]), strripos(trim($rest[$i]), ":") + 1);
                    }
                    if (strpos($rest[$i], '电 话: ') !== false) {
                        $array['buyplace'] = substr(trim($rest[$i]), strripos(trim($rest[$i]), ":") + 1);
                    }
                    if (strpos($rest[$i], '开户行及账号: ') !== false) {
                        $array['buybank'] = substr(trim($rest[$i]), strripos(trim($rest[$i]), ":") + 1);
                    }
                }
                $array['source'] = '1';//来源PDF
            }
            $jc=$billinfo->getInfo(['uid' => $info['id'], 'billcode' => $array['billcode'], 'state' => 0]);
            if ($jc) {
                //$billinfo->updatedatas(['source'=>1],['uid' => $info['id'], 'billcode' => $array['billcode'], 'state' => 0]);
                $aa = [
                    'billid' => $jc['id'],
                    'imgth' => $imgth,
                    'ctime' => date('Y-m-d H:i:s', time())
                ];
                //上传oss
                /*$object=substr($imgth,2);
                $content=file_get_contents($imgth);
                oss::uploadfile($object,$content);*/

                $electron->adddata($aa);
                return $this->json_error('此发票已存在');
                //continue;
            }else{
                if($array['billcode']== false){
                    $save_dir='./uploads/file/xlx/'.$info['id'].'/';
                    $pdf='/home/wwwroot/fapiao/public/'.$ph;
                    $photourl=ppng($pdf,$save_dir);//获取转换成png后的图片地址
                    $da['img']=$photourl[0];
                    $image=file_get_contents($da['img']);
                    $fapiao=fapiao();
                    $result=$fapiao->vatInvoice($image);//增值税发票专用
                    if (array_key_exists('error_code',$result)) {
                        return $this->json_error('empty image');
                    }
                    if ($billinfo->getInfo(['uid'=>$info['id'],'billcode'=>$result['words_result']['InvoiceNum'],'state'=>0])) {
                        //此发票已存在
                        return $this->json_error('此发票已存在');
                    }
                    $req=$this->zzbill($result['words_result'],$info['id'],$da,$ph,$data['folderid']);
                    //$req['photourl']=INLET_PATH . $req['photourl'];
                    $req['photourl']=IMG_PATH . $req['photourl'];
                    if ($req) {
                        return json_encode(['code'=>0,'msg'=>'添加成功','result'=>$req]);
                    }else{
                        return $this->json_error('添加失败');
                    }
                }
                $id = $billinfo->getAdddata($array);
                if ($id) {
                    //记录PDF文件地址
                    $aa = [
                        'billid' => $id,
                        'imgth' => $imgth,
                        'ctime' => date('Y-m-d H:i:s', time())
                    ];
                    //上传oss
                   /* $object=substr($imgth,2);
                    $content=file_get_contents($imgth);
                    oss::uploadfile($object,$content);*/

                    $electron->adddata($aa);
                } else {
                    return $this->json_error('添加失败');
                }
            }
        //}
        return json_encode(['code'=>0,'msg'=>'添加成功']);
    }


    //微信或支付宝发票详情存储
    public function weibill(){
        $info=$this->chektoken();//检测用户token
        if(!$info){
            return json_encode(['code' =>2, 'msg'=>'请登录' ]);
        }
        $billinfo= new BillinfoModel();
        $electron = new ElectronModel();
        $request = Request();
        $data = $request->param();
        $array['folderid']=$data['folderid'];//发票夹ID
        $array['uid']=$info['id'];//用户ID
        $array['typeid']=1;//发票类型
        $array['photourl']='';//发票图片
        $array['invoicetype']=$data['invoicetype'];//发票类型
        $array['invoicename']=$data['invoicetype'];//发票名称
        $array['ctime']=time();//创建时间
        $array['photodate']=date('Y-m-d',time());//拍照时间
        $array['opendate']=date('Y-m-d',$data['opendate']);//开票时间
        if(array_key_exists('consumetype',$data)) {
            $array['consumetype'] = $data['consumetype'];//消费类型
        }else{
            $array['consumetype'] = "服务";
        }
        $array['billcode']=$data['billcode'];//发票号码
        $array['invoicecode']=$data['invoicecode'];//发票代码

        if(array_key_exists('pretax',$data)) {
            $array['pretax']=$data['pretax']/100;//税前金额
        }else{
            $array['pretax'] = "0.00";
        }
        if(array_key_exists('taxamount',$data)) {
            $array['taxamount']=$data['taxamount']/100;//税额
        }else{
            $array['taxamount'] = "0.00";
        }
        $array['amount']=$data['amount']/100;//金额
        if(array_key_exists('checkcode',$data)) {
            $array['checkcode']=$data['checkcode'];//校验码
        }else{
            $array['checkcode'] = "";
        }
        if(array_key_exists('salename',$data)) {
            $array['salename']=$data['salename'];//销售方名称
        }else{
            $array['salename'] = "";
        }
        if(array_key_exists('saleduty',$data)) {
            $array['saleduty']=$data['saleduty'];//销售方纳税人识别号
        }else{
            $array['saleduty'] = "";
        }
        if(array_key_exists('saleplace',$data)) {
            $array['saleplace']=$data['saleplace'];//销售方地址及电话
        }else{
            $array['saleplace']='';
        }
        if(array_key_exists('salebank',$data)) {
            $array['salebank']=$data['salebank'];//销售方开户行及账号
        }else{
            $array['salebank']='';
        }
        if(array_key_exists('buyname',$data)) {
            $array['buyname']=$data['buyname'];//购方名称
        }else{
            $array['buyname']='';
        }
        if(array_key_exists('buyduty',$data)) {
            $array['buyduty']=$data['buyduty'];//购方纳税人识别号
        }else{
            $array['buyduty']='';
        }
        if(array_key_exists('buyplace',$data)) {
            $array['buyplace']=$data['buyplace'];//购方地址及电话
        }else{
            $array['buyplace']='';
        }
        if(array_key_exists('buybank',$data)) {
            $array['buybank']=$data['buybank'];//购方开户行及账号
        }else{
            $array['buybank']='';
        }
        $array['remarks']=$data['remarks'];//备注
        $array['taxa']='0.00';//税率
        $array['source']=$data['source'];//来源
        $jc=$billinfo->getInfo(['uid' => $info['id'], 'billcode' => $array['billcode'], 'state' => 0]);
        if($jc){
            /*if(array_key_exists('pdf_url',$data)){
                $aa = [
                    'billid' => $jc['id'],
                    'imgth' => $data['pdf_url'],
                    'ctime' => date('Y-m-d H:i:s', time())
                ];
                $electron->adddata($aa);
            }*/
            return $this->json_error('此发票已存在');
        }else {
            $in = $billinfo->getAdddata($array);
            if($in){
                /*if (array_key_exists('pdf_url', $data) and $data['pdf_url'] !='https://mp.weixin.qq.com/intp/invoice/getpdf?action=media_pdf') {
                    $aa = [
                        'billid' => $in,
                        'imgth' => $data['pdf_url'],
                        'ctime' => date('Y-m-d H:i:s', time())
                    ];
                    $electron->adddata($aa);
                }*/
                return json_encode(['code' => 0, 'msg' => '添加成功']);
            }else{
                return $this->json_error('添加失败');
            }
        }
    }


    //增值税发票添加数据 返回数据详细信息 电子发票链接调用
    public function zzbill($result,$uid,$data,$imgth,$folderid){
        $billinfo = new BillinfoModel();
        $electron = new ElectronModel();
        $folder = new FolderModel();
        if(isset($data['img'])){
            $inf = pathinfo($data['img'],PATHINFO_DIRNAME);//获取前面部分
            $ext = pathinfo($data['img'],PATHINFO_EXTENSION);//获取后缀
            $newname=$inf.'/'.$result['InvoiceNum'].'.'.$ext;
            if($result['InvoiceNum']!=''){
                rename($data['img'],$newname);
                //上传oss
                $object=substr($newname,2);
                $content=file_get_contents($newname);
                oss::uploadfile($object,$content);
            }else{
                $newname=$data['img'];
                //上传oss
                $object=substr($data['img'],2);
                $content=file_get_contents($data['img']);
                oss::uploadfile($object,$content);
            }
            //rename($data['img'],$newname);
        }
        $result['InvoiceDate']=date('Y-m-d',strtotime( str_replace( array('年','月','日') , array('-','-','') , $result['InvoiceDate'])));
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
            'pretax'=>$result['TotalAmount'],//税前金额
            'invoicename'=>$result['InvoiceTypeOrg'],//发票名称
            'taxamount'=>$result['TotalTax'],//税额
            'amount'=>$result['AmountInFiguers'],//金额
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
            'source'=> '1',//来源PDF
            'ctime'=>time()//创建时间
        ];
        $id=$billinfo->getAdddata($array);
        if($id){
            //记录PDF文件地址
            $aa=[
                'billid'=>$id,
                'imgth'=>$imgth,
                'ctime'=>date('Y-m-d H:i:s',time())
            ];
            //上传oss
            /*$object=substr($imgth,2);
            $content=file_get_contents($imgth);
            oss::uploadfile($object,$content);*/

            $electron->adddata($aa);
            $where=['id'=>$id];
            $field='id,uid,typeid,photourl,invoicetype,photodate,opendate,consumetype,billcode,invoicecode,
			pretax,invoicename,taxamount,amount,checkcode,salename,saleduty,saleplace,salebank,buyname,
			buyduty,buyplace,buybank,remarks,ctime,folderid';
            $res=$billinfo->getXq($where,$field);
            if($res['folderid']==0){
                $res['foldername']='我的发票夹';
            }else{
                $allfolder=$folder->getInfo(['id'=>$res['folderid']]);
                $res['foldername']=$allfolder['title'];
            }
            return $res;
        }else{
            return $this->json_error('添加失败');
        }
    }







    public function pdfaddtest(){
        $billinfo= new BillinfoModel();
        $electron = new ElectronModel();
        $folder = new FolderModel();
        $info['id']='32179';
        $request = Request();
        $data = $request->param();
        $array = [
            'uid' => $info['id'],
            'typeid' => 1,
            //'photourl'=>'',
            'invoicetype' => "电子发票",//发票种类
            'photodate' => date('Y-m-d', time()),//拍照时间
            'consumetype' => "服务",//消费类型
            'remarks' => '',//备注
            'ctime' => time()//创建时间
        ];
        $data['img']='./uploads/file/20201218/9442b600a94e6a9006656cf1b0d4acf5.pdf';
        //$imgth = $imgurl[$i];//保存的发票pdf的地址
        $imgth = $data['img'];//保存的发票pdf的地址
        $ph = substr($imgth, 1);//删除'./'
        //print_r($ph);exit;
        $resqq = readPDF($ph);
        //print_r($resqq);exit;
        if ($resqq == 0) {
            return $this->json_error('请检查发票链接能否正常打开！');
        }
        //$resqq='./uploads/file/20201218/9442b600a94e6a9006656cf1b0d4acf5.txt';
        $rest = array_filter($resqq);
       // print_r($rest);exit;
        for ($i = 0; $i < count($rest); $i++) {
            $array['folderid']=$data['folderid'];
            if (strpos($rest[$i], '发票代码:') !== false) {
                $array['invoicecode'] = substr(trim($rest[$i]), strripos(trim($rest[$i]), ":") + 1);

            }

            if (strpos($rest[$i], "发票号码:") !== false) {
                $array['billcode'] = substr(trim($rest[$i]), strripos(trim($rest[$i]), ":") + 1);

            }
            // print_r($array['billcode']);exit;
            if (strpos($rest[$i], '值税电子') !== false) {
                $array['invoicename'] = trim($rest[$i]);
            }
            if (strpos($rest[$i], '开票日期:') !== false) {
                $da = str_replace(' ', '', substr(trim($rest[$i]), strripos(trim($rest[$i]), ":") + 1));
                //print_r($da);exit;
                $array['opendate'] = date('Y-m-d', strtotime(str_replace(array('年', '月', '日'), array('-', '-', ''), $da)));
            }
            if (strpos($rest[$i], '校 验 码:') !== false) {
                $array['checkcode'] = str_replace(' ', '', substr(trim($rest[$i]), strripos(trim($rest[$i]), ":") + 1));
            }

            if (strpos($rest[$i], '小写') !== false) {
                //print_r("111");
                $q = substr(trim($rest[$i]), strripos(trim($rest[$i]), ")") + 1);
                $array['amount'] = preg_replace('%[^0-9.]%', '', str_replace('¥', '', $q));
            }
            if (strpos($rest[$i], '%') !== false) {
                if (strpos(trim($rest[$i]), ' ') !== false) {
                    $sl = str_replace('%', '', substr(trim($rest[$i]), strripos(trim($rest[$i]), " ") + 1));
                    $array['taxa'] = $sl / 100;
                    $array['pretax'] = substr(trim($rest[$i]), 0, strripos(trim($rest[$i]), " "));
                } else {
                    $sl = str_replace('%', '', trim($rest[$i]));
                    $array['taxa'] = $sl / 100;
                    $array['pretax'] = sprintf("%.2f", round($array['amount'] / (1 + $array['taxa']), 2));
                }
            }
            if($array['amount']==false){
                echo '111';
            }
            print_r($array['amount']);
            print_r($array['taxa']);
            print_r($array['pretax']);exit;
            $array['taxamount'] = $array['amount'] - $array['pretax'];
            if ($i < 50) {
                //销售方信心
                if (strpos($rest[$i], '称:') !== false) {
                    $array['salename'] = substr(trim($rest[$i]), strripos(trim($rest[$i]), ":") + 1);
                }
                if (strpos($rest[$i], '识别号: ') !== false) {
                    $array['saleduty'] = substr(trim($rest[$i]), strripos(trim($rest[$i]), ":") + 1);
                }
                if (strpos($rest[$i], '电 话: ') !== false) {
                    $array['saleplace'] = substr(trim($rest[$i]), strripos(trim($rest[$i]), ":") + 1);
                }
                if (strpos($rest[$i], '开户行及账号: ') !== false) {
                    $array['salebank'] = substr(trim($rest[$i]), strripos(trim($rest[$i]), ":") + 1);
                }
            }
            if ($i > 50) {
                //购买方信息
                if (strpos($rest[$i], '称:') !== false) {
                    $array['buyname'] = substr(trim($rest[$i]), strripos(trim($rest[$i]), ":") + 1);
                }
                if (strpos($rest[$i], '识别号: ') !== false) {
                    $array['buyduty'] = substr(trim($rest[$i]), strripos(trim($rest[$i]), ":") + 1);
                }
                if (strpos($rest[$i], '电 话: ') !== false) {
                    $array['buyplace'] = substr(trim($rest[$i]), strripos(trim($rest[$i]), ":") + 1);
                }
                if (strpos($rest[$i], '开户行及账号: ') !== false) {
                    $array['buybank'] = substr(trim($rest[$i]), strripos(trim($rest[$i]), ":") + 1);
                }
            }
            $array['source'] = '1';//来源PDF
        }
        print_r($array);exit;
        $jc=$billinfo->getInfo(['uid' => $info['id'], 'billcode' => $array['billcode'], 'state' => 0]);
        if ($jc) {
            //$billinfo->updatedatas(['source'=>1],['uid' => $info['id'], 'billcode' => $array['billcode'], 'state' => 0]);
            $aa = [
                'billid' => $jc['id'],
                'imgth' => $imgth,
                'ctime' => date('Y-m-d H:i:s', time())
            ];
            //上传oss
            /*$object=substr($imgth,2);
            $content=file_get_contents($imgth);
            oss::uploadfile($object,$content);*/

            $electron->adddata($aa);
            return $this->json_error('此发票已存在');
            //continue;
        }else{
            if($array['billcode']== false){
                $save_dir='./uploads/file/xlx/'.$info['id'].'/';
                $pdf='/home/wwwroot/fapiao/public/'.$ph;
                $photourl=ppng($pdf,$save_dir);//获取转换成png后的图片地址
                $da['img']=$photourl[0];
                $image=file_get_contents($da['img']);
                $fapiao=fapiao();
                $result=$fapiao->vatInvoice($image);//增值税发票专用
                if (array_key_exists('error_code',$result)) {
                    return $this->json_error('empty image');
                }
                if ($billinfo->getInfo(['uid'=>$info['id'],'billcode'=>$result['words_result']['InvoiceNum'],'state'=>0])) {
                    //此发票已存在
                    return $this->json_error('此发票已存在');
                }
                $req=$this->zzbill($result['words_result'],$info['id'],$da,$ph,$data['folderid']);
                //$req['photourl']=INLET_PATH . $req['photourl'];
                $req['photourl']=IMG_PATH . $req['photourl'];
                if ($req) {
                    return json_encode(['code'=>0,'msg'=>'添加成功','result'=>$req]);
                }else{
                    return $this->json_error('添加失败');
                }
            }
            $id = $billinfo->getAdddata($array);
            if ($id) {
                //记录PDF文件地址
                $aa = [
                    'billid' => $id,
                    'imgth' => $imgth,
                    'ctime' => date('Y-m-d H:i:s', time())
                ];
                //上传oss
                /* $object=substr($imgth,2);
                 $content=file_get_contents($imgth);
                 oss::uploadfile($object,$content);*/

                $electron->adddata($aa);
            } else {
                return $this->json_error('添加失败');
            }
        }
        //}
        return json_encode(['code'=>0,'msg'=>'添加成功']);
    }












}
?>