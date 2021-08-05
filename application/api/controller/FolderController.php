<?php
namespace app\api\controller;
use app\api\controller\base\BaseController;
use think\facade\Log;
use think\facade\Request;
use think\Exception;
use think\Db;
use app\common\model\UserModel;
use app\common\model\FolderModel;
use app\common\model\UfolderModel;
use think\facade\Cache;
use app\common\model\BillinfoModel;
use GuzzleHttp\json_encode;
use mylib\oss;
class FolderController extends BaseController
{
	//新建发票夹
	public function folderadd(){
		$info=$this->chektoken();//检测用户token
		if(!$info){
			return json_encode(['code' =>2, 'msg'=>'请登录' ]);
		}
		$request = Request();
    	$data = $request->param();
    	$folder = new FolderModel();
        $ufolder = new UfolderModel();
        //非会员限制1个发票夹
       if($info['member']!=1){
            if(count($folder->getList(['uid'=>$info['id'],'state'=>0]))>=1){
                return json_encode(['code' =>3, 'msg'=>'非会员发票夹数量不能超过1个！' ]);
            }
        }
    	if($data['title']==''){
    		return $this->json_error('请填写标题');
    	}
    	if($folder->getInfo(['uid'=>$info['id'],'title'=>$data['title'],'state'=>0])){
    		return $this->json_error('此文件夹已存在');
    	}
    	$dat=[
    		'title'=>$data['title'],
    		'uid'=>$info['id'],
            'invitecode'=>$this->checkincl(),
    		'ctime'=>date('Y-m-d H:i:s',time())
    	];
    	$res=$folder->getAdd($dat);
    	if($res){
            $ufolder->adddata(['uid'=>$info['id'],'folderid'=>$res,'ctime'=>date('Y-m-d H:i:s',time())]);//创建对应关系
    		return $this->json_success('创建成功');
    	}else{
    		return $this->json_error('创建失败');
    	}
	}
		


	//发票夹列表
    public function folderlist(){
    	$info=$this->chektoken();//检测用户token
		if(!$info){
			return json_encode(['code' =>2, 'msg'=>'请登录' ]);
		}
    	$folder = new FolderModel();
        $ufolder = new UfolderModel();
        $billinfo= new BillinfoModel();
        $mren[]=[
            "id"=> "0",
            "uid"=> $info['id'],
            "title"=> "我的发票夹",
            "ctime"=> "2020-02-20 19:03:00",
            "state"=> "0",
            'number'=>$billinfo->getNumber(['uid'=>$info['id'],'folderid'=>0,'state'=>0]),
            "invitecode"=>"666666"
        ];
        //获取对应的发票夹的IDs
        $resd=$ufolder->getXlist(['uid'=>$info['id'],'hide'=>0]);
        for($i=0;$i<count($resd);$i++){
            $res[$i]=$folder->getInfo(['id'=>$resd[$i]['folderid'],'state'=>0]);
            $where=['folderid'=>$resd[$i]['folderid'],'state'=>0];
            $res[$i]['number']=$billinfo->getNumber($where);//数量
        }
		if (!$res) {
			$res=[];
		}
        $req=array_merge($mren,$res);
		return json_encode(['code'=>0,'msg'=>'ok','result'=>$req]);
    }
	
    //删除发票夹
    public function folderdel(){
    	$info=$this->chektoken();//检测用户token
		if(!$info){
			return json_encode(['code' =>2, 'msg'=>'请登录' ]);
		}
		$request = Request();
    	$data = $request->param();
    	$folder = new FolderModel();
        $billinfo= new BillinfoModel();
        $ufolder = new UfolderModel();
        //获取此发票夹的来源
        $fol=$ufolder->getInfo(['uid'=>$info['id'],'folderid'=>$data['id']]);
        //echo $ufolder->getLastSql();exit;
        //print_r($fol);exit;
        if($fol['type']==0){
            $where = ['uid'=>$info['id'],'id'=>$data['id']];
            $ufolder->getDel(['folderid'=>$data['id']]);//删除对应关系
            $dat=['state'=>1];
            $res=$folder->updatedatas($dat,$where);
            if($res){
                //修改该文件夹下的所有发票为删除状态
                $billinfo->updatedatas(['state'=>1],['uid'=>$info['id'],'folderid'=>$data['id']]);
                return $this->json_success('删除成功');
            }else{
                return $this->json_error('删除失败');
            }
        }else{
            $res=$ufolder->getDel(['uid'=>$info['id'],'folderid'=>$data['id']]);//删除对应关系
            if($res){
                return $this->json_success('退出成功');
            }else{
                return $this->json_error('退出失败');
            }
        }
    }


