<?php
namespace app\api\controller;
use app\api\controller\base\BaseController;
use think\facade\Log;
use think\facade\Request;
use think\Exception;
use think\Db;
use app\common\model\UserModel;
use app\common\model\BillinfoModel;
use app\common\model\ExportModel;
use app\common\model\ElectronModel;
use app\common\model\TakenotesModel;
use app\common\model\CheckvipModel;
use think\facade\Cache;
use GuzzleHttp\json_encode;
use PHPExcel;
use PHPExcel_IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PHPExcel_Worksheet;
use PHPExcel_Writer_Excel5;
use PHPExcel_Style_Alignment;
use mylib\myphpexcel;
use mylib\oss;
class ExportController extends BaseController
{

	//导出excle
	public function addexport(){
		$res=$this->chektoken();//检测用户token
		if(!$res){
			return json_encode(['code' =>2, 'msg'=>'请登录' ]);
		}
        $request = Request();
        $data = $request->param();
        $user= new UserModel();
        $billinfo= new BillinfoModel();
        $export= new ExportModel();
        if($data['email']==''){
            return $this->json_error('请输入邮箱');
        }
        if($data['email']!=$res['email']){
            $aa=['email'=>$data['email']];
            $user->updatedatas($aa,['id'=>$res['id']]);
            $res['email']=$data['email'];
        }
		$field='id,invoicetype,photodate,opendate,consumetype,billcode,invoicecode,pretax,taxamount,
			amount,checkcode,salename,saleduty,saleplace,salebank,buyname,buyduty,buyplace,buybank,
			remarks,checkbill,checktime,consumetype,specif,unit,number,unitprice,unittotal,taxa,unittax';
		if ($data['type']==1) {
            $where=['uid'=>$res['id'],'state'=>0,'soutype'=>0];
            $bill=$billinfo->getNumber($where);//发票总个数
            if($bill==0){
                return $this->json_error('无可导出发票！');
            }
            $billlist=$billinfo->getXqduo($where,$field);//获取当前文件夹下的所有发票的详细信息
		}else {
			//选择一个或多个发票
			$idas=explode(",",$data['id']);
            $bill=count($idas);
            if($bill==0){
                return $this->json_error('请选择要导出的发票');
            }
			$billlist=$billinfo->getExport($field,$idas);//获取所选发票的详细信息
		}
        for($i=0;$i<count($billlist);$i++){
            $rows[$i]=[
                'invoicetype'=>$billlist[$i]['invoicetype'],
                'photodate'=>$billlist[$i]['photodate'],
                'opendate'=>$billlist[$i]['opendate'],
                'consumetype'=>$billlist[$i]['consumetype'],
                'billcode'=>' '.$billlist[$i]['billcode'],
                'invoicecode'=>' '.$billlist[$i]['invoicecode'],
                'pretax'=>$billlist[$i]['pretax'],
                'taxamount'=>$billlist[$i]['taxamount'],
                'amount'=>$billlist[$i]['amount'],
                'checkcode'=>' '.$billlist[$i]['checkcode'],
                'salename'=>$billlist[$i]['salename'],
                'saleduty'=>' '.$billlist[$i]['saleduty'],
                'saleplace'=>$billlist[$i]['saleplace'],
                'salebank'=>$billlist[$i]['salebank'],
                'buyname'=>$billlist[$i]['buyname'],
                'buyduty'=>' '.$billlist[$i]['buyduty'],
                'buyplace'=>$billlist[$i]['buyplace'],
                'buybank'=>$billlist[$i]['buybank'],
                'specif'=>$billlist[$i]['specif'],
                'unit'=>$billlist[$i]['unit'],
                'number'=>$billlist[$i]['number'],
                'unitprice'=>$billlist[$i]['unitprice'],
                'unittotal'=>$billlist[$i]['unittotal'],
                'taxa'=>$billlist[$i]['taxa'],
                'unittax'=>$billlist[$i]['unittax'],
                'remarks'=>$billlist[$i]['remarks']
            ];
        }
        $time=date('Y-m-d',time());
        $rass=rand(1000,9999);
        //详细表格
        $head=['发票类型','拍照时间','开票时间','消费类型','发票号码','发票代码','税前金额','税额',
        '金额','校验码','销售方名称','销售方纳税人识别号','销售方地址电话','销售方开户行及账户','购买方名称','购买方纳税人识别号',
        '购买方地址电话','购买方开户行及账户','规格型号','单位','数量','单价','单品金额总和','税率','单品税额','备注'];
        $xname='xiangxi_'.$res['id'].$rass.'_'.$time;
        $bi=[
            'cols' =>$head,
            'rows' =>$rows
        ];
        $mu='./uploads/file/xlx/'.$res['id'].'/'.$xname.'.xlsx';
        $this->ec($bi,$xname,$mu);//print_r($a);exit;//生成详细表格
        $exp=[
            'uid'=>$res['id'],
            'data'=>json_encode($data),
            'excelxx'=>'./uploads/file/xlx/'.$res['id'].'/'.$xname.'.xlsx',
            'ctime'=>date('Y-m-d H:i:s',time())
        ];
        $dssa=$export->adddata($exp);
        if ($dssa) {
            $this->sendemail($exp,$res);
            return json_encode(['code'=>0,'msg'=>'发送成功']);
        }else{
            return $this->json_error('数据生成失败，请重新选择');
        }
	}


