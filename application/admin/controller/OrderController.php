<?php
namespace app\admin\controller;

use app\common\model\JtdatModel;
//use app\common\model\BannerModel;
use app\common\model\ReceiptModel;
use app\common\model\ProductModel;
use app\common\model\ProductHaveModel;;
use think\facade\Request;
use think\Db;
use app\common\model\UserModel;
use PHPExcel_Worksheet;
use mylib\myphpexcel;

class OrderController extends BaseController
{
	public function _initialize()
	{
		parent::_initialize();
	}
	public function index()
	{	$request = Request::instance();
		$data = $request->param();
		$receipt= new ReceiptModel();
		if (Request::instance()->isPost()){
			//print_r($_POST);exit;
			$state    = $_POST['state'];
			$id 	  = $_POST['id'];
			if(is_numeric($state)) {
				$date=['state'=>$state];
				$wh=['id'=>$id];
				$result=$receipt->where($wh)->update($date);
				if($result[1]==0){
					echo json_encode(array("result"=>1));    //发送成功
				}else{
					echo json_encode(array("result"=>2));  //发送失败
				}
			}
		}
		$where=[];
        if(empty($data['pattern'])){
            unset($data['pattern']);
        }else{
            $where['pattern']=$data['pattern'];
        }
		$search = $data['keyword'];
		if($data['keyword'] !== '' && isset($data['keyword'])){
			$where['state']=$search;
		}
		//print_r($where);exit;
		$list = $receipt->alias("u")
    		->field('u.*')
	    	->where($where)->order('u.id desc')->paginate(20,false, [
                'query' => Request::instance()->param(),//不丢失已存在的url参数
            ]);
		$data['list']=$list;
		$page = $list->render();
		$weistr=$receipt->getNum(['pattern'=>"微信",'state'=>1]);
        $zhistr=$receipt->getNum(['pattern'=>"支付宝",'state'=>1]);
        $iosstr=$receipt->getNum(['pattern'=>"ios内购",'state'=>1]);
		$this->assign('weistr', $weistr);
        $this->assign('zhistr', $zhistr);
        $this->assign('iosstr', $iosstr);
        $this->assign('page', $page);
		//$this->assign('list', $list);
        //print_r($iosstr);exit;
		return $this->fetch('/base',['_view'=>'order/index','data'=>$data]);
	}


    public function addsave()
    {
        $data = array();
        $module = array();
        $arr = array();
        $receipt= new ReceiptModel();
        $request = Request::instance();
        $data = $request->param();
        $file = request()->file('file');
        if($file){
            $path = '/uploads/file/';
            $info = $file->move(BASEPATH .$path);
            if($info){
                // 成功上传后 获取上传信息
                // 输出 jpg
                $uploadFilename = $path.$info->getSaveName();;
                $arr['url'] = $uploadFilename;
            }else{
                // 上传失败获取错误信息
                return $file->getError();
            }
        }
        if(isset($arr['url'])){
            $data['data']['photo']=INLET_PATH.$arr['url'];
        }
        $opt = isset($data['opt'])?$data['opt']:0;
        $id = isset($data['id'])?$data['id']:0;

        if(!empty($opt) && $opt=='save'){
            //保存
            $datas = array();
            $datas = $data['data'];
            //print_r($datas);exit;
            $datas['password'] = md5($datas['password']);
            if($id>0){
                //update
                $receipt->where(array('id'=>$id))->update($datas);
                //$user->updatedatas(,);
                $this->success('修改成功', 'admin/banner/index');
            }else{
                //insert
                $datas['ctime']= date('Y-m-d H:i:s',time());
                $receipt->insert($datas);
                $this->success('新增成功', 'admin/banner/index');
            }
            //header("Location:/manager/settings_modulelist/");
            exit;
        }

        if($id>0){
            $module = $receipt->where(array('id'=>$id))->find();
        }
        $data['module'] = $module;
        //print_r($data);exit;
        return $this->fetch('/base',['_view'=>'banner/edit','data'=>$data]);
    }


