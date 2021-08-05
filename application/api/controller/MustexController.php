<?php
namespace app\api\controller;
use app\api\controller\base\BaseController;
use think\facade\Log;
use think\facade\Request;
use think\Exception;
use think\Db;
use app\common\model\CheckvipModel;
use app\common\model\TakenotesModel;
use app\common\model\JzDetailedModel;
use app\common\model\JzContactunitModel;
use app\common\model\JzRevenueModel;
use app\common\model\JzReceivableModel;
use app\common\model\JzBookkeepingModel;
use app\common\model\JzAccountModel;
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
class MustexController extends BaseController
{

	//导出excle 应收应付预收预付导出
	public function addex(){
		$res=$this->chektoken();//检测用户token
		if(!$res){
			return json_encode(['code' =>2, 'msg'=>'请登录' ]);
		}
		if($res['email']==''){
			return $this->json_error('请先绑定邮箱');
		}
		$request = Request();
    	$data = $request->param();
        $time=date('Y-m-d',time());
        $receivable= new JzReceivableModel();
        $contactunit= new JzContactunitModel();
        $checkvip= new CheckvipModel();
        if($res['uvip']==0){
            //获取当月导出的次数
            $unm=$checkvip->getNum(['uid'=>$res['id'],'type'=>0]);
            if($unm > 0){
                return json_encode(['code'=>3,'msg'=>'您的免费次数已用完,成为vip用户不限量导出','result'=>'']);
            }
        }
        $list=$receivable->where(['uid'=>$res['id'],'type'=>$data['type'],'state'=>0])->where('surplus > 0')->order('id desc')->select();
        if($list) {
            //获取列表数据
            for ($i = 0; $i < count($list); $i++) {
                $contact = $contactunit->getOne(['id' => $list[$i]['contactunitid']]);
                //计算逾期时间 /天
                // 创建日期时间对象
                $date1 = date_create($list[$i]['incometime']);
                $date2 = date_create($time);
                $interval = date_diff($date1, $date2);
                $yuqi = $interval->format('%R%a 天');
                $rows[$i] = [
                    'contactunit' => $contact['title'],
                    'date' => $list[$i]['incometime'],
                    'money' => $list[$i]['money'],
                    'surplus' => $list[$i]['surplus'],
                    'time' => $yuqi
                ];
            }
            //详细表格0-应收 1-应付 2-预收 3-预付
            if ($data['type'] == 0) {
                $head = ['往来单位', '发生日期', '应收金额', '剩余金额', '逾期时间'];
            } elseif ($data['type'] == 1) {
                $head = ['往来单位', '发生日期', '应付金额', '剩余金额', '逾期时间'];
            } elseif ($data['type'] == 2) {
                $head = ['往来单位', '发生日期', '预收金额', '剩余金额', '逾期时间'];
            } elseif ($data['type'] == 3) {
                $head = ['往来单位', '发生日期', '预付金额', '剩余金额', '逾期时间'];
            } else {
                return $this->json_error('参数错误');
            }
            $xname = 'xiangxi_' . $res['id'] . '_' . $time;
            $bi = [
                'cols' => $head,
                'rows' => $rows
            ];
            $mu = './uploads/file/xlx/' . $res['id'] . '/' . $xname . '.xlsx';
            $this->ec($bi, $xname, $mu);//print_r($a);exit;//生成详细表格
            $ee = $this->sendemail($mu, $res);
            if ($ee) {
                return $this->json_success('发送成功');
            }else{
                return $this->json_error('发送失败');
            }
        }else{
            return $this->json_error('此类型没有数据可导出');
        }
	}


	public function ec($data,$title,$mu){

        $e = myphpexcel::exportexcel($data,$title,$mu);
        return $e;
    }


