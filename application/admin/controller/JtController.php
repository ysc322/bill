<?php
namespace app\admin\controller;

use app\common\model\JtdatModel;
use app\common\model\SoupModel;
use app\common\model\CommentModel;
use think\facade\Request;


class JtController extends BaseController
{
	public function _initialize()
	{
		parent::_initialize();
	}
	public function index()
	{	$request = Request::instance();
		$data = $request->param();
		$soup= new SoupModel();
        $jtdat= new JtdatModel();
		if (Request::instance()->isPost()){
			//print_r($_POST);exit;
			$state    = $_POST['state'];
			$id 	  = $_POST['id'];
			if(is_numeric($state)) {
				$date=['state'=>$state];
				$wh=['id'=>$id];
				$result=$soup->updatedatas($date,$wh);
				if($result[1]==0){
					echo json_encode(array("result"=>1));    //发送成功
				}else{
					echo json_encode(array("result"=>2));  //发送失败
				}
			}
		}
		$where=[];
		$search = $data['keyword'];
		if($data['keyword'] > -1){
			$where=['state'=>$search];
		}
		//print_r($where);exit;
		$list = $soup->alias("u")
    		->field('u.*')
	    	->where($where)->order('u.id desc')->paginate(20,false, [
                'query' => Request::instance()->param(),//不丢失已存在的url参数
            ]);
		//echo $soup->getlastsql();exit;
        for($i=0;$i<count($list);$i++){
            //获取鸡汤对应的日期
            $reww=$jtdat->getOne(['jtid'=>$list[$i]['id']]);
            if($reww){
                $list[$i]['day']=$reww['day'];
            }else{
                $list[$i]['day']='';
            }
        }
		$data['list']=$list;
		$page = $list->render();
		$this->assign('page', $page);
		//$this->assign('list', $list);
		return $this->fetch('/base',['_view'=>'jt/index','data'=>$data]);
	}
	//添加鸡汤
	public function addsave()
	{
		$data = array();
		$module = array();
		$request = Request::instance();
		$data = $request->param();
		//print_r($data);exit;
		$opt = isset($data['opt'])?$data['opt']:0;
		$id = isset($data['id'])?$data['id']:0;
		$soup= new SoupModel();
		$jtdat= new JtdatModel();

		if(!empty($opt) && $opt=='save'){
			//保存
			$datas = array();
			$datas = $data['data'];
			$day=$datas['day'];
			$zhiding=$datas['zhiding'];

			unset($datas['day']);//传过来的时间删除
			unset($datas['zhiding']);//传过来的时间删除
			$datas['comment'] = rawurlencode($datas['comment']);
            //$chek=$soup->getOne(['comment'=>$datas['comment']]);

			if($id>0){
				//update' 1
                if($zhiding==0){
                    $jtdat->adddata(['day'=>$day,'jtid'=>$id,'ctime'=>time()]);
                }
				$soup->updatedatas($datas,array('id'=>$id));
				//echo $soup->getlastsql();exit;
            	$this->success('修改成功', 'admin/jt/index');
			}else{
				//insert
                if($soup->getOne(['comment'=>$datas['comment'],'state'=>1])){
                    return $this->json_error('添加失败,此内容已存在');
                }
                $datas['ctime'] = time();
                $datas['uid'] = rand(909,32000);
                $id=$soup->adddata($datas);
                if($zhiding==0){
                    $jtdat->adddata(['day'=>$day,'jtid'=>$id,'ctime'=>time()]);
                }
                $this->success('新增成功', 'admin/jt/index');
			}
			//header("Location:/manager/settings_modulelist/");
			exit;
		}
	
		if($id>0){
			$module = $soup->getOne(array('id'=>$id));
			//$module = Settings_module::get(array('id'=>$id));
		}
		$module['comment']=rawurldecode($module['comment']);
		$data['module'] = $module;
		//获取鸡汤对应的日期
        $reww=$jtdat->getOne(['jtid'=>$data['module']['id']]);
        if($reww){
            $data['module']['day']=$reww['day'];
        }else{
            $data['module']['day']='';
        }
		return $this->fetch('/base',['_view'=>'jt/edit','data'=>$data]);
	}
	
	
	//评论列表
	public function plindex()
	{	
		$request = Request::instance();
		$res = $request->param();
		$com= new CommentModel();
		if (Request::instance()->isPost()){
			//print_r($_POST);exit;
			$state    = $_POST['state'];
			$id 	  = $_POST['id'];
			if(is_numeric($state)) {
				$date=['state'=>$state];
				$wh=['id'=>$id];
				$result=$com->updatedatas($date,$wh);
				//echo $com->getlastsql();exit;
				if($result[1]==0){
					echo json_encode(array("result"=>1));    //发送成功
				}else{
					echo json_encode(array("result"=>2));  //发送失败
				}
			}
		}
		
		$search = $res['keyword'];
		if($res['keyword'] > -1){
			$where=['state'=>$search];
		}
		$where['jtid']=$res['jtid'];
		$where['pid']=0;
		//print_r($where);exit;
		$list = $com->alias("u")
    		->field('u.*')
	    	->where($where)->order('u.id desc')->paginate(20,false, [
                'query' => Request::instance()->param(),//不丢失已存在的url参数
            ]);
		//echo $com->getlastsql();exit;
		$data['list']=$list;
		foreach($data['list'] as $k=>$vo){
			$jtnum=$com->getCount(['comid'=>$vo['id']]);
			//print_r($jtnum);exit;
				if($jtnum > 0){
					$vo['yunumber']= $jtnum;
				}else{
					$vo['yunumber']=0;
				}
		}
		$page = $list->render();
		$this->assign('page', $page);
		$this->assign('r', $res['jtid']);
		return $this->fetch('/base',['_view'=>'jt/comindex','data'=>$data]);
	}
	
