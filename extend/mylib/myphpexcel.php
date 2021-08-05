<?php
namespace mylib;


use PHPExcel_IOFactory;
use PHPExcel;
use PHPExcel_Worksheet;
use PHPExcel_Writer_Excel5;
use PHPExcel_Style_Alignment;

class myphpexcel{
	
	public static $daytime;
	public function __construct($daytime=''){
		
	}
	//导出设置顶部
	//参数title 导出文件的名称
	public static function setheader($title = '导出'){
		
		//导出
		$e = new \PHPExcel();
		
		$e->getProperties()->setCreator("ihb")
		->setLastModifiedBy("ihb")
		->setTitle($title)
		->setSubject($title)
		->setDescription("")
		->setKeywords($title)
		->setCategory("");
		return $e;
	}
	/*导出主体
	 * 参数：
	 * arr 表头的内容array("IDFA",'关键字','时间','手机版本','ip','渠道');
	 * list 格式array('sheet'=>'sheet','data'=>$lists);sheet工作表名称，data具体数据，应与arr内容对应
	 * e，导出对象
	 * */
	public static function setbody($list,$e,$arr){
		$clone = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P');
		//第一个sheetend
		foreach ($list as $ks=>$vs){
		
			$shee = $ks+1;
			$msgWorkSheet = new PHPExcel_Worksheet($e, $vs['sheet']); //创建一个工作表
			$e->addSheet($msgWorkSheet); //插入工作表
			$e->setActiveSheetIndex($shee); //切换到新创建的工作表
			$row = 1;
			foreach ($arr as $k=>$av){
				$e->getActiveSheet()->setCellValue($clone[$k].$row, $arr[$k]);
			}
			$hsize = 20;
			foreach ($vs['data'] as $kd=>$vd){
		
				$e->getActiveSheet()->getColumnDimension('A')->setWidth($hsize);
				$e->getActiveSheet()->getColumnDimension('B')->setWidth($hsize);
				$e->getActiveSheet()->getColumnDimension('C')->setWidth($hsize);
				$e->getActiveSheet()->getColumnDimension('D')->setWidth($hsize);
				$e->getActiveSheet()->getColumnDimension('E')->setWidth($hsize);
				$e->getActiveSheet()->getColumnDimension('F')->setWidth($hsize);
				$row = $kd+2;
				$dj = 0;
				$price = 0.00;
				
				foreach ($vd as $k=>$v){
					$e->getActiveSheet()->getStyle($clone[$k].$row)->getAlignment()->setWrapText(true);
					$e->getActiveSheet()->setCellValue($clone[$k].$row, $v);
		
				}
				foreach ($vd as $kk=>$vv){
					$e->getActiveSheet()->getStyle($clone[$kk].$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
					$e->getActiveSheet()->getStyle($clone[$kk].$row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
				}
				$row++;
		
			}
		}
		return $e;
	}
	//导出输出
	//参数title 导出文件的名称
	public static function setfooter($e,$title='导出'){

		$w = new PHPExcel_Writer_Excel5($e);
		
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
		header("Content-Type:application/force-download");
		header("Content-Type:application/vnd.ms-execl");
		header("Content-Type:application/octet-stream");
		header("Content-Type:application/download");
		$encoded_filename = urlencode($title);
		$ua = $_SERVER["HTTP_USER_AGENT"];
		if (preg_match("/MSIE/", $ua)) {
			header('Content-Disposition: attachment; filename="' . $encoded_filename . '.xls"');
		} else if (preg_match("/Firefox/", $ua)) {
			header('Content-Disposition: attachment; filename*="utf8\'\'' . $title . '.xls"');
		} else {
			header('Content-Disposition: attachment; filename="' . $title . '.xls"');
		}
		
		header("Content-Transfer-Encoding:binary");
		$w->save('php://output');
	}


	//开始
    /**
     * 数组转xls格式的excel文件
     * @param  array  $data      需要生成excel文件的数组
     * @param  string $filename  生成的excel文件名
     *      示例数据：
    $data = array(
    array(NULL, 2010, 2011, 2012),
    array('Q1',   12,   15,   21),
    array('Q2',   56,   73,   86),
    array('Q3',   52,   61,   69),
    array('Q4',   30,   32,    0),
    );
     */
    public static function export($data='',$title='test'){
        ini_set('max_execution_time', '0');//最长执行时间,php默认为30秒,这里设置为0秒的意思是保持等待直到程序执行完成

        $phpexcel = new PHPExcel();

        // Set properties 设置文件属性
        $properties = $phpexcel->getProperties();
        $properties->setCreator("Boge");//作者是谁 可以不设置
        $properties->setLastModifiedBy("Boge");//最后一次修改的作者
        $properties->setTitle($title);//设置标题
        $properties->setSubject('测试');//设置主题
        $properties->setDescription("备注");//设置备注
        $properties->setKeywords("关键词");//设置关键词
        $properties->setCategory("类别");//设置类别

        // 获取操作单元格对象
        $sheet = $phpexcel->getActiveSheet();
        //数组数据传递用到
        $sheet->fromArray($data);
        $sheet->setTitle('Sheet1');//设置sheet名称


        /**---------- 简单写入 ----------**/
        /*       $sheet ->setCellValue("A1","问卷标题");	//可以指定单元格位置
               $sheet ->setCellValue("A2","今天你吃了吗？");	//可以指定单元格位置*/


//        /**---------- 遍历写入数据 --------------**/
        // 准备数据
        /*        $data = array(			// 按照该结构封装即可
                    'cols' => array('姓名','班级','年龄'),
                    'rows' =>array(
                        array('小明','三年一班','10岁'),
                        array('小波','三年二班','30岁'),
                        array('小薛','三年三班','11岁'),
                    ),
                );

                // 提前定义好 列号映射数组
                $colArray = array('A','B','C'); // 如果列数大于 Z ，使用 AA-AZ  BA-BZ ... ZA-ZZ ；

                // 遍历表头
                foreach ($data['cols'] as $key => $col) {
                    $sheet ->setCellValue($colArray[$key].'1' , $col);
                }

                // 遍历数据
                foreach ($data['rows'] as $key => $row) {
                    $rowNum = $key+2; // 数据从第二行开始写，第一行是表头
                    foreach ($row as $key => $value) {
                        $sheet ->setCellValue($colArray[$key].$rowNum , $value);
                    }
                }*/


        //请求头与curl相似  数据以文件流传递
        $phpexcel->setActiveSheetIndex(0);
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment;filename=".$title.".xls");
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0
        $objwriter = PHPExcel_IOFactory::createWriter($phpexcel, 'Excel5');
        $objwriter->save('php://output');//导出文件
        exit;
    }
    /**
     * 数组转xls格式的excel文件
     * @param  array  $data      需要生成excel文件的数组
     * @param  string $filename  生成的excel文件名
     *      示例数据：
    $data = array(			// 按照该结构封装即可
    'cols' => array('姓名','班级','年龄'),
    'rows' =>array(
    array('小明','三年一班','10岁'),
    array('小波','三年二班','30岁'),
    array('小薛','三年三班','11岁'),
    ),
    );
     */
    //这个封装的方法值得学习一下，可以参看上边的export()方法看看规律
    public static function exportexcel($data,$title,$mu){
        ini_set('max_execution_time', '0');//最长执行时间,php默认为30秒,这里设置为0秒的意思是保持等待直到程序执行完成

        $phpexcel = new PHPExcel();

        // Set properties 设置文件属性
        $properties = $phpexcel->getProperties();
        $properties->setCreator("Boge");//作者是谁 可以不设置
        $properties->setLastModifiedBy("Boge");//最后一次修改的作者
        $properties->setTitle($title);//设置标题
        $properties->setSubject('测试');//设置主题
        $properties->setDescription("备注");//设置备注
        $properties->setKeywords("关键词");//设置关键词
        $properties->setCategory("类别");//设置类别
       $sheet = $phpexcel->getActiveSheet();
        $sheet->setTitle('Sheet1');//设置sheet名称

        //从A开始
        $startLetter = 'A';
        $rowNumber = 1;

        // 遍历表头
        foreach ($data['cols'] as $key => $col) {
            $sheet ->setCellValue($startLetter++.$rowNumber , $col);
        }
        ++$rowNumber;
        // 遍历数据
        $u=0;
        foreach ($data['rows'] as $key => $row) {
            $startLetter = 'A';
            $clone = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
            foreach ($row as $key => $value) {
                $n = $startLetter++;
                $sheet ->setCellValue($n.$rowNumber , $value);
                $sheet->getStyle('J6')->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                foreach ($clone as $kzm=>$vzm){
                    $sheet->getColumnDimension($vzm)->setWidth(12);
                }
                $sheet->getStyle('A1:Z1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
                $sheet->getStyle('A1:Z1')->getFill()->getStartColor() -> setRGB('16365c');
                // 设置单元格高度
                $sheet->getDefaultRowDimension()->setRowHeight(15);
                $sheet->getStyle('A1:Z1')->getFont()->getColor()->setRGB('FFFFFF');
                // 设置默认字体大小
                $sheet->getDefaultStyle()->getFont()->setSize(10);
                // 设置垂直居中
                $phpexcel->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $u += 1;
                $sheet->getStyle("A$u:Z$u")->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $sheet->getStyle("A$u:Z$u")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                //字体加粗
                $sheet->getStyle('A1:Z1')->getFont()->setBold(true);

            }
            ++ $rowNumber;
        }

        /*$phpexcel->getActiveSheet()->getStyle('E')->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_TEXT);
        $phpexcel->getActiveSheet()->getStyle('F')->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_TEXT);
        $phpexcel->getActiveSheet()->getStyle('L')->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_TEXT);
        $phpexcel->getActiveSheet()->getStyle('J')->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_TEXT);
        $phpexcel->getActiveSheet()->getStyle('P')->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_TEXT);
*/
        $phpexcel->setActiveSheetIndex(0);
        /*header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment;filename=".$title.".xls");
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0*/
        $objwriter = PHPExcel_IOFactory::createWriter($phpexcel, 'Excel2007');
        //$objwriter->save('php://output');
        //$_savePath = $mu.$title.'.xlsx';
        $objwriter->save($mu);
        //return $mu;
        //exit;
    }

    /**
     * 数组转xls格式的excel文件
     * @param  array  $data      需要生成excel文件的数组
     * @param  string $filename  生成的excel文件名
     *      示例数据：
    $data = array(			// 按照该结构封装即可
    'cols' => array('姓名','班级','年龄'),
    'rows' =>array(
    array('小明','三年一班','10岁'),
    array('小波','三年二班','30岁'),
    array('小薛','三年三班','11岁'),
    ),
    );
     */
    //这个封装的方法值得学习一下，可以参看上边的export()方法看看规律
    public static function exportfolder($data,$title,$mu){
        ini_set('max_execution_time', '0');//最长执行时间,php默认为30秒,这里设置为0秒的意思是保持等待直到程序执行完成

        $phpexcel = new PHPExcel();

        // Set properties 设置文件属性
        $properties = $phpexcel->getProperties();
        $properties->setCreator("Boge");//作者是谁 可以不设置
        $properties->setLastModifiedBy("Boge");//最后一次修改的作者
        $properties->setTitle($title);//设置标题
        $properties->setSubject('测试');//设置主题
        $properties->setDescription("备注");//设置备注
        $properties->setKeywords("关键词");//设置关键词
        $properties->setCategory("类别");//设置类别
        $sheet = $phpexcel->getActiveSheet();
        $sheet->setTitle('Sheet1');//设置sheet名称

        //从A开始
        $startLetter = 'A';
        $rowNumber = 1;

        // 遍历表头
        foreach ($data['cols'] as $key => $col) {
            $sheet ->setCellValue($startLetter++.$rowNumber , $col);
        }
        ++$rowNumber;
        // 遍历数据
        $u=0;
        foreach ($data['rows'] as $key => $row) {
            $startLetter = 'A';
            $clone = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF');
            foreach ($row as $key => $value) {
                $n = $startLetter++;
                $sheet ->setCellValue($n.$rowNumber , $value);
                $sheet->getStyle('J6')->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                foreach ($clone as $kzm=>$vzm){
                    $sheet->getColumnDimension($vzm)->setWidth(12);
                }
                $sheet->getStyle('A1:AF1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
                $sheet->getStyle('A1:AF1')->getFill()->getStartColor() -> setRGB('16365c');
                // 设置单元格高度
                $sheet->getDefaultRowDimension()->setRowHeight(15);
                $sheet->getStyle('A1:AF1')->getFont()->getColor()->setRGB('FFFFFF');
                // 设置默认字体大小
                $sheet->getDefaultStyle()->getFont()->setSize(10);
                // 设置垂直居中
                $phpexcel->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $u += 1;
                $sheet->getStyle("A$u:AF$u")->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $sheet->getStyle("A$u:AF$u")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                //字体加粗
                $sheet->getStyle('A1:AF1')->getFont()->setBold(true);

            }
            ++ $rowNumber;
        }
        $phpexcel->setActiveSheetIndex(0);
        $objwriter = PHPExcel_IOFactory::createWriter($phpexcel, 'Excel2007');
        $objwriter->save($mu);
    }







}