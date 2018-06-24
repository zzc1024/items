<?php

namespace app\admin\controller;

use think\Controller;
use think\Session;
use think\Request;
use think\Db;

class Base extends Controller
{
	public function  _initialize()
	{
		//登录验证
		if(!Session::has('admin_id'))
		{
			$this->redirect('Login/index','请先登录后操作');
		}
		//权限验证
		$id = Session::get('admin_id');
		$request = Request::instance();
		if($request->controller()!='Index'){

			$menu_a = Db::name('role_menu')->alias('rm')->field('menu_id')->join('role_admin ra','rm.role_id=ra.role_id','left')->where('ra.admin_id',$id)->find()['menu_id'];
			$menu_a = json_decode($menu_a);
			
			$menu_id = Db::name('menu')->field('menu_id')->where('controller',$request->controller())->where('action',$request->action())->find()['menu_id'];
			if(!in_array($menu_id,$menu_a))
			{	
				if($request->action()=='index')
				{
					$this->redirect('Index/index','没有权限');
				}
			}

		}

	}
}