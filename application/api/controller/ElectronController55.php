<?php
namespace app\api\controller;
use app\api\controller\base\BaseController;
use think\facade\Log;
use think\facade\Request;
use think\Exception;
use think\Db;
use Curl\Curl;
use app\common\model\UserModel;
use app\common\model\TakenotesModel;
use app\common\model\XiaowuModel;
use app\common\model\CheckvipModel;
use app\common\model\BillinfoModel;
use app\common\model\ElectronModel;
use app\common\model\FolderModel;
use app\common\model\HistoryModel;
use think\facade\Cache;
use GuzzleHttp\json_encode;

use mylib\curll;
class ElectronController extends BaseController
{
	function ipproxy(){

		$ipp = Db::table('fp_ipproxy')->find();
		//var_dump($ipp);
		if($ipp['endtime']<time()||!$ipp){
			$ip = file_get_contents('http://t.ipjldl.com/index.php/api/entry?method=proxyServer.generate_api_url&packid=1&fa=0&fetch_key=&groupid=0&qty=1&time=1&pro=&city=&port=1&format=txt&ss=1&css=&dt=1&specialTxt=3&specialJson=&usertype=2');
			//var_dump($ip);
			Db::table('fp_ipproxy')->where('1=1')->delete();
			$d['ip'] = $ip;
			$d['addtime'] = time();
			$d['endtime'] = time()+60;
			Db::table('fp_ipproxy')->insert($d);
		}
	}

