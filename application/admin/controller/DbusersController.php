<?php
namespace app\admin\controller;

use app\common\model\UserModel;
use app\common\model\IdfaModel;
use think\facade\Request;


class DbusersController extends BaseController
{
	public function _initialize()
	{
		parent::_initialize();
	}
	public function lists()
	{
		$request = Request::instance();
		$data = $request->param();
		//print_r($data);exit;
		$where=[];
        if(empty($data['id'])){
            unset($data['id']);
        }else{
            $where['id']=$data['id'];
        }
		if(empty($data['nickname'])){
			unset($data['nickname']);
		}else{
			$where['nickname']=rawurlencode($data['nickname']);
		}
		if(empty($data['state'])){
			unset($data['state']);
		}else{
			$where['state']=$data['state'];
		}
        if(empty($data['uvip'])){
            unset($data['uvip']);
        }else{
            $where['uvip']=$data['uvip'];
        }
		$user= new UserModel();
        $idfa= new IdfaModel();
		if (Request::instance()->isPost()){
			//print_r($_POST);exit;
			$state    = $_POST['state'];
			$id 	  = $_POST['id'];
			if(is_numeric($state)) {
				$date=['state'=>$state];
				$wh=['id'=>$id];
				$result=$user->updatedatas($date,$wh);
				if($result[1]==0){
					echo json_encode(array("result"=>1));    //发送成功
				}else{
					echo json_encode(array("result"=>2));  //发送失败
				}
			}
		}
		
		$list =$user->alias("u")
    		->field('u.*')
	    	->where($where)->order('u.id desc')->paginate(20,false, [
                'query' => Request::instance()->param(),//不丢失已存在的url参数
            ]);
		//echo $user->getLastSql();exit;
		//print_r($list);exit;
		for($i=0;$i<count($list);$i++){
			$list[$i]['nickname']=rawurldecode($list[$i]['nickname']);
			if(strpos($list[$i]['uhead'],'http') === false && !empty($list[$i]['uhead'])){ 
				$list[$i]['uhead']= INLET_PATH . $list[$i]['uhead'];
			}
            $idf=$idfa->getOne(['id'=>$list[$i]['idfaid']]);
            if(preg_match("/(.{8}-.{4}-.{4}-.{4}-.{12})/",$idf['idfa'])){
                $list[$i]['lei']='ios';
            }else{
                $list[$i]['lei']='android';
            }
		}
		$data['list'] = $list;
		$page = $list->render();
		$this->assign('page', $page);
		return $this->fetch('/base',['_view'=>'dbuser/list','data'=>$data]);
	}

	public function usersave()
	{
		$data = array();
		$module = array();
		$request = Request::instance();
		$data = $request->param();
		$opt = isset($data['opt'])?$data['opt']:0;
		$id = isset($data['id'])?$data['id']:0;
		$user= new UserModel;
		if(!empty($opt) && $opt=='save'){
			//保存
			$datas = array();
			$datas = $data['data'];
			$datas['password'] = md5($datas['password']);
			if($id>0){
				//update
				$user->updatedatas($datas,array('id'=>$id));
            	$this->success('修改成功', 'admin/users/lists');
			}else{
				//insert
				$user->adddata($datas);
            	$this->success('新增成功', 'admin/users/lists');
			}
			//header("Location:/manager/settings_modulelist/");
			exit;
		}
	
		if($id>0){
			$module = $user->getOne(array('id'=>$id));
			//$module = Settings_module::get(array('id'=>$id));
		}
		$data['module'] = $module;
		return $this->fetch('/base',['_view'=>'user/edit','data'=>$data]);
	}
	
}
