<?php
namespace app\api\controller;
use app\api\controller\base\BaseController;
use think\facade\Log;
use think\facade\Request;
use think\Exception;
use think\Db;
use app\common\model\UserModel;
use app\common\model\JzDetailedModel;
use app\common\model\JzContactunitModel;
use app\common\model\JzRevenueModel;
use app\common\model\JzReceivableModel;
use app\common\model\JzBookkeepingModel;
use app\common\model\JzAccountModel;
use think\facade\Cache;
use GuzzleHttp\json_encode;

use mylib\curll;
class MustController extends BaseController
{
	
    //添加应收、应付等信息
    public function receivableadd(){
		$receivable= new JzReceivableModel();
		$info=$this->chektoken();//检测用户token
		if(!$info){
			return json_encode(['code' =>2, 'msg'=>'请登录' ]);
		}
		$request = Request();
    	$data = $request->param();
        $array=[
            'uid'=>$info['id'],
            'contactunitid'=>$data['contactunitid'],
            'accountid'=>$data['accountid'] ? $data['accountid'] : 0,
            'type'=>$data['type'],
            'money'=>$data['money'],
            'appendix'=>$data['appendix'],
            'tclassify'=>$data['tclassify'],
            'incometime'=>$data['incometime'],
            'surplus'=>$data['money'],
            'remarks'=>$data['remarks'],
            'ctime'=>date('Y-m-d H:i:s',time())//拍照时间
        ];
        $res=$receivable->adddata($array);
        if($res){
            return json_encode(['code'=>0,'msg'=>'添加成功']);
        }else{
            return json_encode(['code'=>1,'msg'=>'添加失败']);
        }
	}

    //预收、预付等信息
    public function receadd(){
        $receivable= new JzReceivableModel();
        $revenue= new JzRevenueModel();
        $info=$this->chektoken();//检测用户token
        if(!$info){
            return json_encode(['code' =>2, 'msg'=>'请登录' ]);
        }
        $request = Request();
        $data = $request->param();
        $array=[
            'uid'=>$info['id'],
            'contactunitid'=>$data['contactunitid'],
            'accountid'=>$data['accountid'],
            'type'=>$data['type'],
            'money'=>$data['money'],
            'tclassify'=>$data['tclassify'],
            'appendix'=>$data['appendix'],
            'incometime'=>$data['incometime'],
            'surplus'=>$data['money'],
            'remarks'=>$data['remarks'],
            'ctime'=>date('Y-m-d H:i:s',time())//拍照时间
        ];
        $res=$receivable->adddata($array);
        if($res){
            $ar=[
                'uid'=>$info['id'],
                'contactunitid'=>$data['contactunitid'],
                'accountid'=>$data['accountid'],
                'appendix'=>$data['appendix'],
                'incometime'=>$data['incometime'],
                'tclassify'=>$data['tclassify'],
                'remarks'=>$data['remarks'],
                'ctime'=>date('Y-m-d H:i:s',time()),//拍照时间
                'source'=>$res
            ];
            if($data['type'] == 2){
                //预收计入一笔收入
               $ar['type']=0;
               $ar['money']=$data['money'];
               $ar['sourcecom']="预收款";
            }else{
                $ar['type']=1;
                $ar['money']='-'.$data['money'];
                $ar['sourcecom']="预付款";
            }
            $revenue->adddata($ar);
            return json_encode(['code'=>0,'msg'=>'添加成功']);
        }else{
            return json_encode(['code'=>1,'msg'=>'添加失败']);
        }
    }





	//删除应收、应付信息
    public function receivabledel(){
        $receivable= new JzReceivableModel();
        $detailed= new JzDetailedModel();
        $revenue= new JzRevenueModel();
        $info=$this->chektoken();//检测用户token
        if(!$info){
            return json_encode(['code' =>2, 'msg'=>'请登录' ]);
        }
        $request = Request();
        $data = $request->param();
        $xinxi=$receivable->getOne(['id'=>$data['id']]);
        if($xinxi['type']==0){
            $sourcecom='收欠款';
        }elseif($xinxi['type']==1){
            $sourcecom='还欠款';
        }else{
            return json_encode(['code'=>1,'msg'=>'检查接口是否正确']);
        }
        //处理拆分问题
        $cun=$detailed->getList(['receivableid'=>$data['id']]);
        if($cun){
            for($i=0;$i<count($cun);$i++){
                //删除总金额及回款信息
                $revenue->getdel(['source'=>$cun[$i]['id'],'uid'=>$info['id'],'sourcecom'=>$sourcecom]);//删除收入明细
            }
            $detailed->getdel(['receivableid'=>$data['id']]);//删除回款记录
        }
        $res=$receivable->getdel(['uid'=>$info['id'],'id'=>$data['id']]);//删除当前信息
        if($res){
            return json_encode(['code'=>0,'msg'=>'删除成功']);
        }else{
            return json_encode(['code'=>1,'msg'=>'删除失败']);
        }
    }

