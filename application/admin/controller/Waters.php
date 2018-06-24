<?php

namespace app\admin\controller;

use app\admin\controller\Base;
use think\Request;
use think\Db;
use think\Validate;
use app\admin\model\Water;

class Waters extends Base
{
	//展示页面
	public function index()
	{
		$water = new Water;
		$result = $water->select();
		$this->assign('result',$result);
		return $this->fetch();
	}

	//添加页面
	public function add()
	{
		return $this->fetch();
	}

	//修改页面
	public function edit(Request $request)
	{
		$id = $request->param('id');
		$result = Water::get($id);
		$this->assign('result',$result);
		return $this->fetch();
	}

	//添加水印图
	public function water_add()
	{
		$data = request()->file('image');	//ajax提交上传图片，用file()接收
		$name = request()->post('name');
		$water = new Water;

		if($name=='')
		{
			exit(Message(2,'水印名称不能为空'));
		}

		if($data!=''){						//判断是否有上传图片
			$info = $data->validate(['ext'=>'jpg,png,jpeg'])->move('./static/img/water/');
							//这是request类里面封装的验证图片格式，不是validate类里面的
							//move是移动，将上传的图片保存到服务器里
			if($info)
			{
				$data = '/static/img/water/'.$info->getSaveName();//路径
				if($water->save(['path'=>$data,'name'=>$name])==1)
				{
					exit(Message(1,'添加成功'));
				}
				else
				{
					exit(Message(2,'添加失败'));
				}
			}
			else
			{
				exit(Message(2,$data->getError()));			//格式不对话返回错误信息
			}
		}
		else
		{
			exit(Message(2,'请上传水印图'));
		}
	}

	//删除水印图
	public function water_delete(Request $request)
	{
		$data = $request->post();
		if(water::destroy($data['id']))
		{
			exit(Message(1,'删除成功'));
		}
		else
		{
			exit(Message(2,'删除失败'));
		}
	}

	//修改水印图
	public function water_edit(Request $request)
	{
		$data = $request->file('image');	//ajax提交上传图片，用file()接收
		$name =$request->post('name');
		$id = $request->post('id');
		// dump($data);dump($name);dump($id);die;
		$water = new Water;

		if($name=='')
		{
			exit(Message(2,'水印名称不能为空'));
		}

		if($data!=''){						//判断是否有上传图片
			$info = $data->validate(['ext'=>'jpg,png,jpeg'])->move('./static/img/water/');
							//这是request类里面封装的验证图片格式，不是validate类里面的
							//move是移动，将上传的图片保存到服务器里
			if($info)
			{
				$data = '/static/img/water/'.$info->getSaveName();//路径
				if($water->save(['path'=>$data,'name'=>$name],['id'=>$id])==1)
				{
					exit(Message(1,'修改成功'));
				}
				else
				{
					exit(Message(2,'修改失败'));
				}
			}
			else
			{
				exit(Message(2,$data->getError()));			//格式不对话返回错误信息
			}
		}
		else
		{
			if($water->save(['name'=>$name],['id'=>$id])==1)
			{
				exit(Message(1,'修改成功'));
			}
			else
			{
				exit(Message(2,'修改失败'));
			}
		}
	}
}