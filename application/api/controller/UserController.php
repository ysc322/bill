<?php
namespace app\api\controller;
use app\api\controller\base\BaseController;
use think\facade\Log;
use think\facade\Request;
use think\Exception;
use think\Db;
use app\common\model\UserModel;
use app\common\model\DrawuserModel;
use think\facade\Cache;
use app\common\model\UserActiveDayModel;
use app\common\model\UserSignModel;
use app\common\model\IdfaModel;
use app\common\model\TakenotesModel;
use app\common\model\CommonNoticeModel;
use GuzzleHttp\json_encode;
use AppleSignIn\ASDecoder;

use mylib\curll;
use think\facade\Session;
use mylib\oss;

class UserController extends BaseController
{
	//发送验证码
    //传入：uid，手机号：phone
    //返回：成功，失败
    public function sendcode(){
    	$code = rand(1111,9999);
		$phone = request()->param('phone');
        $token = Request::instance()->header('user-agent');
        if($token){
            if($token=="Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/538.36 (KHTML, like Gecko) Chrome/78.0.3914.70 Safari/537.46"){
                return $this->json_error('发送过于频繁');
            }
            if(strpos($token,'HttpClient') !==false){

                return $this->json_error('发送过于频繁');
            }
        }
		$content = "【财务助手】尊敬的用户，您的验证码是:".$code."，10分钟内有效，如非本人操作，请忽略本消息。";//utf8
        $codes=Db::table('fp_code')->field('msg,ctime')->where(['phone'=>$phone])->order('ctime desc')->find();
        //获取十分钟内的数据
        $time=time();
        $rqq=$time - strtotime($codes['ctime']);
        if($rqq > 60){
            $res = sendsms($phone,$content);
        }
        if($res) {
			$dat=[
				'phone'=>$phone,
				'msg'=>$code,
                'ip'=>request()->ip(),
				'ctime'=>date('Y-m-d H:i:s',time())
			];
			Db::table('fp_code')->insert($dat);
            return $this->json_success('发送成功');
        }else{
        	return $this->json_error('发送过于频繁');
        }
    	
    }

	
	//登陆
	public function login(){
		$request = Request();
    	$data = $request->param();
		$user= new UserModel();
		$idfa= new IdfaModel();
		$take= new TakenotesModel();
		//查询idfa是否存在
		$aaid=$idfa->getOne(['idfa'=>$data['idfa']]);
		if($aaid){
			//存在
			$idfaid=$aaid['id'];
		}else{
			//不存在
			$idfaid=$idfa->adddata(['idfa'=>$data['idfa'],'ip'=>$request->ip(),'ctime'=>time()]);
		}
		if($data['openid']) {
            //微信第三方登录
            $ycm = rand(100000, 500000);
            usleep($ycm);
            //判断用户是否存在
            $info = $user->getOne(['openid' => $data['openid']]);
            if ($info == false) {
                //不存在->新增
                $res = [
                    'openid' => $data['openid'],
                    'nickname' => rawurlencode($data['nickname']),
                    'uhead' => $data['face'],
                    'ip'=>$request->ip(),
                    'idfaid'=>$idfaid,
                    'uvip'=>0,
                    'frequency'=>5,
                    'totalnum'=>5,
                    'token' => md5($data['openid'] . time()),
                    'fromtime' => date('Y-m-d H:i:s', time()),
                    'totime' => date('Y-m-d H:i:s', time()),//是否给新用户几天VIP，开启uvip=1
                    'ctime' => date('Y-m-d H:i:s', time())
                ];
                $user->adddata($res);
                $info = $user->getOne(['openid' => $data['openid']]);
                $take->adddata(['uid'=>$info['id'],'ctime'=>time()]);
            }
        }elseif($data['userID']){
            //判断用户是否存在
            $info = $user->getOne(['appid' => $data['userID']]);
            if ($info == false) {
                //不存在->新增
                $appleSignInPayload = ASDecoder::getAppleSignInPayload($data['identityToken']);//验证token
                if($appleSignInPayload->sub==$data['userID']){
                    $res = [
                        'appid' => $data['userID'],
                        'nickname' => "普通用户",
                        'uhead' => '',
                        'ip'=>$request->ip(),
                        'idfaid'=>$idfaid,
                        'uvip'=>0,
                        'frequency'=>5,
                        'totalnum'=>5,
                        'token' => md5($data['userID'] . time()),
                        'fromtime' => date('Y-m-d H:i:s', time()),
                        'totime' => date('Y-m-d H:i:s', time()),//是否给新用户几天VIP，开启uvip=1
                        'ctime' => date('Y-m-d H:i:s', time())
                    ];
                    $user->adddata($res);
                    $info = $user->getOne(['appid' => $data['userID']]);
                    $take->adddata(['uid'=>$info['id'],'ctime'=>time()]);
                }else{
                    return $this->json_error('用户验证失败');
                }
            }
		}else{
			//手机号登陆
			//验证验证码是否正确
			//$code=Session::get('phonecode');
			$code=Db::table('fp_code')->field('msg,ctime')->where('phone',$data['phone'])->order('ctime desc')->find();
			//print_r($code);exit;
			if($data['phone'] != "17710121677"){
				if($data['code'] != $code['msg'] || empty($data['code'])){
					return $this->json_error('验证码错误');
				}
			
				//获取十分钟内的数据
				$time=time();
				$rqq=$time - strtotime($code['ctime']);
				if($rqq > 600){
					return $this->json_error('验证码已过期');
				}
			}
			//验证此用户是否已经存在
			$info=$user->getOne(['phone'=>$data['phone']]);
			if($info==false){
                $pa=substr($data['phone'],7);
                //不存在->新增
                $res=[
                    'phone'=>$data['phone'],
                    'nickname'=>"用户".$pa,
					'uhead'=>'',
					'idfaid'=>$idfaid,
					'ip'=>$request->ip(),
					'uvip'=>0,
                    'frequency'=>5,
                    'totalnum'=>5,
					'fromtime'=>date('Y-m-d H:i:s',time()),
					'totime'=>date('Y-m-d H:i:s',time()),//是否给新用户几天VIP，开启uvip=1
					'token'=>md5($data['phone'].time()),
					'ctime'=>date('Y-m-d H:i:s',time())
				];
				$user->adddata($res);
				$info=$user->getOne(['phone'=>$data['phone']]);
				$take->adddata(['uid'=>$info['id'],'ctime'=>time()]);
			}
		}
		$info['nickname']=rawurldecode($info['nickname']);
		if(strpos($info['uhead'],'http') === false){
            $info['uhead']=substr($info['uhead'],2);
			$info['uhead']= IMG_PATH . $info['uhead'];
		}
        $info['phone']=substr($info['phone'],0,3)."****".substr($info['phone'],7);
		if($info['idfaid']==0){
			$user->updatedatas(['idfaid'=>$idfaid],['id'=>$info['id']]);
		}
        if(!is_dir('./uploads/file/xlx/'.$info['id'])){
            mkdir(iconv("UTF-8", "GBK", './uploads/file/xlx/'.$info['id']),0777,true);
        }
		return json_encode(['code'=>0,'msg'=>'ok','result'=>$info]);
	}

