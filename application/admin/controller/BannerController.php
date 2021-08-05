<?php
namespace app\admin\controller;

use app\common\model\JtdatModel;
//use app\common\model\BannerModel;
use app\common\model\CommentModel;
use think\facade\Request;
use think\Db;

class BannerController extends BaseController
{
	public function _initialize()
	{
		parent::_initialize();
	}
	public function index()
	{	$request = Request::instance();
		$data = $request->param();
		//$banner= new BannerModel();
		if (Request::instance()->isPost()){
			//print_r($_POST);exit;
			$state    = $_POST['state'];
			$id 	  = $_POST['id'];
			if(is_numeric($state)) {
				$date=['state'=>$state];
				$wh=['id'=>$id];
				$result=Db::table('fp_banner')->where($wh)->update($date);
				if($result[1]==0){
					echo json_encode(array("result"=>1));    //发送成功
				}else{
					echo json_encode(array("result"=>2));  //发送失败
				}
			}
		}
		$where=[];

		$search = $data['keyword'];
		if($data['keyword'] !== '' && isset($data['keyword'])){
			$where=['state'=>$search];
		}
		//print_r($where);exit;
		$list = Db::table('fp_banner')->alias("u")
    		->field('u.*')
	    	->where($where)->order('u.id desc')->paginate(20,false, [
                'query' => Request::instance()->param(),//不丢失已存在的url参数
            ]);

		$data['list']=$list;
		$page = $list->render();
		$this->assign('page', $page);
		//$this->assign('list', $list);
		return $this->fetch('/base',['_view'=>'banner/index','data'=>$data]);
	}


    public function addsave()
    {
        $data = array();
        $module = array();
        $arr = array();
        $request = Request::instance();
        $data = $request->param();
        $file = request()->file('file');
        if($file){
            $path = '/uploads/file/';
            $info = $file->move(BASEPATH .$path);
            if($info){
                // 成功上传后 获取上传信息
                // 输出 jpg
                $uploadFilename = $path.$info->getSaveName();;
                $arr['url'] = $uploadFilename;
            }else{
                // 上传失败获取错误信息
                return $file->getError();
            }
        }
        if(isset($arr['url'])){
            $data['data']['photo']=INLET_PATH.$arr['url'];
        }
        $opt = isset($data['opt'])?$data['opt']:0;
        $id = isset($data['id'])?$data['id']:0;

        if(!empty($opt) && $opt=='save'){
            //保存
            $datas = array();
            $datas = $data['data'];
            //print_r($datas);exit;
            $datas['password'] = md5($datas['password']);
            if($id>0){
                //update
                Db::table('fp_banner')->where(array('id'=>$id))->update($datas);
                //$user->updatedatas(,);
                $this->success('修改成功', 'admin/banner/index');
            }else{
                //insert
                $datas['ctime']= date('Y-m-d H:i:s',time());
                Db::table('fp_banner')->insert($datas);
                $this->success('新增成功', 'admin/banner/index');
            }
            //header("Location:/manager/settings_modulelist/");
            exit;
        }

        if($id>0){
            $module = Db::table('fp_banner')->where(array('id'=>$id))->find();
        }
        $data['module'] = $module;
        //print_r($data);exit;
        return $this->fetch('/base',['_view'=>'banner/edit','data'=>$data]);
    }
	
	
	
	
	
	
}
