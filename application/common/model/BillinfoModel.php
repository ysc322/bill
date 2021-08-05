<?php
namespace app\common\model;
use think\Model;
use think\Db;

class BillinfoModel extends Model
{
    protected $table = 'fp_billinfo';
	/*public $def_type = [
					'1'=>"增值税专用发票",
					'2'=>"出租车发票",
					'3'=>"火车票",
					'4'=>"飞机票",
					'5'=>"通用机打发票",
					'6'=>"定额发票"
				];
	//类型查询
	public function getTypeAttr($value) {
        return $this->def_type[$value];
    }
	*/
    //查询是否已经存在
    function getInfo($where){
        return Db::name('fp_billinfo')->where($where)->find();
    }
    //更新数据
    function updatedatas($data,$where){
        return Db::name('fp_billinfo')->where($where)->update($data);
    }

    //更新和删除id为集合的专用
    function updateFolder($data,$idas){
        return Db::name('fp_billinfo')->where('id', 'IN', $idas)->update($data);
    }
    //新增数据
    function adddata($data){
        return Db::name('fp_billinfo')->insert($data);
    }
    //新增数据并返回数据id
    function getAdddata($data){
        return Db::name('fp_billinfo')->insertGetId($data);
    }
    
    //获取列表
    function getList($where){
        return Db::name('fp_billinfo')->where($where)->select();
    }

    //获取列表
    function getAll($ids){
        return Db::name('fp_billinfo')->where('id', 'IN', $ids)->select();
    }

    //获取发票的数量
    function getNumber($where){
        $res=Db::name('fp_billinfo')->where($where)->count();
        if(!$res){
            $res=0;
        }
        return $res;
    }
    //获取某个文件夹中的发票价值的和
    function getAmount($where){
        $res=Db::name('fp_billinfo')->where($where)->sum('amount');
        if(!$res){
            $res=0;
        }
        return $res;
    }

    //获取某一段时间的发票数量
    function getDayNumber($where){
        $start = date('Y-m-d 00:00:00');
        $end = date('Y-m-d H:i:s');
        return Db::name('fp_billinfo')->where($where)->whereBetweenTime('ctime',$start,$end)->count();
    }

    //查询此条数据
    function getXq($where,$field){
        return Db::name('fp_billinfo')->field($field)->where($where)->find();
    }

    //查询多条数据
    function getXqduo($where,$field){
        return Db::name('fp_billinfo')->field($field)->where($where)->select();
    }

    //查询当前文件中的每个月的发票的信息，并按照开票时间月分组
    function getYuek($where){
        $subQuery = Db::table('fp_billinfo')->field('id,uid,typeid,invoicetype,invoicename,photodate,opendate,photourl,pretax,amount,checkbill,checkstate,billcode,buyname,ctime,source,soutype,checktime')
        ->where($where)->buildSql();
        $info=Db::table($subQuery .'a')
        ->field('left(a.opendate,7) as yearmoth,sum(amount) as kindamount')
        ->group('yearmoth')->order('yearmoth', 'desc')->select();
       // echo Db::table($subQuery .'a')->getlastsql();exit;
        if ($info) {
            for ($i=0; $i < count($info); $i++) { 
                $as=Db::table($subQuery .'a')->where([['a.opendate','like','%'.$info[$i]['yearmoth'].'%']])->order('a.ctime','desc')->select();
                for ($j=0; $j < count($as); $j++) { 
                    //$as[$j]['photourl']=INLET_PATH . $as[$j]['photourl'];
                    if($as[$j]['photourl']!='') {
                        $sas=substr($as[$j]['photourl'],2);
                        $as[$j]['photourl'] = IMG_PATH . $sas . '?x-oss-process=image/resize,l_100';
                    }else{
                        $as[$j]['photourl'] = INLET_PATH;
                    }
                    $as[$j]['ctime']=date('Y-m-d H:i:s',$as[$j]['ctime']);
                }
                $info[$i]['list']=$as;
                $info[$i]['numgs']=count($as);
            }
            $res=$info;
        }else{
            $res=[];
        }
        return $res;
    }