    //首页我的发票夹
    public function indexfolder(){
        $res=$this->chektoken();//检测用户token
        if(!$res){
            return json_encode(['code' =>2, 'msg'=>'请登录' ]);
        }
        //获取发票夹列表
        $folder = new FolderModel();
        $billinfo= new BillinfoModel();
        $ufolder = new UfolderModel();
        //获取默认发票夹的数据
        $aas=$billinfo->getAmount(['uid'=>$res['id'],'folderid'=>0,'state'=>0]);
        $yicha=$billinfo->getAmount(['uid'=>$res['id'],'state'=>0,'checkbill'=>1,'soutype'=>0]);
        $mren[]=[
            'id'=>-1,
            'uid'=>$res['id'],
            'state'=>0,
            'ctime'=>'',
            'invitecode'=>'888888',
            'title'=>"已查验",
            'power'=>0,
            'number'=>$billinfo->getNumber(['uid'=>$res['id'],'state'=>0,'checkbill'=>1,'soutype'=>0]),
            'amount'=>sprintf("%.2f",round($yicha,2))
        ];
        $mren[]=[
            'id'=>0,
            'uid'=>$res['id'],
            'state'=>0,
            'ctime'=>'',
            'invitecode'=>'666666',
            'title'=>"我的发票夹",
            'power'=>0,
            'number'=>$billinfo->getNumber(['uid'=>$res['id'],'folderid'=>0,'state'=>0]),
            'amount'=>sprintf("%.2f",round($aas,2))
        ];
        //获取对应的发票夹的IDs
        $resd=$ufolder->getXlist(['uid'=>$res['id'],'hide'=>0]);
        for($i=0;$i<count($resd);$i++){
            $info[$i]=$folder->getInfo(['id'=>$resd[$i]['folderid'],'state'=>0]);
            $info[$i]['power']=$resd[$i]['type'];
        }
        //$info = $folder->getList(['uid'=>$res['id'],'state'=>0]);
        if ($info) {
            //获取此文件中的数据
            for($i=0;$i<count($info);$i++){
                //获取某个文件夹中的发票的个数和总价格
                $where=['folderid'=>$info[$i]['id'],'state'=>0];
                $info[$i]['number']=$billinfo->getNumber($where);//数量
                $n=$billinfo->getAmount($where);//总价格
                //sprintf("%.2f", round($n,2))
                $info[$i]['amount']=sprintf("%.2f",round($n,2));
            }
        }else{
            $info=[];
        }
        $info=array_merge($mren,$info);
        return json_encode(['code'=>0,'msg'=>'ok','result'=>$info]);
    }

    /*
    *   首页发票列表
    *   token  folderid发票夹id
    *   type 1-时间 不传或其他值-开票时间
    */
    public function folderxq(){
        $res=$this->chektoken();//检测用户token
        if(!$res){
            return json_encode(['code' =>2, 'msg'=>'请登录' ]);
        }
        $request = Request();
        $data = $request->param();
        $billinfo= new BillinfoModel();
        $type= $data['type'] ? $data['type'] : 0;
        $where=['uid'=>$res['id'],'state'=>0,'checkbill'=>1,'soutype'=>0];
        $field="id,buyname,billcode,amount,opendate,checkstate,checktime,checkbill";
        if($billinfo->getInfo($where)){
            if ($type==1) {
                //按开票时间排序
                $info=$billinfo->getBillxsort($field,$where);
            }else{
                //按照查验时间排序
                $info=$billinfo->getBillxcheck($field,$where);
            }
            //echo $billinfo->getLastSql();exit;
        }else{
            $info=[];
        }
        return json_encode(['code'=>0,'msg'=>'ok','result'=>$info]);
    }

