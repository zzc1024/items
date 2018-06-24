<?php

namespace app\admin\controller;

use app\admin\controller\Base;
use app\admin\model\Series;
use think\Request;

class Seriess extends Base
{
	public function index()
	{
		$result = Series::select();
		$this->assign('result',$result);
		return $this->fetch();
	}

	public function edit(Request $request)
	{
		if($request->isPost())
		{
			$data=$request->post();
			if($data['name']=='')
			{
				exit(Message(2,'系列名称不能为空'));
			}
			elseif(Series::update($data))
			{
				exit(Message(1,'修改成功'));
			}
			else
			{
				exit(Message(2,'修改失败'));
			}
		}
		$id = $request->param();
		$result = Series::get($id['id']);
		$this->assign('result',$result);
		return $this->fetch();
	}
}