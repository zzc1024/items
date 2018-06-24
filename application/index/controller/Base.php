<?php

namespace app\index\controller;

use think\Controller;
use think\Session;
use think\Request;
use app\index\model\User;
use app\index\model\ShopCart;

class Base extends Controller
{
	public function _initialize()
	{
		//判断登录
		if(Session::has('user_id'))
		{
			$id = Session::get('user_id');
			$user = User::get($id);
			$shopcart = ShopCart::where('user_id',$id)->field('id')->select();
			$count = count($shopcart);
			$this->assign(['user_id'=>$id,'user_name'=>$user['name'],'user_balance'=>$user['balance'],'shopcart_count'=>$count]);
		}
		else
		{
			$this->assign(['user_id'=>0,'shopcart_count'=>0]);
			$request = Request::instance();
			$da = $request->controller()=='Index'||$request->controller()=='Shop'||$request->controller()=='Login';
			if(!$da)
			{
				$this->redirect('Login/index','请先登录');
			}
		}
	}
}