    //导出数据
    public function exceldc(){
        $request = Request::instance();
        $data = $request->param();
        //print_r($data);exit;
        $receipt    = new ReceiptModel();
        $user= new UserModel();
        $daytime = date('Y-m-d');
        $daytimeend = date('Y-m-d');
        $ayrrsy=[];
        $where=['state'=>1];
        if(!empty($data['stime'])) {
            $daytime = $data['stime'];
            $daytime = explode(' - ',$daytime);
            $daytimeend = $daytime[1];
            $daytime = $daytime[0];
        }
        $dc = $data['dc'];
        $wheretime = "ctime >='".$daytime.' 00:00:00'."' and ctime<='".$daytimeend.' 23:59:59'."'";
        if($data['stime']){
            if($dc==1){
                $wheretime = "ctime >='".$daytime.' 00:00:00'."' and ctime<='".$daytimeend.' 23:59:59'."'";
            }elseif($dc==2){
                $dalist=$this->dayList($daytime,$daytimeend);
                //print_r($dalist);exit;
                if(empty($dalist)){
                    $this->error('请选择时间范围！', 'admin/order/exceldc');
                }
            }
        }
        $list =$receipt->alias("u")
            ->field('u.*')
            ->where($wheretime)
            ->where($where)
            ->order('u.id desc')->paginate(20,false, [
                'query' => Request::instance()->param(),//不丢失已存在的url参数
            ]);

        if($dc == 1){
            $earrs =$receipt->alias("u")
                ->field('u.*')
                ->where($wheretime)
                ->where($where)
                ->order('u.id desc')->select();
            $earrs=$earrs->toArray();
            //print_r($earrs);exit;
            $title = $daytime."-".$daytimeend.'明细列表';
            $e = myphpexcel::setheader($title);
            //封面excel
            $clone = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T');

            $shee = 1;
            $msgWorkSheet = new PHPExcel_Worksheet($e, '明细列表'); //创建一个工作表
            $e->addSheet($msgWorkSheet); //插入工作表
            $e->setActiveSheetIndex($shee); //切换到新创建的工作表

            $arrs = ['id','金额','方式','来源','订单日期'];
            $row = 1;
            foreach ($arrs as $k=>$av){
                $e->getActiveSheet()->setCellValue($clone[$k].$row, $av);
            }

            $e->getActiveSheet()->freezePane('A1');
            $row++;
            foreach ($earrs as $k=>$v){
//                if(preg_match("/(.{8}-.{4}-.{4}-.{4}-.{12})/",$v['idfa'])){
//                    $v['lei']='ios';
//                }else{
//                    $v['lei']='android';
//                }
                $hbstart = $row;
                $e->getActiveSheet()->setCellValue('A'.$row, $v['id']);
                $e->getActiveSheet()->setCellValue('B'.$row, $v['money']);
                $e->getActiveSheet()->setCellValue('C'.$row, $v['pattern']);
                $e->getActiveSheet()->setCellValue('D'.$row, $v['source']);
                $e->getActiveSheet()->setCellValue('E'.$row, $v['ctime']);
                $row++;
            }
            //exit;
            myphpexcel::setfooter($e,$title);
        }elseif($dc == 2){
            //处理统计问题
            foreach ($dalist as $k=>$v){
                //获取当天的注册人数
                $wheretime = "ctime >='".$v.' 00:00:00'."' and ctime<='".$v.' 23:59:59'."'";
                $znum=$user->where($wheretime)->count();
                $cnum=$receipt->where($wheretime)->where(['state'=>1])->count();
                $sum=$receipt->where($wheretime)->where(['state'=>1])->sum('money');
                $ayrrsy[]=[
                    'day'=>$v,
                    'znum'=>$znum,
                    'cnum'=>$cnum,
                    //'lv'=>round(,4) ."%",
                    //'lv'=>sprintf("%.2f",$cnum/$znum * 100) ."%",
                    'sum'=>$sum
                ];
            }
            $title = $daytime."-".$daytimeend.'统计列表';
            $e = myphpexcel::setheader($title);
            //封面excel
            $clone = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T');

            $shee = 1;
            $msgWorkSheet = new PHPExcel_Worksheet($e, '明细列表'); //创建一个工作表
            $e->addSheet($msgWorkSheet); //插入工作表
            $e->setActiveSheetIndex($shee); //切换到新创建的工作表

            $arrs = ['日期','注册人数','充值人数','每日充值金额'];
            $row = 1;
            foreach ($arrs as $k=>$av){
                $e->getActiveSheet()->setCellValue($clone[$k].$row, $av);
            }

            $e->getActiveSheet()->freezePane('A1');
            $row++;
            foreach ($ayrrsy as $k=>$v){
//                if(preg_match("/(.{8}-.{4}-.{4}-.{4}-.{12})/",$v['idfa'])){
//                    $v['lei']='ios';
//                }else{
//                    $v['lei']='android';
//                }
                $hbstart = $row;
                $e->getActiveSheet()->setCellValue('A'.$row, $v['day']);
                $e->getActiveSheet()->setCellValue('B'.$row, $v['znum']);
                $e->getActiveSheet()->setCellValue('C'.$row, $v['cnum']);
                //$e->getActiveSheet()->setCellValue('D'.$row, $v['lv']);
                $e->getActiveSheet()->setCellValue('D'.$row, $v['sum']);
                $row++;
            }
            //exit;
            myphpexcel::setfooter($e,$title);
        }elseif($dc==3){
            $dalist=$this->dayList($daytime,$daytimeend);
            //print_r($dalist);exit;
            foreach ($dalist as $k=>$v){
                //获取当天的注册人数
                $wheretime = "ctime >='".$v.' 00:00:00'."' and ctime<='".$v.' 23:59:59'."'";
                $znum=$user->where($wheretime)->count();
                $cnum=$receipt->where($wheretime)->where(['state'=>1])->count();
                $sum=$receipt->where($wheretime)->where(['state'=>1])->sum('money');
                $ayrrsy[]=[
                    'day'=>$v,
                    'znum'=>$znum,
                    'cnum'=>$cnum,
                    //'lv'=>round(,4) ."%",
                    //'lv'=>sprintf("%.2f",$cnum/$znum * 100) ."%",
                    'sum'=>$sum
                ];
            }
            $data['listss']=$ayrrsy;
            return $this->fetch('/base',['_view'=>'order/exceltj','data'=>$data]);
        }

        $data['list'] = $list;
        //$data['listss']=$ayrrsy;
        $page = $list->render();
        $this->assign('page', $page);
        return $this->fetch('/base',['_view'=>'order/exceldc','data'=>$data]);
    }