	public function ec($data,$title,$mu){
        $e = myphpexcel::exportexcel($data,$title,$mu);
        return $e;
    }


    //确认发送
    public function sendemail($data,$res){
        $take= new TakenotesModel();
        $checkvip= new CheckvipModel();
        $toemail=$res['email'];//收件人邮箱
        $name='亲爱的'.$res['nickname'].'，您好！';//接收邮件者名称
        $subject='发票查验导出邮件';//邮件主题
        $content='感谢使用本公司产品，产品正在不断优化，欢迎您提出宝贵意见！';//邮件内容
        $attachment=[$data['excelxx']];
        //调用方法发送邮件
        $cpemail=$data['cpemail'] ? $data['cpemail'] : '';
        $info=send_mail($toemail,$name,$cpemail,$subject,$content,$attachment);
        if($info==true){
            $take->getInc(['uid'=>$res['id']],'expnumber');
            $checkvip->adddata(['uid'=>$res['id'],'type'=>0,'ctime'=>date('Y-m-d H:i:s',time())]);
            return $this->json_success('发送成功');
        }else{
            return $this->json_error('发送失败');
        }
    }





    //PHPExcel测试用，有问题
    public function aa($fileName,$headArr = [], $data = []){
    	$fileName .= "_" . date("Y_m_d", Request::instance()->time()) . ".xls";
		$objPHPExcel = new \PHPExcel();
		$objPHPExcel->getProperties();
		$key = ord("A"); // 设置表头
		foreach ($headArr as $v) {
		    $colum = chr($key);
		    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($colum . '1', $v);
		    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($colum . '1', $v);
		    $key += 1;
		}
		$column = 2;
		$objActSheet = $objPHPExcel->getActiveSheet();
		foreach ($data as $key => $rows) { // 行写入
		    $span = ord("A");
		    foreach ($rows as $keyName => $value) { // 列写入
		        $objActSheet->setCellValue(chr($span) . $column, $value);
		        $span++;
		    }
		    $column++;
		}
		$fileName = iconv("utf-8", "gb2312", $fileName); // 重命名表
		$objPHPExcel->setActiveSheetIndex(0); // 设置活动单指数到第一个表,所以Excel打开这是第一个表
		header('Content-Type: application/vnd.ms-excel');
		header("Content-Disposition: attachment;filename='$fileName'");
		header('Cache-Control: max-age=0');
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output'); // 文件通过浏览器下载
		exit();
    }


    