	//新增评论
	public function addpl(){
		$com= new CommentModel();
		$soup= new SoupModel();
		$request = Request::instance();
    	$data = $request->param();
		$opt = isset($data['opt'])?$data['opt']:0;
		$id = isset($data['id'])?$data['id']:0;
		//print_r($data);exit;
		if(!empty($opt) && $opt=='save'){
			//保存
			$res = $data['data'];
			$res['ctime']=time();
			if($id>0){
				$com->updatedatas($res,array('id'=>$id));
            	$this->success('修改成功', 'admin/jt/plindex?jtid='.$data['jtid']);
			}else{
				//insert
				$ser=$com->adddata($res);
				if($ser) {
					//鸡汤评论数+1
					if($res['pid']==0){
						//鸡汤的评论数量
						$where=['id'=>$res['jtid']];
						$soup->getNum($where,'comnumber');
					}
					$this->success('新增成功', 'admin/jt/plindex?jtid='.$data['jtid']);
				}else{
					return $this->json_error('添加失败');
				}
			}
			exit;
		}

		if($id>0){
			$module = $com->getOne(array('id'=>$id));
		}
		$module['comment']=rawurldecode($module['comment']);
		$module['jtid']=$data['jtid'];
		$module['uid']=rand(909,32083);
		$data['module'] = $module;
		return $this->fetch('/base',['_view'=>'jt/pingl','data'=>$data]);
	}
	
