<?php
namespace app\admin\controller;

use app\common\model\JtdatModel;
use app\common\model\SoupModel;
use think\facade\Request;


class JtdatController extends BaseController
{
	public function _initialize()
	{
		parent::_initialize();
	}
	public function lists()
	{	$request = Request::instance();
		$data = $request->param();
		//print_r($data);exit;
		$where=[];
		if(empty($data['day'])){
			unset($data['day']);
		}else{
			$where['day']=$data['day'];
		}
		$jtdat= new JtdatModel;
		
		$list =$jtdat->alias("u")
    		->field('u.*')
	    	->where($where)->order('u.id desc')->paginate(20,false, [
                'query' => Request::instance()->param(),//不丢失已存在的url参数
            ]);
		$data['list'] = $list;
		$page = $list->render();
		$this->assign('page', $page);
		return $this->fetch('/base',['_view'=>'jtdat/list','data'=>$data]);
	}

	public function jtdatsave()
	{
		$data = array();
		$module = array();
		$request = Request::instance();
		$data = $request->param();
		$opt = isset($data['opt'])?$data['opt']:0;
		$id = isset($data['id'])?$data['id']:0;
		$jtdat= new JtdatModel;
		if(!empty($opt) && $opt=='save'){
			//保存
			$datas = array();
			$datas = $data['data'];
			if($id>0){
				//update
				$jtdat->updatedatas($datas,array('id'=>$id));
            	$this->success('修改成功', 'admin/jtdat/lists');
			}else{
			    if($jtdat->getOne(array('day'=>$datas['day']))){
			        //update
                    $jtdat->updatedatas($datas,array('day'=>$datas['day']));
                    $this->success('修改成功', 'admin/jtdat/lists');
                }else{
                    //insert
                    $datas['ctime'] = time();
                    $jtdat->adddata($datas);
                    $this->success('新增成功', 'admin/jtdat/lists');
                }

			}
			//header("Location:/manager/settings_modulelist/");
			exit;
		}
	
		if($id>0){
			$module = $jtdat->getOne(array('id'=>$id));
			//$module = Settings_module::get(array('id'=>$id));
		}
        $soup= new SoupModel();
		$where=['state'=>1];
		$souplist=$soup->getList($where);
		$data['module'] = $module;
        $this->assign('soup', $souplist);
		return $this->fetch('/base',['_view'=>'jtdat/edit','data'=>$data]);
	}
	
}