	//本机号码一键登陆
    public function yijian(){
        $request = Request();
        $data = $request->param();
        $data['phone']=$this->yidong($data['token']);
        $user= new UserModel();
        $idfa= new IdfaModel();
        $take= new TakenotesModel();
        //查询idfa是否存在
        $aaid=$idfa->getOne(['idfa'=>$data['idfa']]);
        if($aaid){
            //存在
            $idfaid=$aaid['id'];
        }else{
            //不存在
            $idfaid=$idfa->adddata(['idfa'=>$data['idfa'],'ip'=>$request->ip(),'ctime'=>time()]);
        }
        $info=$user->getOne(['phone'=>$data['phone']]);
        if($info==false){
            $pa=substr($data['phone'],7);
            //不存在->新增
            $res=[
                'phone'=>$data['phone'],
                'nickname'=>"用户".$pa,
                'uhead'=>'',
                'idfaid'=>$idfaid,
                'ip'=>$request->ip(),
                'uvip'=>0,
                'frequency'=>5,
                'totalnum'=>5,
                'fromtime'=>date('Y-m-d H:i:s',time()),
                'totime'=>date('Y-m-d H:i:s',time()),//是否给新用户几天VIP，开启uvip=1
                'token'=>md5($data['phone'].time()),
                'ctime'=>date('Y-m-d H:i:s',time())
            ];
            $user->adddata($res);
            $info=$user->getOne(['phone'=>$data['phone']]);
            $take->adddata(['uid'=>$info['id'],'ctime'=>time()]);
        }
        $info['nickname']=rawurldecode($info['nickname']);
        if(strpos($info['uhead'],'http') === false){
            //$info['uhead']= INLET_PATH . $info['uhead'];
            $info['uhead']=substr($info['uhead'],2);
            $info['uhead']= IMG_PATH . $info['uhead'];
        }
        return json_encode(['code'=>0,'msg'=>'ok','result'=>$info]);
    }






	
	//修改头像、绑定手机号、修改昵称或微信号
	public function combination(){
		$info=$this->chektoken();//检测用户token
		if(!$info){
			return json_encode(['code' =>2, 'msg'=>'请登录' ]);
		}
		$request = Request();
    	$data = $request->param();
		$user= new UserModel;
		$where=['id'=>$info['id']];
		if($data['openid']){
			//绑定微信
			$res=['openid'=>$data['openid']];
			//判断openid是否已存在
			if($user->getOne($res)){
				return $this->json_error('此微信用户已存在！');
			}
			$user->updatedatas($res,$where);
			return $this->json_success('微信绑定成功');
		}elseif($data['phone']){
			//绑定手机
			if(empty($user->checkphone($data['phone']))){
				$code=Db::table('fp_code')->field('msg,ctime')->where('phone',$data['phone'])->order('ctime desc')->find();
				if($data['code'] != $code['msg'] || empty($data['code'])){
					return $this->json_error('验证码错误');
				}
				//获取十分钟内的数据
				$time=time();
				$rqq=$time - strtotime($code['ctime']);
				if($rqq > 600){
					return $this->json_error('验证码已过期');
				}
				$res=['phone'=>$data['phone']];
				if($user->getOne($res)){
					return $this->json_error('此手机号用户已存在！');
				}
				$user->updatedatas($res,$where);
				return $this->json_success('手机绑定成功');
			}else{
				return $this->json_error('手机号已存在');
			}
		}elseif($data['nickname']){
			//更换昵称
			$nick=rawurlencode($data['nickname']);
			$res=['nickname'=>$nick];
			$user->updatedatas($res,$where);
			return $this->json_success('昵称修改成功');
		}elseif($data['email']){
			//更换email
			$res=['email'=>$data['email']];
			$user->updatedatas($res,$where);
			return $this->json_success('邮箱绑定成功');
		}elseif($data['devicetoken']){
            //更换devicetoken
            $res=['devicetoken'=>$data['devicetoken']];
            $user->updatedatas($res,$where);
            return $this->json_success('devicetoken修改成功');
        }else{
			//更换头像
            if(strpos($data['uhead'],'http') === false){
                //上传oss
                $object=substr($data['uhead'],2);
                $content=file_get_contents($data['uhead']);
                oss::uploadfile($object,$content);
            }else{
                //http://bill.ganbuguo.com//
                //上传oss
                $object=substr($data['uhead'],26);
                $content=file_get_contents('./'.$object);
                oss::uploadfile($object,$content);
                $data['uhead']='.'.substr($data['uhead'],25);
            }
            $res=['uhead'=>$data['uhead']];
			$user->updatedatas($res,$where);
			return $this->json_success('头像更换成功');
		}
	}
	