    //删除预收、预付等信息
    public function recedel(){
        $receivable= new JzReceivableModel();
        $detailed= new JzDetailedModel();
        $revenue= new JzRevenueModel();
        $info=$this->chektoken();//检测用户token
        if(!$info){
            return json_encode(['code' =>2, 'msg'=>'请登录' ]);
        }
        $request = Request();
        $data = $request->param();
        $xinxi=$receivable->getOne(['id'=>$data['id']]);
        if($xinxi['type']==2){
            $sourcecom='退收入';
        }elseif($xinxi['type']==3){
            $sourcecom='退支出';
        }else{
            return json_encode(['code'=>1,'msg'=>'检查接口是否正确']);
        }
        //处理拆分问题
        $cun=$detailed->getList(['receivableid'=>$data['id']]);
        if($cun){
            for($i=0;$i<count($cun);$i++){
                //删除总金额及回款信息
                $revenue->getdel(['source'=>$cun[$i]['id'],'uid'=>$info['id'],'sourcecom'=>$sourcecom]);//删除收入明细
            }
            $detailed->getdel(['receivableid'=>$data['id'],'uid'=>$info['id']]);//删除回款记录
        }
        if($xinxi['type']==2){
            $sourcecom='预收款';
        }elseif($xinxi['type']==3){
            $sourcecom='预付款';
        }
        $revenue->getdel(['source'=>$data['id'],'uid'=>$info['id'],'sourcecom'=>$sourcecom]);//删除收入支出记录
        $res=$receivable->getdel(['uid'=>$info['id'],'id'=>$data['id']]);//删除当前信息
        if($res){
            return json_encode(['code'=>0,'msg'=>'删除成功']);
        }else{
            return json_encode(['code'=>1,'msg'=>'删除失败']);
        }
    }


    //编辑应收、应付、预收、预付等信息
    public function receivableup(){
        $receivable= new JzReceivableModel();
        $detailed= new JzDetailedModel();
        $info=$this->chektoken();//检测用户token
        if(!$info){
            return json_encode(['code' =>2, 'msg'=>'请登录' ]);
        }
        $request = Request();
        $data = $request->param();
        //编辑时查询次项目是否有拆分，如果有不许编辑
        $xq=$receivable->getOne(['id'=>$data['id']]);
        $cun=$detailed->getOne(['receivableid'=>$xq['id']]);
        if($cun){
            return json_encode(['code'=>1,'msg'=>'此账单已拆分，不能进行修改操作！']);
        }
        if($data['money'] != $xq['money']){
            return json_encode(['code'=>1,'msg'=>'此账单金额已录入不可修改！']);
        }
        $array=[
            'contactunitid'=>$data['contactunitid'],
            'accountid'=>$data['accountid'],
            'tclassify'=>$data['tclassify'],
            'money'=>$data['money'],
            'surplus'=>$data['money'],
            'appendix'=>$data['appendix'],
            'incometime'=>$data['incometime'],
            'remarks'=>$data['remarks'],
        ];
        $res=$receivable->updatedatas($array,['uid'=>$info['id'],'id'=>$data['id']]);
        if($res){
            return json_encode(['code'=>0,'msg'=>'修改成功']);
        }else{
            return json_encode(['code'=>1,'msg'=>'修改失败']);
        }
    }
    //收欠款、还欠款、退收入、退支出
    public function collectmoney(){
        $info=$this->chektoken();//检测用户token
        if(!$info){
            return json_encode(['code' =>2, 'msg'=>'请登录' ]);
        }
        $request = Request();
        $data = $request->param();
        $receivable= new JzReceivableModel();
        $contactunit= new JzContactunitModel();
        //获取应收的项目的公司或个人信息
        $res=$receivable->getList(['uid'=>$info['id'],'type'=>$data['type']]);
        for ($i=0;$i<count($res);$i++){
            if($res[$i]['surplus'] != 0){
                $ress[]=$res[$i]['contactunitid'];
            }
        }
        if(empty($ress)){
            return json_encode(['code'=>0,'msg'=>'成功','result'=>[]]);
        }
        $qs=array_unique($ress);
        if(isset($data['rentype'])){
            $type=$data['rentype'];
        }else{
            $type=0;
        }
        $con=$contactunit->field('id,uid,title,type')->where(['type'=>$type])->where(['id'=>$qs])->order('id desc')->select();
        if(!$con){
            $con=[];
        }
        return json_encode(['code'=>0,'msg'=>'成功','result'=>$con]);
    }

