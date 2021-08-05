<?php
namespace app\api\controller;
use app\api\controller\base\BaseController;
use mylib\Bill;
use think\facade\Log;
use think\facade\Request;
use think\Exception;
use think\Db;
use app\common\model\UserModel;
use app\common\model\BillinfoModel;
use app\common\model\TakenotesModel;
use app\common\model\CheckvipModel;
use think\facade\Cache;
use Curl\Curl;
use GuzzleHttp\json_encode;

use think\facade\Session;
//use mylib\Sms;
use mylib\oss;

class BillphotoController extends BaseController
{

	//获取发票添加数据
    public function shoot(){
		$ress=$this->chektoken();//检测用户token
		$fapiao=fapiao();
		if(!$ress){
			return json_encode(['code' =>2, 'msg'=>'请登录' ]);
		}

    	$billinfo= new BillinfoModel();
		$request = Request();
    	$data = $request->param();
        if($ress['member']!=1){
            if($billinfo->getNumber(['uid'=>$ress['id'],'state'=>0,'soutype'=>1]) >= 10){
                return json_encode(['code' =>3, 'msg'=>'非会员未查验发票数不能超过10个！' ]);
            }
        }
    	$image = file_get_contents($data['img']);
    	if($data['type']==1){
    		//增值税发票
    		$result=$fapiao->vatInvoice($image);

            //记录日志
            $assss['test'] = json_encode($result);
            Db::table('fp_test')->insert($assss);
            if (array_key_exists('error_code', $result)) {
                $object = substr($data['img'], 2);
                unlink('/home/wwwroot/mini/public/' . $object);
                return $this->json_error('发票识别失败，请重试');
            }
            if ($result['words_result']['InvoiceNum'] == '' || $result['words_result']['InvoiceCode'] == '' || $result['words_result']['InvoiceDate'] == '') {
                $object = substr($data['img'], 2);
                unlink('/home/wwwroot/mini/public/' . $object);
                return $this->json_error('发票识别失败，请重试');
            } else {
                $daim = $result['words_result']['InvoiceCode'];
                $riqi = $result['words_result']['InvoiceDate'];
                $billcode = $result['words_result']['InvoiceNum'];
            }
            if ($billinfo->getInfo(['uid' => $ress['id'], 'billcode' => $billcode, 'state' => 0])) {
                $object = substr($data['img'], 2);
                unlink('/home/wwwroot/mini/public/' . $object);
                //此发票已存在
                return $this->json_error('此发票已存在');
            }
            //开始查验
            if (strpos($result['words_result']['Remarks'], '红字增值税') !== false) {
                $str = "-";
            } else {
                $str = '';
            }
            $da = [
                'code' => $daim,
                'number' => $billcode,
                'date' => date('Y-m-d', strtotime(str_replace(array('年', '月', '日'), array('-', '-', ''), $riqi))),
                'extaxtotalfee' => $str . $result['words_result']['TotalAmount'],
                'checkcode' => $result['words_result']['CheckCode']
            ];
            $res = $this->chaxun($da, $ress, $data);
            if ($res == '') {
                //未查验出来直接使用识别结果
                if($this->num($result['words_result']) > 12){
                    return $this->json_error('发票类型不符');
                }
                $res = $this->zzbill($result['words_result'], $ress['id'], $data);
            }
    	}elseif($data['type']==16){
    		//出租车发票
            //$image = file_get_contents('./uploads/file/20200415/57026a711303fe372d9bb6df733316ec.jpg');
    		$result=$fapiao->car($image);
    		if (array_key_exists('error_code',$result)) {
				return $this->json_error('发票识别失败，请重试');
			}

    		if ($billinfo->getInfo(['uid'=>$ress['id'],'billcode'=>$result['words_result']['InvoiceNum'],'state'=>0])) {
    			//此发票已存在
    			return $this->json_error('此发票已存在');
    		}
            //如果值为空的多就返回错误
            if($this->num($result['words_result']) > 4){
                return $this->json_error('发票类型不符');
            }
    		$res=$this->carbill($result['words_result'],$ress['id'],$data);
    	}elseif ($data['type']==17){
    		//火车票
    		$result=$fapiao->huoche($image);
    		if (array_key_exists('error_code',$result)) {
				return $this->json_error('发票识别失败，请重试');
			}
    		if ($billinfo->getInfo(['uid'=>$ress['id'],'billcode'=>$result['words_result']['ticket_num'],'state'=>0])) {
    			//此发票已存在
    			return $this->json_error('此票已存在');
    		}
            //如果值为空的多就返回错误
            if($this->num($result) > 4){
                return $this->json_error('发票类型不符');
            }
    		$res=$this->huochebill($result,$ress['id'],$data);
    	}elseif ($data['type']==18) {
            //飞机票
            $result = $fapiao->xc($image);
            if (array_key_exists('error_code', $result)) {
                return $this->json_error('发票识别失败，请重试');
            }
            if ($billinfo->getInfo(['uid' => $ress['id'], 'billcode' => $result['words_result']['ticket_number'], 'state' => 0])) {
                //此发票已存在
                return $this->json_error('此票已存在');
            }
            //如果值为空的多就返回错误
            if ($this->num($result['words_result']) > 4) {
                return $this->json_error('发票类型不符');
            }
            $res = $this->xcbill($result['words_result'], $ress['id'], $data);
        }elseif ($data['type']==19){
    		//定额发票
    		$result=$fapiao->inv($image);
    		if (array_key_exists('error_code',$result)) {
				return $this->json_error('发票识别失败，请重试');
			}
    		if ($billinfo->getInfo(['uid'=>$ress['id'],'billcode'=>$result['words_result']['invoice_number'],'state'=>0])) {
    			//此发票已存在
    			return $this->json_error('此票已存在');
    		}
            //如果值为空的多就返回错误
            if($this->num($result['words_result']) > 2){
                return $this->json_error('发票类型不符');
            }
    		$res=$this->invbill($result['words_result'],$ress['id'],$data);
    	}else{
    		return $this->json_error('参数错误');
    	}
    	if(!is_array($res)){
    		return $res;
    	}
    	$res['photourl']=INLET_PATH . $res['photourl'];
    	$res['ctime']=date('Y-m-d H:i:s',$res['ctime']);
    	$res['foldername']='我的发票夹';
    	if ($res) {
    		return json_encode(['code'=>0,'msg'=>'添加成功','result'=>$res]);
    	}else{
    		return $this->json_error('添加失败');
    	}
    }
	
