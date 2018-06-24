<?php

namespace app\admin\validate;

use think\Validate;

class Order extends Validate
{
	protected $rule = [
		['company',
		 'require|max:30',
		 '请输入快递公司名称|快递公司名称不能超过30个字符',	
		],

		['express',
		 'require|number',
		 '请输入物流单号|单号需填写数字',	
		]
	];
}