    //获取某一个单位收欠款、还欠款、退收入、退支出列表
    public function operationlist(){
        $info=$this->chektoken();//检测用户token
        if(!$info){
            return json_encode(['code' =>2, 'msg'=>'请登录' ]);
        }
        $request = Request();
        $data = $request->param();
        $receivable= new JzReceivableModel();
        $contactunit= new JzContactunitModel();
        $bookkeep= new JzBookkeepingModel();
        $ccount= new JzAccountModel();
        $dan=$contactunit->getOne(['id'=>$data['id']]);//单位信息
        //获取单位应收、、、的总额度
        //$dan['total']=$receivable->getNum(['contactunitid'=>$data['id'],'type'=>$data['type']]);//该项目对应的总款
        $dan['money']=$receivable->getNum(['contactunitid'=>$data['id'],'type'=>$data['type']]);//该项目对应的总款
        $arr['mation']=$dan;
        //获取列表
        $where='surplus != 0.00 and contactunitid = '.$data['id'].' and type = '.$data['type'];
        $arr['list']=$receivable->getList($where);//该公司对应的列表
        for($i=0;$i<count($arr['list']);$i++){
            $arr['list'][$i]['qiid']=$arr['list'][$i]['contactunitid'];

            $arr['list'][$i]['fenid']=$arr['list'][$i]['tclassify'];

            $arr['list'][$i]['zhangid']=$arr['list'][$i]['accountid'];

            $qi=$contactunit->getOne(['id'=>$arr['list'][$i]['contactunitid']]);
            $arr['list'][$i]['title']=$qi['title'];
            //分类的名称
            $fen=$bookkeep->getOne(['id'=>$arr['list'][$i]['tclassify']]);
            unset($arr['list'][$i]['tclassify']);
            $arr['list'][$i]['tclassify']=$fen['title'];
            //账户的名称
            if($arr['list'][$i]['accountid']==0){
                $arr['list'][$i]['account']='';
            }else{
                $acc=$ccount->getOne(['id'=>$arr['list'][$i]['accountid']]);
                $arr['list'][$i]['account']=$acc['title'];
            }
            unset($arr['list'][$i]['contactunitid']);
            unset($arr['list'][$i]['accountid']);
        }
        return json_encode(['code'=>0,'msg'=>'成功','result'=>$arr]);
    }
    //应收、应付、预收、预付的列表
    public function collist(){
        $info=$this->chektoken();//检测用户token
        if(!$info){
            return json_encode(['code' =>2, 'msg'=>'请登录' ]);
        }
        $request = Request();
        $data = $request->param();
        $receivable= new JzReceivableModel();
        $contactunit= new JzContactunitModel();
        $bookkeep= new JzBookkeepingModel();
        $ccount= new JzAccountModel();
        //$res=$receivable->getList();//获取列表

        $list =$receivable->alias("u")
            ->field('u.*')
            ->where(['type'=>$data['type'],'uid'=>$info['id']])
            ->where('u.surplus > 0')->order('u.id desc')->paginate(10,false, [
                'query' => Request::instance()->param(),//不丢失已存在的url参数
            ])->toArray();
       // print_r($list);exit;
        if($list){
            for($i=0;$i<count($list['data']);$i++){
                    $qi=$contactunit->getOne(['id'=>$list['data'][$i]['contactunitid']]);
                    $list['data'][$i]['title']=$qi['title'];
                    //分类的名称
                    $fen=$bookkeep->getOne(['id'=>$list['data'][$i]['tclassify']]);
                    $list['data'][$i]['tclassid']=$list['data'][$i]['tclassify'];
                    $list['data'][$i]['tclassify']=$fen['title'];
                    //账户名称
                    if($list['data'][$i]['accountid']==0){
                        $list['data'][$i]['account']='';
                    }else{
                        $acc=$ccount->getOne(['id'=>$list['data'][$i]['accountid']]);
                        $list['data'][$i]['account']=$acc['title'];
                    }
            }
            $list['amount']=$receivable->getNum(['type'=>$data['type'],'uid'=>$info['id']]);//总数
        }else{
            $list=[];
        }
        return json_encode(['code'=>0,'msg'=>'成功','result'=>$list]);
    }
    //汇总
    public function collisth(){
        $info=$this->chektoken();//检测用户token
        if(!$info){
            return json_encode(['code' =>2, 'msg'=>'请登录' ]);
        }
        $request = Request();
        $data = $request->param();
        $receivable= new JzReceivableModel();
        $contactunit= new JzContactunitModel();
        $bookkeep= new JzBookkeepingModel();
        //$res=$receivable->getList();//获取列表

        $list =$receivable->alias("u")
            ->field('u.*')
            ->where(['type'=>$data['type'],'uid'=>$info['id']])
            ->where('u.surplus > 0')
            ->order('u.id desc')->group('u.contactunitid')->paginate(10,false, [
                'query' => Request::instance()->param(),//不丢失已存在的url参数
            ])->toArray();
        // print_r($list);exit;
        if($list){
            for($i=0;$i<count($list['data']);$i++){
                $list['data'][$i]['surplus']=$receivable->getNum(['type'=>$data['type'],'contactunitid'=>$list['data'][$i]['contactunitid'],'uid'=>$info['id']]);
                //echo $receivable->getLastSql();exit;
                $qi=$contactunit->getOne(['id'=>$list['data'][$i]['contactunitid']]);
                $list['data'][$i]['title']=$qi['title'];
            }
            $list['amount']=$receivable->getNum(['type'=>$data['type'],'uid'=>$info['id']]);//总数
        }else{
            $list=[];
        }
        return json_encode(['code'=>0,'msg'=>'成功','result'=>$list]);
    }