    //查询当前文件中的每个月的发票的信息，并按照拍照时间月分组
    function getYuep($where){
        $subQuery = Db::table('fp_billinfo')->field('id,uid,typeid,invoicetype,invoicename,photodate,opendate,photourl,pretax,amount,checkbill,checkstate,billcode,buyname,ctime,source,soutype,checktime')
        ->where($where)->buildSql();
        $info=Db::table($subQuery .'a')
        ->field('left(a.photodate,7) as yearmoth,sum(amount) as kindamount')
        ->group('yearmoth')->order('yearmoth', 'desc')->select();
        if ($info) {
            for ($i=0; $i < count($info); $i++) { 
                $as=Db::table($subQuery .'a')->where([['a.photodate','like','%'.$info[$i]['yearmoth'].'%']])->order('a.ctime','desc')->select();
                for ($j=0; $j < count($as); $j++) { 
                    //$as[$j]['photourl']=INLET_PATH . $as[$j]['photourl'];
                    if($as[$j]['photourl']!=''){
                        $sas=substr($as[$j]['photourl'],2);
                        $as[$j]['photourl'] = IMG_PATH . $sas . '?x-oss-process=image/resize,l_100';
                    }else{
                        $as[$j]['photourl'] = INLET_PATH ;
                    }
                    $as[$j]['ctime']=date('Y-m-d H:i:s',$as[$j]['ctime']);
                }
                $info[$i]['list']=$as;
                $info[$i]['numgs']=count($as);
            }
            $res=$info;
        }else{
            $res=[];
        }
        return $res;
    }

    //获取发票夹中的不同分类的发票个数及金额总和
    function getBillFolder($where){
        return Db::name('fp_billinfo')->field('invoicetype,count(id) as billnumber,sum(amount) as amountsum')->where($where)->group('invoicetype')->select();
    }

    //获取idas为集合的发票信息
    function getExport($field,$idas){
        return Db::name('fp_billinfo')->field($field)->where('id', 'IN', $idas)->select();
    }
    //获取发票夹中的不同分类的发票个数及金额总和
    function getBillidas($idas){
        return Db::name('fp_billinfo')->field('invoicetype,count(id) as billnumber,sum(amount) as amountsum')->where('id', 'IN', $idas)->group('invoicetype')->select();
    }
    //获取所选的发票的总金额
    function getAmountidas($idas){
        $res=Db::name('fp_billinfo')->where('id', 'IN', $idas)->sum('amount');
        if(!$res){
            $res=0;
        }
        return $res;
    }


    //获取发票列表，按照查验时间、开票时间倒序排列
    function  getBillxsort($field,$where){
        return Db::name('fp_billinfo')->field($field)->where($where)->order("opendate desc")->select();
    }

    //获取发票列表，按照查验时间、开票时间倒序排列
    function  getBillxcheck($field,$where){
        return Db::name('fp_billinfo')->field($field)->where($where)->order("checktime desc")->select();
    }

    //获取用户的一段时间的查验时间
    function getChektime($uid,$start,$end){
        return Db::name('fp_billinfo')->field("DATE_FORMAT(`checktime`,'%Y-%m-%d') as opendate")->where(['uid'=>$uid,'state'=>0,'checkbill'=>1,'soutype'=>0])->whereTime('checktime','between',[$start." 00:00:00",$end." 23:59:59"])->order("id desc")->select();
    }

    //获取用户的一段时间的开票时间
    function getOpentime($uid,$start,$end){
        return Db::name('fp_billinfo')->field('opendate')->where(['uid'=>$uid,'state'=>0,'checkbill'=>1,'soutype'=>0])->whereTime('opendate','between',[$start,$end])->order("id desc")->select();
    }











}