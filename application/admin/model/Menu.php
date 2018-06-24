<?php

namespace app\admin\model;

use think\Model;

class Menu extends Model
{
	protected $field = true; //过滤字段
	
	protected $insert = [			//添加时，状态默认开启
		'statu'
	];

	public function setStatuAttr($val)
	{
		return 1;
	}
}