<?php

namespace app\admin\validate;

use think\Validate;				//引入Validate类

class Banner extends Validate 	//继承
{

	// protected $rule = [								//$rule参数是填写验证规则的
	// 	'name'		=>	'require|max:25',			//name是需要验证的字段，require是必填不能为空，max是最大长度
	// 	'age'   	=> 	'number|between:1,120',		//number是数字验证，between限制范围
	// 	'email'		=>	'email',					//email邮箱格式
	// ];

	// protected $msg = [								//$msg验证返回的信息
	// 	'name.require'	=>	'名称不能为空',			//.require，name没有填写的话，会返回名称不能为空
	// 	'name.max'		=>	'名称不能超过25个字符',
	// 	'age.number'	=>	'年龄需要填写数字',
	// 	'age.between'	=>	'年龄只能填写1-120之间',
	// 	'email'			=>	'请填写正确的邮箱格式'
	// ];

	// protected $scene = [							//$scene验证环境
	// 	'file'		=>	['name','age'],				//在edit环境下只验证name和age字段
	// ];

}