    //ip代理生成
    function mkipdl(){
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

	function tests(){
		echo 'aa';exit;
		
	}
	function test(){
		//cd /home/wwwroot/caiji/fapiao/ && python fp3.py 1100201130 18268336 20201026 94339.62 111 2>&1
		$res = $this->getfpinfohaoxin('1100201130','18268336','20201026','94339.62');
		var_dump($res);
	}
	//
//	function getfpinfohaoxin($fpdm='',$fphm='',$date='',$jym=''){
//
//		Db::table('fp_test')->insert(['test'=>'cd /home/wwwroot/caiji/fapiao/html/ && python fp1.py '.$fpdm.' '.$fphm.' '.$date.' '.$jym.' '.$ipp['ip']." 2>&1"]);
//
//		//$this->ipproxy();
//		$ipp = Db::table('fp_ipproxy')->find();
//		$ipp['ip'] = 111;
//		$res = exec('cd /home/wwwroot/caiji/fapiao/html/ && python fp1.py '.$fpdm.' '.$fphm.' '.$date.' '.$jym.' '.$ipp['ip']." 2>&1",$out,$ress);
//		//var_dump('cd /home/wwwroot/caiji/fapiao/html/ && python fp1.py '.$fpdm.' '.$fphm.' '.$date.' '.$jym.' '.$ipp['ip']." 2>&1");
//		//var_dump('aa');exit;
//
//		Db::table('fp_test')->insert(['test'=>$res]);
//		$res = str_replace("'", '"', $res);
//
//		if($res){
//			return $res;
//		}
//	}
	
    //电子发票链接
    public function billlink(){
		$billinfo= new BillinfoModel();
        $electron = new ElectronModel();
        $folder = new FolderModel();
		$info=$this->chektoken();//检测用户token
		if(!$info){
			return json_encode(['code' =>2, 'msg'=>'请登录' ]);
		}
		$request = Request();
    	$data = $request->param();
		$url=$data['imgurl'];
        $timetxt = $info['id'].":"."--".$url."---".date('Y-m-d')."\r\n";
        // 写日志
        $bruce=fopen("/home/wwwroot/fapiao/public/uploads/file/dianzi.txt","ab+"); //打开日志文件
        if(!$bruce)
        {
           echo'文件不存在';
           exit;
        }
        fwrite($bruce,$timetxt);
        fclose($bruce);
		$save_dir='./uploads/file/xlx/'.$info['id'].'/';
		$filename= time().'.pdf';
		$img=getImage($url,$save_dir,$filename);//处理远程图片
		$res=json_decode($img,true);
        $array=[
            'uid'=>$info['id'],
            'typeid'=>1,
            //'photourl'=>'',
            'invoicetype'=>"电子发票",//发票种类
            'photodate'=>date('Y-m-d',time()),//拍照时间
            'consumetype'=>"服务",//消费类型
            'remarks'=>'',//备注
            'ctime'=>time()//创建时间
        ];
		if($res['error']==0){
			$imgth=$res['save_path'];//保存的发票pdf的地址
			$ph=substr($imgth,2);//删除'./'
            //print_r($ph);exit;
            //$pdf='/home/wwwroot/fapiao/public/'.$ph;
            //$photourl=ppng($pdf,$save_dir);//获取转换成png后的图片地址
            //print_r($photourl);exit;
            //$array['photourl']=$photourl[0];
            $resqq=readPDF($ph);
            if($resqq==0){
                return $this->json_error('请检查发票链接能否正常打开！');
            }
            $rest=array_filter($resqq);
            for($i=0;$i<count($rest);$i++){
                if(strpos($rest[$i],'发票代码:') !== false){
                    $array['invoicecode']= substr(trim($rest[$i]),strripos(trim($rest[$i]),":")+1);

                }

                if(strpos($rest[$i],"发票号码:") !== false){
                    $array['billcode']= substr(trim($rest[$i]),strripos(trim($rest[$i]),":")+1);
                }
               // print_r($array['billcode']);exit;
                if(strpos($rest[$i],'值税电子') !== false){
                    $array['invoicename']= trim($rest[$i]);
                }
                if(strpos($rest[$i],'开票日期:') !== false){
                    $da=str_replace(' ','',substr(trim($rest[$i]),strripos(trim($rest[$i]),":")+1));
                    //print_r($da);exit;
                    $array['opendate']= date('Y-m-d',strtotime( str_replace( array('年','月','日') , array('-','-','') , $da)));
                }
                if(strpos($rest[$i],'校 验 码:') !== false){
                    $array['checkcode']= str_replace(' ','',substr(trim($rest[$i]),strripos(trim($rest[$i]),":")+1));
                }

                if(strpos($rest[$i],'小写') !== false){
                    //print_r("111");
                    $q=substr(trim($rest[$i]),strripos(trim($rest[$i]),")")+1);
                    $array['amount']= preg_replace( '%[^0-9.]%', '',str_replace('¥','',$q));
                }
                if(strpos($rest[$i],'%') !== false){
                    if(strpos(trim($rest[$i]),' ') !== false) {
                        $sl = str_replace('%', '', substr(trim($rest[$i]), strripos(trim($rest[$i]), " ") + 1));
                        $array['taxa'] = $sl / 100;
                        $array['pretax']=substr(trim($rest[$i]),0,strripos(trim($rest[$i])," "));
                    }else{
                        $sl = str_replace('%', '', trim($rest[$i]));
                        $array['taxa'] = $sl / 100;
                        $array['pretax']=sprintf("%.2f", round($array['amount']/(1+$array['taxa']),2));
                    }
                }
                $array['taxamount']=$array['amount']-$array['pretax'];
                if($i<50){
                    //销售方信心
                    if(strpos($rest[$i],'称:') !== false){
                        $array['salename']= substr(trim($rest[$i]),strripos(trim($rest[$i]),":")+1);
                    }
                    if(strpos($rest[$i],'识别号: ') !== false){
                        $array['saleduty']= substr(trim($rest[$i]),strripos(trim($rest[$i]),":")+1);
                    }
                    if(strpos($rest[$i],'电 话: ') !== false){
                        $array['saleplace']= substr(trim($rest[$i]),strripos(trim($rest[$i]),":")+1);
                    }
                    if(strpos($rest[$i],'开户行及账号: ') !== false){
                        $array['salebank']= substr(trim($rest[$i]),strripos(trim($rest[$i]),":")+1);
                    }
                }
                if($i>50){
                    //购买方信息
                    if(strpos($rest[$i],'称:') !== false){
                        $array['buyname']= substr(trim($rest[$i]),strripos(trim($rest[$i]),":")+1);
                    }
                    if(strpos($rest[$i],'识别号: ') !== false){
                        $array['buyduty']= substr(trim($rest[$i]),strripos(trim($rest[$i]),":")+1);
                    }
                    if(strpos($rest[$i],'电 话: ') !== false){
                        $array['buyplace']= substr(trim($rest[$i]),strripos(trim($rest[$i]),":")+1);
                    }
                    if(strpos($rest[$i],'开户行及账号: ') !== false){
                        $array['buybank']= substr(trim($rest[$i]),strripos(trim($rest[$i]),":")+1);
                    }
                }
                $array['source']='1';//来源PDF
            }
            if ($billinfo->getInfo(['uid'=>$info['id'],'billcode'=>$array['billcode'],'state'=>0])) {
                //此发票已存在
                return $this->json_error('此发票已存在');
            }

            $id=$billinfo->getAdddata($array);
            if($id){
                //记录PDF文件地址
                $aa=[
                    'billid'=>$id,
                    'imgth'=>$imgth,
                    'ctime'=>date('Y-m-d H:i:s',time())
                ];
                $electron->adddata($aa);
                $where=['id'=>$id];
                $field='id,uid,typeid,photourl,invoicetype,photodate,opendate,consumetype,billcode,invoicecode,
			pretax,invoicename,taxamount,amount,checkcode,checkstate,salename,saleduty,saleplace,salebank,buyname,
			buyduty,buyplace,buybank,remarks,ctime,folderid';
                $res=$billinfo->getXq($where,$field);
                if($res['folderid']==0){
                    $res['foldername']='我的发票夹';
                }else{
                    $allfolder=$folder->getInfo(['id'=>$res['folderid']]);
                    $res['foldername']=$allfolder['title'];
                }
                //return $res;
            }else{
                return $this->json_error('添加失败');
            }
	    	if ($res) {
                $res['photourl']=INLET_PATH . $res['photourl'];
	    		return json_encode(['code'=>0,'msg'=>'添加成功','result'=>$res]);
	    	}else{
	    		return $this->json_error('添加失败');
	    	}
		}else{
			return $this->json_error('获取图片失败，请确认链接是否正确');
		}
	}

		
	//增值税发票添加数据 返回数据详细信息 电子发票链接调用
	public function zzbill($result,$uid,$data,$imgth){
		$billinfo = new BillinfoModel();
		$electron = new ElectronModel();
		$folder = new FolderModel();
        if(isset($data['img'])){
            $inf = pathinfo($data['img'],PATHINFO_DIRNAME);//获取前面部分
            $ext = pathinfo($data['img'],PATHINFO_EXTENSION);//获取后缀
            $newname=$inf.'/'.$result['InvoiceNum'].'.'.$ext;
            rename($data['img'],$newname);
        }
		$result['InvoiceDate']=date('Y-m-d',strtotime( str_replace( array('年','月','日') , array('-','-','') , $result['InvoiceDate'])));
		$array=[
			'uid'=>$uid,
			'typeid'=>1,
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
	
	


	//二维码扫描
	public function qcode(){
		$billinfo= new BillinfoModel();
		$info=$this->chektoken();//检测用户token
		if(!$info){
			return json_encode(['code' =>2, 'msg'=>'请登录' ]);
		}
		$request = Request();
    	$data = $request->param();
    	//$str="01,04,012001800104,42247092,9433.96,20190710,56605089491318728361,F73F,";
    	$res=explode(',', $data['strcode']);
    	if(count($res)<2){
    		return $this->json_error('识别失败,暂不支持此类型发票');
    	}
        if($res[1]=='04'){
            $res[1]="增值税普通发票";
        }elseif($res[1]=='01'){
            $res[1]="增值税专用发票";
        }elseif($res[1]=='10'){
            $res[1]="增值税电子普通发票";
        }
        $res[5]=substr($res[5],0,4).'-'.substr($res[5],4,2).'-'.substr($res[5],6);
        $array=[
            'uid'=>$info['id'],
            'typeid'=>1,
            'photourl'=>'',
            'folderid'=>0,
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
        if ($billinfo->getInfo(['uid'=>$info['id'],'billcode'=>$res[3],'state'=>0])) {
			//此发票已存在
			return $this->json_error('此发票已存在你的发票夹');
		}
        $req=$billinfo->getAdddata($array);
        if($req){
        	$array['id']=$req;
            $array['taxa']='0';
            $array['taxamount']='0.00';
            $array['amount']='0.00';
        	$array['foldername']='我的发票夹';
        	return json_encode(['code'=>0,'msg'=>'扫描成功','result'=>$array]);
        }else{
        	return $this->json_error('识别失败');
        }

	}

	//发票验证(祥云)
	public function billcheck_jiu(){
		
		$info=$this->chektoken();//检测用户token
		if(!$info){
			return json_encode(['code' =>2, 'msg'=>'请登录' ]);
		}
		$request = Request();
    	$data = $request->param();
    	$billinfo= new BillinfoModel();
    	$folder = new FolderModel();
        $take= new TakenotesModel();
        $checkvip= new CheckvipModel();
        $history= new HistoryModel();
        $user= new UserModel();
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
        /*if($info['uvip']==0){
            //获取已经验证的次数
//            $notes=$take->getOne(['uid'=>$info['id']]);
//            if($notes['checknumber']>4){
//                return json_encode(['code'=>3,'msg'=>'您的免费次数已用完,成为vip用户不限量验证','result'=>'']);
//            }
            $unm=$checkvip->getNum(['uid'=>$info['id'],'type'=>1]);
            if($unm >= 5){
                return json_encode(['code'=>3,'msg'=>'您的免费次数已用完,成为vip用户不限量导出']);
            }
        }*/
        $reee=$history->getCheck($info['id']);
        if($reee==0){
            if($info['frequency'] <= 0){
                return json_encode(['code'=>4,'msg'=>'您的查验次数已用完！']);
            }
        }
        $ycm = rand(100000, 500000);
        usleep($ycm);
        $check=$billinfo->getInfo(['uid'=>$info['id'],'billcode'=>$number,'state'=>0]);
		//echo $billinfo->getlastsql();exit;
		//print_r($check);exit;
		if($check['checkbill']==1){
            return $this->json_error('此发票已验证');
        }
        $rq=$this->alicheck($number,$date,$extaxtotalfee,$checkcode,$code);
        $re=json_decode($rq,true);
        //print_r($re);exit;
        if($re['message']['status']== -1){
            return $this->json_error('识别失败');
        }elseif($re['message']['status']== 2) {
            $se = $re['invoice'][0]['veritem'];
            $resa = [];
            foreach ($se as $k => $v) {
                $resa[$v['name']] = $v['content'];
            }
            if ($resa['invoiceType'] == '04') {
                $resa['invoiceType'] = "增值税普通发票";
            } elseif ($resa['invoiceType'] == '01') {
                $resa['invoiceType'] = "增值税专用发票";
            } elseif ($resa['invoiceType'] == '10') {
                $resa['invoiceType'] = "增值税电子普通发票";
            }
            $arr = [
                'uid' => $info['id'],
                'typeid' => 1,
                'photourl' => '',
                'folderid' => 0,
                'invoicetype' => $resa['invoiceType'],//发票种类
                'photodate' => date('Y-m-d', time()),//拍照时间
                'opendate' => $resa['billingDate'],//开票时间
                'consumetype' => "服务",//消费类型
                'billcode' => $resa['invoiceNumber'],//发票号码
                'invoicecode' => $resa['invoiceCode'],//发票代码
                'pretax' => $resa['totalAmount'],//税前金额
                'invoicename' => $resa['administrativeDivisionName'] . $resa['invoiceType'],//发票名称
                'taxamount' => $resa['totalTax'],//税额
                'amount' => $resa['amountTax'],//金额
                'checkcode' => $resa['checkCode'],//校验码
                'salename' => $resa['salesName'],//销售方名称
                'saleduty' => $resa['salesTaxNo'],//销售方纳税人识别号
                'saleplace' => $resa['salesAddressPhone'],//销售方地址及电话
                'salebank' => $resa['salesBank'],//销售方开户行及账号
                'buyname' => $resa['purchaserName'],//购方名称
                'buyduty' => $resa['purchaserTaxNo'],//购方纳税人识别号
                'buyplace' => $resa['purchaserAddressPhone'],//购方地址及电话
                'buybank' => $resa['purchaserBank'],//购方开户行及账号
                'checkbill' => 1,//已验证
                'checktime' => date('Y-m-d H:i:s', time()),//验证时间
                //'remarks' => $resa['remarks'] . '机器编码：' . $resa['machineCode'],//备注
                'remarks' => $resa['remarks'],//备注
                'checkstate'=>$resa['state'],
                'ctime' => time()//创建时间
            ];
            if($arr['checkstate']==2){
                $arr['remarks']= $arr['remarks'].'--作废--';
            }
            $check=$billinfo->getInfo(['uid'=>$info['id'],'billcode'=>$number,'state'=>0]);
            if($check['checkbill']==1){
                return $this->json_error('此发票已验证');
            }
            if ($check) {
                //此发票已存在
                $id = $check['id'];
                $where = ['id' => $id];
                unset($arr['photodate']);//删除新的拍照时间
                unset($arr['folderid']);//删除发票夹ID
                unset($arr['photourl']);//删除空的发票路径
                unset($arr['ctime']);//删除创建时间保留之前的
                $billinfo->updatedatas($arr, $where);
            } else {
                $id = $billinfo->getAdddata($arr);
            }
            if ($id) {
                $where = ['id' => $id];
                $field = 'id,uid,typeid,photourl,invoicetype,photodate,opendate,consumetype,billcode,invoicecode,
                pretax,invoicename,taxamount,amount,checkcode,salename,saleduty,saleplace,salebank,buyname,
                buyduty,buyplace,buybank,remarks,ctime,folderid,checkstate,checkbill';
                $resq = $billinfo->getXq($where, $field);
                if ($resq['folderid'] == 0) {
                    $resq['foldername'] = '我的发票夹';
                } else {
                    $allfolder = $folder->getInfo(['id' => $resq['folderid']]);
                    $resq['foldername'] = $allfolder['title'];
                }
                $take->getInc(['uid'=>$info['id']],'checknumber');
                $checkvip->adddata(['uid'=>$info['id'],'type'=>1,'ctime'=>date('Y-m-d H:i:s',time())]);
                //$reee=$history->getCheck($info['id']);
                if($reee ==0){
                    //$info['frequency']=$info['frequency']-1;
                    //$user->updatedatas(['frequency'=>$info['frequency']],['id'=>$info['id']]);//验证次数减1
                    $user->where(['id' => $info['id']])->setDec('frequency');//验证次数减1
                }
                return json_encode(['code' => 0, 'msg' => '验证成功', 'result' => $resq]);
            } else {
                return $this->json_error('验证失败');
            }
        }elseif($re['message']['status']== '5'){
            return $this->json_error('请求不合法');
        }elseif($re['message']['status']== '6'){
            return $this->json_error('发票信息不一致');
        }elseif($re['message']['status']== '9'){
            return $this->json_error('所查发票不存在');
        }elseif($re['message']['status']== '20'){
            return $this->json_error('超过该张发票当天查验次数');
        }elseif($re['message']['status']== '3020'){
            return $this->json_error('该票在本平台核验失败已超5次');
        }elseif($re['message']['status']>29 && $re['message']['status']<40 ){
            return $this->json_error('该票在此平台核验失败已超过5次');
        }elseif($re['message']['status']== '1004'){
            return $this->json_error('已超过最大查验量');
        }elseif($re['message']['status']== '1005'){
            return $this->json_error('查询发票不规范');
        }elseif($re['message']['status']== '1006'){
            return $this->json_error('查验异常');
        }elseif($re['message']['status']== '1008'){
            return $this->json_error('参数缺失');
        }elseif($re['message']['status']== '1009'){
            return $this->json_error('参数长度不正确');
        }elseif($re['message']['status']== '1014'){
            return $this->json_error('日期当天不能查验');
        }elseif($re['message']['status']== '1015'){
            return $this->json_error('超过5年不能查验');
        }elseif($re['message']['status']== '1021'){
            return $this->json_error('网络超时');
        }elseif($re['message']['status']== '100000'){
            return $this->json_error('缺少参数');
        }elseif($re['message']['status']== '100001'){
            return $this->json_error('参数取值范围错误');
        }elseif($re['message']['status']== '100005'){
            return $this->json_error('请求过于频繁');
        }elseif($re['message']['status']== '100006'){
            return $this->json_error('远程访问错误');
        }elseif($re['message']['status']== '100010'){
            return $this->json_error('请求超时');
        }elseif($re['message']['status']== '60'){
            return $this->json_error('未知错误');
        }else{
            return $this->json_error('此发票识别失败！');
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


    //获取电子发票pdf地址
    public function billpdf(){
        $info=$this->chektoken();//检测用户token
        if(!$info){
            return json_encode(['code' =>2, 'msg'=>'请登录' ]);
        }
        $request = Request();
        $data = $request->param();
        $electron = new ElectronModel();
        $res=$electron->getOne(['billid'=>$data['billid']]);
        if($res){
            $res['imgth']= INLET_PATH . $res['imgth'];
            return json_encode(['code' => 0, 'msg' => '获取成功', 'result' => $res['imgth']]);
        }else{
            return json_encode(['code' => 1, 'msg' => '此发票不是电子发票或获取失败', 'result' => '']);
        }

    }

    //发票详情页一键验证(祥云)
    public function verification_jiu(){
        $info=$this->chektoken();//检测用户token
        if(!$info){
            return json_encode(['code' =>2, 'msg'=>'请登录' ]);
        }
        $request = Request();
        $data = $request->param();
        $billinfo= new BillinfoModel();
        $folder = new FolderModel();
        $take= new TakenotesModel();
        $checkvip= new CheckvipModel();
        $history= new HistoryModel();
        $user= new UserModel();
        //获取发票详情
        $bill=$billinfo->getInfo(['id'=>$data['billid']]);
        if($bill){
            $number=$bill['billcode'];
            $date=$bill['opendate'];
            $extaxtotalfee=$bill['pretax'];
            $checkcode=$bill['checkcode'];
            $code=$bill['invoicecode'];
        }else{
            return json_encode(['code' =>1, 'msg'=>'请确定选择发票是否正确!']);
        }
        /*if($info['uvip']==0){
            //获取已经验证的次数
//            $notes=$take->getOne(['uid'=>$info['id']]);
//            if($notes['checknumber']>4){
//                return json_encode(['code'=>3,'msg'=>'您的免费次数已用完,成为vip用户不限量验证','result'=>'']);
//            }
            $unm=$checkvip->getNum(['uid'=>$info['id'],'type'=>1]);
            if($unm >= 5){
                return json_encode(['code'=>3,'msg'=>'您的免费次数已用完,成为vip用户不限量导出']);
            }
        }*/
        $reee=$history->getCheck($info['id']);
        if($reee==0){
            if($info['frequency'] <= 0){
                return json_encode(['code'=>4,'msg'=>'您的查验次数已用完！']);
            }
        }
        $ycm = rand(100000, 500000);
        usleep($ycm);
        $check=$billinfo->getInfo(['uid'=>$info['id'],'billcode'=>$number,'state'=>0]);
        //echo $billinfo->getlastsql();exit;
        //print_r($check);exit;
        if($check['checkbill']==1){
            return $this->json_error('此发票已验证');
        }
        $rq=$this->alicheck($number,$date,$extaxtotalfee,$checkcode,$code);
        //用免费的
        $re = [];
        //$src = 'http://billadmin.ganbuguo.com/'.str_replace('./', '', $bill['photourl']);

        //var_dump( 'http://ihbadmintest.1314wallet.com/api/ocrapi/?src='.$src);
        //$rq=file_get_contents('http://ihbadmintest.1314wallet.com/api/ocrapi/?src='.$src);
        //var_dump($rq);
        //exit;
        if($rq){

            $re=json_decode($rq,true);
        }
        //print_r($re);exit;
        if($re['message']['status']== -1){
            return $this->json_error('识别失败');
        }elseif($re['message']['status']== 2) {
            $se = $re['invoice'][0]['veritem'];
            $resa = [];
            foreach ($se as $k => $v) {
                $resa[$v['name']] = $v['content'];
            }
            //$resa = $re['content'];
            if ($resa['invoiceType'] == '04') {
                $resa['invoiceType'] = "增值税普通发票";
            } elseif ($resa['invoiceType'] == '01') {
                $resa['invoiceType'] = "增值税专用发票";
            } elseif ($resa['invoiceType'] == '10') {
                $resa['invoiceType'] = "增值税电子普通发票";
            }
            $arr = [
                'typeid' => 1,
                'invoicetype' => $resa['invoiceType'],//发票种类
                'opendate' => $resa['billingDate'],//开票时间
                'billcode' => $resa['invoiceNumber'],//发票号码
                'invoicecode' => $resa['invoiceCode'],//发票代码
                'pretax' => $resa['totalAmount'],//税前金额
                'invoicename' => $resa['administrativeDivisionName'] . $resa['invoiceType'],//发票名称
                'taxamount' => $resa['totalTax'],//税额
                'amount' => $resa['amountTax'],//金额
                'checkcode' => $resa['checkCode'],//校验码
                'salename' => $resa['salesName'],//销售方名称
                'saleduty' => $resa['salesTaxNo'],//销售方纳税人识别号
                'saleplace' => $resa['salesAddressPhone'],//销售方地址及电话
                'salebank' => $resa['salesBank'],//销售方开户行及账号
                'buyname' => $resa['purchaserName'],//购方名称
                'buyduty' => $resa['purchaserTaxNo'],//购方纳税人识别号
                'buyplace' => $resa['purchaserAddressPhone'],//购方地址及电话
                'buybank' => $resa['purchaserBank'],//购方开户行及账号
                'checkbill' => 1,//已验证
                'checktime' => date('Y-m-d H:i:s', time()),//验证时间
                //'remarks' => $resa['remarks'] . '机器编码：' . $resa['machineCode'],//备注
                'remarks' => $resa['remarks'],//备注
                'checkstate'=>$resa['state']
            ];
            if($arr['checkstate']==2){
                $arr['remarks']= $arr['remarks'].'--作废--';
            }
            $check=$billinfo->getInfo(['uid'=>$info['id'],'billcode'=>$number,'state'=>0]);
            if($check['checkbill']==1){
                return $this->json_error('此发票已验证');
            }
            $where = ['id' => $data['billid']];
            $billinfo->updatedatas($arr, $where);

            if ($data['billid']) {
                $where = ['id' => $data['billid']];
                $field = 'id,uid,typeid,photourl,invoicetype,photodate,opendate,consumetype,billcode,invoicecode,
                pretax,invoicename,taxamount,amount,checkcode,salename,saleduty,saleplace,salebank,buyname,
                buyduty,buyplace,buybank,remarks,ctime,folderid,checkstate,checkbill';
                $resq = $billinfo->getXq($where, $field);
                if ($resq['folderid'] == 0) {
                    $resq['foldername'] = '我的发票夹';
                } else {
                    $allfolder = $folder->getInfo(['id' => $resq['folderid']]);
                    $resq['foldername'] = $allfolder['title'];
                }
                $take->getInc(['uid'=>$info['id']],'checknumber');
                $checkvip->adddata(['uid'=>$info['id'],'type'=>1,'ctime'=>date('Y-m-d H:i:s',time())]);
                if($reee==0) {
                    //$info['frequency']=$info['frequency']-1;
                   // $user->updatedatas(['frequency'=>$info['frequency']],['id'=>$info['id']]);//验证次数减1
                    $user->where(['id' => $info['id']])->setDec('frequency');//验证次数减1
                }
                return json_encode(['code' => 0, 'msg' => '验证成功', 'result' => $resq]);
            } else {
                return $this->json_error('验证失败');
            }
        }elseif($re['message']['status']== '5'){
            return $this->json_error('请求不合法');
        }elseif($re['message']['status']== '6'){
            return $this->json_error('发票信息不一致');
        }elseif($re['message']['status']== '9'){
            return $this->json_error('所查发票不存在');
        }elseif($re['message']['status']== '20'){
            return $this->json_error('超过该张发票当天查验次数');
        }elseif($re['message']['status']== '3020'){
            return $this->json_error('该票在本平台核验失败已超5次');
        }elseif($re['message']['status']>29 && $re['message']['status']<40 ){
            return $this->json_error('该票在此平台核验失败已超过5次');
        }elseif($re['message']['status']== '1004'){
            return $this->json_error('已超过最大查验量');
        }elseif($re['message']['status']== '1005'){
            return $this->json_error('查询发票不规范');
        }elseif($re['message']['status']== '1006'){
            return $this->json_error('查验异常');
        }elseif($re['message']['status']== '1008'){
            return $this->json_error('参数缺失');
        }elseif($re['message']['status']== '1009'){
            return $this->json_error('参数长度不正确');
        }elseif($re['message']['status']== '1014'){
            return $this->json_error('日期当天不能查验');
        }elseif($re['message']['status']== '1015'){
            return $this->json_error('超过5年不能查验');
        }elseif($re['message']['status']== '1021'){
            return $this->json_error('网络超时');
        }elseif($re['message']['status']== '100000'){
            return $this->json_error('缺少参数');
        }elseif($re['message']['status']== '100001'){
            return $this->json_error('参数取值范围错误');
        }elseif($re['message']['status']== '100005'){
            return $this->json_error('请求过于频繁');
        }elseif($re['message']['status']== '100006'){
            return $this->json_error('远程访问错误');
        }elseif($re['message']['status']== '100010'){
            return $this->json_error('请求超时');
        }elseif($re['message']['status']== '60'){
            return $this->json_error('未知错误');
        }else{
            return $this->json_error('此发票识别失败！');
        }
    }

    public function alitest(){
        $data['strcode']='01,10,012002000211,21107229,22.43,20200915,47890520833743987089,8FA5,';
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

        $rq=$this->alicheck($number,$date,$extaxtotalfee,$checkcode,$code);
        print_r($rq);exit;
    }



    /**
     * PHP发送Json对象数据
     *
     * @param $url 请求url
     * @param $jsonStr 发送的json字符串
     * @return array
     */
    function   http_post_json( $url ,  $jsonStr ){
        $ch   = curl_init();
        curl_setopt( $ch , CURLOPT_POST, 1);
        curl_setopt( $ch , CURLOPT_URL,  $url );
        curl_setopt( $ch , CURLOPT_POSTFIELDS,  $jsonStr );
        curl_setopt( $ch , CURLOPT_RETURNTRANSFER, 1);
        curl_setopt( $ch , CURLOPT_HTTPHEADER,  array (
                'Content-Type: application/json; charset=utf-8' ,
                'Content-Length: '   .  strlen ( $jsonStr )
            )
        );
        $response   = curl_exec( $ch );
        $httpCode   = curl_getinfo( $ch , CURLINFO_HTTP_CODE);
        curl_close( $ch );
        //return $response;
        return   array ( $httpCode ,  $response );
    }

    /**
     * 发送数据
     * @param String $url   请求的地址
     * @param Array $header 自定义的header数据
     * @param Array $content POST的数据
     * @return String
     */
    function tocurl($url, $header, $content){
        $ch = curl_init();
        if(substr($url,0,5)=='https'){
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, true); // 从证书中检查SSL加密算法是否存在
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        //curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt( $ch , CURLOPT_HTTPHEADER,  array (
                'Content-Type: application/json; charset=utf-8' ,
                'token: ' . $header,
                'Content-Length: '   .  strlen ( $content )
            )
        );
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$content); //http_build_query($content)
        $response = curl_exec($ch);
        if($error=curl_error($ch)){
            die($error);
        }
        curl_close($ch);
        return $response;
    }


    //发票验证
    public function billcheck(){

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
        if($info['shakynum'] > 0 && time()<'1622476799'){

        }else{
            if($info['frequency'] <= 0){
                return json_encode(['code'=>4,'msg'=>'您的查验次数已用完！']);
            }
        }
        if($data['type']==1){
            //扫码验证
            $res=explode(',', $data['strcode']);
            if(count($res)<2){
                return $this->json_error('识别失败,暂不支持此类型发票');
            }
            //01-增值税专票 03-机动车销售统一发票 04-普通增值税发票 10-电子发票 11-卷式普通发票 14-电子普通发票（通行费）15-二手车统一发票
            if ($res[1] == '04' || $res[1] == '10' || $res[1] == '14' || $res[1] == '11') {
                $code = substr($res[6], -6);
            } else {
                $code = $res[4];
            }
            $contents = [
                "fpdm" => $res[2],   // 发票代码
                "fphm" => $res[3],       // 发票号码
                "date" => $res[5],       // 发票日期
                "code" => $code       // 校验码后6位/税前金额
            ];
            $sqje = $res[4];
            $ycm = rand(100000, 500000);
            usleep($ycm);
            $check=$billinfo->getInfo(['uid'=>$info['id'],'billcode'=>$res[3],'state'=>0]);
            if($check['checkbill']==1){
                return $this->json_error('此发票已存在');
            }
            $list=$this->getfpinfohaoxin($contents['fpdm'],$contents['fphm'],$contents['date'],$contents['code']);
            $qq=json_decode($list,true);
        }else{
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
            $check=$billinfo->getInfo(['uid'=>$info['id'],'billcode'=>$data['number'],'state'=>0]);
            if($check['checkbill']==1){
                return $this->json_error('此发票已存在');
            }
            if($info['id']==1){
                Db::name('fp_test')->insert(['test'=>json_encode($data)]);
            }
            $list=$this->getfpinfohaoxin($contents['fpdm'],$contents['fphm'],$contents['date'],$contents['code']);
            $qq=json_decode($list,true);
            
            if($qq['code']==1){
                if($contents['code']==$data['extaxtotalfee']){
                    $contents['code']=$lwjym;
                }else{
                    $contents['code']=$data['extaxtotalfee'];
                }
                $list=$this->getfpinfohaoxin($contents['fpdm'],$contents['fphm'],$contents['date'],$contents['code']);
                $qq=json_decode($list,true);
            }
        }
        if($qq['code']==0){
            $jian=$this->untrue($qq);
            if($jian !=''){
                return $this->json_error($jian);
            }
            if($qq['type']=='01'){
                $arr=$this->zp($qq);
            }elseif($qq['type']=='03'){
                $arr=$this->jdc($qq);
            }elseif($qq['type']=='04'){
                $arr=$this->pp($qq);
            }elseif($qq['type']=='08'){
                $arr=$this->zz($qq);
            }elseif($qq['type']=='10'){
                $arr=$this->dzfp($qq);
            }elseif($qq['type']=='11'){
                $arr=$this->jsfp($qq,$sqje);
            }elseif($qq['type']=='14'){
                $arr=$this->abc($qq,$contents['date']);
//            }elseif($qq['type']=='15'){
//                $arr=$this->escfp($qq);
            }else{
                return $this->json_error('此发票暂不支持验证');
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
            $check=$billinfo->getInfo(['uid'=>$info['id'],'billcode'=>$data['number'],'state'=>0]);
            if($check['checkbill']==1){
                return $this->json_error('此发票已验证');
            }
            $arr['uid']=$info['id'];
            $arr['photodate']=date('Y-m-d',time());
            $arr['ctime']=time();
            $id = $billinfo->getAdddata($arr);
            if ($id) {
                $where = ['id' => $id];
                $field = 'id,uid,typeid,photourl,invoicetype,photodate,opendate,consumetype,billcode,invoicecode,
                        pretax,invoicename,taxamount,amount,checkcode,salename,saleduty,saleplace,salebank,buyname,
                        buyduty,buyplace,buybank,remarks,ctime,folderid,taxa,checkbill,checkstate,remind,remindtime,reminddate,checktime';
                $resq = $billinfo->getXq($where, $field);
                $take->getInc(['uid'=>$info['id']],'checknumber');
                $checkvip->adddata(['uid'=>$info['id'],'type'=>1,'ctime'=>date('Y-m-d H:i:s',time())]);
                if($info['uvip']==0){
                    if($info['shakynum'] > 0 && time()<'1622476799'){
                        $user->where(['id' => $info['id']])->setDec('shakynum');//验证次数减1
                    }else{
                        $user->where(['id' => $info['id']])->setDec('frequency');//验证次数减1
                    }
                }
                return json_encode(['code' => 0, 'msg' => '验证成功', 'result' => $resq]);
            } else {
                return $this->json_error('验证失败');
            }
        }else{
            return $this->json_error('查询失败，可使用手动输入查询');
        }
    }


    //增值税专用发票01
    public function zp($qq){
        $qq['zfbz']=$qq['zfbz']?$qq['zfbz']:0;
        $ta='';
        if($qq['summarys'][0]['taxRate']>=1){
            $ta=$qq['summarys'][0]['taxRate']."%";
        }
        $qq['kprq_zp']=date('Y-m-d',strtotime( str_replace( array('年','月','日') , array('-','-','') , $qq['kprq_zp'])));
        $arr = [
            'typeid' => 1,
            'invoicetype' => $qq['typeName'],//发票类型
            'opendate' => $qq['kprq_zp'],//开票时间
            'billcode' => $qq['fphm_zp'],//发票号码
            'invoicecode' => $qq['fpdm_zp'],//发票代码
            'pretax' => $qq['je_zp'],//税前金额
            'invoicename' => $qq['fpcc_zp'],//发票名称
            'taxamount' => $qq['se_zp'],//税额
            'amount' => $qq['jshj_zp'],//金额
            'checkcode' => $qq['jym_zp'],//校验码
            'salename' => $qq['fpmx_xfmc'],//销售方名称
            'saleduty' => $qq['xfsbh_zp'],//销售方纳税人识别号
            'saleplace' => $qq['xfdzdh_zp'],//销售方地址及电话
            'salebank' => $qq['xfyhzh_zp'],//销售方开户行及账号
            'buyname' => $qq['fpmx_gfmc'],//购方名称
            'buyduty' => $qq['gfsbh_zp'],//购方纳税人识别号
            'buyplace' => $qq['gfdzdh_zp'],//购方地址及电话
            'buybank' => $qq['gfyhzh_zp'],//购方开户行及账号
            'checkbill' => 1,//已验证
            'checktime' => date('Y-m-d H:i:s', time()),//验证时间
            //'remarks' => $qq['bz_zp'] . '机器编码：' . $qq['sbbh_dzfp'],//备注
            'remarks' => $qq['bz_zp'],//备注
            'checkstate'=>$qq['zfbz'],//0代表正常 Y或者2代表作废 3是红冲

            'consumetype'=>$qq['summarys'][0]['name'],//消费类型名称
            'specif'=>$qq['summarys'][0]['type'],//规格型号
            'unit'=>$qq['summarys'][0]['unit'],//单位
            'number'=>$qq['summarys'][0]['amount'],//数量
            'unitprice'=>$qq['summarys'][0]['priceUnit'],//单价
            'unittotal'=>$qq['summarys'][0]['priceSum'],//单品金额总和
            //'taxa'=>($qq['summarys'][0]['taxRate'] * 100)."%",//税率
            'taxa'=>$ta,//税率
            'unittax'=>$qq['summarys'][0]['taxSum'],//单品税额
            'whole'=>json_encode($qq)//返回的完整的值
        ];
        return $arr;
    }

    //增值税电子普通发票10
    public function dzfp($qq){
        $qq['zfbz']=$qq['zfbz']?$qq['zfbz']:0;
        $ta='';
        if(intval($qq['summarys'][0]['taxRate'])>=1){
            $ta=$qq['summarys'][0]['taxRate']."%";
        }
        $qq['kprq_dzfp']=date('Y-m-d',strtotime( str_replace( array('年','月','日') , array('-','-','') , $qq['kprq_dzfp'])));
        $arr = [
            'typeid' => 10,
            'invoicetype' => $qq['typeName'],//发票种类
            'opendate' => $qq['kprq_dzfp'],//开票时间
            'billcode' => $qq['fphm_dzfp'],//发票号码
            'invoicecode' => $qq['fpdm_dzfp'],//发票代码
            'pretax' => $qq['je_dzfp'],//税前金额
            'invoicename' => $qq['fpcc_dzfp'],//发票名称
            'taxamount' => $qq['se_dzfp'],//税额
            'amount' => $qq['jshjxx_dzfp'],//金额
            'checkcode' => $qq['jym_dzfp'],//校验码
            'salename' => $qq['xfmc_dzfp'],//销售方名称
            'saleduty' => $qq['xfsbh_dzfp'],//销售方纳税人识别号
            'saleplace' => $qq['xfdzdh_dzfp'],//销售方地址及电话
            'salebank' => $qq['xfyhzh_dzfp'],//销售方开户行及账号
            'buyname' => $qq['gfmc_dzfp'],//购方名称
            'buyduty' => $qq['gfsbh_dzfp'],//购方纳税人识别号
            'buyplace' => $qq['gfdzdh_dzfp'],//购方地址及电话
            'buybank' => $qq['gfyhzh_dzfp'],//购方开户行及账号
            'checkbill' => 1,//已验证
            'checktime' => date('Y-m-d H:i:s', time()),//验证时间
            //'remarks' => $qq['bz_dzfp'] . '机器编码：' . $qq['sbbh_dzfp'],//备注
            'remarks' => $qq['remarks'],//备注
            'checkstate'=>$qq['zfbz'],//0代表正常 Y或者2代表作废 3是红冲

            'consumetype'=>$qq['summarys'][0]['name'],//消费类型名称
            'specif'=>$qq['summarys'][0]['type'],//规格型号
            'unit'=>$qq['summarys'][0]['unit'],//单位
            'number'=>$qq['summarys'][0]['amount'],//数量
            'unitprice'=>$qq['summarys'][0]['priceUnit'],//单价
            'unittotal'=>$qq['summarys'][0]['priceSum'],//单品金额总和
            //'taxa'=>($qq['summarys'][0]['taxRate'] * 100)."%",//税率
            'taxa'=>$ta,//税率
            'unittax'=>$qq['summarys'][0]['taxSum'],//单品税额
            'whole'=>json_encode($qq)//返回的完整的值
        ];
        return $arr;
    }

    //机动车销售统一发票03
    public function jdc($qq){
        $qq['zfbz']=$qq['zfbz']?$qq['zfbz']:0;
        $qq['kprq_jdcfp']=date('Y-m-d',strtotime( str_replace( array('年','月','日') , array('-','-','') , $qq['kprq_jdcfp'])));
        $arr = [
            'typeid' => 3,
            'invoicetype' => $qq['typeName'],//发票种类
            'opendate' => $qq['kprq_jdcfp'],//开票时间
            'billcode' => $qq['fphm_jdcfp'],//发票号码
            'invoicecode' => $qq['fpdm_jdcfp'],//发票代码
            'pretax' => $qq['je_jdcfp'],//税前金额
            'invoicename' => $qq['fpcc_jdcfp'],//发票名称
            'taxamount' => $qq['se_jdcfp'],//税额
            'amount' => $qq['jshjxx_jdcfp'],//金额
            'checkcode' => $qq['jym_jdcfp'],//校验码
            'salename' => $qq['fpmx_xfmc'],//销售方名称
            'saleduty' => $qq['xfsbh_jdcfp'],//销售方纳税人识别号
            'saleplace' => $qq['xfdzdh_jdcfp'],//销售方地址及电话
            'salebank' => $qq['xfyhzh_jdcfp'],//销售方开户行及账号
            'buyname' => $qq['fpmx_gfmc'],//购方名称
            'buyduty' => $qq['gfsbh_jdcfp'],//购方纳税人识别号
            'buyplace' => $qq['gfdzdh_jdcfp'],//购方地址及电话
            'buybank' => $qq['gfyhzh_jdcfp'],//购方开户行及账号
            'checkbill' => 1,//已验证
            'checktime' => date('Y-m-d H:i:s', time()),//验证时间
            //'remarks' => $qq['bz_dzfp'] . '机器编码：' . $qq['jqbm_jdcfp'],//备注
            'remarks' => $qq['remarks'],//备注
            'checkstate'=>$qq['zfbz'],//0代表正常 Y或者2代表作废 3是红冲

            'whole'=>json_encode($qq)//返回的完整的值
        ];
        return $arr;
    }

    //增值税普通发票04
    public function pp($qq){
        $qq['zfbz']=$qq['zfbz']?$qq['zfbz']:0;
        $ta='';
        if(intval($qq['summarys'][0]['taxRate'])>=1){
            $ta=$qq['summarys'][0]['taxRate']."%";
        }
        $qq['kprq_pp']=date('Y-m-d',strtotime( str_replace( array('年','月','日') , array('-','-','') , $qq['kprq_pp'])));
        $arr = [
            'typeid' => 4,
            'invoicetype' => $qq['typeName'],//发票种类
            'opendate' => $qq['kprq_pp'],//开票时间
            'billcode' => $qq['fphm_pp'],//发票号码
            'invoicecode' => $qq['fpdm_pp'],//发票代码
            'pretax' => $qq['je_pp'],//税前金额
            'invoicename' => $qq['fpcc_pp'],//发票名称
            'taxamount' => $qq['se_pp'],//税额
            'amount' => $qq['jshj_pp'],//金额
            'checkcode' => $qq['jym_pp'],//校验码
            'salename' => $qq['fpmx_xfmc'],//销售方名称
            'saleduty' => $qq['xfsbh_pp'],//销售方纳税人识别号
            'saleplace' => $qq['xfdzdh_pp'],//销售方地址及电话
            'salebank' => $qq['xfyhzh_pp'],//销售方开户行及账号
            'buyname' => $qq['fpmx_gfmc'],//购方名称
            'buyduty' => $qq['gfsbh_pp'],//购方纳税人识别号
            'buyplace' => $qq['gfdzdh_pp'],//购方地址及电话
            'buybank' => $qq['gfyhzh_pp'],//购方开户行及账号
            'checkbill' => 1,//已验证
            'checktime' => date('Y-m-d H:i:s', time()),//验证时间
            //'remarks' => $qq['bz_pp'] . '机器编码：' . $qq['jqbh_pp'],//备注
            'remarks' => $qq['remarks'],//备注
            'checkstate'=>$qq['zfbz'],//0代表正常 Y或者2代表作废 3是红冲

            'consumetype'=>$qq['summarys'][0]['name'],//消费类型名称
            'specif'=>$qq['summarys'][0]['type'],//规格型号
            'unit'=>$qq['summarys'][0]['unit'],//单位
            'number'=>$qq['summarys'][0]['amount'],//数量
            'unitprice'=>$qq['summarys'][0]['priceUnit'],//单价
            'unittotal'=>$qq['summarys'][0]['priceSum'],//单品金额总和
            //'taxa'=>($qq['summarys'][0]['taxRate'] * 100)."%",//税率
            'taxa'=>$ta,//税率
            'unittax'=>$qq['summarys'][0]['taxSum'],//单品税额
            'whole'=>json_encode($qq)//返回的完整的值
        ];
        return $arr;
    }

    //增值税电子专用发票08
    public function zz($qq){
        $qq['zfbz']=$qq['zfbz']?$qq['zfbz']:0;
        $ta='';
        if(intval($qq['summarys'][0]['taxRate'])>=1){
            $ta=$qq['summarys'][0]['taxRate']."%";
        }
        $qq['kprq_zp']=date('Y-m-d',strtotime( str_replace( array('年','月','日') , array('-','-','') , $qq['kprq_zp'])));
        $arr = [
            'typeid' => 8,
            'invoicetype' => $qq['typeName'],//发票种类
            'opendate' => $qq['kprq_zp'],//开票时间
            'billcode' => $qq['fphm_zp'],//发票号码
            'invoicecode' => $qq['fpdm_zp'],//发票代码
            'pretax' => $qq['je_zp'],//税前金额
            'invoicename' => $qq['fpcc_zp'],//发票名称
            'taxamount' => $qq['se_zp'],//税额
            'amount' => $qq['jshj_zp'],//金额
            'checkcode' => $qq['jym_zp'],//校验码
            'salename' => $qq['fpmx_xfmc'],//销售方名称
            'saleduty' => $qq['xfsbh_zp'],//销售方纳税人识别号
            'saleplace' => $qq['xfdzdh_zp'],//销售方地址及电话
            'salebank' => $qq['xfyhzh_zp'],//销售方开户行及账号
            'buyname' => $qq['fpmx_gfmc'],//购方名称
            'buyduty' => $qq['gfsbh_zp'],//购方纳税人识别号
            'buyplace' => $qq['gfdzdh_zp'],//购方地址及电话
            'buybank' => $qq['gfyhzh_zp'],//购方开户行及账号
            'checkbill' => 1,//已验证
            'checktime' => date('Y-m-d H:i:s', time()),//验证时间
            //'remarks' => $qq['bz_zp'] . '机器编码：' . $qq['jqbh_zp'],//备注
            'remarks' => $qq['remarks'],//备注
            'checkstate'=>$qq['zfbz'],//0代表正常 Y或者2代表作废 3是红冲

            'consumetype'=>$qq['summarys'][0]['name'],//消费类型名称
            'specif'=>$qq['summarys'][0]['type'],//规格型号
            'unit'=>$qq['summarys'][0]['unit'],//单位
            'number'=>$qq['summarys'][0]['amount'],//数量
            'unitprice'=>$qq['summarys'][0]['priceUnit'],//单价
            'unittotal'=>$qq['summarys'][0]['priceSum'],//单品金额总和
            //'taxa'=>($qq['summarys'][0]['taxRate'] * 100)."%",//税率
            'taxa'=>$ta,//税率
            'unittax'=>$qq['summarys'][0]['taxSum'],//单品税额
            'whole'=>json_encode($qq)//返回的完整的值
        ];
        return $arr;
    }

    //增值税普通发票（卷票)11
    public function jsfp($qq,$sqje){
        $qq['zfbz']=$qq['zfbz']?$qq['zfbz']:0;
        $ta='';
        if(intval($qq['summarys'][0]['taxRate'])>=1){
           $ta=$qq['summarys'][0]['taxRate']."%";
        }
        $qq['kprq_jp']=date('Y-m-d',strtotime( str_replace( array('年','月','日') , array('-','-','') , $qq['kprq_jp'])));
        $arr = [
            'typeid' => 11,
            'invoicetype' => $qq['typeName'],//发票种类
            'opendate' => $qq['kprq_jp'],//开票时间
            'billcode' => $qq['fphm_jp'],//发票号码
            'invoicecode' => $qq['fpdm_jp'],//发票代码
            'pretax' => $qq['je_jp'],//税前金额
            'invoicename' => $qq['fpcc_jp'],//发票名称
            'taxamount' => $qq['se_jp'],//税额
            'amount' => $qq['jshj_jp'],//金额
            'checkcode' => $qq['jym_jp'],//校验码
            'salename' => $qq['fpmx_xfmc'],//销售方名称
            'saleduty' => $qq['xfsbh_jp'],//销售方纳税人识别号
            'saleplace' => $qq['xfdzdh_jp'],//销售方地址及电话
            'salebank' => $qq['xfyhzh_jp'],//销售方开户行及账号
            'buyname' => $qq['fpmx_gfmc'],//购方名称
            'buyduty' => $qq['gfsbh_jp'],//购方纳税人识别号
            'buyplace' => $qq['gfdzdh_jp'],//购方地址及电话
            'buybank' => $qq['gfyhzh_jp'],//购方开户行及账号
            'checkbill' => 1,//已验证
            'checktime' => date('Y-m-d H:i:s', time()),//验证时间
            //'remarks' => $qq['bz_jp'] . '机器编码：' . $qq['fphmjp'],//备注
            'remarks' => $qq['remarks'],//备注
            'checkstate'=>$qq['zfbz'],//0代表正常 Y或者2代表作废 3是红冲

            'consumetype'=>$qq['summarys'][0]['name'],//消费类型名称
            'specif'=>$qq['summarys'][0]['type'],//规格型号
            'unit'=>$qq['summarys'][0]['unit'],//单位
            'number'=>$qq['summarys'][0]['amount'],//数量
            'unitprice'=>$qq['summarys'][0]['priceUnit'],//单价
            'unittotal'=>$qq['summarys'][0]['priceSum'],//单品金额总和
            //'taxa'=>($qq['summarys'][0]['taxRate'] * 100)."%",//税率
            'taxa'=>$ta,//税率
            'unittax'=>$qq['summarys'][0]['taxSum'],//单品税额
            'whole'=>json_encode($qq)//返回的完整的值
        ];
        return $arr;
    }

    //通行费xxx增值税电子普通发票14
    public function abc($qq,$ktime){
        $qq['zfbz']=$qq['zfbz']?$qq['zfbz']:0;
        $ta='';
        if(intval($qq['summarys'][0]['taxRate'])>=1){
            $ta=$qq['summarys'][0]['taxRate']."%";
        }
        $qq['kprq_pp']=date('Y-m-d',strtotime($ktime));
        $arr = [
            'typeid' => 14,
            'invoicetype' => $qq['typeName'],//发票种类
            'opendate' => $qq['kprq_pp'],//开票时间
            'billcode' => $qq['fphm_pp'],//发票号码
            'invoicecode' => $qq['fpdm_pp'],//发票代码
            'pretax' => $qq['je_pp'],//税前金额
            'invoicename' => $qq['fpcc_pp'],//发票名称
            'taxamount' => $qq['se_pp'],//税额
            'amount' => $qq['jshjxx_pp'],//金额
            'checkcode' => $qq['jym_pp'],//校验码
            'salename' => $qq['fpmx_xfmc'],//销售方名称
            'saleduty' => $qq['xfsbh_pp'],//销售方纳税人识别号(为获取)
            'saleplace' => $qq['xfdzdh_pp'],//销售方地址及电话
            'salebank' => $qq['xfyhzh_pp'],//销售方开户行及账号
            'buyname' => $qq['fpmx_gfmc'],//购方名称
            'buyduty' => $qq['gfsbh_pp'],//购方纳税人识别号
            'buyplace' => $qq['gfdzdh_pp'],//购方地址及电话
            'buybank' => $qq['gfyhzh_pp'],//购方开户行及账号
            'checkbill' => 1,//已验证
            'checktime' => date('Y-m-d H:i:s', time()),//验证时间
            'remarks' => $qq['remarks'],//备注
            'checkstate'=>$qq['zfbz'],//0代表正常 Y或者2代表作废 3是红冲

            'consumetype'=>$qq['summarys'][0]['name'],//消费类型名称
            'specif'=>$qq['summarys'][0]['type'],//规格型号
            'unit'=>$qq['summarys'][0]['unit'],//单位
            'number'=>$qq['summarys'][0]['amount'],//数量
            'unitprice'=>$qq['summarys'][0]['priceUnit'],//单价
            'unittotal'=>$qq['summarys'][0]['priceSum'],//单品金额总和
            //'taxa'=>($qq['summarys'][0]['taxRate'] * 100)."%",//税率
            'taxa'=>$ta,//税率
            'unittax'=>$qq['summarys'][0]['taxSum'],//单品税额
            'whole'=>json_encode($qq)//返回的完整的值

        ];
        return $arr;
    }

    //二手车销售统一发票15
    public function escfp($qq){
        $qq['zfbz']=$qq['zfbz']?$qq['zfbz']:0;
        $ta='';
        if(intval($qq['summarys'][0]['taxRate'])>=1){
            $ta=$qq['summarys'][0]['taxRate']."%";
        }
        $qq['kprq_escfp']=date('Y-m-d',strtotime( str_replace( array('年','月','日') , array('-','-','') , $qq['kprq_escfp'])));
        $arr = [
            'typeid' => 15,
            'invoicetype' => $qq['typeName'],//发票种类
            'opendate' => $qq['kprq_escfp'],//开票时间
            'billcode' => $qq['fphm_escfp'],//发票号码
            'invoicecode' => $qq['fpdm_escfp'],//发票代码
            'pretax' => '0.00',//税前金额
            'invoicename' => $qq['fpcc_escfp'],//发票名称
            'taxamount' => '0.00',//税额
            'amount' => $qq['cjhjxx_escfp'],//金额
            'checkcode' => $qq['jqbm_escfp'],//校验码
            'salename' => $qq['xfmc_escfp'],//销售方名称
            'saleduty' => $qq['xfdm_escfp'],//销售方纳税人识别号
            'saleplace' => $qq['xfdz_escfp'].$qq['xfdh_escfp'],//销售方地址及电话
            'salebank' => $qq['escscyh_escfp'],//销售方开户行及账号
            'buyname' => $qq['mfmc_escfp'],//购方名称
            'buyduty' => $qq['mfdm_escfp'],//购方纳税人识别号
            'buyplace' => $qq['mfdz_escfp'].$qq['mfdh_escfp'],//购方地址及电话
            'buybank' => '',//购方开户行及账号
            'checkbill' => 1,//已验证
            'checktime' => date('Y-m-d H:i:s', time()),//验证时间
            //'remarks' => $qq['bz_escfp'] . '机器编码：' . $qq['jqbm_escfp'],//备注
            'remarks' => $qq['bz_escfp'],//备注
            'checkstate'=>$qq['zfbz']//0代表正常 Y或者2代表作废 3是红冲
        ];
        return $arr;
    }

    //错误信息处理
    /*
    * message.put("002","超过该张发票当日查验次数(请于次日再次查验)!");
    * message.put("003","发票查验请求太频繁，请稍后再试！");
    * message.put("004","超过服务器最大请求数，请稍后访问!");
    * message.put("005","请求不合法!");
    * message.put("006","不一致");
    * message.put("007","验证码失效!");
    * message.put("008","验证码错误!");
    * message.put("009","查无此票");
    * message.put("010","查无此票");
    * message.put("010_","系统异常，请重试！(05)");
    * message.put("015","系统异常，请重试！(015)");
    * message.put("016","服务器接收的请求太频繁，请稍后再试！");
    * message.put("020","由于查验行为异常，涉嫌违规，当前无法使用查验服务！");
    * message.put("rqerr","当日开具发票可于次日进行查验！");
     */
    public function untrue($qq){
        if($qq['key1']=='002'||$qq['key1']=='003'||$qq['key1']=='004'||$qq['key1']=='005'||$qq['key1']=='006'||$qq['key1']=='007'||$qq['key1']=='008'||$qq['key1']=='009'||$qq['key1']=='010'||$qq['key1']=='010_'||$qq['key1']=='015'||$qq['key1']=='016'||$qq['key1']=='020'||$qq['key1']=='rqerr'){
            $fds=$qq['message'];
        }else{
            $fds='';
        }
        return $fds;
    }

    /**
     * curl post方法
     * @param string $url 请求地址
     * @param array|string $param post参数
     */
    function http_request($url, $data = null)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!empty($data)){
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }

   /* //祥云官网接口（验真）
    public function xiangyun($data){
        $url="https://netocr.com/verapi/verInvoice.do";

        $res=explode(',', $data['strcode']);
        if(count($res)<2){
            return $this->json_error('识别失败,暂不支持此类型发票');
        }
        $ree=[
            'key'=>"V5KM4ond2s68LNCbnhfoGu",
            'secret'=>"81e5e503add441d88dd096b9c9863c65",
            'invoiceCode'=>$res[2],
            'invoiceNumber'=>$res[3],
            'billingDate'=>date('Y-m-d',strtotime($res[5])),
            'typeId'=>3007,
            'format'=>'json'
        ];
        //01-增值税专票 03-机动车销售统一发票 04-普通增值税发票 10-电子发票 11-卷式普通发票 14-电子普通发票（通行费）15-二手车统一发票
        if($res[1]=='04'||$res[1]=='10'||$res[1]=='14'||$res[1]=='11'){
            $ree['checkCode']=substr($res[6],-6);
        }else{
            $ree['totalAmount']=$res[4];
        }
        $list=$this->http_request($url,$ree);
        $re=json_decode($list,true);
        if($re['']){

        }


    }*/




}
?>