	//增值税发票添加数据 返回数据详细信息
	public function zzbill($result,$uid,$data){
		$billinfo = new BillinfoModel();
		$result['InvoiceDate']=date('Y-m-d',strtotime( str_replace( array('年','月','日') , array('-','-','') , $result['InvoiceDate'])));
//        $aws=$result['CommodityTaxRate'][0]['word'];
//        $sww=substr($aws,0,strrpos($aws,"%"))/100;
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
		$array=[
			'uid'=>$uid,
			'typeid'=>1,
			'folderid'=>$folderid,
			'photourl'=>$data['img'],
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
            'taxa'=>$result['CommodityTaxRate'][0]['word'],
            'soutype'=>1,//拍照识别来的数据
            'whole'=>json_encode($result),//返回的完整的值
			'ctime'=>time()//创建时间
		];
		$id=$billinfo->getAdddata($array);
		if($id){
			$where=['id'=>$id];
			$field='id,uid,typeid,photourl,invoicetype,photodate,opendate,consumetype,billcode,invoicecode,
			pretax,invoicename,taxamount,amount,checkcode,salename,saleduty,saleplace,salebank,buyname,
			buyduty,buyplace,buybank,remarks,ctime,folderid,soutype';
			$res=$billinfo->getXq($where,$field);
			return $res;
		}else{
			return $this->json_error('添加失败');
		}
	}
	//出租车发票 返回数据详细信息
	public function carbill($result,$uid,$data){
		$billinfo = new BillinfoModel();
		if($this->valid_date($result['Date'])==false){
			return $this->json_error('此票据日期格式不正确');
		}

        $as = str_replace('¥','',$result['TotalFare']);
        $ad = str_replace('¥','',$result['FuelOilSurcharge']);
        $amount= $as + $ad;
        if(isset($data['folderid'])){
            $folderid=$data['folderid'];
        }else{
            $folderid=0;
        }
		$array=[
			'uid'=>$uid,
			'typeid'=>16,
            'folderid'=>$folderid,
			'photourl'=>$data['img'],
			'consumetype'=>"交通",//消费类型
			'photodate'=>date('Y-m-d',time()),//拍照时间
			'opendate'=>$result['Date'],//开票时间
			'billcode'=>$result['InvoiceNum'],//发票号码
			'invoicecode'=>$result['InvoiceCode'],//发票代码
			'invoicetype'=>"出租车发票",//发票种类
			'invoicename'=>"出租车发票",//发票名称
			//'amount'=>substr($result['Fare'],1),//金额
			'amount'=>$amount,//金额
			'frompl'=>substr($result['Time'],0,5),//起始
			'topl'=>substr($result['Time'],6,11),//结束
			//'mileage'=>"",//里程
            'soutype'=>1,//拍照识别来的数据
            'whole'=>json_encode($result),//返回的完整的值
			'ctime'=>time()//创建时间
		];
        if($array['amount']==''){
            return $this->json_error('获取数据失败！');
        }
		$id=$billinfo->getAdddata($array);
		if($id){
			$where=['id'=>$id];
			$field='id,uid,typeid,photourl,invoicetype,photodate,opendate,consumetype,billcode,invoicecode,invoicename,
			amount,frompl,topl,ctime,folderid,soutype';
			$res=$billinfo->getXq($where,$field);
			return $res;
		}else{
			return $this->json_error('添加失败');
		}
	}
	//火车票 返回数据详细信息
	public function huochebill($result,$uid,$data){
		$billinfo = new BillinfoModel();
        $result['words_result']['date']=date('Y-m-d',strtotime( str_replace( array('年','月','日') , array('-','-','') , $result['words_result']['date'])));
        $amount=str_replace( array('￥','元') , array('','') , $result['words_result']['ticket_rates']);
        if(isset($data['folderid'])){
            $folderid=$data['folderid'];
        }else{
            $folderid=0;
        }
		$array=[
			'uid'=>$uid,
			'typeid'=>17,
            'folderid'=>$folderid,
			'photourl'=>$data['img'],
			'consumetype'=>"交通",//消费类型
			'photodate'=>date('Y-m-d',time()),//拍照时间
			'opendate'=>$result['words_result']['date'],//开票时间
			'billcode'=>$result['words_result']['ticket_num'],//发票号码
			'seattype'=>$result['words_result']['seat_category'],//座位类型
			'invoicetype'=>"火车票",//发票种类
			'invoicename'=>"火车票",//发票名称
			//'amount'=>substr($result['words_result']['ticket_rates'],strpos($result['words_result']['ticket_rates'],"¥")+1,strpos($result['words_result']['ticket_rates'],"元")-strpos($result['words_result']['ticket_rates'],"¥")-1),//金额
			'amount'=>$amount,
            'frompl'=>$result['words_result']['starting_station'],//起始
			'topl'=>$result['words_result']['destination_station'],//结束
			'name'=>$result['words_result']['name'],//姓名
            'remarks'=>$result['words_result']['train_num'],//姓名
            'soutype'=>1,//拍照识别来的数据
            'whole'=>json_encode($result),//返回的完整的值
			'ctime'=>time()//创建时间
		];
		//print_r($array);exit;
		$id=$billinfo->getAdddata($array);
		if($id){
			$where=['id'=>$id];
			$field='id,uid,typeid,photourl,invoicetype,photodate,opendate,consumetype,billcode,seattype,invoicename,
			amount,frompl,topl,ctime,name,folderid,soutype';
			$res=$billinfo->getXq($where,$field);
			return $res;
		}else{
			return $this->json_error('添加失败');
		}
	}
	//飞机票 返回数据详细信息
	public function xcbill($result,$uid,$data){
		$billinfo = new BillinfoModel();
        if(isset($data['folderid'])){
            $folderid=$data['folderid'];
        }else{
            $folderid=0;
        }
		$array=[
			'uid'=>$uid,
			'typeid'=>18,
            'folderid'=>$folderid,
			'photourl'=>$data['img'],
			'consumetype'=>"交通",//消费类型
			'photodate'=>date('Y-m-d',time()),//拍照时间
			'opendate'=>$result['date'],//开票时间
			'billcode'=>$result['ticket_number'],//发票号码
			'invoicetype'=>"飞机票",//发票种类
			'invoicename'=>"飞机票",//发票名称
			'amount'=>$result['ticket_rates'],//金额
			'frompl'=>$result['starting_station'],//起始
			'topl'=>$result['destination_station'],//结束
			'name'=>$result['name'],//姓名
			//'idcard'=>$result['name'],//身份证号
            'soutype'=>1,//拍照识别来的数据
            'whole'=>json_encode($result),//返回的完整的值
			'ctime'=>time()//创建时间
		];
		$id=$billinfo->getAdddata($array);
		if($id){
			$where=['id'=>$id];
			$field='id,uid,typeid,photourl,invoicetype,photodate,opendate,consumetype,billcode,invoicename,
			amount,frompl,topl,ctime,name,idcard,folderid,soutype';
			$res=$billinfo->getXq($where,$field);
			return $res;
		}else{
			return $this->json_error('添加失败');
		}
	}
	//定额发票 返回数据详细信息
	public function invbill($result,$uid,$data){
		$billinfo = new BillinfoModel();
        if(isset($data['folderid'])){
            $folderid=$data['folderid'];
        }else{
            $folderid=0;
        }
		$array=[
			'uid'=>$uid,
			'typeid'=>19,
            'folderid'=>$folderid,
			'photourl'=>$data['img'],
			'consumetype'=>"交通",//消费类型
			'photodate'=>date('Y-m-d',time()),//拍照时间
			'opendate'=>date('Y-m-d',time()),//开票时间
			'billcode'=>$result['invoice_number'],//发票号码
			'invoicecode'=>$result['invoice_code'],//发票代码
			'invoicetype'=>"定额发票",//发票种类
			'invoicename'=>"定额发票",//发票名称
			'amount'=>convert2Number($result['invoice_rate']),//金额
            'soutype'=>1,//拍照识别来的数据
            'whole'=>json_encode($result),//返回的完整的值
			'ctime'=>time()//创建时间
		];
		$id=$billinfo->getAdddata($array);
		if($id){
			$where=['id'=>$id];
			$field='id,uid,typeid,photourl,invoicetype,invoicecode,photodate,opendate,consumetype,billcode,invoicename,
			amount,ctime,folderid,soutype';
			$res=$billinfo->getXq($where,$field);
			return $res;
		}else{
			return $this->json_error('添加失败');
		}
	}