	//新增回复评论
	public function addhfpl(){
		$com= new CommentModel();
		$soup= new SoupModel();
		$request = Request::instance();
    	$data = $request->param();
		$opt = isset($data['opt'])?$data['opt']:0;
		$id = isset($data['id'])?$data['id']:0;
		if(!empty($opt) && $opt=='save'){
			//保存
			$res = $data['data'];
			$res['ctime']=time();
			//print_r($res);exit;
			if($id>0){
				$com->updatedatas($res,array('id'=>$id));
            	$this->success('修改成功', 'admin/jt/huifu?jtid='.$data['jtid'].'&comid='.$data['data']['comid']);
			}else{
				//insert
				$ser=$com->adddata($res);
				if($ser) {
					$this->success('新增成功', 'admin/jt/huifu?jtid='.$data['jtid'].'&comid='.$data['comid']);
				}else{
					return $this->json_error('添加失败');
				}
			}
			exit;
		}
		if($id>0){
			$module = $com->getOne(array('id'=>$id));
		}
		$module['comment']=rawurldecode($module['comment']);
		$module['jtid']=$data['jtid'];
		$module['comid']=isset($data['comid'])?$data['comid']:$module['comid'];
		$data['module'] = $module;
		if($data['module']['pid']==0){
			$data['module']['comid']=$data['module']['id'];
		}
		return $this->fetch('/base',['_view'=>'jt/pinglhf','data'=>$data]);
	}
	
	
	//回复页面的回复
	public function hfpl(){
		$com= new CommentModel();
		$soup= new SoupModel();
		$request = Request::instance();
    	$data = $request->param();
		//print_r($data);exit;
		$opt = isset($data['opt'])?$data['opt']:0;
		if(!empty($opt) && $opt=='save'){
			//保存
			$res = $data['data'];
			$res['ctime']=time();
			//print_r($res);exit;
			$ser=$com->adddata($res);
			if($ser) {
				$this->success('新增成功', 'admin/jt/huifu?jtid='.$data['jtid'].'&comid='.$data['comid']);
			}else{
				return $this->json_error('添加失败');
			}
			exit;
		}
		$module['jtid']=$data['jtid'];
		$module['comid']=$data['comid'];
		$module['pid']=$data['id'];
		$data['module'] = $module;
		return $this->fetch('/base',['_view'=>'jt/hfpl','data'=>$data]);
	}
	
	
	
	
	
	
	//评论的回复详情
	public function huifu()
	{	$request = Request::instance();
		$res = $request->param();
		$com= new CommentModel();
		if (Request::instance()->isPost()){
			//print_r($_POST);exit;
			$state    = $_POST['state'];
			$id 	  = $_POST['id'];
			if(is_numeric($state)) {
				$date=['state'=>$state];
				$wh=['id'=>$id];
				$result=$com->updatedatas($date,$wh);
				//echo $com->getlastsql();exit;
				if($result[1]==0){
					echo json_encode(array("result"=>1));    //发送成功
				}else{
					echo json_encode(array("result"=>2));  //发送失败
				}
			}
		}
		
		$search = $res['keyword'];
		if($res['keyword'] > -1){
			$where=['state'=>$search];
		}
		$where['jtid']=$res['jtid'];
		$where['comid']=$res['comid'];
		//print_r($where);exit;
		$list = $com->alias("u")
    		->field('u.*')
	    	->where($where)->order('u.id desc')->paginate(20,false, [
                'query' => Request::instance()->param(),//不丢失已存在的url参数
            ]);
		//echo $com->getlastsql();exit;
		$data['list']=$list;
		$page = $list->render();
		$this->assign('page', $page);
		$this->assign('res', $res);
		return $this->fetch('/base',['_view'=>'jt/huifu','data'=>$data]);
	}
	
	
	
	//删除鸡汤
    public function delsoup(){
        $request = Request::instance();
        $data = $request->param();
        $soup= new SoupModel();
        $res=$soup->where(['id'=>$data['id']])->delete();
        if ($res){
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
    }
	
	
	//判断鸡汤是否已经提交过

    //判断某一天是否已经设置过鸡汤
    public function chackjt(){
        $request = Request::instance();
        $jtid = $request->param('jtid');
        $jtdat= new JtdatModel();
        $res=$jtdat->checkjtid($jtid);
        if($res){
            return 1;
        }else{
            return 0;
        }

    }
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
}