    //Spreadsheet导出excel
    public function outdata($uid, $name, $data, $head, $keys){
    	
    	if(!is_dir('./uploads/file/xlx/'.$uid)){
		    mkdir(iconv("UTF-8", "GBK", './uploads/file/xlx/'.$uid),0777,true);
		}
        $count = count($head);  //计算表头数量
 
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
 
        for ($i = 65; $i < $count + 65; $i++) {     //数字转字母从65开始，循环设置表头：
            $sheet->setCellValue(strtoupper(chr($i)) . '1', $head[$i - 65]);
        }
 
        /*--------------开始从数据库提取信息插入Excel表中------------------*/
 
 
        foreach ($data as $key => $item) {             //循环设置单元格：
            //$key+2,因为第一行是表头，所以写到表格时   从第二行开始写
 
            for ($i = 65; $i < $count + 65; $i++) {     //数字转字母从65开始：
                $sheet->setCellValue(strtoupper(chr($i)) . ($key + 2), $item[$keys[$i - 65]]);
                $spreadsheet->getActiveSheet()->getColumnDimension(strtoupper(chr($i)))->setWidth(20); //固定列宽
                //$spreadsheet->getDefaultStyle()->getAlignment()->setWrapText(true);
                //$spreadsheet->getActiveSheet()->setCellValue('P', 'Percentage value:');
                //$spreadsheet->getActiveSheet()->getCell("P".($key + 2))->getStyle()->setQuotePrefix(true);
                //$spreadsheet->getActiveSheet()->getCell("L".($key + 2))->getStyle()->setQuotePrefix(true);
                //$spreadsheet->getActiveSheet()->getCell("J".($key + 2))->getStyle()->setQuotePrefix(true);
            }
 
        }
 
        //header('Content-Type: application/vnd.ms-excel');
        //header('Content-Disposition: attachment;filename="' . $name . '.xlsx"');
        //header('Cache-Control: max-age=0');
        $writer = new Xlsx($spreadsheet);
        $writer->save('./uploads/file/xlx/'.$uid.'/'.$name.'.xlsx');
 
        //删除清空：
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
        return;
    }




    //压缩成压缩包
    public function yasuo($uid,$zipName,$file){
        //$zipName = date('His') . mt_rand(1000, 9999) . '.zip'; //压缩包文件名
        ini_set ('memory_limit', '2G');
        $zipNameUrl = './uploads/file/xlx/'.$uid.'/'.$zipName; //文件路径
        if(!is_dir('./uploads/file/xlx/'.$uid)){
		    mkdir(iconv("UTF-8", "GBK", './uploads/file/xlx/'.$uid),0777,true);
		}
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


    //生成结算单
    public function exportqdjsdc($uid, $name, $data, $head, $keys){
        if(!is_dir('./uploads/file/xlx/'.$uid)){
            mkdir(iconv("UTF-8", "GBK", './uploads/file/xlx/'.$uid),0777,true);
        }
        $count = count($head);  //计算表头数量
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $spreadsheet->getActiveSheet()->mergeCells('A1:C1');
        $spreadsheet->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);      //第一行是否加粗
        $spreadsheet->getActiveSheet()->getStyle('A1')->getFont()->setSize(18);
        $spreadsheet->getActiveSheet()->getRowDimension('1')->setRowHeight(30);    //第一行行高
        $spreadsheet->getActiveSheet()->setCellValue('A1', '发票全能王结算单');
        for ($i = 65; $i < $count + 65; $i++) {     //数字转字母从65开始，循环设置表头：
            $sheet->setCellValue(strtoupper(chr($i)) . '2', $head[$i - 65]);
        }
        /*--------------开始从数据库提取信息插入Excel表中------------------*/
        foreach ($data as $key => $item) {             //循环设置单元格：
            //$key+2,因为第一行是表头，所以写到表格时   从第二行开始写
            for ($i = 65; $i < $count + 65; $i++) {     //数字转字母从65开始：
                $sheet->setCellValue(strtoupper(chr($i)) . ($key + 3), $item[$keys[$i - 65]]);
                $spreadsheet->getActiveSheet()->getColumnDimension(strtoupper(chr($i)))->setWidth(20); //固定列宽
            }
        }
        $writer = new Xlsx($spreadsheet);
        $writer->save('./uploads/file/xlx/'.$uid.'/'.$name.'.xlsx');
        //删除清空：
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
        return;
    }

