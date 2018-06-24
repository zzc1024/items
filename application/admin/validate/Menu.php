<?php

namespace app\admin\validate;

use think\Validate;

class Menu extends Validate
{
	protected $rule = [
		['menu_name',
		 'require',
		 '请输入菜单名',	
		],

		['controller',
		 'require',
		 '请输入控制器文件名',	
		],

		['action',
		 'require',
		 '请输入视图文件名',	
		],
	];
}