    /*
    *   文件夹中发票列表
    *   token  folderid发票夹id
    *   type 1-时间 不传或其他值-开票时间
    */
    public function folderlistxq(){
        $res=$this->chektoken();//检测用户token
        if(!$res){
            return json_encode(['code' =>2, 'msg'=>'请登录' ]);
        }
        $request = Request();
        $data = $request->param();
        $billinfo= new BillinfoModel();
        if($data['folderid']=='-1'){
            $where=['uid'=>$res['id'],'state'=>0,'checkbill'=>1,'soutype'=>0];
        }elseif($data['folderid']=='0'){
            $where=['uid'=>$res['id'],'folderid'=>$data['folderid'],'state'=>0];
        }else{
            $where=['folderid'=>$data['folderid'],'state'=>0];
        }

        if($billinfo->getInfo($where)){
            if ($data['type']==1) {
                //按拍照时间排序
                $info=$billinfo->getYuep($where);
            }else{
                //按开票时间排序
                $info=$billinfo->getYuek($where);
            }
        }else{
            $info=[];
        }
        return json_encode(['code'=>0,'msg'=>'ok','result'=>$info]);
    }




    //生成不重复的邀请码
    public function checkincl(){
       $folder = new FolderModel();
       $code=genLetterDigitRandom(8);
       $es=$folder->getInfo(['invitecode'=>$code]);
       if($es){
           $this->checkincl();
       }else{
           return $code;
       }
    }

    //发票夹成员列表
    public function userlist(){
        $res=$this->chektoken();//检测用户token
        if(!$res){
            return json_encode(['code' =>2, 'msg'=>'请登录' ]);
        }
        $request = Request();
        $data = $request->param();
        $ufolder = new UfolderModel();
        $user    = new UserModel();
        $res=$ufolder->getList(['folderid'=>$data['folderid']]);
        if(!$res){
            $res=[];
        }else{
            for($i=0;$i<count($res);$i++){
                $sq=$user->getInfo(['id'=>$res[$i]['uid']]);
                $res[$i]['uhead']=$sq['uhead'];
                if(strpos($res[$i]['uhead'],'http') === false){
                    $res[$i]['uhead']= "http://imgs.ganbuguo.com/" . $res[$i]['uhead'];
                }
                $res[$i]['nickname']=rawurldecode($sq['nickname']);
            }
        }
        return json_encode(['code'=>0,'msg'=>'ok','result'=>$res]);
    }

    //删除发票夹成员
    public function deluser(){
        $info=$this->chektoken();//检测用户token
        if(!$info){
            return json_encode(['code' =>2, 'msg'=>'请登录' ]);
        }
        $request = Request();
        $data = $request->param();
        $ufolder = new UfolderModel();
        $res=$ufolder->getInfo(['uid'=>$info['id'],'folderid'=>$data['folderid']]);//操作人
        if(!$res){
            return $this->json_error('移除失败');
        }
        if($info['id']==$data['uid']){ //自己删除自己
            if($res['type']=='0'){
                return $this->json_error('管理员不能自己删除自己哦');
            }
            $eee=$ufolder->getDel(['uid'=>$data['uid'],'folderid'=>$data['folderid']]);
            if($eee){
                return $this->json_success('退出成功');
            }else{
                return $this->json_error('退出失败');
            }
        }else{
            if($res['type']=='1'){
                return $this->json_error('您没有操作权限');
            }else{
                $eee=$ufolder->getDel(['uid'=>$data['uid'],'folderid'=>$data['folderid']]);
                if($eee){
                    return $this->json_success('移除成功');
                }else{
                    return $this->json_error('移除失败');
                }
            }
        }
    }