    //应收、应付、预收、预付的信息详情
    public function clldetails(){
        $request = Request();
        $data = $request->param();
        $receivable= new JzReceivableModel();
        $contactunit= new JzContactunitModel();
        $bookkeep= new JzBookkeepingModel();
        $ccount= new JzAccountModel();
        $detailed= new JzDetailedModel();
        $res=$receivable->getOne(['id'=>$data['id']]);
        //查询企业名称、销售类型
        $qi=$contactunit->getOne(['id'=>$res['contactunitid']]);
        $fen=$bookkeep->getOne(['id'=>$res['tclassify']]);
        $account=$ccount->getOne(['id'=>$res['accountid']]);
        $arr['mation']=[
            'id'=>$res['id'],
            'title'=>$qi['title'],
            'qiid'=>$res['contactunitid'],
            'fenid'=>$res['tclassify'],
            'zhangid'=>$res['accountid'],
            'incometime'=>$res['incometime'],
            'money'=>$res['money'],
            'surplus'=>$res['surplus'],
            'account'=>$account['title'],
            'remarks'=>$res['remarks'],
            'tclassify'=>$fen['title']
        ];
        //收欠款、还欠款、退收入、退支出列表
        $arr['history']=$detailed->getList(['receivableid'=>$data['id']]);
        return json_encode(['code'=>0,'msg'=>'成功','result'=>$arr]);
    }
    //核销
    public function verificat(){
        $info=$this->chektoken();//检测用户token
        if(!$info){
            return json_encode(['code' =>2, 'msg'=>'请登录' ]);
        }
        $request = Request();
        $data = $request->param();
        $receivable= new JzReceivableModel();
        $revenue= new JzRevenueModel();
        $detailed= new JzDetailedModel();
        //查询要核销的内容
        $res=$receivable->getOne(['id'=>$data['id']]);
        //修改核销的金额
        $aa=$res['surplus']- $data['money'];
        if($aa < 0){
            return json_encode(['code' =>1, 'msg'=>'检查金额是否正确！' ]);
        }
        $he=$receivable->updatedatas(['surplus'=>$aa],['id'=>$data['id']]);
        if($he){
            //写入流水表
            $liu=[
                'receivableid'=>$res['id'],
                'amount'=>$data['money'],
                'remarks'=>$data['remarks'],
                'ctime'=>date('Y-m-d H:i:s',time())
            ];
            $liuid=$detailed->adddata($liu);
            //添加收入或支出记录
            $ar=[
                'uid'=>$info['id'],
                'contactunitid'=>$data['contactunitid'],
                'accountid'=>$data['accountid'],
                'appendix'=>$data['appendix'],
                'incometime'=>$data['incometime'],
                'tclassify'=>$data['tclassify'],
                'remarks'=>$data['remarks'],
                'ctime'=>date('Y-m-d H:i:s',time()),//拍照时间
                'source'=>$liuid
            ];
            if($res['type'] == 0){//收欠款
                //预收计入一笔收入
                $ar['type']=0;
                $ar['money']=$data['money'];
                $ar['sourcecom']="收欠款";
            }elseif($res['type'] == 1){
                $ar['type']=1;
                $ar['money']='-'.$data['money'];
                $ar['sourcecom']="还欠款";
            }elseif($res['type'] == 2){
                $ar['type']=1;
                $ar['money']='-'.$data['money'];
                $ar['sourcecom']="退收入";
            }elseif($res['type'] == 3){
                $ar['type']=0;
                $ar['money']=$data['money'];
                $ar['sourcecom']="退支出";
            }
            $aa=$revenue->adddata($ar);
            if($aa){
                return json_encode(['code' =>0, 'msg'=>'核销成功！' ]);
            }else{
                return json_encode(['code' =>1, 'msg'=>'核销失败！' ]);
            }
        }else{
            return json_encode(['code' =>1, 'msg'=>'核销失败！' ]);
        }
    }
    //删除核销记录
    public function verificatdel(){
        $info=$this->chektoken();//检测用户token
        if(!$info){
            return json_encode(['code' =>2, 'msg'=>'请登录' ]);
        }
        $request = Request();
        $data = $request->param();
        //print_r($data);
        $receivable= new JzReceivableModel();
        $revenue= new JzRevenueModel();
        $detailed= new JzDetailedModel();
        //查询要删除的核销的内容
        $res=$detailed->getOne(['id'=>$data['id']]);
        //print_r($res);
        //还原原来的金额
        $rqq=$receivable->getOne(['id'=>$res['receivableid']]);

        $money=$rqq['surplus']+$res['amount'];
        $he=$receivable->updatedatas(['surplus'=>$money],['id'=>$res['receivableid']]);
        //echo $receivable->getLastSql();exit;
        if($he !== false){
            if($rqq['type']==0){
                $sourcecom='收欠款';
            }elseif($rqq['type']==1){
                $sourcecom='还欠款';
            }elseif($rqq['type']==2){
                $sourcecom='退收入';
            }elseif($rqq['type']==3){
                $sourcecom='退支出';
            }
            //删除收入支出表记录
            $revenue->getdel(['source'=>$res['id'],'uid'=>$info['id'],'sourcecom'=>$sourcecom]);
            //删除本条数据
            $aa=$detailed->getdel(['id'=>$data['id']]);
            if($aa){
                return json_encode(['code' =>0, 'msg'=>'删除成功！' ]);
            }else{
                return json_encode(['code' =>1, 'msg'=>'删除失败2！' ]);
            }
        }else{
            return json_encode(['code' =>1, 'msg'=>'删除失败1！' ]);
        }

    }

