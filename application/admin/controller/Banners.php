<?php

namespace app\admin\controller;
use app\admin\controller\Base;
use think\Request;
use think\Db;
use think\Validate;
use app\admin\model\Banner;

class Banners extends Base
{
	//展示页面
	public function index()
	{
		$banner = new Banner;
		$result = $banner->select();
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
		$result = Banner::get($id);
		$this->assign('result',$result);
		return $this->fetch();
	}

	//添加banner轮播图
	public function banner_add()
	{
		$data = request()->file('image');	//ajax提交上传图片，用file()接收
		$banner = new Banner;

		if($data!=''){						//判断是否有上传图片
			$info = $data->validate(['ext'=>'jpg,png,jpeg'])->move('./static/img/banner/');
							//这是request类里面封装的验证图片格式，不是validate类里面的
							//move是移动，将上传的图片保存到服务器里
			if($info)
			{
				$data = '/static/img/banner/'.$info->getSaveName();//路径
				if($banner->save(['path'=>$data])==1)
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
				exit(Message(2,$data->getError()));				//格式不对话返回错误信息
			}
		}
		else
		{
			exit(Message(2,'请上传图片'));
		}
	}

	//展示和停用修改
	public function banner_statu(Request $request)
	{
		$data = $request->post();
		$banner = new Banner;
		$statuNum = count($banner->where('statu',1)->select());

		if($data['statu']==1 && $statuNum>=3)
		{
			exit(Message(2,'展示图片不能超过3张'));
		}
		elseif($banner->update($data))
		{
			$info = [
				'code'=>1
			];
			exit(json_encode($info));
		}
		else
		{
			exit(Message(2,'错误'));
		}
	}

	//删除banner
	public function banner_delete(Request $request)
	{
		$data = $request->post();
		if(Banner::destroy($data['id']))
		{
			exit(Message(1,'删除成功'));
		}
		else
		{
			exit(Message(2,'删除失败'));
		}
	}

	//修改banner轮播图
	public function banner_edit(Request $request)
	{
		$data = $request->file('image');	//ajax提交上传图片，用file()接收
		$id = $request->post('id');
		$banner = new Banner;

		if($data!=''){						//判断是否有上传图片
			$info = $data->validate(['ext'=>'jpg,png,jpeg'])->move('./static/img/banner/');
							//这是request类里面封装的验证图片格式，不是validate类里面的
							//move是移动，将上传的图片保存到服务器里
			if($info)
			{
				$data = '/static/img/banner/'.$info->getSaveName();//路径
				if($banner->save(['path'=>$data],['id'=>$id])==1)
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
			exit(Message(1,'修改成功'));
		}
	}
}