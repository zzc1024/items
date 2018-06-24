<?php

namespace app\admin\controller;

use app\admin\controller\Base;
use think\Db;
use think\Request;
use app\admin\model\Admin;
use app\admin\model\RoleAdmin;
use app\admin\validate\Admin as validate;


class Admins extends Base
{
	public function index(Request $request)
	{
		//启用与停用修改
		if($request->isPost())
		{
			$data = $request->post();
			if(Admin::where('admin_id',$data['admin_id'])->update($data))
			{
				$info = [
					'code'=>1
				];
				exit(json_encode($info));
			}
			else
			{
				$info = [
					'code'=>2
				];
				exit(json_encode($info));
			}
		}
		$res = Admin::select();  			//查询管理员表
		foreach($res as $k => $v)
		{
			$v['roleadmin']=RoleAdmin::get($v['admin_id'])['role_id'];
		}
		$this->assign('result',$res);       //view层的变量赋值
		return $this->fetch();
	}

	public function add(Request $request)		
	{
		//添加数据
		if($request->isPost()){				//判断post是否为空		
			$data = $request->post();		// 获取到post数据

			//验证信息
			$validate = new validate;
			if(!$validate->check($data))
			{
				exit(Message(2,$validate->getError()));
			}
			
			$data['admin_password']= md5(crypt($data['admin_password'],'test'));

			$res = Admin::where('admin_account',$data['admin_account'])->find();
			if($res)
			{
				exit(Message(2,'该账户名已存在'));
			}
			else{
				$res2 = Admin::create($data,true);
				if($res2)   //insert() 添加数据到数据库
				{   
					RoleAdmin::create($res2->toArray(),true);
					exit(Message(1,'添加成功'));
				}
				else
				{
					exit(Message(2,'添加失败'));
				}
			}
		}

		$res = Db::table('role')->select();
		$this->assign('res',$res);
		return $this->fetch();
	}

	public function edit(Request $request)
	{
		//修改数据
		if($request->isPost())
		{
			$data = $request->post();
			$id=$data['admin_id'];
			unset($data['admin_id']);

			//验证信息
			$validate = new validate;
			if(!$validate->check($data))
			{
				exit(Message(2,$validate->getError()));
			}
			
			$data['admin_password'] = md5(crypt($data['admin_password'],'test'));
			unset($data['admin_password2']);
			$admin = new Admin;
			$roleadmin = new RoleAdmin;
			if($admin->save($data,['admin_id'=>$id]))
			{
				$roleadmin->save($data,['admin_id'=>$id]);
				exit(Message(1,'修改成功'));
			}
			else
			{
				exit(Message(2,'修改失败'));
			}
		}
		$id = $request -> param();
		$result = Admin::where('admin_id',$id['admin_id'])->find();
		$result2 = Db::table('role_admin')->where('admin_id',$result['admin_id'])->find();
		$this->assign('result',$result);
		$this->assign('result2',$result2);
		$res = Db::table('role')->select();
		$this->assign('res',$res);
		return $this->fetch();
	}

	public function delete(Request $request)
	{
		//删除数据
		$id = $request->post();
		
		if(Admin::destroy($id['admin_id']) and $dump = Db::table('role_admin')->where('admin_id',$id['admin_id'])->delete())
		{
			exit(Message(1,'删除成功'));
		}
		else
		{
			exit(Message(2,'删除失败'));
		}
	}

	//批量删除
    public function admin_deletes(Request $request)
    {
    	if(isset($request->post()['checkID'])){
	        $data = $request->post()['checkID'];
	        if(Admin::destroy($data))
	        {
	            exit(Message(1,'删除成功'));
	        }
	        else
	        {
	            exit(Message(2,'删除失败'));
	        }
	    }
	    else
	    {
	    	exit(Message(2,'请勾选需要删除的数据'));
	    }
    }

    //搜用户
    public function product_search()
    {
        $data = Request::instance();    //这里要用Request静态调用，不然后面paginate传的参数会无效
        $id = input('search');
        $where = $id==''?"%%":"%$id%";                               
        $result = Admin::where("admin_name|admin_phone|admin_email","like",$where)->select();
        foreach($result as $k => $v)
		{
			$v['roleadmin']=RoleAdmin::get($v['admin_id'])['role_id'];
		}
        $this->assign('result',$result);
        return $this->fetch('index');
    }
}