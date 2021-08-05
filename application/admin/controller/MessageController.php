<?php
namespace app\admin\controller;

use app\admin\model\MessageModel;
use app\common\model\IdfaModel;
use app\common\model\UserModel;
use think\facade\Request;


class MessageController extends BaseController
{
	public function _initialize()
	{
		parent::_initialize();
	}
	public function lists()
	{
		$where = array();
		$orderby = 'id desc';
		$Message= new MessageModel();
        $idfa= new IdfaModel();
        $user= new UserModel();
        $pagesize=20;
		//$list = $Message->getList($where,$orderby);
        $list=$Message->where($where)->group('uid')->order('id desc')->paginate($pagesize);
        $data['list'] = $list;
		$page = $list->render();
		$qq=$list->toArray();
		for ($i=0;$i<count($qq['data']);$i++){
            $cunzai=$Message->where(['uid'=>$qq['data'][$i]['uid']])->order('id desc')->find();
            $users=$user->getOne(['id'=>$qq['data'][$i]['uid']]);
            if($users){
                $idf=$idfa->getOne(['id'=>$users['idfaid']]);
                if(preg_match("/(.{8}-.{4}-.{4}-.{4}-.{12})/",$idf['idfa'])){
                    $cunzai['lei']='ios';
                }else{
                    $cunzai['lei']='android';
                }
                $cunzai['version']=$users['version'];
            }else{
                $cunzai['lei']='已注销';
                $cunzai['version']='';
            }
           if($cunzai){
               if($cunzai['state']==0){
                   $ass[$i]['uid']=$qq['data'][$i]['uid'];
                   $ass[$i]['aa']=1;
               }else{
                   $ass[$i]['uid']='';
                   $ass[$i]['aa']=0;
               }
           }else{
               $ass[$i]['uid']='';
               $ass[$i]['aa']=0;
           }
            $dat['list'][]=$cunzai;
        }
        $last_names = array_column($dat['list'],'addtime');
        array_multisort($last_names,SORT_DESC,$dat['list']);
		//print_r($ass);exit;
		$this->assign('page', $page);
		//$this->assign('ass', $ass);
		//print_r($data);exit;
		return $this->fetch('/base',['_view'=>'message/list','data'=>$dat,'ass'=>$ass]);
	}
	
	public function messagedel(){
		$Message= new MessageModel;
		$request = Request::instance();
		$data = $request->param();
		$Message->where('id',$data['id'])->delete();
		$this->success('删除成功', 'admin/message/lists');
	}

    public function mesdel(){
        $Message= new MessageModel;
        $request = Request::instance();
        $data = $request->param();
        $Message->where('id',$data['id'])->delete();
        return  $this->success('删除成功',"/message/uesrlist?uid=".$data['uid']);
    }

    public function uesrlist()
    {
        $Message= new MessageModel;
        $request = Request::instance();
        $dat = $request->param();
        if(isset($dat['uid'])){
            $where = ['uid'=>$dat['uid']];
            $Message->updatedatas(['state'=>1],['uid'=>$dat['uid'],'type'=>0]);
        }else{
            $where='';
        }
        $pagesize=20;
        $list=$Message->where($where)->order('id desc')->paginate($pagesize);
        $data['list'] = $list;
        $page = $list->render();
        $this->assign('page', $page);
        return $this->fetch('/base',['_view'=>'message/uesrlist','data'=>$data]);
    }


    public function mes()
    {
        $Message= new MessageModel;
        if($_POST){
            $array=[
                'uid'=>$_POST['uid'],
                'type'=>1,
                'content'=>$_POST['content'],
                'addtime'=>time()
            ];
            $aa=$Message->adddata($array);
            if($aa){
                return  $this->success('修改成功',"/message/uesrlist?uid=".$_POST['uid']);
            }else{
                return $this->error('回复失败');
            }
        }
        //return $this->redirect('message/uesrlist?uid='.$_GET['uid']);
    }

}
