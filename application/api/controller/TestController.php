<?php
namespace app\api\controller;
use app\api\controller\base\BaseController;
use think\facade\Log;
use think\facade\Request;
use think\Exception;
use think\Db;
use app\common\model\UserModel;
use app\common\model\BillinfoModel;
use app\common\model\TakenotesModel;
use think\facade\Cache;
use GuzzleHttp\json_encode;
use PHPMailer\PHPMailer\PHPMailer;
use JPush\Client as JPush;
use Tomsgu\PdfMerger\PdfFile;
use Tomsgu\PdfMerger\PdfCollection;
use Tomsgu\PdfMerger\PdfMerger;
use setasign\Fpdi\Fpdi;
use Raulr\GooglePlayScraper\Scraper;



use mylib\curll;
class TestController extends BaseController
{

    //谷歌抓取
    public function guge(){
        $scraper = new Scraper();
        //$app = $scraper->getApp('com.mojang.minecraftpe');
        //$categories = $scraper->getCategories();
        //$collections = $scraper->getCollections();
        //$apps = $scraper->getList('topselling_free', 'SOCIAL');
        $apps = $scraper->getListChunk('topselling_free', 'SOCIAL', 20, 80);
        print_r($apps);exit;
    }








    //合并PDF没调通
    public function pdfhe(){

        $pdfCollection = new PdfCollection();
        $pdfCollection->addPdf('./4.pdf', PdfFile::ALL_PAGES, '');
        $pdfCollection->addPdf('./5.pdf', PdfFile::ALL_PAGES, '');
        $pdfCollection->addPdf('./3.pdf');

        //$merger = new PdfCollection();
        $fpdi = new Fpdi();
        while (ob_get_level()) ob_end_clean();
        header("Content-Encoding: None", true);
        $mer = new PdfMerger($fpdi);

        //print_r($mer);exit;
        //$merger->addPDF('samplepdfs/one.pdf', '1, 3, 4')->addPDF('samplepdfs/two.pdf', '1-2')->addPDF('samplepdfs/three.pdf', 'all')->merge('file', 'samplepdfs/TEST2.pdf');

        //$pdfCollection= $merger->addPdf('./2.pdf', PdfFile::ALL_PAGES,'')->addPdf('./1.pdf', PdfFile::ALL_PAGES, '');
        $mer->merge($pdfCollection, './6.pdf', PdfMerger::MODE_FILE, PdfFile::ORIENTATION_LANDSCAPE);
    }


