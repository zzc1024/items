<?php

namespace app\admin\model;

use think\Model;

class Product extends Model
{
	protected $field = true; //过滤字段

	protected $autoWriteTimestamp = true;//开启自动添加时间戳

	protected $auto = [
		'classify',
		'attribute',
		'imgpath'
	];

	public function getClassifyAttr($val)
	{
		return json_decode($val);
	}

	public function getAttributeAttr($val)
	{
		return json_decode($val);
	}

	public function getImgpathAttr($val)
	{
		return json_decode($val);
	}
}