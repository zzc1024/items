<?php

namespace app\admin\validate;

use think\Validate;

class Exhibition extends Validate
{
	protected $rule = [
		'name'		=>	'require|max:20',
		'number'	=>	'require|number|min:6|max:6',
		'abstract'	=>	'require|max:100',
		'content'	=>	'require|max:500'
	];

	protected $message = [
		'name.require'		=> 	'名称不能为空',
		'name.max'			=> 	'名称不能超过20个字符',
		'number.require'	=> 	'商品编号不能为空',
		'number.number'		=>	'商品编号需要填写数字',
		'number.min'		=>	'商品编号是6位数',
		'number.max'		=>	'商品编号是6位数',
		'abstract.require'	=>	'摘要不能为空',
		'abstract.max'		=>	'摘要不能超过100个字符',
		'content.require'	=>	'内容不能为空',
		'content.max'		=>	'内容不能超过500个字符',
	];

	protected $scene = [
		'add'	=>	['name'],
	];
}