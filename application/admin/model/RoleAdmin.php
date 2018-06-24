<?php

namespace app\admin\model;

use think\Model;
use think\Db;

class RoleAdmin extends Model
{
	protected $field = true; //过滤字段
	
	public function getRoleIdAttr($val)
	{
		$result = Db::table('role')->select(); 	//查权限(菜单)表
		$menuname='';							//拥有权限的权限名
		foreach($result as $k => $v)
		{
			if($v['role_id']==$val)	//菜单id在角色拥有的权限里面的话，
			{
				return $v['role_name'];
			}
		}
	}
}