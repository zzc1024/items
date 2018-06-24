<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/23 0023
 * Time: 下午 17:04
 */

namespace app\api\controller;
use app\api\model\Banner as Bannersql;

class Banner
{
    public function getBanner(){
        $banner = Bannersql::where('statu',1)->select();
        $img = [];
        foreach ($banner as $k => $v)
        {
            $img[$k] = 'http://zhu.com'.$banner[$k]['path'];
            $img[$k] =str_replace('\\', '/',$img[$k]);
        }
        exit(Message(1,$img));
    }
}