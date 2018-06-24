<?php

namespace app\admin\controller;
use think\Controller;
use think\Request;
use think\Db;
use app\admin\model\Admin;
use app\admin\validate\Login as validate;
use think\Session;

class Login extends Controller
{
	public function index(Request $request)
	{
		if($request->isPost())
		{
			$data = $request->post();    

			//验证信息
			$validate = new validate;
			if(!$validate->check($data))
			{
				exit(Message(2,$validate->getError()));
			}
			if(captcha_check($data['vcode'])) //验证码
			{
				$account = Admin::where('admin_account',$data['admin_account'])->find();	//验证账号
				if(!$account)
				{
					exit(Message(2,'账号错误'));
				}
				else
				{		
					$password = Admin::get($account->toArray()['admin_id']);
					$data['admin_password']=md5(crypt($data['admin_password'],'test'));
					if($password->toArray()['admin_password']!=$data['admin_password'])
					{
						exit(Message(2,'密码错误'));
					}
					elseif($password->toArray()['statu']!=1)
					{
						exit(Message(2,'该管理员已被禁用'));
					}
					else
					{
						Session::set('admin_id',$account['admin_id']);
						exit(Message(1,'登入成功','/admin/Index/index'));
					}
				}
			}
			else
			{
				exit(Message(2,'验证码错误'));
			}
		}
		return view();
	}

	//退出登录
	public function logout()
	{
		Session::delete('admin_id');
		return $this->fetch('login/index');
	}

}