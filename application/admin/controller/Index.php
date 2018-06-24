<?php

namespace app\admin\controller;

use app\admin\controller\Base;
use think\Db;
use think\Session;

header("content-type:text/html;charset=utf-8");

class Index extends Base
{
	public function index()
	{
		//查询菜单表
		$result = Db::table('menu')->where('menu_pid',0)->select();
		foreach($result as $k => $v){
			if(is_array($v)){
				$sql = Db::table('menu')->where('menu_pid',$v['menu_id'])->where('statu',1)->select();
				$result[$k]['next'] = $sql;
			}
		}
		$this->assign('result',$result);

		//管理员角色权限
		$role_id = Db::table('role_admin')->where('admin_id',Session::get('admin_id'))->field('role_id')->find()['role_id'];
		$menu_id = Db::table('role_menu')->where('role_id',$role_id)->field('menu_id')->find()['menu_id'];
		$menu_id = json_decode($menu_id);
		$this->assign('menu_id',$menu_id);

		return $this -> fetch();
	}

	public function welcome()
	{
		return $this -> fetch();
	}
}