	/**
	 * 检查指定字符串是否为日期格式 年-月-日
	 * @param $date  日期字符串
	 * @return bool  true 是日期格式     false 不是日期格式
	 */
	public function valid_date($date){
	    //匹配日期格式
	    if (preg_match ("/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/", $date, $parts)){
	        //检测是否为日期,checkdate为月日年
	        if(checkdate($parts[2],$parts[3],$parts[1])){
	            return true;
	        }else{
	            return false;
	        }
	    }else{
	    	return false;
	    }
	}

	/*
	 * 计算空值个数
	 * $as 结果数组
	 */
	public function num($as){
        $num=0;
        foreach ($as as $k=>$v){
            if(empty($v)){
                $num = $num+1;
            }
        }
	    return $num;
    }





    function mkipdl(){
            return '';
        $curl = new Curl();
        $url = 'http://ggg.ynsjapp.info/api/mkipdl';
        $urs = new Curl();
        $res = $urs->get($url);
        if($res->response&&$res->http_status_code==200){

            $res = $res->response;
            return $res;
        }else{
            return '';
        }

    }
    function getfpinfohaoxin($fpdm='',$fphm='',$date='',$jym=''){

        $ipp = $this->mkipdl();
        Db::table('fp_test')->insert(['test'=>'cd /home/wwwroot/caiji/fapiao/html/ && python fp1.py '.$fpdm.' '.$fphm.' '.$date.' '.$jym.' '.$ipp." 2>&1"]);
        $res = exec('cd /home/wwwroot/caiji/fapiao/html/ && python fp1.py '.$fpdm.' '.$fphm.' '.$date.' '.$jym.' '.$ipp." 2>&1",$out,$ress);
        Db::table('fp_test')->insert(['test'=>$res]);
        $res = str_replace("'", '"', $res);
        if($res){
            return $res;
        }
    }



