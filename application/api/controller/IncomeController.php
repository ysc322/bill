<?php
namespace app\api\controller;
use app\api\controller\base\BaseController;
use think\facade\Log;
use think\facade\Request;
use think\Exception;
use think\Db;
use app\common\model\UserModel;
use app\common\model\JzRevenueModel;
use app\common\model\JzContactunitModel;
use app\common\model\JzBookkeepingModel;
use app\common\model\JzAccountModel;
use think\facade\Cache;
use GuzzleHttp\json_encode;

use mylib\curll;
class IncomeController extends BaseController
{
	
    //添加收入
    public function incomeadd(){
		$revenue= new JzRevenueModel();
		$info=$this->chektoken();//检测用户token
		if(!$info){
			return json_encode(['code' =>2, 'msg'=>'请登录' ]);
		}
		$request = Request();
    	$data = $request->param();
        if($data['type']==1){
            $data['money']='-'.$data['money'];
        }
        $array=[
            'uid'=>$info['id'],
            'contactunitid'=>$data['contactunitid'],
            'accountid'=>$data['accountid'],
            'type'=>$data['type'],
            'money'=>$data['money'],
            'appendix'=>$data['appendix'],
            'incometime'=>$data['incometime'],
            'tclassify'=>$data['tclassify'],
            'remarks'=>$data['remarks'],
            'ctime'=>date('Y-m-d H:i:s',time())//拍照时间
        ];
        $res=$revenue->adddata($array);
        if($res){
            return json_encode(['code'=>0,'msg'=>'添加成功']);
        }else{
            return json_encode(['code'=>1,'msg'=>'添加失败']);
        }
	}


    //编辑收入/支出
    public function incomeup(){
        $revenue= new JzRevenueModel();
        $info=$this->chektoken();//检测用户token
        if(!$info){
            return json_encode(['code' =>2, 'msg'=>'请登录' ]);
        }
        $request = Request();
        $data = $request->param();
        $res=$revenue->getOne(['id'=>$data['id'],'uid'=>$info['id']]);
        if($res['source']!=0){
            return json_encode(['code' =>1, 'msg'=>"此核销内容不可编辑！" ]);
        }
        $array=[
            'contactunitid'=>$data['contactunitid'],
            'accountid'=>$data['accountid'],
            'money'=>$data['money'],
            'appendix'=>$data['appendix'],
            'incometime'=>$data['incometime'],
            'tclassify'=>$data['tclassify'],
            'remarks'=>$data['remarks']
        ];
        $res=$revenue->updatedatas($array,['uid'=>$info['id'],'id'=>$data['id']]);
        //echo $revenue->getLastSql();exit;
        if($res !== false){
            return json_encode(['code'=>0,'msg'=>'修改成功']);
        }else{
            return json_encode(['code'=>1,'msg'=>'修改失败']);
        }
    }

    //收入支出列表
    public function incomlist(){
        $info=$this->chektoken();//检测用户token
        if(!$info){
            return json_encode(['code' =>2, 'msg'=>'请登录' ]);
        }
        $request = Request();
        $data = $request->param();
        $revenue= new JzRevenueModel();
        $contactunit= new JzContactunitModel();
        $bookkeep= new JzBookkeepingModel();
        $ccount= new JzAccountModel();
        $where=['type'=>$data['type'],'uid'=>$info['id']];
        if(empty($data['time'])){
            $data['time'] = date('Y-m',time());//当前月份
            $wh[] = ['incometime','like',$data['time'].'%'];
            //$where['incometime']=
        }else{
            $wh[] = ['incometime','like',$data['time'].'%'];
        }
        if(empty($data['tclassify'])){
            unset($data['tclassify']);
        }else{
            $where['tclassify']=$data['tclassify'];
        }
        $list =$revenue->alias("u")
            ->field('u.*')
            ->where($where)->where($wh)->order('u.incometime desc')->paginate(10,false, [
                'query' => Request::instance()->param(),//不丢失已存在的url参数
            ])->toArray();
        // print_r($list);exit;
        if($list){
            for($i=0;$i<count($list['data']);$i++){
                if($list['data'][$i]['contactunitid']==0){
                    $qi['title']='转账';
                }else{
                    $qi=$contactunit->getOne(['id'=>$list['data'][$i]['contactunitid']]);
                }
                $fen=$bookkeep->getOne(['id'=>$list['data'][$i]['tclassify']]);
                $list['data'][$i]['title']=$qi['title'];
                $list['data'][$i]['fentitle']=$fen['title'];
                //账户名称
                $acc=$ccount->getOne(['id'=>$list['data'][$i]['accountid']]);
                $list['data'][$i]['zhangtitle']=$acc['title'];
            }
        }else{
            $list=[];
        }
        $list['zongzc'] = $revenue->where($where)->where($wh)->sum('money');
        return json_encode(['code'=>0,'msg'=>'成功','result'=>$list]);
    }
    //删除收支记录
    public function incomedel(){
        $info=$this->chektoken();//检测用户token
        if(!$info){
            return json_encode(['code' =>2, 'msg'=>'请登录' ]);
        }
        $request = Request();
        $data = $request->param();
        $revenue= new JzRevenueModel();
        $res=$revenue->getOne(['id'=>$data['id'],'uid'=>$info['id']]);
//        if($res['source']!=0){
//            return json_encode(['code' =>1, 'msg'=>"请在".$res['sourcecom']."处删除此条收入！" ]);
//        }
        $qs=$revenue->getdel(['id'=>$data['id'],'uid'=>$info['id']]);
        if($qs){
            return json_encode(['code' =>0, 'msg'=>"删除成功" ]);
        }else{
            return json_encode(['code' =>1, 'msg'=>"删除失败" ]);
        }
    }

    //收入支出详情
    public function indetails(){
        $request = Request();
        $data = $request->param();
        $revenue= new JzRevenueModel();
        $contactunit= new JzContactunitModel();
        $bookkeep= new JzBookkeepingModel();
        $ccount= new JzAccountModel();
        $res=$revenue->getOne(['id'=>$data['id']]);
        //查询企业名称、销售类型
        if($res['contactunitid']==0){
            $qi['title']='转账';
        }else{
            $qi=$contactunit->getOne(['id'=>$res['contactunitid']]);
        }
        $fen=$bookkeep->getOne(['id'=>$res['tclassify']]);
        $account=$ccount->getOne(['id'=>$res['accountid']]);
        $arr=[
            'id'=>$res['id'],
            'title'=>$qi['title'],
            'incometime'=>$res['incometime'],
            'money'=>$res['money'],
            'account'=>$account['title'],
            'remarks'=>$res['remarks'],
            'tclassify'=>$fen['title']
        ];
        return json_encode(['code'=>0,'msg'=>'成功','result'=>$arr]);
    }



	
}
?>