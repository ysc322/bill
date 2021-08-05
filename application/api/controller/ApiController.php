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
class ApiController extends BaseController
{
	
	function getdklist(){

		$request = Request();
		$data = $request->param();
		$jfqid=$data['jfqid'];
		
		$uid = 36717;
		$w = '';
		$arrs = [];
	
		
		$list = Db::name('fp_billinfo')->where('(uid='.$uid.')')->order('opendate')->select();
		foreach ($list as $k=>$v){
			$fpinfo = Db::name('fp_billinfo_jfq')->where(['billid'=>$v['id'],'type'=>1])->select();
			$usemoney = 0;
			$jfqids = [];
			$jfqidss = [];
			if($fpinfo){
			foreach ($fpinfo as $ks=>$vs){

				$usemoney+=$vs['money'];
				$jfqidss[] = $vs['billid'];
			}
			}
			//var_dump($fpinfo);
			$list[$k]['symoney'] = $v['amount']-$usemoney;
			$fpinfos = Db::name('fp_billinfo_jfq')->where(['billid'=>$v['id'],'type'=>1])->select();

			foreach ($fpinfos as $ks=>$vs){
				$jfqids[] = $vs['jfqid'];
			}
			$list[$k]['jfqid'] =$jfqids;
			if($list[$k]['symoney']<=0&&!in_array($jfqid,$jfqids)){
				unset($list[$k]);
			}
		}
		$arr = [];
		foreach ($list as $k=>$v){
			$arr[] = $v;
		}
	//var_dump($list);
		echo json_encode($arr);
	}
	
	function getkplist(){
		$request = Request();
		$data = $request->param();
		$jfqid=$data['jfqid'];
		$w = '';
		if($jfqid){
			$w = ' or kpid='.$jfqid;
		}
		$uid = 39663;
		
		
		$list = Db::name('fp_billinfo')->where('(uid='.$uid.')')->order('opendate')->select();
		foreach ($list as $k=>$v){
			$fpinfo = Db::name('fp_billinfo_jfq')->where(['billid'=>$v['id'],'type'=>2])->select();
			$usemoney = 0;
			$jfqids = [];
			$jfqidss = [];
			if($fpinfo){
			foreach ($fpinfo as $ks=>$vs){

				$usemoney+=$vs['money'];
				$jfqidss[] = $vs['billid'];
			}
			}
			//var_dump($fpinfo);
			$list[$k]['symoney'] = $v['amount']-$usemoney;
			$fpinfos = Db::name('fp_billinfo_jfq')->where(['billid'=>$v['id'],'type'=>2])->select();

			foreach ($fpinfos as $ks=>$vs){
				$jfqids[] = $vs['jfqid'];
			}
			$list[$k]['jfqid'] =$jfqids;
			if($list[$k]['symoney']<=0&&!in_array($jfqid,$jfqids)){
				unset($list[$k]);
			}
		}
		$arr = [];
		foreach ($list as $k=>$v){
			$arr[] = $v;
		}
	//var_dump($list);
		echo json_encode($arr);
	}
	

	function settdkid(){
		$request = Request();
    	$data = $request->param();
		$id=$data['id'];
		$money=$data['money'];
		$jfqid=$data['jfqid'];
		$type=$data['type'];
		$arr = explode(',',$id);
		$langth = count($arr);
		$retrurnmoney = 0;

		$funum = 0;
		if($jfqid){ //先删除
			$list = Db::name('fp_billinfo_jfq')->where(['jfqid'=>$jfqid,'type'=>$type])->select();

			foreach ($list as $k=>$v){
				$info = Db::name('fp_billinfo')->where('id ='.$v['billid'])->find();
				$symoney = $info['symoney']+$v['money'];
				Db::name('fp_billinfo')->where('id ='.$v['billid'])->update(['symoney'=>$symoney]);
				Db::name('fp_billinfo_jfq')->where('id ='.$v['id'])->delete();
			}
		}
		
		$if = Db::name('fp_billinfo')->where('id in('.$id.')')->select();
		if($if){
			foreach ($if as $k=>$v){
					//$has = Db::name('fp_billinfo')->where('dkid like"'.$jfqid.',%"')->select();
					
					//Db::name('fp_billinfo')->where('dkid like"'.$jfqid.',%"')->update(['dkid'=>0]);
					if($langth == 1){ //就一张 发票金额大，申请金额小
						

						$yusem = 0;
						$jlist = Db::name('fp_billinfo_jfq')->where('billid ='.$v['id'])->select();
						foreach ($jlist as $kg=>$g){
							$yusem+=$g['money'];
						}
						
						$vmoney = $v['amount']-$yusem;
						
						$usemoney = $vmoney-$money;
						if($usemoney<0){
							$usemoney = $vmoney;
						}else{
							$usemoney = $money;
						}
						
						$d = [];
						$d['jfqid'] = $jfqid;
						$d['billid'] = $v['id'];
						$d['type'] = $type;
						$d['money'] = $usemoney;
						Db::name('fp_billinfo_jfq')->insert($d);
					}else{
						$yusem = 0;
						$jlist = Db::name('fp_billinfo_jfq')->where('billid ='.$v['id'])->select();
						foreach ($jlist as $kg=>$g){
							$yusem+=$g['money'];
						}
						
						
						


						//var_dump($money);
						$vmoney = $v['money']-$yusem;
						//var_dump($vmoney);
						$money = $money-$vmoney;
						//var_dump($money);
						if($money>0){
							$usemoney = $vmoney;
							$fmoney = $fmoney-$vmoney;
						}else{
							$funum++;
							$usemoney = $fmoney;
						}
						
						//$vmoney = $v['amount']-$yusem;
						//$money = $money-$vmoney;
						//$usemoney = $vmoney;

						if($funum<2){
						$d = [];
						$d['jfqid'] = $jfqid;
						$d['billid'] = $v['id'];
						$d['type'] = $type;
						$d['money'] = $usemoney;
						Db::name('fp_billinfo_jfq')->insert($d);
						}
					}
					
				
			}
		}
		echo json_encode(['usemoney'=>$usemoney]);
	}
}
?>