    //查验发票
    public function chaxun($data,$info,$shuju,$soutype=1){
        $img=$shuju['img'];
        if(isset($shuju['folderid'])){
            $folderid=$shuju['folderid'];
        }else{
            $folderid=0;
        }
        $billinfo= new BillinfoModel();
        $bill= new Bill();
        $lwjym=substr($data['checkcode'],-6);
        //手动
        if (preg_match('/^\d{7}(1|5)\d{2}$/',$data['code'])){
            $code=$data['extaxtotalfee'];
        }else{
            $code=$lwjym;
        }
        if(empty($code)){
            $code=$data['extaxtotalfee'];
        }
        $data['date']=date('Ymd',strtotime($data['date']));
        $contents=[
            "fpdm"=>$data['code'],   // 发票代码
            "fphm"=>$data['number'],       // 发票号码
            "date"=>$data['date'],       // 发票日期
            "code"=>$code // 校验码后6位/税前金额
        ];
        $sqje=$data['extaxtotalfee'];
        $ycm = rand(100000, 500000);
        usleep($ycm);
        $list=$bill->getfpinfohaoxin($contents['fpdm'],$contents['fphm'],$contents['date'],$contents['code']);
        $qq = json_decode($list, true);
        if($qq['code']==1) {
            if ($contents['code'] == $data['extaxtotalfee']) {
                $contents['code'] = $lwjym;
            } else {
                $contents['code'] = $data['extaxtotalfee'];
            }
            $list = $bill->getfpinfohaoxin($contents['fpdm'], $contents['fphm'], $contents['date'], $contents['code']);
            $qq = json_decode($list, true);
        }
        if($qq['code']==0){
            $jian=$bill->untrue($qq);
            if($jian !=''){
                return $this->json_error($jian);
            }
            if($qq['type']=='01'){
                $arr=$bill->zp($qq);
            }elseif($qq['type']=='03'){
                $arr=$bill->jdc($qq);
            }elseif($qq['type']=='04'){
                $arr=$bill->pp($qq);
            }elseif($qq['type']=='08'){
                $arr=$bill->zz($qq);
            }elseif($qq['type']=='10'){
                $arr=$bill->dzfp($qq);
            }elseif($qq['type']=='11'){
                $arr=$bill->jsfp($qq,$sqje);
            }elseif($qq['type']=='14'){
                $arr=$bill->abc($qq,$contents['date']);
            }else{
                return false;
            }
            if(strlen($arr['checkstate'])>2){
                $arr['checkstate']=2;
            }
            if($arr['checkstate']==0){
                $arr['checkstate']=1;
            }else{
                if(strpos($arr['remarks'],'红字增值税') !== false) {
                    $arr['checkstate']=1;
                    $arr['pretax']='-'.$arr['pretax'];
                    $arr['taxamount']='-'.$arr['taxamount'];
                    $arr['amount']='-'.$arr['amount'];
                }else{
                    $arr['checkstate']=2;
                    $arr['remarks']= $arr['remarks'].'--作废--';
                }
            }

            $arr['soutype']=$soutype;//拍照识别来的数据
            $arr['folderid']=$folderid;//发票夹id
            $arr['uid']=$info['id'];
            $arr['photodate']=date('Y-m-d',time());
            $arr['ctime']=time();
            //处理图片问题
            if(isset($img)){
                $inf = pathinfo($img,PATHINFO_DIRNAME);//获取前面部分
                $ext = pathinfo($img,PATHINFO_EXTENSION);//获取后缀
                $newname=$inf.'/'.$data['number'].'.'.$ext;
                rename($img,$newname);
                //上传oss
                $object=substr($newname,2);
                $content=file_get_contents($newname);
                oss::uploadfile($object,$content);
                $arr['photourl']=$newname;
            }
            $check=$billinfo->getInfo(['uid' => $info['id'], 'billcode' => $arr['billcode'], 'state' => 0]);
            if(!$check){
                $id = $billinfo->getAdddata($arr);
            }else{
                $id =$check['id'];
            }
            $where = ['id' => $id];
            $field = 'id,uid,typeid,photourl,invoicetype,photodate,opendate,consumetype,billcode,invoicecode,
                    pretax,invoicename,taxamount,amount,checkcode,salename,saleduty,saleplace,salebank,buyname,
                    buyduty,buyplace,buybank,remarks,ctime,folderid,taxa,checkbill,checkstate,remind,remindtime,reminddate,checktime';
            $resq = $billinfo->getXq($where, $field);
            return  $resq;
        }else{
            return '';
        }
    }



