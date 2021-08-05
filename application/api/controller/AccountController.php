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
use app\common\model\JzAccountModel;
use think\facade\Cache;
use GuzzleHttp\json_encode;

use mylib\img;
class AccountController extends BaseController
{
	//我的账号
	function myaccount(){

		$res=$this->chektoken();//检测用户token
		if(!$res){
			return json_encode(['code' =>2, 'msg'=>'请登录' ]);
		}else{
			$zongzc = 0;
			$jingzc = 0;
			$zijin = 0;
			$list = Db::table('fp_jz_account')->where('uid='.$res['id'])->select();
			if($list){
				foreach ($list as $k=>$v){
                        //计算统计金额，此账号下所有收支的总额
                        $sr = Db::name('fp_jz_revenue')->where('accountid='.$v['id'].' and uid='.$res['id'].' and type=0')->sum('money');

                        $zc = Db::name('fp_jz_revenue')->where('accountid='.$v['id'].' and uid='.$res['id'].' and type=1')->sum('money');
                        $list[$k]['money'] = $sr+$zc+$list[$k]['icon'];
                        $list[$k]['issz'] = '1';
                        $zijin = $zijin+$sr+$zc+$list[$k]['icon'];
				}
				$aa['zhanghu']=$list;
				//应收
				$ys = Db::name('fp_jz_receivable')->where('type=0 and uid='.$res['id'].'')->sum('surplus');
				$li[] = ['uid'=>'0','title'=>'应收款','money'=>$ys,'issz'=>'0','type'=>'0'];

				//应付
				$yf = Db::name('fp_jz_receivable')->where('type=1 and uid='.$res['id'].' ')->sum('surplus');
                $li[] = ['uid'=>'0','title'=>'应付款','money'=>$yf,'issz'=>'0','type'=>'1'];
				
				//预收
				$ysou = Db::name('fp_jz_receivable')->where('type=2 and uid='.$res['id'].' ')->sum('surplus');
                $li[] = ['uid'=>'0','title'=>'预收款','money'=>$ysou,'issz'=>'0','type'=>'2'];
                //预付
                $fyf = Db::name('fp_jz_receivable')->where('type=3 and uid='.$res['id'].' ')->sum('surplus');
                $li[] = ['uid'=>'0','title'=>'预付款','money'=>$fyf,'issz'=>'0','type'=>'3'];
                $aa['wanglai']=$li;
                //固定资产
                $gud=Db::name('fp_jz_fixed')->where(['uid'=>$res['id'],'type' =>0])->sum('money');
                $lis[] = ['uid'=>'0','title'=>'固定资产','money'=>$gud,'issz'=>'2','type'=>'0'];
                //其它
                $qita=Db::name('fp_jz_fixed')->where(['uid'=>$res['id'],'type' =>1])->sum('money');
                $lis[] = ['uid'=>'0','title'=>'其他','money'=>$qita,'issz'=>'2','type'=>'1'];
                $aa['gu']=$lis;

				$jingzc = $zijin-$yf;
                $zongzc = $jingzc+$gud+$qita+$ys;
				//计算资产资金
				
			}

		return json_encode(['code'=>0,'msg'=>'ok','result'=>$aa,'zijin'=>$zijin,'jingzc'=>$jingzc,'zongzc'=>$zongzc]);
		}
	}
	
