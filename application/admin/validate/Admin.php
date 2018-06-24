<?php

namespace app\admin\validate;

use think\Validate;

class Admin extends Validate
{
	protected $rule = [
		['admin_name',
		'require|/[\x{4e00}-\x{9fa5}]+/u|max:20',
		'请输入昵称|昵称需要填写中文|用户名不能超过20个字符'],

		['admin_account',
		'require|max:18|min:8|/^[a-zA-Z]{1}([a-zA-Z0-9._]){3,17}$/',
		'请输入账号|账号不能大于18位数|账号不能小于8位数|账号格式不对,需要首字母,由字母和数字组成'],

		['admin_password',
		'require|max:18|min:8|confirm:admin_password2',
		'请输入密码|密码不能大于18位数|密码不能小于8位数|确认密码不一致'],

		['admin_phone',
		['require','/^((13[0-9])|(14[5|7])|(15([0-3]|[5-9]))|(18[0,5-9]))\\d{8}$/'],
		'请输入手机号|手机号格式不对'],

		['admin_email',
		['require','/^[a-zA-Z0-9_.-]+@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*\.[a-zA-Z0-9]{2,6}$/'],
		'请输入邮箱号|邮箱格式不对'],
	];

	protected $scene = [
		
	];
}