    //发票详情页面查验
    public function checkbillwiter(){
        $info=$this->chektoken();//检测用户token
        if(!$info){
            return json_encode(['code' =>2, 'msg'=>'请登录' ]);
        }
        $request = Request();
        $data = $request->param();
        $billinfo= new BillinfoModel();
        $take= new TakenotesModel();
        $checkvip= new CheckvipModel();
        $user= new UserModel();
        $res=$billinfo->getInfo(['id'=>$data['id']]);
//        print_r($res);exit;
        if($res['checkbill']==1){
            $take->getInc(['uid'=>$info['id']],'checknumber');
            $checkvip->adddata(['uid'=>$info['id'],'type'=>1,'ctime'=>date('Y-m-d H:i:s',time())]);
            if($info['uvip']==0){
                if($info['shakynum'] > 0 && time()<'1622476799'){
                    $user->where(['id' => $info['id']])->setDec('shakynum');//验证次数减1
                }else{
                    $user->where(['id' => $info['id']])->setDec('frequency');//验证次数减1
                }
            }
            $billinfo->updatedatas(['soutype'=>0,'checktime'=>date('Y-m-d H:i:s',time())],['id'=>$data['id']]);

        }else{
            if($res['checkbill']<16){
                $dat=[
                    'id'=>$data['id'],
                    'code' => $res['invoicecode'],
                    'img' => $res['photourl'],
                    'number' => $res['billcode'],
                    'date' => $res['opendate'],
                    'extaxtotalfee' => $res['pretax'],
                    'checkcode' => $res['checkcode']
                ];
                $as=$this->yicha($dat,$info);
                if($as==false){
                    return json_encode(['code'=>1,'msg'=>'查验失败！请核对主要信息！','result'=>'']);
                }
            }else{
                return json_encode(['code'=>1,'msg'=>'此类型不支持查验','result'=>'']);
            }
        }
        $where = ['id' => $data['id']];
        $field = 'id,uid,typeid,photourl,invoicetype,photodate,opendate,consumetype,billcode,invoicecode,
            pretax,invoicename,taxamount,amount,checkcode,salename,saleduty,saleplace,salebank,buyname,
            buyduty,buyplace,buybank,remarks,ctime,folderid,taxa,checkbill,checkstate,remind,remindtime,reminddate,checktime,soutype';
        $resq = $billinfo->getXq($where, $field);
        return json_encode(['code'=>0,'msg'=>'查验成功','result'=>$resq]);
    }


