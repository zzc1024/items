<?php

namespace app\index\validate;

use think\Validate;

class Login extends Validate
{
	protected $rule = [
		['name',
		 'require|max:20',
		 '请输入用户名|用户名不能超过20个字符'
		],
		['account',
		 'require|max:20|min:8|/^[a-zA-Z]{1}([a-zA-Z0-9._]){3,17}$/',
		 '请输入账号|账号不能超过20个字符|账号不能少于8个字符|格式不对,需要首字母,由字母和数字组成'
		],
		['email',
		 'require|email',
		 '请输入邮箱|邮箱格式不对'
		],
		['tel',
		 'require|/^1[3-8]{1}[0-9]{9}$/',
		 '请输入手机号|手机号格式不对'
		],
		['paypassword', 
		 'require|number|min:6|max:6',
		 '请输入支付密码|支付密码必须是数字|支付密码必须是6位数|支付密码必须是6位数'
		],
		['password', 
		 'require|min:6|max:18',
		 '请输入密码|密码不能小于6个字符|密码不能超过18个字符'
		]
	];

	protected $scene = [
		'login'	=> ['account','password'],
		'getcode' =>['tel'],
		'email'	=>['email'],
		'edit'	=>['email','password']
	];

}