    //收入类型列表
    public function tylist(){
        $info=$this->chektoken();//检测用户token
        if(!$info){
            return json_encode(['code' =>2, 'msg'=>'请登录' ]);
        }
        $request = Request();
        $data = $request->param();
        $bookkeep= new JzBookkeepingModel();
        $list=$bookkeep->field('id,uid,title')->where(['state'=>0,'type'=>$data['type']])->whereIn('uid','0,'.$info['id'])->select();
        return json_encode(['code'=>0,'msg'=>'ok','result'=>$list]);
    }
    //转账/存取
    public function unloading(){
        $info=$this->chektoken();//检测用户token
        if(!$info){
            return json_encode(['code' =>2, 'msg'=>'请登录' ]);
        }
        $request = Request();
        $data = $request->param();
        $revenue= new JzRevenueModel();
        //转入记录
        $ru=[
            'uid'=>$info['id'],
            'contactunitid'=>0,
            'accountid'=>$data['ru'],
            'type'=>0,
            'money'=>$data['money'],
            'appendix'=>$data['appendix'],
            'tclassify'=>11,
            'incometime'=>$data['incometime'],
            'remarks'=>$data['remarks'],
            'ctime'=>date('Y-m-d H:i:s',time())//拍照时间
        ];
        //转出记录
        $chu=[
            'uid'=>$info['id'],
            'contactunitid'=>0,
            'accountid'=>$data['chu'],
            'type'=>1,
            'money'=>'-'.$data['money'],
            'appendix'=>$data['appendix'],
            'tclassify'=>11,
            'incometime'=>$data['incometime'],
            'remarks'=>$data['remarks'],
            'ctime'=>date('Y-m-d H:i:s',time())//拍照时间
        ];
        $res=$revenue->adddata($chu);
        if($res){
            $revenue->adddata($ru);
            return json_encode(['code' =>0, 'msg'=>'操作成功！' ]);
        }else{
            return json_encode(['code' =>1, 'msg'=>'操作失败！' ]);
        }
    }