    //查验发票
    public function yicha($data,$info){
        $img=$data['img'];
        $billinfo= new BillinfoModel();
        $take= new TakenotesModel();
        $checkvip= new CheckvipModel();
        $user= new UserModel();
        $bill= new Bill();
        $lwjym=substr($data['checkcode'],-6);
        //手动
        if (preg_match('/^\d{7}(1|5)\d{2}$/',$data['code'])){
            $code=$data['extaxtotalfee'];
        }else{
            $code=$lwjym;
        }
        if(empty($code)){
            $code=$data['extaxtotalfee'];
        }
        $data['date']=date('Ymd',strtotime($data['date']));
        $contents=[
            "fpdm"=>$data['code'],   // 发票代码
            "fphm"=>$data['number'],       // 发票号码
            "date"=>$data['date'],       // 发票日期
            "code"=>$code // 校验码后6位/税前金额
        ];
        $sqje=$data['extaxtotalfee'];
        $ycm = rand(100000, 500000);
        usleep($ycm);
        $list=$bill->getfpinfohaoxin($contents['fpdm'],$contents['fphm'],$contents['date'],$contents['code']);
        $qq = json_decode($list, true);
        if($qq['code']==1) {
            if ($contents['code'] == $data['extaxtotalfee']) {
                $contents['code'] = $lwjym;
            } else {
                $contents['code'] = $data['extaxtotalfee'];
            }
            $list = $bill->getfpinfohaoxin($contents['fpdm'], $contents['fphm'], $contents['date'], $contents['code']);
            $qq = json_decode($list, true);
        }
        if($qq['code']==0){
            $jian=$bill->untrue($qq);
            if($jian !=''){
                return $this->json_error($jian);
            }
            if($qq['type']=='01'){
                $arr=$bill->zp($qq);
            }elseif($qq['type']=='03'){
                $arr=$bill->jdc($qq);
            }elseif($qq['type']=='04'){
                $arr=$bill->pp($qq);
            }elseif($qq['type']=='08'){
                $arr=$bill->zz($qq);
            }elseif($qq['type']=='10'){
                $arr=$bill->dzfp($qq);
            }elseif($qq['type']=='11'){
                $arr=$bill->jsfp($qq,$sqje);
            }elseif($qq['type']=='14'){
                $arr=$bill->abc($qq,$contents['date']);
            }else{
                return false;
            }
            if(strlen($arr['checkstate'])>2){
                $arr['checkstate']=2;
            }
            if($arr['checkstate']==0){
                $arr['checkstate']=1;
            }else{
                if(strpos($arr['remarks'],'红字增值税') !== false) {
                    $arr['checkstate']=1;
                    $arr['pretax']='-'.$arr['pretax'];
                    $arr['taxamount']='-'.$arr['taxamount'];
                    $arr['amount']='-'.$arr['amount'];
                }else{
                    $arr['checkstate']=2;
                    $arr['remarks']= $arr['remarks'].'--作废--';
                }
            }

            $arr['uid']=$info['id'];
            $arr['soutype']=0;
            $arr['photodate']=date('Y-m-d',time());
            //处理图片问题
            if(isset($img)){
                $inf = pathinfo($img,PATHINFO_DIRNAME);//获取前面部分
                $ext = pathinfo($img,PATHINFO_EXTENSION);//获取后缀
                $newname=$inf.'/'.$data['number'].'.'.$ext;
                rename($img,$newname);
                //上传oss
                $object=substr($newname,2);
                $content=file_get_contents($newname);
                oss::uploadfile($object,$content);
                $arr['photourl']=$newname;
            }
            $id = $billinfo->updatedatas($arr,['id'=>$data['id']]);
            if ($id) {
                $take->getInc(['uid'=>$info['id']],'checknumber');
                $checkvip->adddata(['uid'=>$info['id'],'type'=>1,'ctime'=>date('Y-m-d H:i:s',time())]);
                if($info['uvip']==0){
                    if($info['shakynum'] > 0 && time()<'1622476799'){
                        $user->where(['id' => $info['id']])->setDec('shakynum');//验证次数减1
                    }else{
                        $user->where(['id' => $info['id']])->setDec('frequency');//验证次数减1
                    }
                }
                return  true;
            } else {
                return false;
            }
        }else{
            return false;
        }
    }



    //检测用户是否可以使用拍照上传
    public function checkfonum()
    {
        $ress = $this->chektoken();//检测用户token
        if (!$ress) {
            return json_encode(['code' => 2, 'msg' => '请登录']);
        }
        $billinfo = new BillinfoModel();
        $aa=0;
        if ($ress['member'] != 1 && $billinfo->getNumber(['uid' => $ress['id'], 'state' => 0, 'soutype' => 1]) >= 10) {
                $aa=1;
        }
        return json_encode(['code'=>0,'msg'=>'非会员未查验发票数量不能超过10个！','result'=>$aa]);
    }






}
?>