    public function aaa(){
        $arrs = ['类别','发票张数','总计'];
        $title='jianhua';
        $keys=['invoicetype','billnumber','amountsum'];
        $outapp[]=['invoicetype'=>"总计",'billnumber'=>'1','amountsum'=>'12'];
        $e = myphpexcel::setheader($title);
        //封面excel
        $clone = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P');

        $shee = 1;
        $msgWorkSheet = new PHPExcel_Worksheet($e, '结算单'); //创建一个工作表
        $e->addSheet($msgWorkSheet); //插入工作表
        $e->setActiveSheetIndex($shee); //切换到新创建的工作表
        $styleThinBlackBorderOutline = array(
            'borders' => array(
                'allborders' => array( //设置全部边框
                    'style' => \PHPExcel_Style_Border::BORDER_THIN //粗的是thick
                ),

            ),
        );
        $outlineThinBlackBorderOutline = array(
            'borders' => array(
                'outline' => array( //设置全部边框
                    'style' => \PHPExcel_Style_Border::BORDER_THIN //粗的是thick
                ),

            ),
        );

        $e->getActiveSheet()->getDefaultStyle()->getFont()->setName('微软雅黑');
        $row = 1;
        $e->getActiveSheet()->getStyle('A1')->getAlignment()->setWrapText(true);
        $e->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $e->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);      //第一行是否加粗
        $e->getActiveSheet()->getStyle('A1')->getFont()->setSize(18);
        $e->getActiveSheet()->getRowDimension('1')->setRowHeight(30);    //第一行行高
        $e->getActiveSheet()->setCellValue('A1', 'i红包-结算单');
        $e->getActiveSheet()->mergeCells('A1:C1');
        $row++;

        //$arrs = ['产品名称','结算周期','合作方式','结算量级','单价(元)','合计金额（元）','结算金额（元）','备注'];
        foreach ($arrs as $k=>$av){
            $e->getActiveSheet()->setCellValue($clone[$k].$row, $av);
        }
        $row++;


