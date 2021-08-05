<?php
namespace app\api\controller;
use app\api\controller\base\BaseController;
use think\facade\Log;
use think\facade\Request;
use think\Exception;
use think\Db;
use app\common\model\EventModel;
use think\facade\Cache;
use GuzzleHttp\json_encode;

use mylib\img;
class ImgController extends BaseController
{

	public function imgya(){
        $request = Request();
        $data = $request->param();
        $source = $data['url'];
        $dst_img = './uploads/file/copy1.png'; //可加存放路径
        $percent = 1;  #原图压缩，不缩
        $img= new img($source, $percent);
        $image = $img->compressImg($dst_img);
        return json_encode(['code'=>1,'info'=>$image]);
    }
	
	
	
}
?>