    //PDF生成
    public function shengpdf(){
        //实例化
        $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        // 设置文档信息
        /*$pdf->SetCreator(PDF_CREATOR); //创建者
        $pdf->SetAuthor('ysc'); //文档作者
        $pdf->SetTitle('Order'); //文档标题
        $pdf->SetSubject('TCPDF Tutorial'); //定义文档主体
        $pdf->SetKeywords('Order'); //关键字
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, '', PDF_HEADER_STRING);// 设置页眉信息
        $pdf->setPrintHeader(true);  //是否打印页眉*/
        /*// 设置文档信息
        $pdf->SetCreator('Helloweba');
        $pdf->SetAuthor('yueguangguang');
        $pdf->SetTitle('Welcome to helloweba.com!');
        $pdf->SetSubject('TCPDF Tutorial');
        $pdf->SetKeywords('TCPDF, PDF, PHP');
        // 设置页眉和页脚信息
        $pdf->SetHeaderData('logo.png', 30, 'Helloweba.com', '致力于WEB前端技术在中国的应用',
            array(0,64,255), array(0,64,128));
        $pdf->setFooterData(array(0,64,0), array(0,64,128));
        // 设置页眉和页脚字体
        $pdf->setHeaderFont(Array('stsongstdlight', '', '10'));
        $pdf->setFooterFont(Array('helvetica', '', '8'));
        // 设置默认等宽字体
        $pdf->SetDefaultMonospacedFont('courier');
        // 设置间距
        $pdf->SetMargins(15, 27, 15);
        $pdf->SetHeaderMargin(5);
        $pdf->SetFooterMargin(10);
        // 设置分页
        $pdf->SetAutoPageBreak(TRUE, 25);
        // set image scale factor
        $pdf->setImageScale(1.25);
        // set default font subsetting mode
        $pdf->setFontSubsetting(true);
        //设置字体
        $pdf->SetFont('stsongstdlight', '', 14);
        $pdf->AddPage();
        $str1 = '欢迎来到Helloweba.com';
        $pdf->Write(0,$str1,'', 0, 'L', true, 0, false, false, 0);
        */
        $pdf->SetCreator('阿诺大脑研报预览');
        $pdf->SetAuthor('阿诺大脑研报预览');
        $pdf->SetTitle('中文');
        $pdf->SetSubject('阿诺大脑研报预览');
        $pdf->SetKeywords('TCPDF, PDF, PHP');
        // $logo_path =  $_SERVER['DOCUMENT_ROOT'].'upload/images/logo.png';
        $logo_path = 'http://fapiao.com/uploads/12.jpg';

        // 设置页眉和页脚信息
        $pdf->SetHeaderData( 'http://fapiao.com/uploads/12.jpg', 30, 'fapiao.com', '阿诺大脑研报预览', [0, 64, 255], [0, 64, 128]);
        $pdf->setFooterData([0, 64, 0], [0, 64, 128]);

        // 设置页眉和页脚字体
        $pdf->setHeaderFont(['stsongstdlight', '', '10']);
        $pdf->setFooterFont(['helvetica', '', '8']);

        // 设置默认等宽字体
        $pdf->SetDefaultMonospacedFont('courier');

        // 设置间距
        $pdf->SetMargins(15, 15, 15);//页面间隔
        $pdf->SetHeaderMargin(0);//页眉top间隔
        $pdf->SetFooterMargin(0);//页脚bottom间隔
        // remove default footer
        $pdf->setPrintFooter(false);

        // 设置分页
        $pdf->SetAutoPageBreak(true, 25);

        // set default font subsetting mode
        $pdf->setFontSubsetting(true);

        //设置图片缩放比例
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        //设置字体 stsongstdlight支持中文
        $pdf->SetFont('stsongstdlight', '', 14);

        // remove default header
        //$pdf->setPrintHeader(false);//移除头部信息

        //第一页
        // $pdf->AddPage();

        //设置背景色
        // $pdf->SetFillColor(52, 21, 0, 76);
        //  $pdf->Rect(0, 0, $pdf->getPageWidth(), $pdf->getPageHeight(), 'DF', "");

        //根据研报id获取对应的用户的所在的图片
        //$report_id = $request->get('report_id') ? $request->get('report_id') : 1;//研报id
        $list =  [['image'=>'http://fapiao.com/uploads/12.jpg']];
        if ($list){
            foreach ($list as $k=>$val){
                $pdf->AddPage();//添加一个页面
                $pdf->SetFillColor(255, 255, 255); //设置背景色
                $pdf->Rect(0, 0, $pdf->getPageWidth(), $pdf->getPageHeight(), 'DF', "");
                //图片显示 Palign：图片位置，L，偏左，C，居中，R，偏右
                $pdf->Image($val->image, '', '', 0, 0, '', '', 'center', false, '', 'C', false, false, 1, false, false, false);

            }
        }

        //输出PDF
        $pdf->Output('./t.pdf', 'D');//D下载I读取
    }



    //ppt转pdf
    public function pptpdf(){

    }












    /*
    * ss 可以改成数组'registration_id'=>['1','2']
    */
    public function jpush(){
        $app_key="";
        $master_secret="";
        $client = new JPush\Client($app_key, $master_secret);
        //$device->addTags($registration_id, 'tag');
        $client->push()
            ->setPlatform('all')
            //->addAllAudience(['registration_id'=>'ss'])
            ->setAudience(['registration_id'=>'ss'])
            ->setNotificationAlert('您有未报销的发票，请及时处理！')
            ->send();
    }



    public function abb(){
        $number='29409165';
        $date='2020-02-06';
        $extaxtotalfee='94339.62';
        $checkcode='';
        $code='012001900104';
        $res = curll::checkbill($number,$date,$extaxtotalfee,$checkcode,$code);
        if (!isset($res['type'])) {
            print_r("11111");
        }
        print_r($res);exit;
    }

    public function del(){
       // unlink("./uploads/file/xlx/32179/1502434924.zip");exit;
       $billinfo=new  BillinfoModel();
        $res=$billinfo->where('id', '>', 2)->where('id', '<', 33000)->where('state=1')->select();;
        //echo $billinfo->getLastSql();exit;
        for($i=0;$i<count($res);$i++){
            if(empty($res[$i]['photourl'])){
                unset($res[$i]);
                continue;
            }else{
                $img[]=$res[$i]['photourl'];
            }
        }
        print_r($img);exit;

        //unlink("./uploads/file/20200319/ab951b4bc66424b369bbe4b1773830ff.png");//删除图片
    }




