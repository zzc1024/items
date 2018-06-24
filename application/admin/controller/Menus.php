<?php

namespace app\admin\controller;

use app\admin\controller\Base;
use think\Request;
use app\admin\model\Menu;
use app\admin\model\RoleMenu;
use think\Db;
use app\admin\validate\Menu as validate;

class Menus extends Base
{
	public function index(Request $request)
	{
		//启用与停用修改
		if($request->isPost())
		{
			$data = $request->post();
			if(Menu::where('menu_id',$data['menu_id'])->update($data))
			{
				$info = [
					'code'=>1
				];
				exit(json_encode($info));
			}
			else
			{
				$info = [
					'code'=>2
				];
				exit(json_encode($info));
			}
		}
		//查询数据
		$result = Menu::select();
		$this->assign('result',$result);
		return $this->fetch();
	}

	public function add(Request $request)
	{
		//添加数据
		if($request->isPost())
		{
			$data = $request->post();
			//验证信息
			$validate = new validate;
			if(!$validate->check($data))
			{
				exit(Message(2,$validate->getError()));
			}

			if(Menu::create($data))
			{
				$info = [
					'code'=>1,
					'message'=>'添加成功'
				];
				exit(json_encode($info));
			}
			else
			{
				$info = [
					'code'=>2,
					'message'=>'添加失败'
				];
				exit(json_encode($info));
			}
		}
		$result = Menu::where('menu_pid',0)->select();
		$this->assign('result',$result);
		return $this->fetch();
	}

	public function edit(Request $request)
	{
		//修改数据
		if($request->isPost())
		{
			$data = $request->post();
			
			//验证信息
			$validate = new validate;
			if(!$validate->check($data))
			{
				exit(Message(2,$validate->getError()));
			}				

			if(Menu::where('menu_id',$data['menu_id'])->update($data))
			{
				$info = [
					'code'=>1,
					'message'=>'修改成功'
				];
				exit(json_encode($info));
			}
			else
			{
				$info = [
					'code'=>2,
					'message'=>'修改失败'
				];
				exit(json_encode($info));
			}
		}
		$id = $request->param();
		$res = Menu::where('menu_id',$id['menu_id'])->find();
		$this->assign('res',$res);
		$result = Menu::where('menu_pid',0)->select();
		$this->assign('result',$result);
		return $this->fetch();
	}

	public function delete(Request $request)
	{
		//删除数据
		$id = $request->post();
		
		$res = Menu::where('menu_pid',$id['menu_id'])->find();
		if($res)
		{
			$info = [
				'code'=>2,
				'message'=>'菜单下有子菜单，不能删除'
			];
			exit(json_encode($info));
		}

		$where = "%\"".$id['menu_id']."%";
		$result = RoleMenu::where("menu_id","like",$where)->find();
		if($result)
		{
			$info = [
				'code'=>2,
				'message'=>'请先删除有对应权限的角色'
			];
			exit(json_encode($info));
		}
		else
		{
			if(Menu::destroy($id['menu_id']))
			{
				$info = [
					'code'=>1,
					'message'=>'删除成功'
				];
				exit(json_encode($info));
			}
			else
			{
				$info = [
					'code'=>2,
					'message'=>'删除失败'
				];
				exit(json_encode($info));
			}			
		}
	}

	//菜单搜索
    public function product_search()
    {
        $data = Request::instance();
        $id = input('search');
        $where = $id==''?"%%":"%$id%";                               
        $result = Menu::where("menu_id|menu_name","like",$where)->select();
        $this->assign('result',$result);
		return $this->fetch('index');    
    }

}