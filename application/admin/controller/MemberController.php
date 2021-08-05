<?php
namespace app\admin\controller;

use think\Controller;
use think\facade\Request;
use think\facade\Session;
use app\admin\model\UserModel;


class MemberController extends  Controller
{
	public function login()
	{
		$where = array();
		$data = array();
		
		return view('/Member/login',['data'=>$data]);
	}

	public function loginin()
	{
		$request = Request::instance();
		$data = $request->param();
		$model = new UserModel();
		$user = $model->getOne(array('username'=>$data['username']));
		if($user&&$user['password'] == md5($data['password'])){
			session::set('userinfo',$user);
            Session::set('adminid', $user['id']);
            Session::set('admin_name', $user['username']);
			$this->redirect('/Index/index');
		}else{
			$this->error('密码错误');
		}
	
	}

    public function out() {
        //$this->assign('OSSPATH', Config::get('app.OSS_PATH'));
        Session::delete('adminid');
        Session::delete('admin_name');
        Session::delete('userinfo');
        $this->redirect('/Member/login');
    }


    //修改密码
    public function uplogin()
    {
        $request = Request::instance();
        $data = $request->param();
        $model = new UserModel();
        $user = $model->getOne(array('username'=>$data['username']));
        $a=$model->updatedatas(['password'=>md5($data['password'])],['id'=>$user['id']]);
        if($a){
            $this->success('修改成功');
        }else{
            $this->error('密码错误');
        }

    }

	
}
