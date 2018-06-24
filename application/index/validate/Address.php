<?php

namespace app\index\validate;

use think\Validate;

class Address extends Validate
{
	protected $rule = [
		['name',
		 'require|/[\x{4e00}-\x{9fa5}]+/u|max:20',
		 '请输入收货人姓名|收货人姓名需要填写中文|收货人姓名不能超过20个字符'
		],
		['region',
		 'require|/[\x{4e00}-\x{9fa5}]+/u|max:80',
		 '请输入地区|地区需要填写中文|地区字符超出'
		],
		['address',
		 'require|/[\x{4e00}-\x{9fa5}]+/u|max:200',
		 '请输入详细地址|详细地址需要填写中文|详细地址字符超出'
		],
		['phone',
		 ['require','/^((13[0-9])|(14[5|7])|(15([0-3]|[5-9]))|(18[0,5-9]))\\d{8}$/'],
		 '请输入手机号|手机号格式不对'
		],
	];
}