<?php

namespace app\admin\controller;

use app\admin\controller\Base;
use think\Request;
use think\Db;

class Comment extends Base
{
	//评论展示页面
	public function index()
	{
		$comment = Db::table('product_comment')->alias('p')->where('p.status','neq',1)->join('user u','u.id=p.user_id')->field('p.id,p.product_id,p.content,p.score,p.create_time,u.name')->order('id desc')->paginate(10);
		$this->assign('comment',$comment);
		return $this->fetch();
	}

	//删除评价
	public function dele(Request $request)
	{
		$id = $request->post('id');
		if(Db::table('product_comment')->where('id',$id)->update(['status'=>1]))
		{
			exit(Message(1,'删除成功'));
		}
		else
		{
			exit(Message(2,'删除失败'));
		}
	}
}