        $hsize = 20;
        $hj = 0;
        $hjsl = 0;
        $hjhq = 0;
        foreach ($outapp as $kd=>$vd){
            $e->getActiveSheet()->getColumnDimension('A')->setWidth(25);
            $e->getActiveSheet()->getColumnDimension('B')->setWidth(12);
            $e->getActiveSheet()->getColumnDimension('C')->setWidth(10);
            $e->getActiveSheet()->getColumnDimension('D')->setWidth(10);
            $e->getActiveSheet()->getColumnDimension('E')->setWidth(12);
            $e->getActiveSheet()->getColumnDimension('F')->setWidth(12);
            $e->getActiveSheet()->getColumnDimension('G')->setWidth(12);

            $dj = 0;
            $price = 0.00;


            $arr = [$vd['invoicetype'],$vd['billnumber'],$vd['amountsum']];

            foreach ($arr as $k=>$v){
                $e->getActiveSheet()->getStyle($clone[$k].$row)->getAlignment()->setWrapText(true);
                $e->getActiveSheet()->setCellValue($clone[$k].$row, $v);

            }
            foreach ($arr as $kk=>$vv){
                $e->getActiveSheet()->getStyle($clone[$kk].$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                $e->getActiveSheet()->getStyle($clone[$kk].$row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            }
            $row++;

        }
        $row +=2;

        $e->getActiveSheet()->getStyle('D'.$row)->getFont()->setBold(true);
        $e->getActiveSheet()->setCellValue('D'.$row, $hjsl);
        $e->getActiveSheet()->setCellValue('A'.$row, '合计量级');
        $e->getActiveSheet()->getStyle('F'.$row)->getFont()->setBold(true);
        $e->getActiveSheet()->setCellValue('F'.$row, $hjhq);
        $row++;
        $e->getActiveSheet()->setCellValue('A'.$row, '开票金额');
        $e->getActiveSheet()->mergeCells('A'.$row.':A'.($row+1));
        $e->getActiveSheet()->setCellValue('B'.$row, '小写');
        $e->getActiveSheet()->setCellValue('B'.($row+1), '大写（人民币）');
        $e->getActiveSheet()->mergeCells('C'.$row.':G'.$row);
        $e->getActiveSheet()->mergeCells('C'.($row+1).':G'.($row+1));
        $e->getActiveSheet()->setCellValue('C'.$row, $hj);
        $e->getActiveSheet()->getStyle('C'.$row)->getFont()->setBold(true);
        $e->getActiveSheet()->setCellValue('C'.($row+1), numTrmb($hj));
        $e->getActiveSheet()->getStyle('C'.($row+1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        //设置边框
        $e->getActiveSheet()->getStyle( 'A1:G'.($row+1))->applyFromArray($styleThinBlackBorderOutline);

        $row++;
        $row++;
        $e->getActiveSheet()->mergeCells('A'.$row.':C'.($row));
        $e->getActiveSheet()->mergeCells('A'.$row.':C'.($row));
        $gsarr = ['甲方信息：'.$qddj['company_name'],
            ' 纳税人识别号：'.$qddj['duty_account'],
            '开户地址：'.$qddj['address'].'    '.$qddj['company_phone'],
            '开户银行：'.$qddj['bank'],
            '银行账号：'.$qddj['bank_account'],
            '数据确认盖章（公章或财务章）：','盖章（签字）：',
            ' 日期：        年     月    日'];
        $rows = $row;
        $e->getActiveSheet()->setCellValue('D'.$row, '乙方信息：');
        foreach ($gsarr as $kg=>$vg){

            $e->getActiveSheet()->setCellValue('A'.$row, $vg);

            $row++;
        }
        $e->getActiveSheet()->getStyle( 'A'.$rows.':C'.($row-1))->applyFromArray($outlineThinBlackBorderOutline);


        $e->getActiveSheet()->getStyle( 'D'.$rows.':G'.($row-1))->applyFromArray($outlineThinBlackBorderOutline);

        $arr = array("产品",'计划量级','完成量级','结算量级','时间','备注');
        $earr=[];
        $list[1] = array('sheet'=>'sheet','data'=>$earr);
        $e = myphpexcel::setbody($list, $e, $arr);
        myphpexcel::setfooter($e,$title);
    }

    //导出excle(2020-06-02)
    public function exportxin(){
        $res=$this->chektoken();//检测用户token
        if(!$res){
            return json_encode(['code' =>2, 'msg'=>'请登录' ]);
        }
        if($res['email']==''){
            return $this->json_error('请先绑定邮箱');
        }
        $request = Request();
        $data = $request->param();
        $export= new ExportModel();
        $billinfo= new BillinfoModel();
        $electron = new ElectronModel();
        $field='id,invoicetype,photourl,photodate,opendate,consumetype,billcode,invoicecode,pretax,taxamount,
			amount,checkcode,salename,saleduty,saleplace,salebank,buyname,buyduty,buyplace,buybank,name,
			idcard,frompl,topl,mileage,seattype,remarks,checkbill,checktime';
        if ($data['type']==1) {
            //选择文件夹
            if($data['folderid']==0){
                $where=['uid'=>$res['id'],'folderid'=>$data['folderid'],'state'=>0];
            }else{
                $where=['folderid'=>$data['folderid'],'state'=>0];
            }
            //获取文件夹中的发票数量
            $bill=$billinfo->getNumber($where);//发票总个数
            if($bill==0){
                return $this->json_error('当前发票夹内没有发票可用');
            }
            $billnumber=$billinfo->getBillFolder($where);//发票分类的个数及金额
            $billsum=$billinfo->getAmount($where);//发票夹总金额
            $billlist=$billinfo->getXqduo($where,$field);//获取当前文件夹下的所有发票的详细信息
        }else {
            //选择一个或多个发票
            $idas=explode(",",$data['id']);
            $bill=count($idas);
            if($bill==0){
                return $this->json_error('请选择要导出的发票');
            }
            $billnumber=$billinfo->getBillidas($idas);//发票分类的个数及金额
            $billsum=$billinfo->getAmountidas($idas);//所选发票总金额
            $billlist=$billinfo->getExport($field,$idas);//获取所选发票的详细信息
        }
        //压缩包

        //获取要压缩的发票图片
        for($i=0;$i<count($billlist);$i++){
            if($billlist[$i]['photourl'] != ''){
                //下载
                /*$object=substr($billlist[$i]['photourl'],2);
                $content=$billlist[$i]['photourl'];
                oss::donfile($object,$content);*/
               $imgurl= $this->ossdon($billlist[$i]['photourl']);
               // $file[]=$billlist[$i]['photourl'];
                $file[]=$imgurl;
            }
            $idpdf=$electron->getOne(['billid'=>$billlist[$i]['id']]);
            if($idpdf){
                //下载
                /*$object=substr($idpdf['imgth'],2);
                $content=$idpdf['imgth'];
                oss::donfile($object,$content);*/
                //$pdfp[]=$idpdf['imgth'];
                $pdfp[]=$idpdf['id'];
            }
            //处理导出文件后税号不正确
            // $rows[$i]['buyduty']='\''.$billlist[$i]['buyduty'];
            // $rows[$i]['saleduty']='\''.$billlist[$i]['saleduty'];
            $rows[$i]=[
                'invoicetype'=>$billlist[$i]['invoicetype'],
                'photodate'=>$billlist[$i]['photodate'],
                'opendate'=>$billlist[$i]['opendate'],
                'consumetype'=>$billlist[$i]['consumetype'],
                'billcode'=>' '.$billlist[$i]['billcode'],
                'invoicecode'=>' '.$billlist[$i]['invoicecode'],
                'pretax'=>$billlist[$i]['pretax'],
                'taxamount'=>$billlist[$i]['taxamount'],
                'amount'=>$billlist[$i]['amount'],
                'checkcode'=>' '.$billlist[$i]['checkcode'],
                'salename'=>$billlist[$i]['salename'],
                'saleduty'=>' '.$billlist[$i]['saleduty'],
                'saleplace'=>$billlist[$i]['saleplace'],
                'salebank'=>$billlist[$i]['salebank'],
                'buyname'=>$billlist[$i]['buyname'],
                'buyduty'=>' '.$billlist[$i]['buyduty'],
                'buyplace'=>$billlist[$i]['buyplace'],
                'buybank'=>$billlist[$i]['buybank'],
                'name'=>$billlist[$i]['name'],
                'idcard'=>$billlist[$i]['idcard'],
                'frompl'=>$billlist[$i]['frompl'],
                'topl'=>$billlist[$i]['topl'],
                'mileage'=>$billlist[$i]['mileage'],
                'seattype'=>$billlist[$i]['seattype'],
                'remarks'=>$billlist[$i]['remarks']
            ];
        }
        if ($pdfp) {
            $pdfp=implode(',', $pdfp);
        }else{
            $pdfp='';
        }
        //print_r($file);exit;
        if(!empty($file)){
            // print_r('11');exit;
            $zipName = date('His') . mt_rand(1000, 9999) . '.zip';
            $this->yasuo($res['id'],$zipName,$file);//生成压缩包
            $ya='./uploads/file/xlx/'.$res['id'].'/'.$zipName;
            //上传oss
            $object=substr($ya,2);
            $content=file_get_contents($ya);
            oss::uploadfile($object,$content);

        }else{
            $ya='';
        }
        $ss[]=['invoicetype'=>"总计",'billnumber'=>$bill,'amountsum'=>$billsum];//详情的统计可参照，后期添加
        $info=array_merge($billnumber,$ss);//简化表数据

        $time=date('Y-m-d',time());
        $rass=rand(1000,9999);
        //$this->exportexcel($res['id'],$info);exit;
        $headArr = ['类别','发票张数','总计'];
        $name='jianhua_'.$res['id'].$rass.'_'.$time;
        $keys=['invoicetype','billnumber','amountsum'];
        //详细表格
        $head=['发票类型','拍照时间','开票时间','消费类型','发票号码','发票代码','税前金额','税额',
            '金额','校验码','销售方名称','销售方纳税人识别号','销售方地址电话','销售方开户行及账户','购买方名称','购买方纳税人识别号',
            '购买方地址电话','购买方开户行及账户','姓名','身份证号','起始','结束','里程','座位类别','备注'];
        $xname='xiangxi_'.$res['id'].$rass.'_'.$time;
        $keysx=['invoicetype','photodate','opendate','consumetype','billcode','invoicecode','pretax','taxamount',
            'amount','checkcode','salename','saleduty','saleplace','salebank','buyname','buyduty','buyplace','buybank','name',
            'idcard','frompl','topl','mileage','seattype','remarks'];

        $bi=[
            'cols' =>$head,
            'rows' =>$rows
        ];
        if(count($rows)>100){
            $jin=1;//不让访问详细列表
        }else{
            $jin=0;
        }
        //$mu='/home/wwwroot/testfapiao/public/uploads/file/xlx/'.$res['id'].'/';
        $mu='./uploads/file/xlx/'.$res['id'].'/'.$xname.'.xlsx';
        //print_r($mu);exit;
        $this->ec($bi,$xname,$mu);//print_r($a);exit;//生成详细表格
        //$this->outdata($res['id'],$xname, $billlist, $head, $keysx);//生成详细表格
        $this->exportqdjsdc($res['id'],$name, $info, $headArr, $keys);//生成简化表格
        $exp=[
            'uid'=>$res['id'],
            'data'=>json_encode($data),
            'exceljh'=>'./uploads/file/xlx/'.$res['id'].'/'.$name.'.xlsx',
            'excelxx'=>'./uploads/file/xlx/'.$res['id'].'/'.$xname.'.xlsx',
            'compress'=>$ya,//压缩包地址
            'pdfplace'=>$pdfp,//pdf地址
            'ctime'=>date('Y-m-d H:i:s',time())
        ];
        $dssa=$export->gitdata($exp);
        if ($dssa) {
            $shuchu=[
                'email'=>$res['email'],
                'excel'=>$dssa,
                'jin'=>$jin,
                //'xiangxi'=>$billlist,
                'exceljh'=>'./uploads/file/xlx/'.$res['id'].'/'.$name.'.xlsx',
                'excelxx'=>'./uploads/file/xlx/'.$res['id'].'/'.$xname.'.xlsx',
                'compress'=>$ya,//压缩包地址
                'pdfplace'=>$pdfp,//pdf地址
            ];
            return json_encode(['code'=>0,'msg'=>'ok','result'=>$shuchu]);
        }else{
            return $this->json_error('数据生成失败，请重新选择');
        }
    }

    //读取excel
    public function readexcel(){
        $res=$this->chektoken();//检测用户token
        if(!$res){
            return json_encode(['code' =>2, 'msg'=>'请登录' ]);
        }
        $export= new ExportModel();
        $request = Request();
        $data = $request->param();
        //获取要读取的Excel地址
        $info=$export->getOne(['id'=>$data['excel']]);
        if($data['type']==1){
            //详细
            $url=$info['excelxx'];
        }else{
            //简单
            $url=$info['exceljh'];
        }
        $da = $this->import_excel($url);//execl的数据
        if($data['type']==0){
            array_shift($da);
            foreach ($da as $k=>$v){
                array_pop($v);
                $req[$k]['invoicetype']=$v[0];
                $req[$k]['billnumber']=$v[1];
                $req[$k]['amountsum']=$v[2];
            }
        }else{
            //array_shift($da);
            foreach ($da as $k=>$v){
                array_pop($v);
                $reqs=[
                    'invoicetype'=>$v[0],
                    'photodate'=>$v[1],
                    'opendate'=>$v[2],
                    'consumetype'=>$v[3],
                    'billcode'=>$v[4],
                    'invoicecode'=>$v[5],
                    'pretax'=>$v[6],
                    'taxamount'=>$v[7],
                    'amount'=>$v[8],
                    'checkcode'=>$v[9],
                    'salename'=>$v[10],
                    'saleduty'=>$v[11],
                    'saleplace'=>$v[12],
                    'salebank'=>$v[13],
                    'buyname'=>$v[14],
                    'buyduty'=>$v[15],
                    'buyplace'=>$v[16],
                    'buybank'=>$v[17],
                    'name'=>$v[18],
                    'idcard'=>$v[19],
                    'frompl'=>$v[20],
                    'topl'=>$v[21],
                    'mileage'=>$v[22],
                    'seattype'=>$v[23],
                    'remarks'=>$v[24]
                ];
                //$req[$k]=json_decode(str_replace('null','',json_encode($req[$k])),true);
                //$aa=json_encode($req[$k]);
                //print_r($aa);exit;
                $ree=str_replace('null','""',json_encode($reqs));
                stripslashes($ree);//去除转义字符
                $req[]=json_decode($ree,true);
            }
        }
        //$da=json_encode($da);
        return json_encode(['code'=>0,'msg'=>'ok','result'=>$req]);
    }




    function import_excel($file)
    {
        // 判断文件是什么格式
        $type = pathinfo($file);
        $type = strtolower($type["extension"]);
        if($type==='csv'){
            $type='csv';
        }elseif ($type==='xls'){
            $type='Excel5';
        }elseif ($type==='xlsx'){
            $type='Excel2007';
        }
        //$objPHPExcel = PHPExcel_IOFactory::load
        //$type=$type==='csv' ? $type : 'Excel5';     //这里需要与格式对应
        ini_set('max_execution_time', '0');

        // 判断使用哪种格式PHPExcel_IOFactory::load
        //$objReader = \PHPExcel_IOFactory::createReader($type);
        $objPHPExcel = \PHPExcel_IOFactory::load($file);
        $sheet = $objPHPExcel->getSheet(0);
        // 取得总行数
        //$highestRow = $sheet->getHighestRow();
        // 取得总列数
        //$highestColumn = $sheet->getHighestColumn();
        //循环读取excel文件,读取一条,插入一条


        $allColumn = $sheet->getHighestColumn();        //**取得最大的列号*/
        $allRow = $sheet->getHighestRow();        //**取得一共有多少行*/
        $ColumnNum = \PHPExcel_Cell::columnIndexFromString($allColumn);     // 列号 转 列数

        $data = array();
        for($rowIndex=2;$rowIndex<=$allRow;$rowIndex++){        //循环读取每个单元格的内容。注意行从1开始，列从A开始
            for($colIndex=0;$colIndex<=$ColumnNum;$colIndex++){
                $data[$rowIndex][] =$sheet->getCellByColumnAndRow($colIndex, $rowIndex)->getValue();
            }
        }
        return $data;
    }

    public function ossdon($url){
        //$url="./uploads/file/20200325/65847939.png";
        $object=substr($url,2);
        $th=pathinfo($url,PATHINFO_BASENAME);//获取名称及后缀！
        $content='./uploads/file/linshi/'.$th;
        oss::donfile($object,$content);
        return $content;
    }


    public function delsub(){
	    $directory='./uploads/file/linshi/20200820';
       $this->delDir($directory, $subdir = true);
    }

    /**
     * 删除目录（包括下面的文件）
     * @return void
     */
    function delDir($directory, $subdir = true) {
        if (is_dir($directory) == false) {
            return false;
        }
        $handle = opendir($directory);
        while (($file = readdir($handle)) !== false) {
            if ($file != "." && $file != "..") {
                is_dir("$directory/$file") ? delDir("$directory/$file") : unlink("$directory/$file");
            }
        }
        if (readdir($handle) == false) {
            closedir($handle);
            rmdir($directory);
        }
    }





}
?>