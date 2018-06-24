<?php
namespace app\index\controller;

use app\index\controller\Base;
use app\index\model\Banner;
use app\index\model\Exhibition;
use app\index\model\Classify;
use app\index\model\Series;
use app\index\model\Product;

class Index extends Base
{
	//首页
    public function index()
    {
    	//banner轮播图
    	$banner = Banner::where('statu',1)->select();
    	$this->assign('banner',$banner);
    	//首页展示
    	$exhibition = Exhibition::where('statu',1)->select();
        foreach($exhibition as $k => $v)
        {   
            $v['pid'] = Product::where('number',$v['number'])->field('id')->find()['id'];
        }
    	$this->assign('exhibition',$exhibition);
    	//商品分类
    	$classify = Classify::where('classify_pid',0)->select();
    	foreach($classify as $k => $v)
    	{
    		$v['next'] = Classify::where('classify_pid',$v->toArray()['classify_id'])->select();
    	}
    	$this->assign('classify',$classify);
    	//商品系列
    	$series = Series::select();
    	$this->assign('series',$series);
       	return $this->fetch();
    }

}