	//确认发送
	public function sendemail($mu,$res){
    	$take= new TakenotesModel();
        $checkvip= new CheckvipModel();
		$toemail=$res['email'];//收件人邮箱
        $name='亲爱的'.$res['nickname'].'，您好！';//接收邮件者名称
        $subject='发票全能王-记账';//邮件主题
        $content='感谢使用本公司产品，产品正在不断优化，欢迎您提出宝贵意见！';//邮件内容
        $attachment=[$mu];
        //调用方法发送邮件
        $cpemail=[];
        $info=send_mail($toemail,$name,$cpemail,$subject,$content,$attachment);
        if($info==true){
        	$take->getInc(['uid'=>$res['id']],'expnumber');
            $checkvip->adddata(['uid'=>$res['id'],'type'=>0,'ctime'=>date('Y-m-d H:i:s',time())]);
        	//return $this->json_success('发送成功');
        	return true;
        }else{
        	//return $this->json_error('发送失败');
        	return false;
        }
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

    //账户的导出
    public function zhangexcel(){
        $res=$this->chektoken();//检测用户token
        if(!$res){
            return json_encode(['code' =>2, 'msg'=>'请登录' ]);
        }
        if($res['email']==''){
            return $this->json_error('请先绑定邮箱');
        }
        $request = Request();
        $data = $request->param();
        $checkvip= new CheckvipModel();
        $time=date('Y-m-d',time());
        $contactunit= new JzContactunitModel();
        if($res['uvip']==0){
            //获取当月导出的次数
            $unm=$checkvip->getNum(['uid'=>$res['id'],'type'=>0]);
            if($unm > 0){
                return json_encode(['code'=>3,'msg'=>'您的免费次数已用完,成为vip用户不限量导出','result'=>'']);
            }
        }
        $list=Db::table('fp_jz_revenue')->where(['uid'=>$res['id'],'accountid'=>$data['id'],'state'=>0])->order('id desc')->select();
        if($list){
            //获取列表数据
            $rows=[];
            for($i=0;$i<count($list);$i++) {
                $contact=$contactunit->getOne(['id'=>$list[$i]['contactunitid']]);
                $accoun=Db::table('fp_jz_account')->where(['id'=>$list[$i]['accountid']])->find();
                if($list[$i]['type']==0){
                    $lei='收入';
                }else{
                    $lei='支出';
                }
                $rows[$i]=[
                    'contactunit'=>$contact['title'],
                    'date'=>$accoun['title'],
                    'type'=>$lei,
                    'money'=>$list[$i]['money'],
                    'time'=>$list[$i]['incometime']
                ];
            }
            $info = Db::table('fp_jz_account')->where('id='.$data['id'])->find();
            $asdd=[
                'contactunit'=>'初始金额',
                'date'=>$info['title'],
                'type'=>'初始金额',
                'money'=>$info['icon'],
                'incometime'=>date("Y-m-d",strtotime($info['ctime']))
            ];
            array_push($rows,$asdd);
            $head=['往来单位','收款账户','类型','金额','进/出账时间'];
            $xname='xiangxi_'.$res['id'].'_'.$time;
            $bi=[
                'cols' =>$head,
                'rows' =>$rows
            ];
            $mu='./uploads/file/xlx/'.$res['id'].'/'.$xname.'.xlsx';
            $this->ec($bi,$xname,$mu);//print_r($a);exit;//生成详细表格
            $ee=$this->sendemail($mu,$res);
            if($ee){
                return $this->json_success('发送成功');
            }else{
                return $this->json_error('发送失败');
            }
        }else{
            return $this->json_error('此账户没有数据可导出');
        }
    }

    //收入支出导出
    public function shouzhiexcel(){
        $res=$this->chektoken();//检测用户token
        if(!$res){
            return json_encode(['code' =>2, 'msg'=>'请登录' ]);
        }
        if($res['email']==''){
            return $this->json_error('请先绑定邮箱');
        }
        $request = Request();
        $data = $request->param();
        $checkvip= new CheckvipModel();
        $revenue= new JzRevenueModel();
        $time=date('Y-m-d',time());
        $contactunit= new JzContactunitModel();
        if($res['uvip']==0){
            //获取当月导出的次数
            $unm=$checkvip->getNum(['uid'=>$res['id'],'type'=>0]);
            if($unm > 0){
                return json_encode(['code'=>3,'msg'=>'您的免费次数已用完,成为vip用户不限量导出','result'=>'']);
            }
        }
        if(empty($data['time'])){
            $list=$revenue->where(['type'=>$data['type'],'uid'=>$res['id'],'state'=>0])->order('incometime desc')->select();
        }else{
            $wh[] = ['incometime','like','%'.$data['time'].'%'];
            $list=$revenue->where(['type'=>$data['type'],'uid'=>$res['id'],'state'=>0])->where($wh)->order('incometime desc')->select();
        }
        if($list){
            //获取列表数据
            $rows=[];
            for($i=0;$i<count($list);$i++) {
                $contact=$contactunit->getOne(['id'=>$list[$i]['contactunitid']]);
                $accoun=Db::table('fp_jz_account')->where(['id'=>$list[$i]['accountid']])->find();
                if($list[$i]['type']==0){
                    $lei='收入';
                }else{
                    $lei='支出';
                }
                $rows[$i]=[
                    'contactunit'=>$contact['title'],
                    'date'=>$accoun['title'],
                    'type'=>$lei,
                    'money'=>$list[$i]['money'],
                    'time'=>$list[$i]['incometime']
                ];
            }
            $head=['往来单位','收款账户','类型','金额','进/出账时间'];
            $xname='xiangxi_'.$res['id'].'_'.$time;
            $bi=[
                'cols' =>$head,
                'rows' =>$rows
            ];
            $mu='./uploads/file/xlx/'.$res['id'].'/'.$xname.'.xlsx';
            $this->ec($bi,$xname,$mu);//print_r($a);exit;//生成详细表格
            $ee=$this->sendemail($mu,$res);
            if($ee){
                return $this->json_success('发送成功');
            }else{
                return $this->json_error('发送失败');
            }
        }else{
            return $this->json_error('此账户没有数据可导出');
        }
    }

    //固定资产和其它导出
    public function guqiexcel(){
        $res=$this->chektoken();//检测用户token
        if(!$res){
            return json_encode(['code' =>2, 'msg'=>'请登录' ]);
        }
        if($res['email']==''){
            return $this->json_error('请先绑定邮箱');
        }
        $request = Request();
        $data = $request->param();
        $checkvip= new CheckvipModel();
        $revenue= new JzRevenueModel();
        $time=date('Y-m-d',time());
        $contactunit= new JzContactunitModel();
        if($res['uvip']==0){
            //获取当月导出的次数
            $unm=$checkvip->getNum(['uid'=>$res['id'],'type'=>0]);
            if($unm > 0){
                return json_encode(['code'=>3,'msg'=>'您的免费次数已用完,成为vip用户不限量导出','result'=>'']);
            }
        }
        $list=Db::name('fp_jz_fixed')->where(['uid'=>$res['id'],'type' => $data['type']])->select();
        if($list){
            //获取列表数据
            $rows=[];
            for($i=0;$i<count($list);$i++) {
                $rows[$i]=[
                    'contactunit'=>$list[$i]['title'],
                    'date'=>$list[$i]['incometime'],
                    'money'=>$list[$i]['money'],
                    'remarks'=>$list[$i]['remarks']
                ];
            }
            $head=['名称','时间','金额','备注'];
            $xname='xiangxi_'.$res['id'].'_'.$time;
            $bi=[
                'cols' =>$head,
                'rows' =>$rows
            ];
            $mu='./uploads/file/xlx/'.$res['id'].'/'.$xname.'.xlsx';
            $this->ec($bi,$xname,$mu);//print_r($a);exit;//生成详细表格
            $ee=$this->sendemail($mu,$res);
            if($ee){
                return $this->json_success('发送成功');
            }else{
                return $this->json_error('发送失败');
            }
        }else{
            return $this->json_error('此账户没有数据可导出');
        }
    }


	
}
?>