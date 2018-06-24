<?php

namespace app\admin\controller;

use app\admin\controller\Base;
use think\Request;
use app\admin\model\Attribute;
use think\Db;

class Attributes extends Base
{
	public function index()
	{
		//展示商品属性
		$result = Attribute::select();
		$this->assign('result',$result);
		return $this->fetch();
	}

	public function add(Request $request)
	{
		//添加商品属性
		if($request->isPost())
		{
			$data = $request->post();
			if($data['name']=='')
			{
				exit(Message(2,'属性名称不能为空'));
			}
			if(Attribute::create($data))
			{
				exit(Message(1,'添加成功'));
			}
			else
			{
				exit(Message(2,'添加失败'));
			}
		}
		return $this->fetch();
	}

	public function edit(Request $request)
	{
		//修改商品属性
		if($request->isPost())
		{
			$data=$request->post();
			if($data['name']=='')
			{
				exit(Message(2,'属性名称不能为空'));
			}
			elseif(Attribute::update($data))
			{
				exit(Message(1,'修改成功'));
			}
			else
			{
				exit(Message(2,'修改失败'));
			}
		}
		$id = $request->param();
		$result = Attribute::get($id['id']);
		$this->assign('result',$result);
		return $this->fetch();
	}

	public function delete(Request $request)
	{
		//删除商品属性
		$data = $request->post();

		$where = "%\"".$data['id']."\"%";
		$result = Db::table('product')->where("attribute","like",$where)->find();
		if($result)
		{
			exit(Message(2,'请先删除有对应属性的商品'));
		}
		elseif(Attribute::where($data)->delete())
		{
			exit(Message(1,'删除成功'));
		}
		else
		{
			exit(Message(2,'删除失败'));
		}

	}
}