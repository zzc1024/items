<?php

namespace app\admin\model;

use think\Model;

class ProductAttributes extends Model
{
	protected $auto = [
		'classify'
	];

	public function getClassifyAttr($val)
	{
		return json_decode($val);
	}
}