	//编辑我的账号
	function editaccount(){

		$res=$this->chektoken();//检测用户token
		if(!$res){
			return json_encode(['code' =>2, 'msg'=>'请登录' ]);
		}else{

			$request = Request();
			$data = $request->param();
            $data['icon']=$data['icon'] ? $data['icon']: 0;
			if($data['id']){
				$ishas = Db::table('fp_jz_account')->where('id='.$data['id'])->find();
				if($ishas['uid'] == 0){
					return $this->json_error('系统账号不可修改');
				}

				$da['title'] = $data['title'];
				$da['icon'] = $data['icon'];
				$list = Db::table('fp_jz_account')->where('id='.$data['id'])->update($da);
				if($list){
					return $this->json_success('成功');
				}
			}
			$da['title'] = $data['title'];
			$da['icon'] = $data['icon'];
			$da['uid'] = $res['id'];
			$da['ctime'] = date('Y-m-d H:i:s');
			$list = Db::table('fp_jz_account')->insert($da);
			if($list){
            return $this->json_success('成功');
			}

			return $this->json_error('失败');
		
		
		}
	}
	//我的账户明细
	function getaccount(){
		$res=$this->chektoken();//检测用户token
		if(!$res){
			return json_encode(['code' =>2, 'msg'=>'请登录' ]);
		}else{
			$request = Request();
			$data = $request->param();
			//$wh = "incometime like'".date('Y-m')."%'";
			/*if($data['date']){
				$wh = "incometime like'".$data['date']."%'";
			}*/
			if(!isset($data['type'])){
                $data['type']=0;
            }
			$start=$data['page'] ? $data['page'] : 0;
			$where=['uid'=>$res['id'],'accountid'=>$data['id'],'type'=>$data['type'],'state'=>0];
			//$whereor['amount|salename|buyname'] = ['like', '%' . $data['key'] . '%'];
			//$list=Db::table('fp_jz_revenue')->where($where)->where($wh)
			$list=Db::table('fp_jz_revenue')->where($where)
			->order('id desc')->limit($start * 10, 10)->select();
			if($res['id']==32179){
               // echo Db::table('fp_jz_revenue')->getLastSql();exit;
            }
			for($i=0;$i<count($list);$i++){
			    //获取往来单位名称
                if($list[$i]['contactunitid'] != 0 ){
                    $tit=Db::table('fp_jz_contactunit')->where(['id'=>$list[$i]['contactunitid']])->find();
                    $list[$i]['title']=$tit['title'];
                }else{
                    $list[$i]['title']="自己";
                }
                //账户名称
                $zh=Db::table('fp_jz_account')->where(['id'=>$list[$i]['accountid']])->find();
                $list[$i]['zhangtitle']=$zh['title'];
                //分类名称
                $fen=Db::table('fp_jz_bookkeeping')->where(['id'=>$list[$i]['tclassify']])->find();
                $list[$i]['fentitle']=$fen['title'];
            }
			
			//$sr = Db::name('fp_jz_revenue')->where($wh)->where('accountid='.$data['id'].' and uid='.$res['id'].' and type=0')->sum('money');
			$sr = Db::name('fp_jz_revenue')->where('accountid='.$data['id'].' and uid='.$res['id'].' and type=0')->sum('money');

			//$zc = Db::name('fp_jz_revenue')->where($wh)->where('accountid='.$data['id'].' and uid='.$res['id'].' and type=1')->sum('money');
			$zc = Db::name('fp_jz_revenue')->where('accountid='.$data['id'].' and uid='.$res['id'].' and type=1')->sum('money');
			$info = Db::table('fp_jz_account')->where('id='.$data['id'])->find();
			$asdd=[
			    'id'=>'0',
                'fentitle'=>'初始金额',
                'title'=>'初始金额',
                'incometime'=>date("Y-m-d",strtotime($info['ctime'])),
                'money'=>$info['icon'],
                'zhangtitle'=>'初始金额',
            ];
            array_push($list,$asdd);
			$info['sr'] = $sr;
			$info['zc'] = $zc;
			$info['money'] = $zc+$sr+$info['icon'];
			
			return json_encode(['code'=>0,'msg'=>'ok','result'=>$list,'info'=>$info]);
				
		}
	}
	//新增编辑我的供应商客户
	function editlw(){
	
		$res=$this->chektoken();//检测用户token
		if(!$res){
			return json_encode(['code' =>2, 'msg'=>'请登录' ]);
		}else{
	
			$request = Request();
			$data = $request->param();

			$da['title'] = $data['title'];
			$da['contacts'] = $data['contacts'].'';
			$da['phone'] = $data['phone'].'';
			$da['telephone'] = $data['telephone'].'';
			$da['email'] = $data['email'].'';
			$da['wechat'] = $data['wechat'].'';
			$da['type'] = $data['type'].'';
			$da['remarks'] = $data['remarks'].'';
			$da['incommon'] = $data['incommon'].'';
			if($data['id']){
				$ishas = Db::table('fp_jz_contactunit')->where('id='.$data['id'])->find();
				if($ishas['uid'] == 0){
					//return $this->json_error('系统账号不可修改');
				}


                $list = Db::table('fp_jz_contactunit')->where('id='.$data['id'])->update($da);
				if($list){
					return $this->json_success('成功');
				}
			}
			$da['uid'] = $res['id'];
			$da['ctime'] = date('Y-m-d H:i:s');
			$list = Db::table('fp_jz_contactunit')->insert($da);
			if($list){
				return $this->json_success('成功');
			}
	
			return $this->json_error('失败');
	
	
		}
	}
	//我的供应商客户
	function mylw(){

		$res=$this->chektoken();//检测用户token
		if(!$res){
			return json_encode(['code' =>2, 'msg'=>'请登录' ]);
		}else{
			$request = Request();
			$data = $request->param();
			$list = Db::table('fp_jz_contactunit')->where('type='.$data['type'].' and uid='.$res['id'])->order('id desc')->select();
			if(!$list){
                $list=[];
            }
            return json_encode(['code'=>0,'msg'=>'ok','result'=>$list]);
		}
	}

	//删除供应商客户
    function mylwdel(){
        $res=$this->chektoken();//检测用户token
        if(!$res){
            return json_encode(['code' =>2, 'msg'=>'请登录' ]);
        }else{
            $request = Request();
            $data = $request->param();
            $wang=Db::table('fp_jz_revenue')->where('contactunitid='.$data['id'].' and uid='.$res['id'])->find();
            $ying=Db::table('fp_jz_receivable')->where('contactunitid='.$data['id'].' and uid='.$res['id'])->find();
            if($wang || $ying){
                return $this->json_error('此单位或个人已有往来账款，不能删除！');
            }
            $list = Db::table('fp_jz_contactunit')->where('id='.$data['id'].' and uid='.$res['id'])->delete();
            if($list){
                return $this->json_success('删除成功');
            }else{
                return $this->json_error('删除失败');
            }
        }
    }