    public function aa(){
        $res=array(
            'CommodityTaxRate' => Array (
                Array (
                    'word' => '6%',
                    'row' => '1'
                )
            )
        );
        $aws=$res['CommodityTaxRate'][0]['word'];
        //print_r($aws);exit;
        $sww=substr($aws,0,strrpos($aws,"%"))/100;
        print_r($sww);

    }





    public function ocr(){
        $res=fapio_token();
        return $res;
    }

    public function sas(){
        $result['words_result']['ticket_rates']="￥93.0元";
        $a = $result['words_result']['ticket_rates'];
        $dd=str_replace( array('￥','元') , array('','') , $a);
        //echo mb_substr($a,$b+1,$c-$b-1);
     //$aa=substr($result['words_result']['ticket_rates'],strpos($result['words_result']['ticket_rates'],"¥")+1,strpos($result['words_result']['ticket_rates'],"元")-strpos($result['words_result']['ticket_rates'],"¥")-1);
        print_r($dd);exit;
    }





    public function ocrq(){
        $res=fapiao();

        //$image = file_get_contents('./uploads/file/1.jpg'); // 图片地址 (远程地址https可用这个)增值税
        //$result = $res->receipt($image);
        //$image = file_get_contents('./uploads/file/20200420/809195efd72b277b3413ae9ec78ad668.png'); // 图片地址 (远程地址https可用这个)增值税
        //$image = file_get_contents('./uploads/file/20200302/60415847ec9521edbf4ebe14a9b0cf1e.jpg'); // 图片地址 (远程地址https可用这个)增值税

        //$image = file_get_contents('./uploads/file/20200415/57026a711303fe372d9bb6df733316ec.jpg'); // 图片地址 (远程地址https可用这个)--出租车票
        //$image = file_get_contents('./uploads/file/20200317/48987b1b9c4d7071d4c8bcf10228abc3.jpg'); // 图片地址 (远程地址https可用这个)--定额发票
        //$image = file_get_contents('./uploads/file/20200429/7247f1252a0f82522544c2c4c9266d14.jpg');//火车票
        //$image = file_get_contents('./uploads/file/20200604/0908a066c2303aa9c39809d570879ddb.jpg');//
        $image = file_get_contents('./uploads/file/20191119/image.jpg');//
        //$image = file_get_contents("http://fwwnsl.51taopiao.cn/api/captcha/image?t=1609248847134");//
//print_r($image);exit;
        //$result = $res->basicGeneral($image);

        //$result = $res->car($image); //出租车
        //$result = $res->inv($image); //定额
        $result = $res->basicGeneral($image);//--增值税
        //$result = $res->qrcode($image);
        //$result = $res->huoche($image);
       // $result = $res->basicGeneral($image);
        print_r($result);
    }
   

