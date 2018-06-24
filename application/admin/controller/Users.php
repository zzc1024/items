<?php

namespace app\admin\controller;

use app\admin\controller\Base;
use app\admin\model\User;
use think\Request;

class Users extends Base
{
	//会员列表展示
	public function index()
	{
		$data = User::order('id desc')->paginate(10);
		$this->assign('user',$data);
		return $this->fetch();
	}

	//搜索用户
	public function search()
	{
		$data = Request::instance();
		$search = $data->post('search');
		$where = $search==''?"%%":"%$search%";
		$data = User::where('name|tel|email','like',$where)->order('id desc')->paginate(10,false,['query'=>$data->post()]);
		$this->assign('user',$data);
        return $this->fetch('index');  
	}

	//封号和解封
	public function edit_status(Request $request)
	{
		$data = $request->post();
		if(User::where('id',$data['id'])->update(['status'=>$data['status']]))
		{
			exit(Message(1));
		}
		else
		{
			exit(Message(2));
		}
	}

}