    /***
     * 返回两个时间内所有的时间集合 单位：天
     * @param $startTime  开始时间：eg示例：2020-04-01
     * @param $endTime    结束时间：eg示例：2020-04-20
     * @return array      返回集合列表
     */
    public  function dayList($startTime,$endTime)
    {
        $dayList  = [];
        if(strtotime($startTime) <= strtotime($endTime)) {
            do {
                //向dayList尾部添加日期
                array_push($dayList,date('Y-m-d',strtotime($startTime)));
                //计算下次添加日期
                $startTime = date("Y-m-d",strtotime("+1 day",strtotime($startTime)));
                //判读条件
            } while (strtotime($startTime) <=  strtotime($endTime));
        }
        //返回结果
        return $dayList;
    }

    /**
     * 21000 App Store不能读取你提供的JSON对象
     * 21002 receipt-data域的数据有问题
     * 21003 receipt无法通过验证
     * 21004 提供的shared secret不匹配你账号中的shared secret
     * 21005 receipt服务器当前不可用
     * 21006 receipt合法，但是订阅已过期。服务器接收到这个状态码时，receipt数据仍然会解码并一起发送
     * 21007 receipt是Sandbox receipt，但却发送至生产系统的验证服务
     * 21008 receipt是生产receipt，但却发送至Sandbox环境的验证服务
     */
    public function acurl($receipt_data, $sandbox=0){

        //小票信息
        //$POSTFIELDS = array("receipt-data" => $receipt_data);
        //$POSTFIELDS = json_encode($POSTFIELDS);
        $POSTFIELDS=$receipt_data;
//print_r($POSTFIELDS);exit;
        //正式购买地址 沙盒购买地址
        $url_buy     = "https://buy.itunes.apple.com/verifyReceipt";
        $url_sandbox = "https://sandbox.itunes.apple.com/verifyReceipt";
        $url = $sandbox ? $url_sandbox : $url_buy;
        //简单的curl
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $POSTFIELDS);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        curl_close($ch);
        //print_r($result);exit;
        return $result;
    }

    //ios充值验证
    public function xiang(){
        $request = Request();
        $res = $request->param();
        $receipt    = new ReceiptModel();
        $product    = new ProductModel();
        $producth    = new ProductHaveModel();
        $User       = new UserModel();
        //查询此此订单信息
        $ss=$receipt->getInfo(['id'=>$res['id']]);
        //请求验证
        // $html = $this->acurl($ss['receipt'],$sandbox = 1);
        $html = $this->acurl($ss['receipt'],$sandbox = false);
        $data = json_decode($html,1);

        if($data['status']=='21005'){
            $st['test']='receipt服务器当前不可用';
            Db::name('db_test')->insert($st);
            return $this->json_error('receipt服务器当前不可用');
        }
        //如果是沙盒数据 则验证沙盒模式
        if($data['status']=='21007'){
            //请求验证
            //print_r($data);exit;
            $html = $this->acurl($ss['receipt'], $sandbox=1);
            $data = json_decode($html,1);
            $data['sandbox'] = '1';
            //return json_encode(['code'=>0,'msg'=>'ok','result'=>$data]);//返回沙盒测试数据（测试用）
        }
        //print_r($data);exit;
        if (isset($_GET['debug'])) {
            exit(json_encode($data));
        }
        $data = json_decode($html,1);
        $check=0;
        $etime=date('Y-m-d H:i:s',time());
        if(!empty($data['receipt']['in_app'])){
            for($i=0;$i<count($data['receipt']['in_app']);$i++){
                if($ss['outtradeno']==$data['receipt']['in_app'][$i]['transaction_id']){
                    $check=1;
                    $etime=substr($data['receipt']['in_app'][$i]['original_purchase_date_ms'],0,10);
                    $etime=date("Y-m-d H:i:s",$etime);
                    break;
                }
            }
        }
        if($check==0){
            $st['test']='此订单不合法！';
            Db::name('db_test')->insert($st);
            $receipt->updatedatas(['checkproof'=>1,'comment'=>json_encode($data)],['id'=>$res['id']]);
            return $this->json_error('此订单不合法！');
        }
        if($data['status']==0){
            $info=$User->getInfo(['id'=>$ss['uid']]);
            if($ss['type']==1){
                $rqq=$producth->getInfo(['id'=>$ss['productid']]);
                $receipt->updatedatas(['state'=>1,'checkproof'=>1,'etime'=>$etime,'comment'=>json_encode($data)],['id'=>$res['id']]);
                $num=$info['frequency']+$rqq['number'];
                $zong=$info['totalnum']+$rqq['number'];
                $User->updatedatas(['frequency' => $num, 'totalnum' => $zong], ['id'=>$info['id']]);//修改用户次数
            }else{
                $rqq=$product->getInfo(['id'=>$ss['productid']]);
                $receipt->updatedatas(['state'=>1,'etime'=>$etime,'comment'=>json_encode($data),'checkproof'=>1],['id'=>$res['id']]);
                //获取支付的订单的商品id，给用户添加会员周期
                if($info['uvip']==0){
                    //不是会员，修改会员时间和vip状态
                    $start_time=time();
                }else{
                    $start_time=strtotime($info['totime']);
                }
                if (!$rqq) {
                    return $this->json_error('订单不存在');
                }
                $time=date('Y-m-d H:i:s',strtotime("+".$rqq['number']." month",$start_time ));
                $num=$info['frequency']+$rqq['frequency'];
                $zong=$info['totalnum']+$rqq['frequency'];
                $User->updatedatas(['uvip'=>1,'totime'=>$time,'frequency' => $num, 'totalnum' => $zong],['id'=>$info['id']]);//修改用户vip信息
            }
            echo json_encode(array("result"=>1));    //重新获取成功
        }else{
            echo json_encode(array("result"=>2));    //重新获取失败
        }

    }












}
