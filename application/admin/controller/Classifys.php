<?php

namespace app\admin\controller;

use app\admin\controller\Base;
use think\Request;
use app\admin\model\Classify;
use app\admin\model\Product;

class Classifys extends Base
{
	public function index()
	{
		//查出全部分类名
		$data = Classify::select();
		$this->assign('data',$data);
		return $this->fetch();
	}

	public function add(Request $request)
	{
		//添加数据
		if($request->isPost())
		{
			$data = $request->post();
			if($data['name']=='')
			{
				exit(Message(2,'分类名称不能为空'));
			}
			//顶级分类添加
			if($data['classify_pid']==0)
			{	
				$path = Classify::create($data);
				$data2 = [];
				$data2['classify_path'] = '0,'.$path['classify_id'];
				$data2['classify_level'] = 1;
				//添加判断
				if(classify::where('classify_id',$path['classify_id'])->update($data2))
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
				$path2 = Classify::get($data['classify_pid']);
				$path = Classify::create($data);
				$data2 = [];
				$data2['classify_path'] = $path2['classify_path'].','.$path['classify_id'];
				$data2['classify_level'] = 2;
				//添加判断
				if(classify::where('classify_id',$path['classify_id'])->update($data2))
				{
					exit(Message(1,'添加成功'));
				}
				else
				{
					exit(Message(2,'添加失败'));
				}
			}

			
		}
		//查出顶级分类
		$data = Classify::where('classify_pid',0)->select();
		$this->assign('data',$data);
		return $this->fetch();
	}

	public function edit()
	{
		//查出顶级分类
		$data = Classify::where('classify_pid',0)->select();
		$this->assign('data',$data);
		return $this->fetch();
	}
		
	public function classify_ajax()
	{
		//获取分类地址
		$data = Classify::field('classify_id,classify_pid,name')->select();

		echo json_encode($data);
	}


	public function classify_delete(Request $request)
	{
		//删除分类
		$id = $request->get('id'); 
		if(Classify::where('classify_pid',$id)->find())
		{
			exit(Message(2,'分类下有子分类，不允许删除'));
		}
		else
		{
			if(Product::where('classify','like','%"'.$id.'"%')->find())
			{
				exit(Message(2,'请先删除对应分类的商品'));
			}
			if(Classify::destroy($id))
			{
				exit(Message(1,'删除成功'));
			}
			else
			{
				exit(Message(2,'删除失败'));
			}
		}
	}
}