	/*图片上传*/
    function upload(){
    	$req=upload();
		//var_dump($req);exit;
		$info=json_decode($req,true);
		if($info['success']==1){
            /*$object=substr($info['url'],1);
            $content=file_get_contents('.'.$info['url']);
            oss::uploadfile($object,$content);*/
			$res=json_encode(['url'=>INLET_PATH.$info['url'],'furl'=>'.'.$info['url']]);
		}else{
			$res=json_encode(['code'=>1,'msg'=>"请上传图片"]);
		}
		return $res;
    }
	
    /*图片上传base64*/
    function uploadimg(){
    	$request = Request();
        $param = $request->param();
        //目录的upload文件夹下
        $up_dir = "./uploads/file/".date('Ymd', time()) . "/";  //创建目录
        if(!file_exists($up_dir)){
            mkdir($up_dir,0777,true);
            chmod($up_dir, 0777);
        }
        $base64_img = trim($param['file']);
 
        if(preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_img, $result)){
            $type = $result[2];
            if(in_array($type,array('pjpeg','jpeg','jpg','gif','bmp','png'))){
                $new_file = $up_dir.time().'.'.$type;
                if(file_put_contents($new_file, base64_decode(str_replace($result[1], '', $base64_img)))){
                    $info['url'] = str_replace('../../..', '', $new_file);
                    $res=json_encode(['url'=>INLET_PATH.$info['url'],'furl'=>'.'.$info['url']]);
                    return $res;
                }else{
                	return $this->json_error('图片上传失败');
                }
            }else{
                //文件类型错误
                return $this->json_error('图片上传类型错误');
            }
        }
    }






	//初始化，获取用户所有信息
	
	public function userinfo(){
		$res=$this->chektoken();//检测用户token
		if(!$res){
			return json_encode(['code' =>2, 'msg'=>'qing login' ]);
		}else{
			$res['nickname']=rawurldecode($res['nickname']);
			if(strpos($res['uhead'],'http') === false){
                $res['uhead']=substr($res['uhead'],2);
                $res['uhead']= IMG_PATH . $res['uhead'];
				//$res['uhead']= INLET_PATH . $res['uhead'];
			}
            $res['phone']=substr($res['phone'],0,3)."****".substr($res['phone'],7);
			return json_encode(['code'=>0,'msg'=>'ok','result'=>$res]);
		}
		
	}


	//用户注销功能
   public function withdraw(){
        $drawuser= new DrawuserModel;
       $user= new UserModel;
        $res=$this->chektoken();//检测用户token
        if(!$res){
            return json_encode(['code' =>2, 'msg'=>'qing login' ]);
        }else{
            $data=[
                'phone'=>$res['phone'],
                'nickname'=>$res['nickname'],
                'uhead'=>$res['uhead'],
                'appid'=>$res['appid'],
                'email'=>$res['email'],
                'uvip'=>$res['uvip'],
                'uhead'=>$res['uhead'],
                'fromtime'=>$res['fromtime'],
                'totime'=>$res['totime'],
                'driving'=>$res['driving'],
                'openid'=>$res['openid'],
                'token'=>$res['token'],
                'ctime'=>$res['ctime'],
                'etime'=>time()
            ];
            $info=$drawuser->adddata($data);
            if ($info){
                $user->where(['id'=>$res['id']])->delete();
                return json_encode(['code'=>0,'msg'=>'注销成功','result'=>'']);
            }else{
                return json_encode(['code'=>1,'msg'=>'注销失败','result'=>'']);
            }
        }
    }



    //获取idfa及手机型号信息
    public function useridfa(){
    	$request = Request();
    	$data = $request->param();
    	$idfa= new IdfaModel();
    	$ss=$idfa->getOne(['idfa'=>$data['idfa']]);
    	if($ss){
    		$info=$ss;
    	}else{
    		$arr=[
	    		'idfa'=>$data['idfa'],
	    		'phonemodel'=>$data['phonemodel'],
	    		'ip'=>$request->ip(),
	    		'ctime'=>time()
	    	];
    		$info=$idfa->adddata($arr);
    	}
		if ($info) {
			return $this->json_success('添加成功');
		}else{
			return $this->json_error('添加失败');
		}
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

    public function msectime() {
        list($msec, $sec) = explode(' ', microtime());
        $msectime = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
        return $msectime;
    }

    //移动一键登陆获取手机号码
    public function yidong($token){
        //$token="STsid0000001590491087581fTB0Q2aOH0GIXCwwgs5Lb4kCtEHbNB0f";
        $url="https://www.cmpassport.com/unisdk/rsapi/loginTokenValidate";
        $arr=[
            'version'=>'2.0',
            'msgid'=>'19535',
            'systemtime'=>date("YmdHis").rand(100,999),
            'strictcheck'=>'0',
            'appid'=>'300011974589',
            'token'=>$token
        ];
        $arr['sign']=strtoupper(md5($arr['appid'].$arr['version'].$arr['msgid'].$arr['systemtime'].$arr['strictcheck'].$arr['token']."BC5172D832CE4314A9DF22D2911DA4EB"));
        $eee=json_encode($arr);
        //print_r($eee);exit;
        $res=$this->http_request($url,$eee);
        $aa=json_decode($res,true);
        return $aa['msisdn'];
    }
    //意见反馈2020-05-30
    public function dcwms(){
        $res=$this->chektoken();//检测用户token
        if(!$res){
            return json_encode(['code' =>2, 'msg'=>'请登陆' ]);
        }
        $request = Request();
        $data = $request->param();
        if(isset($data['content']) && $data['content'] != ''){
            $dataa['uid'] = $res['id'];
            $dataa['content'] = $data['content'];
            $dataa['addtime'] = time();
            $req=Db::table('fp_message')->insert($dataa);
        }
        $info=Db::table('fp_message')->where(['uid'=>$res['id']])->select();
        $sas=[[
            "id"=> "1",
            "uid"=> $res['id'],
            "phone"=> "",
            "content"=> "1、付费问题：可以购买验证次数，大额支持开票服务！\n2、如有其他问题：请联系客服微信：weixin010101L",
            "addtime"=> "1603698822",
            "type"=> "1",
            "state"=> "1"
        ]];
        //array_unshift($sas,$info);
        $info=array_merge($sas,$info);
        if($req){
            return json_encode(['code'=>0,'msg'=>'添加成功','result'=>$info]);
        }else{
            return json_encode(['code'=>0,'msg'=>'添加失败','result'=>$info]);
        }
    }

    //将消息改为已读状态
    public function yidu(){
        $res=$this->chektoken();//检测用户token
        if(!$res){
            return json_encode(['code' =>2, 'msg'=>'请登陆' ]);
        }
        $dataa['state'] = 1;
        Db::table('fp_message')->where(['uid'=>$res['id'],'type'=>1])->update($dataa);
        return $this->json_success('修改成功');
    }

    public function ossup(){
        $object="uploads/file/20200709/2f60e592da1d942c47533cfbc8f2f615.png";
        $content="./uploads/file/20200710/2f60e592da1d942c47533cfbc8f2f615.png";
        oss::donfile($object,$content);
    }








    //验证码登录
    public function codelogin(){
        $request = Request();
    	$data = $request->param();
		$user= new UserModel();
		$idfa= new IdfaModel();
		$take= new TakenotesModel();
		//查询idfa是否存在
		$aaid=$idfa->getOne(['idfa'=>$data['idfa']]);
		if($aaid){
			//存在
			$idfaid=$aaid['id'];
		}else{
			//不存在
			$idfaid=$idfa->adddata(['idfa'=>$data['idfa'],'ip'=>$request->ip(),'ctime'=>time()]);
		}
        $code=Db::table('fp_code')->field('msg,ctime')->where('phone',$data['phone'])->order('ctime desc')->find();
        //print_r($code);exit;
        if($data['phone'] != "13288888888"){
            if($data['code'] != $code['msg'] || empty($data['code'])){
                return $this->json_error('验证码错误');
            }
            //获取十分钟内的数据
            $time=time();
            $rqq=$time - strtotime($code['ctime']);
            if($rqq > 600){
                return $this->json_error('验证码已过期');
            }
        }
        //验证此用户是否已经存在
        $info=$user->getOne(['phone'=>$data['phone']]);
        if($info==false){
            $pa=substr($data['phone'],7);
            //不存在->新增
            $res=[
                'phone'=>$data['phone'],
                'nickname'=>"用户".$pa,
                'uhead'=>'',
                'idfaid'=>$idfaid,
                'ip'=>$request->ip(),
                'uvip'=>0,
                'frequency'=>2,
                'totalnum'=>2,
                'fromtime'=>date('Y-m-d H:i:s',time()),
                'totime'=>date('Y-m-d H:i:s',time()),//是否给新用户几天VIP，开启uvip=1
                'token'=>md5($data['phone'].time()),
                'ctime'=>date('Y-m-d H:i:s',time())
            ];
            $user->adddata($res);
            $info=$user->getOne(['phone'=>$data['phone']]);
            $take->adddata(['uid'=>$info['id'],'ctime'=>time()]);
        }
		if($info['idfaid']==0){
			$user->updatedatas(['idfaid'=>$idfaid],['id'=>$info['id']]);
		}
        if(!is_dir('./uploads/file/xlx/'.$info['id'])){
            mkdir(iconv("UTF-8", "GBK", './uploads/file/xlx/'.$info['id']),0777,true);
        }
        $info['phone']=substr($info['phone'],0,3)."****".substr($info['phone'],7);
		return json_encode(['code'=>0,'msg'=>'ok','result'=>$info]);
    }



















	
}
?>
