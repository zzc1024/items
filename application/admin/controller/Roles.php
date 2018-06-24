<?php

namespace app\admin\controller;

use app\admin\controller\Base;
use think\Db;
use think\Request;
use app\admin\model\Role;
use app\admin\model\RoleMenu;
use app\admin\validate\Role as validate;

class Roles extends Base
{
	public function index()
	{
		//查找角色表
		$result = Role::select();

		$menu = Db::table('menu')->select();
		
		foreach($result as $k => $v)
		{
			$v['menu_id'] = '';
			$menu_id = json_decode(RoleMenu::get($v['role_id'])['menu_id']);
			foreach($menu as $k1 => $v1)
			{
				if(in_array($v1['menu_id'],$menu_id))
				{
					$v['menu_id'].='| '.$v1['menu_name'].' ';
				}
			}
			$v['menu_id'].='|';
		}

		$this->assign('result',$result);
		return $this->fetch();
	}

	public function add(Request $request)
	{
		if($request->isPost())
		{
			//添加数据
			$data = $request->post();

			//验证数据
			$validate = new validate;
			if(!$validate->check($data))
			{
				exit(Message(2,$validate->getError()));
			}
			if(!array_key_exists("menu_id",$data))
			{
				exit(Message(2,'至少选一个权限'));
			}
			$roleadd = Role::create($data,true);
			//获取到post的数据有menu_id,角色表不存在这个字段，开启true过滤掉
			
			// dump($roleadd->toArray());die;
			//$roleadd返回数据有role_id自增id,但是个对象，需要toArray()转换
			$roleadd['menu_id']=json_encode($roleadd['menu_id']);
			$menuadd = RoleMenu::create($roleadd->toArray(),true);
			//添加role_id角色id,menu_id权限id组成的数组
			if($roleadd&&$menuadd)
			{     
				exit(Message(1,'添加成功'));
			}
			else
			{
				exit(Message(2,'添加失败'));
			}
		}

		//遍历菜单表(权限)数据
		$result = Db::table('menu')->where('menu_pid',0)->select();
		foreach($result as $k => $v){
			if(is_array($v)){
				$sql = Db::table('menu')->where('menu_pid',$v['menu_id'])->where('statu',1)->select();
				$result[$k]['next'] = $sql;
			}
		}
		$this->assign('result',$result);
		return $this -> fetch();
	}

	public function edit(Request $request)
	{
		if($request->isPost())
		{
			//更新数据
			$data = $request->post();
			$id=$data['role_id'];
			unset($data['role_id']);

			//验证数据
			$validate = new validate;
			if(!$validate->check($data))
			{
				exit(Message(2,$validate->getError()));
			}
			if(!array_key_exists("menu_id",$data))
			{
				exit(Message(2,'至少选一个权限'));
			}
			
			$data['menu_id']=json_encode($data['menu_id']);

			$role = new Role;
			$roleadd = $role->save($data,['role_id'=>$id]);
			
			$rolemenu = new RoleMenu;
			$menuadd = $rolemenu->save($data,['role_id'=>$id]);
			
			if($roleadd or $menuadd)
			{     
				exit(Message(1,'修改成功'));
			}
			else
			{
				exit(Message(2,'修改失败'));
			}
		}


		//遍历菜单表(权限)数据
		$result = Db::table('menu')->where('menu_pid',0)->select();
		foreach($result as $k => $v){
			if(is_array($v)){
				$sql = Db::table('menu')->where('menu_pid',$v['menu_id'])->where('statu',1)->select();
				$result[$k]['next'] = $sql;
			}
		}
		$this->assign('result',$result);
		//查询当前id的数据
		$id = $request->param();
		$res = Role::where('role_id',$id['role_id'])->find();
		$res2 = Db::table('role_menu')->where('role_id',$id['role_id'])->find();

		$res2['menu_id'] = json_decode($res2['menu_id']);

		$this->assign('res',$res);
		$this->assign('res2',$res2);
		return $this->fetch();
	}

	public function delete(Request $request)
	{
		//删除角色
		$id = $request->post();

		$res = Db::table('role_admin')->where('role_id',$id['role_id'])->find();
		$resadmin = Db::table('admin')->where('admin_id',$res['admin_id'])->find();
		if(isset($resadmin))
		{
			exit(Message(2,'请先删除对应角色的管理员'));
		}

		elseif(Role::destroy($id['role_id']) and Db::table('role_menu')->where('role_id',$id['role_id'])->delete())
		{
			exit(Message(1,'删除成功'));
		}
		else
		{
			exit(Message(2,'删除失败'));
		}
	}
}