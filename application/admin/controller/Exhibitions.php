<?php

namespace app\admin\controller;

use app\admin\controller\Base;
use think\Request;
use think\Db;
use app\admin\validate\Exhibition as Vali;
use app\admin\model\Exhibition;
use app\admin\model\Product;

class Exhibitions extends Base
{
	//展示页面
	public function index()
	{
		$result = Exhibition::paginate(5);
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
		$result = Exhibition::get($id);
		$this->assign('result',$result);
		return $this->fetch();
	}

	//添加
	public function exhibition_add()
	{
		$data = request()->file('image');	//ajax提交上传图片，用file()接收
		$name = request()->post();
		// dump($data);dump($name);die;
		$exhibition = new Exhibition;

		//验证信息
		$validate = new Vali;
		if(!$validate->check($name))
		{
			exit(Message(2,$validate->getError()));
		}

		$id = Product::where('number',$name['number'])->field('id')->find();
		if(!$id)
		{
			exit(Message(2,'商品编号错误，没有此商品'));
		}
		$name['price']=Db::table('product_attributes')->where('product_id',$id['id'])->field('price')->find()['price'];

		if($data!=''){						//判断是否有上传图片
			$info = $data->validate(['ext'=>'jpg,png,jpeg'])->move('./static/img/exhibition/');
							//这是request类里面封装的验证图片格式，不是validate类里面的
							//move是移动，将上传的图片保存到服务器里
			if($info)
			{
				$data = '/static/img/exhibition/'.$info->getSaveName();//路径
				$name['path']=$data;
				if($exhibition->save($name)==1)
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
				echo $data->getError();					//格式不对话返回错误信息
			}
		}
		else
		{
			exit(Message(2,'请上传图片'));
		}
	}

	//删除
	public function exhibition_delete(Request $request)
	{
		$data = $request->post();
		if(Exhibition::destroy($data['id']))
		{
			exit(Message(1,'删除成功'));
		}
		else
		{
			exit(Message(2,'删除失败'));
		}
	}

	//修改
	public function exhibition_edit(Request $request)
	{
		$data = request()->file('image');	//ajax提交上传图片，用file()接收
		$name = request()->post();
		$exhibition = new Exhibition;
		//验证信息
		$validate = new Vali;
		if(!$validate->check($name))
		{
			exit(Message(2,$validate->getError()));
		}

		$id = Product::where('number',$name['number'])->field('id')->find();
		if(!$id)
		{
			exit(Message(2,'商品编号错误，没有此商品'));
		}
		$name['price']=Db::table('product_attributes')->where('product_id',$id['id'])->field('price')->find()['price'];

		if(isset($data)){						//判断是否有上传图片
			$info = $data->validate(['ext'=>'jpg,png,jpeg'])->move('./static/img/exhibition/');
							//这是request类里面封装的验证图片格式，不是validate类里面的
							//move是移动，将上传的图片保存到服务器里
			if($info)
			{
				$data = '/static/img/exhibition/'.$info->getSaveName();//路径
				$name['path']=$data;
				if($exhibition->update($name))
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
				echo $data->getError();					//格式不对话返回错误信息
			}
		}
		else
		{
			if($exhibition->update($name))
			{
				exit(Message(1,'修改成功'));
			}
			else
			{
				exit(Message(2,'修改失败'));
			}
		}
	}

	//展示和停用修改
	public function exhibition_statu(Request $request)
	{
		$data = $request->post();
		if($data['statu']==1)
		{
			$result = Db::table('exhibition')->where('id',$data['id'])->find();

			if($result['title']==1)
			{
				$count = count(Db::table('exhibition')->where('title',1)->where('statu',1)->select());
				if($count>=3)
				{
					exit(Message(2,'最新商品展示不能超过3个'));
				}
			}
			elseif($result['title']==2)
			{
				$count = count(Db::table('exhibition')->where('title',2)->where('statu',1)->select());
				if($count>=4)
				{
					exit(Message(2,'热门商品展示不能超过4个'));
				}
			}
			elseif($result['title']==3)
			{
				$count = count(Db::table('exhibition')->where('title',3)->where('statu',1)->select());
				if($count>=1)
				{
					exit(Message(2,'推荐商品展示不能超过1个'));
				}
			}
			elseif($result['title']==4)
			{
				$count = count(Db::table('exhibition')->where('title',4)->where('statu',1)->select());
				if($count>=6)
				{
					exit(Message(2,'爆款推荐展示不能超过6个'));
				}
			}
		}
		

		if(Db::table('exhibition')->update($data)==1)
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
}