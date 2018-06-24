<?php

namespace app\admin\model;

use think\Model;

class Exhibition extends Model
{
	protected $field = true; //过滤字段
	protected $autoWriteTimestamp = true;//开启自动添加时间戳

	protected $insert = [
		'statu'
	];

	protected $auto = [
		'title'
	];

	public function setStatuAttr($val)
	{
		return 0;
	}

	public function getTitleAttr($val)
	{
		switch($val)
		{
			case 1:
				return '最新商品';
				break;
			case 2:
				return '热门商品';
				break;
			case 3:
				return '推荐商品';
				break;
			case 4:
				return '爆款推荐';
				break;
		}
	}

}