    //添加固定资产+其它
    public function regul(){
        $info=$this->chektoken();//检测用户token
        if(!$info){
            return json_encode(['code' =>2, 'msg'=>'请登录' ]);
        }
        $request = Request();
        $data = $request->param();
        $array=[
            'uid'=>$info['id'],
            'title'=>$data['title'],
            'type'=>$data['type'],
            'money'=>$data['money'],
            'appendix'=>$data['appendix'],
            'incometime'=>$data['incometime'],
            'remarks'=>$data['remarks'],
            'ctime'=>date('Y-m-d H:i:s',time())//拍照时间
        ];
        $res=Db::name('fp_jz_fixed')->insert($array);
        if($res){
            return json_encode(['code'=>0,'msg'=>'添加成功']);
        }else{
            return json_encode(['code'=>1,'msg'=>'添加失败']);
        }
    }

    //删除固定资产和其它
    public function delregul()
    {
        $info = $this->chektoken();//检测用户token
        if (!$info) {
            return json_encode(['code' => 2, 'msg' => '请登录']);
        }
        $request = Request();
        $data = $request->param();
        $res=Db::name('fp_jz_fixed')->where(['uid'=>$info['id'],'id' => $data['id']])->delete();
        if($res){
            return json_encode(['code'=>0,'msg'=>'删除成功']);
        }else{
            return json_encode(['code'=>1,'msg'=>'删除失败']);
        }
    }

    //固定资产和其它列表
    public function regullist(){
        $info = $this->chektoken();//检测用户token
        if (!$info) {
            return json_encode(['code' => 2, 'msg' => '请登录']);
        }
        $request = Request();
        $data = $request->param();
        $res=Db::name('fp_jz_fixed')->where(['uid'=>$info['id'],'type' => $data['type']])->select();
        if(!$res){
            $res=[];
        }
        return json_encode(['code'=>0,'msg'=>'成功','result'=>$res]);
    }

    //账户列表
    public function acclist(){
        $info=$this->chektoken();//检测用户token
        if(!$info){
            return json_encode(['code' =>2, 'msg'=>'请登录' ]);
        }
        $request = Request();
        $data = $request->param();
        $ccount= new JzAccountModel();
        $list=$ccount->field('id,uid,title')->where(['state'=>0])->whereIn('uid','0,'.$info['id'])->select();
        return json_encode(['code'=>0,'msg'=>'ok','result'=>$list]);
    }

    //通过订单查询核销记录
    public function hexiaolist(){
        $info=$this->chektoken();//检测用户token
        if(!$info){
            return json_encode(['code' =>2, 'msg'=>'请登录' ]);
        }
        $request = Request();
        $data = $request->param();
        $detailed= new JzDetailedModel();
        $rqq=$detailed->getList(['receivableid'=>$data['id']]);
        if(!$rqq){
            $rqq=[];
        }
        return json_encode(['code'=>0,'msg'=>'ok','result'=>$rqq]);
    }

    //删除账户
    public function delacc(){
        $info=$this->chektoken();//检测用户token
        if(!$info){
            return json_encode(['code' =>2, 'msg'=>'请登录' ]);
        }
        $request = Request();
        $data = $request->param();
        $ccount= new JzAccountModel();
        //查询是否用相应的类型数据存在
        $receivable= new JzReceivableModel();
        $rqq=$receivable->getOne(['accountid'=>$data['id']]);
        if($rqq){
            return json_encode(['code' =>1, 'msg'=>'删除失败，此账户已有往来账款！' ]);
        }else{
            $aa=$ccount->where(['id'=>$data['id']])->delete();
            if($aa){
                return json_encode(['code' =>0, 'msg'=>'删除成功' ]);
            }else{
                return json_encode(['code' =>1, 'msg'=>'删除失败' ]);
            }
        }
    }

}
?>