<?php

namespace app\admin\validate;

use think\Validate;

class Login extends Validate
{
	protected $rule = [
		['admin_account',
		 'require',
		 '请输入用户名'
		],
		['admin_password',
		 'require',
		 '请输入密码'
		]
	];
}