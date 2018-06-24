<?php

namespace app\admin\model;

use think\Model;

class Admin extends Model
{
	protected $field = true; //过滤字段
	
	protected $autoWriteTimestamp = true;		//开启自动添加时间戳的功能

	protected $insert = [						//添加时，状态默认开启
		'statu'
	];

	public function setStatuAttr($val)
	{
		return 1;
	}
}