    //输入邀请码添加
    public function incode(){
        $info=$this->chektoken();//检测用户token
        if(!$info){
            return json_encode(['code' =>2, 'msg'=>'请登录' ]);
        }
        $request = Request();
        $data = $request->param();
        $ufolder = new UfolderModel();
        $folder = new FolderModel();
        //获取邀请码对应的文件夹ID
        $res=$folder->getInfo(['invitecode'=>$data['incode'],'state'=>0]);
        if(!$res){
            return $this->json_error('此邀请码不存在或已过期！');
        }
        $sqw=$ufolder->getInfo(['uid'=>$info['id'],'folderid'=>$res['id']]);
        if($sqw){
            return $this->json_error('您已在此发票夹管理列表！');
        }else{
            $arr=[
                'uid'=>$info['id'],
                'folderid'=>$res['id'],
                'type'=>1,
                'ctime'=>date('Y-m-d H:i:s',time())
            ];
           $sas=$ufolder->adddata($arr);
            if($sas){
                return $this->json_success('申请成功');
            }else{
                return $this->json_error('加入失败');
            }
        }

    }

    //修改发票夹名称
    public function folderxg(){
        $info = $this->chektoken();//检测用户token
        if (!$info) {
            return json_encode(['code' => 2, 'msg' => '请登录']);
        }
        $request = Request();
        $data = $request->param();
        $folder = new FolderModel();
        $where=['uid'=>$info['id'],'id'=>$data['id']];
        $res=$folder->updatedatas(['title'=>$data['title']],$where);
        if($res){
            return $this->json_success('修改成功');
        }else{
            return $this->json_error('修改失败');
        }
    }

    //隐藏发票夹
    public function folderhide(){
        $info = $this->chektoken();//检测用户token
        if (!$info) {
            return json_encode(['code' => 2, 'msg' => '请登录']);
        }
        $request = Request();
        $data = $request->param();
        $ufolder = new UfolderModel();
        $where=['uid'=>$info['id'],'folderid'=>$data['folderid']];
        $ee=$ufolder->updatedatas(['hide'=>$data['hide']],$where);
        if($ee){
            if($data['hide']==0){
                return $this->json_success('显示成功');
            }else{
                return $this->json_success('隐藏成功');
            }
        }else{
            if($data['hide']==0){
                return $this->json_error('显示失败');
            }else{
                return $this->json_error('隐藏失败');
            }
        }
    }

    //发票夹排序列表
    public function pailist(){
        $info=$this->chektoken();//检测用户token
        if(!$info){
            return json_encode(['code' =>2, 'msg'=>'请登录' ]);
        }
        $folder = new FolderModel();
        $ufolder = new UfolderModel();
        //获取对应的发票夹的IDs
        $resd=$ufolder->getXlist(['uid'=>$info['id']]);
        for($i=0;$i<count($resd);$i++){
            $res[$i]=$folder->getInfo(['id'=>$resd[$i]['folderid'],'state'=>0]);
            $res[$i]['hide']=$resd[$i]['hide'];
        }
        if (!$res) {
            $res=[];
        }
        return json_encode(['code'=>0,'msg'=>'ok','result'=>$res]);
    }

    //排序
    public function foldersort(){
        $info=$this->chektoken();//检测用户token
        if(!$info){
            return json_encode(['code' =>2, 'msg'=>'请登录' ]);
        }
        $request = Request();
        $data = $request->param();
        $ufolder = new UfolderModel();
        $ids=explode(',', $data['folderid']);
        //print_r($data);exit;
        $ss=0;
        for($i=0;$i<count($ids);$i++){
            $aa=$ufolder->updatedatas(['sort'=>$i],['uid'=>$info['id'],'folderid'=>$ids[$i]]);
            if($aa===false){
                $ss=1;
                break;
            }
        }
        if($ss==0){
            return $this->json_success('修改成功');
        }else{
            return $this->json_error('修改失败');
        }

    }

}
?>