<?php

namespace app\admin\validate;

use think\Validate;

class Role extends Validate
{
	protected $rule = [
		['role_name',
		 'require',
		 '请输入角色名'
		]
	];
}