	//首页统计
	function tjindex(){

		$res=$this->chektoken();//检测用户token
		if(!$res){
			return json_encode(['code' =>2, 'msg'=>'请登录' ]);
		}else{
			$request = Request();
			$data = $request->param();
            $ycm = rand(100000, 500000);
            usleep($ycm);
            $this->shengcheng($res['id']);
			$month = date('Y-m');
			if($data['date']){
				$month = $data['date'];
			}
			$wn = "incometime like'".$month."%'";
			//收入
			$ysou = Db::name('fp_jz_revenue')->where('type=0 and uid='.$res['id'].' ')->where($wn)->sum('money');
			$ysounum = Db::name('fp_jz_revenue')->where('type=0 and uid='.$res['id'].' ')->where($wn)->count();
			$dm['shou'] = $ysou;
			$dm['shounum'] = $ysounum;

			//支出
			$fyf = Db::name('fp_jz_revenue')->where('type=1 and uid='.$res['id'].' ')->where($wn)->sum('money');
			$fyfnum = Db::name('fp_jz_revenue')->where('type=1 and uid='.$res['id'].' ')->where($wn)->count();
			$dm['zhi'] = $fyf;
			$dm['zhinum'] = $fyfnum;

			//应收
			$ys = Db::name('fp_jz_receivable')->where('type=0 and uid='.$res['id'].'')->where($wn)->sum('surplus');
			$ysnum = Db::name('fp_jz_receivable')->where('type=0 and uid='.$res['id'].'')->where($wn)->count();
			$dm['yshou'] = $ys;
			$dm['yshounum'] = $ysnum;
			//应付
			$yf = Db::name('fp_jz_receivable')->where('type=1 and uid='.$res['id'].' ')->where($wn)->sum('surplus');
			$yfnum = Db::name('fp_jz_receivable')->where('type=1 and uid='.$res['id'].' ')->where($wn)->count();
			$dm['yfu'] = $yf;
			$dm['yfunum'] = $yfnum;
			//初始金额
            $icon = Db::table('fp_jz_account')->where('uid='.$res['id'])->sum('icon');
            $iconnum = Db::table('fp_jz_account')->where('uid='.$res['id'])->count();
			$jieyu = $ysou+$fyf;
			$dm['jieyu'] = $jieyu+$icon;
			$dm['jieyunum'] = $fyfnum+$ysounum+$iconnum;

			return json_encode(['code'=>0,'msg'=>'ok','result'=>$dm]);
		}
	}

	//往来账款
    function myzhang(){

        $res=$this->chektoken();//检测用户token
        if(!$res){
            return json_encode(['code' =>2, 'msg'=>'请登录' ]);
        }else{
            //应收
            $ys = Db::name('fp_jz_receivable')->where('type=0 and uid='.$res['id'].'')->sum('surplus');
            $list[] = ['uid'=>'0','title'=>'应收款','money'=>$ys,'type'=>'0'];
            //应付
            $yf = Db::name('fp_jz_receivable')->where('type=1 and uid='.$res['id'].' ')->sum('surplus');
            $list[] = ['uid'=>'0','title'=>'应付款','money'=>$yf,'type'=>'1'];
            //预收
            $ysou = Db::name('fp_jz_receivable')->where('type=2 and uid='.$res['id'].' ')->sum('surplus');
            $list[] = ['uid'=>'0','title'=>'预收款','money'=>$ysou,'type'=>'2'];
            //预付
            $fyf = Db::name('fp_jz_receivable')->where('type=3 and uid='.$res['id'].' ')->sum('surplus');
            $list[] = ['uid'=>'0','title'=>'预付款','money'=>$fyf,'type'=>'3'];
            return json_encode(['code'=>0,'msg'=>'ok','result'=>$list]);
        }
    }

    //生成默认数据
    public function shengcheng($uid){
        $ccount= new JzAccountModel();
        $you=$ccount->getOne(['uid'=>$uid]);
        if(!$you){
            $da[] = [
                'uid'=>$uid,
                'title'=>'银行（对公）',
                'icon'=>'0.00',
                'ctime'=>date('Y-m-d H:i:s'),
                'state'=>0
            ];
            $da[] = [
                'uid'=>$uid,
                'title'=>'支付宝',
                'icon'=>'0.00',
                'ctime'=>date('Y-m-d H:i:s'),
                'state'=>0
            ];
            $da[] = [
                'uid'=>$uid,
                'title'=>'微信',
                'icon'=>'0.00',
                'ctime'=>date('Y-m-d H:i:s'),
                'state'=>0
            ];
            $da[] = [
                'uid'=>$uid,
                'title'=>'我的现金',
                'icon'=>'0.00',
                'ctime'=>date('Y-m-d H:i:s'),
                'state'=>0
            ];
            $ccount->insertAll($da);
        }
    }



}
?>