    /**
    * PDF2PNG
    * @param $pdf  待处理的PDF文件
    * @param $path 待保存的图片路径
    * @param $page 待导出的页面 -1为全部 0为第一页 1为第二页
    * @return      保存好的图片路径和文件名
    */
    public function ppng(){ 
        $pdf='/home/wwwroot/fapiao/public/./uploads/file/2020-04-29/as.pdf';
        $path='./uploads/file/xlx/';
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
           $filename = $path."/". md5($Key.time()).'.png';
           if($Var->writeImage($filename) == true){ 
               $Return[] = $filename; 
           } 
        } 
        print_r($Return);exit;
        return $Return; 
    }

    /*
    *功能：php完美实现下载远程图片保存到本地
    *参数：文件url,保存文件目录,保存文件名称，使用的下载方式
    *当保存文件名称为空时则使用远程文件原来的名称
    */
    function getImage(){
        //$url="https://einvoicelink.51fapiao.cn:8181/FPFX/actions/f0e83464cca850591643c0b660564a1604e281";
        $url="https://einvoicelink.51fapiao.cn:8181/FPFX/actions/f0e83464cca850591643c0b660564a1604e281";
        $save_dir='./uploads/file/'.date('Y-m-d');
        $filename='as.pdf';
        $type=1;
        if(trim($url)==''){
            return json_encode(['file_name'=>'','save_path'=>'','error'=>1]);
        }
        if(trim($save_dir)==''){
            $save_dir='./';
        }
        if(trim($filename)==''){//保存文件名
            $ext=strrchr($url,'.');
            if($ext!='.gif'&&$ext!='.jpg'){
                return json_encode(['file_name'=>'','save_path'=>'','error'=>3]);
            }
                $filename=time().$ext;
        }
        if(0!==strrpos($save_dir,'/')){
            $save_dir.='/';
        }
        //创建保存目录
        if(!file_exists($save_dir)&&!mkdir($save_dir,0777,true)){
            return json_encode(['file_name'=>'','save_path'=>'','error'=>5]);
        }
        //获取远程文件所采用的方法
        if($type==1){
            $ch=curl_init();
            $timeout=5;
            curl_setopt($ch,CURLOPT_URL,$url);
            curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
            curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
            $img=curl_exec($ch);
            curl_close($ch);
        }else{
            ob_start();
            readfile($url);
            $img=ob_get_contents();
            ob_end_clean();
        }
        //$size=strlen($img);
        //文件大小
        $fp2=@fopen($save_dir.$filename,'a');
        fwrite($fp2,$img);
        fclose($fp2);
        unset($img,$url);
        return json_encode(['file_name'=>$filename,'save_path'=>$save_dir.$filename,'error'=>0]);
    }


    //处理二维码数据
    public function er(){
        $str="01,04,012001800104,42247092,9433.96,20190710,56605089491318728361,F73F,";
        $res=explode(',', $str);
        if($res[1]=='04'){
            $res[1]="增值税普通发票";
        }elseif($res[1]=='01'){
            $res[1]="增值税专用发票";
        }elseif($res[1]=='10'){
            $res[1]="增值税电子普通发票";
        }
        $res[5]=substr($res[5],0,4).'-'.substr($res[5],4,2).'-'.substr($res[5],6);
        $array=[
            'uid'=>1,
            'typeid'=>1,
            'photourl'=>'',
            'invoicetype'=>$res[1],//发票种类
            'photodate'=>date('Y-m-d',time()),//拍照时间
            'opendate'=>$res[5],//开票时间
            'consumetype'=>"服务",//消费类型
            'billcode'=>$res[3],//发票号码
            'invoicecode'=>$res[2],//发票代码
            'pretax'=>$res[4],//税前金额
            'invoicename'=>$res[1],//发票名称
            'checkcode'=>$res[6],//校验码
            'ctime'=>time()//创建时间
        ];

       // $id=$billinfo->getAdddata($array);

        print_r($array);exit;
    }
    


    public function yasuo(){
        $file =[
            './uploads/file/20200302/65eb9f1d0113a493267902495069647d.jpg'
        ];
        $zipName = date('His') . mt_rand(1000, 9999) . '.zip'; //压缩包文件名
        $zipNameUrl = './uploads/file/xlx/' . $zipName; //文件路径
        // 生成文件
        $zip = new \ZipArchive();
        $zip->open($zipNameUrl, \ZipArchive::CREATE);
        foreach($file as $key => $v){
            //抓取图片内容
            $fileContent = file_get_contents($v);
            //添加图片
            $zip->addFromString(basename($v), $fileContent);
        }
        // 关闭
        $zip->close();
    }


    public function send_email()
    {
        $toemail='ysc322@163.com';//收件人邮箱
        $name='亲爱的Pedro，您好！';//接收邮件者名称
        $subject='QQ邮件发送测试';//邮件主题
        $content='恭喜你，邮件测试成功。';//邮件内容
        $attachment=array('./uploads/file/20191119/1.png');
        //调用方法发送邮件
        //send_mail($toemail,$name,$cpemail,$subject,$content,$attachment);
        dump(send_mail($toemail,$name,'',$subject,$content,$attachment));
    }

    //
    public function aass(){
        $to='ysc322@163.com';//收件人邮箱
        $title='QQ邮件发送测试';//邮件主题
        $content='恭喜你，邮件测试成功。';//邮件内容
        $res=$this->aasend($to,$title,$content);
        print_r($res);
    }

    //测试邮件
    public function aasend($to,$title,$content){
        $mail = new PHPMailer();           //实例化PHPMailer对象
        //是否启用smtp的debug进行调试 开发环境建议开启 生产环境注释掉即可 默认关闭debug调试模式
        $mail->SMTPDebug = 1;
        //使用smtp鉴权方式发送邮件
        $mail->isSMTP();
        //smtp需要鉴权 这个必须是true
        $mail->SMTPAuth=true;
        //链接qq域名邮箱的服务器地址
        $mail->Host = 'smtp.exmail.qq.com';
        //设置使用ssl加密方式登录鉴权
        $mail->SMTPSecure = 'ssl';
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        //设置ssl连接smtp服务器的远程服务器端口号，以前的默认是25，但是现在新的好像已经不可用了 可选465或587
        $mail->Port = 465;
        //设置发件人的主机域 可有可无 默认为localhost 内容任意，建议使用你的域名
        $mail->Hostname = '';

        //设置发送的邮件的编码 可选GB2312 我喜欢utf-8 据说utf8在某些客户端收信下会乱码
        $mail->CharSet = 'UTF-8';

        //设置发件人姓名（昵称） 任意内容，显示在收件人邮件的发件人邮箱地址前的发件人姓名
        $mail->FromName = 'ceshi';
        //smtp登录的账号 这里填入字符串格式的qq号即可
        $mail->Username ='fapiao@xman-aso.com';
        //smtp登录的密码 使用生成的授权码（就刚才叫你保存的最新的授权码）
        $mail->Password = 'Quannengwang2020';
        //设置发件人邮箱地址 这里填入上述提到的“发件人邮箱”
        $mail->From = 'fapiao@xman-aso.com';

        //邮件正文是否为html编码 注意此处是一个方法 不再是属性 true或false
        $mail->isHTML(true);

        //设置收件人邮箱地址 该方法有两个参数 第一个参数为收件人邮箱地址 第二参数为给该地址设置的昵称 不同的邮箱系统会自动进行处理变动 这里第二个参数的意义不大
        $mail->addAddress($to,$title);

        //添加多个收件人 则多次调用方法即可
        // $mail->addAddress('xxx@163.com','lsgo在线通知');

        //添加该邮件的主题
        $mail->Subject = $title;

        //添加邮件正文 上方将isHTML设置成了true，则可以是完整的html字符串 如：使用file_get_contents函数读取本地的html文件
        $mail->Body = $content;

        //为该邮件添加附件 该方法也有两个参数 第一个参数为附件存放的目录（相对目录、或绝对目录均可） 第二参数为在邮件附件中该附件的名称
        // $mail->addAttachment('./d.jpg','mm.jpg');
        //同样该方法可以多次调用 上传多个附件
        // $mail->addAttachment('./Jlib-1.1.0.js','Jlib.js');

        $status = $mail->send();

        //简单的判断与提示信息
        if($status) {
            return true;
        }else{
            return false;
        }

    }



    //写takenotes表
    public function taken(){
        $take= new TakenotesModel();
        $user= new UserModel();
        $res=$user->select();
        //print_r($res[0]->id);exit;
        for ($i=0;$i<count($res);$i++){
            $aaa=$take->getOne(['uid'=>$res[$i]->id]);
            if(!$aaa){
                $arr=['uid'=>$res[$i]->id,'ctime'=>strtotime($res[$i]->fromtime)];
                $take->adddata($arr);
            }else{
                continue;
            }
        }

    }

    //下载文件
    public function download()
    {
        //$famlePath = $_GET['resum'];
        $file_dir ='http://bill.ganbuguo.com/down/app-release.apk';    // 下载文件存放目录

        // 检查文件是否存在
        if (! file_exists($file_dir) ) {
            $this->error('文件未找到');
        }else{
            // 打开文件
            $file1 = fopen($file_dir, "r");
            // 输入文件标签
            Header("Content-type: application/octet-stream");
            Header("Accept-Ranges: bytes");
            Header("Accept-Length:".filesize($file_dir));
            Header("Content-Disposition: attachment;filename=" . $file_dir);
            ob_clean();     // 重点！！！
            flush();        // 重点！！！！可以清除文件中多余的路径名以及解决乱码的问题：
            //输出文件内容
            //读取文件内容并直接输出到浏览器
            echo fread($file1, filesize($file_dir));
            fclose($file1);
            exit();
        }
    }

    public function hc(){
        //header('Content-Type: text/html; charset=utf-8');
        $parser = new \Smalot\PdfParser\Parser();
        $pdf    = $parser->parseFile('/home/wwwroot/fapiao/public/./uploads/file/2020-04-29/as.pdf');
        //$pdf    = $parser->parseFile('/home/wwwroot/fapiao/public/./uploads/file/2020-04-29/12eaa7b53986ce53168ebda842482c27.png');
        //$pages  = $pdf->getPages();
        //$page   = $pages[0];
        //$this->SetFont('stsongstdlight', 'B', 10);
        //$fonts = $page->getFonts();
        //$pdf->SetFont('stsongstdlight','B', 20);
        $text = $pdf->getText();
        print_r($text);
       // $details  = $pdf->getDetails();
        //print_r($details);

        //echo $text;
    }








}
?>