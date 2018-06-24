<?php

namespace app\index\controller;

use app\index\controller\Base;
use think\Request;
use think\Db;
use app\index\model\Classify;
use app\index\model\Series;
use app\index\model\User;
use app\index\validate\Login as Validate;
use app\index\miaodi\miaodi;
use think\Session;

class Login extends Base
{
	//登录页面
	public function index()
	{

    	//商品分类
    	$classify = Classify::where('classify_pid',0)->select();
    	foreach($classify as $k => $v)
    	{
    		$v['next'] = Classify::where('classify_pid',$v->toArray()['classify_id'])->select();
    	}
    	$this->assign('classify',$classify);
    	//商品系列
    	$series = Series::select();
    	$this->assign('series',$series);
       	return $this->fetch();
	}

	//注册页面
	public function register()
	{
    	//商品分类
    	$classify = Classify::where('classify_pid',0)->select();
    	foreach($classify as $k => $v)
    	{
    		$v['next'] = Classify::where('classify_pid',$v->toArray()['classify_id'])->select();
    	}
    	$this->assign('classify',$classify);
    	//商品系列
    	$series = Series::select();
    	$this->assign('series',$series);
       	return $this->fetch();
	}

	//注册会员
	public function register_add(Request $request)
	{
		$data = $request->post();
		//验证信息
		$validate = new Validate;
		if(!$validate->check($data))
		{
			exit(Message(2,$validate->getError()));
		}
		elseif($data['code']==''||$data['code']!=Session::get('vcode'))
		{
			exit(Message(2,'验证码错误，请重新输入'));
		}
		elseif($data['password']!=$data['password2'])
		{
			exit(Message(2,'确认密码不正确'));
		}

		$user = new User;
		if($user->where('account',$data['account'])->find())
		{
			exit(Message(2,'该账号已注册'));
		}
		elseif($user->where('email',$data['email'])->find())
		{
			exit(Message(2,'该邮箱已注册'));
		}
		elseif($user->where('tel',$data['tel'])->find())
		{
			exit(Message(2,'该手机已注册'));
		}

		$data['password']=md5(crypt($data['password'],'test'));
		$data['paypassword']=md5($data['paypassword']);
		$data['balance']=0;
		$data['status']=1;

		//验证成功后添加到数据库
		if($user->save($data)==1)
		{
			exit(Message(1,'注册成功','/index/Login/index'));
		}
		else
		{
			exit(Message(2,'注册失败，请稍后重试'));
		}

	}

	//手机验证码
	public function get_code(Request $request)
	{
		$data = $request->post();
		$validate = new Validate;
		if(!$validate->scene('getcode')->check($data))
		{
			exit(Message(2,$validate->getError()));
		}

		$user = new User;
		if($user->where('tel',$data['tel'])->find())
		{
			exit(Message(2,'该手机号已注册'));
		}

		$stry="1234567890";
		$stry=substr(str_shuffle($stry),2,6);

		$str = "【微众网络科技】您的验证码为".$stry."，请于5分钟内正确输入，如非本人操作，请忽略此短信。";  
		$md = new Miaodi();
		$arr = $md->sendSMS($data['tel'],$str);
		if($arr['respCode']==00000)
		{

			Session::set('vcode',$stry);
			
			exit(Message(1,'发送成功'));
		}
		else
		{
			exit(Message(2,'发送失败，请稍后再重试'));
		}
	}

	//登录验证
	public function login(Request $request)
	{
		if(Session::has('user_id'))
		{
			exit(Message(2,'您已登录'));
		}
		$data = $request->post();
		$validate = new Validate;
		if(!$validate->scene('login')->check($data))
		{
			exit(Message(2,$validate->getError()));
		}
		elseif(!captcha_check($data['code']))
		{
			exit(Message(2,'验证码错误'));
		}
		$user = new User;
		$password = $user->where('account',$data['account'])->field('id,password,status')->find();
		$data['password'] = md5(crypt($data['password'],'test'));
		if(!isset($password))
		{
			exit(Message(2,'账号不存在'));
		}
		if($data['password']==$password['password'])
		{
			if($password['status']==0)
			{
				exit(Message(2,'该账号已被封号'));
			}
			Session::set('user_id',$password['id']);
			exit(Message(1,'登录成功','/index/Index/index'));
		}
		else
		{
			exit(Message(2,'密码错误'));
		}
	}

	//找回密码页面
	public function retrieve()
	{
    	//商品分类
    	$classify = Classify::where('classify_pid',0)->select();
    	foreach($classify as $k => $v)
    	{
    		$v['next'] = Classify::where('classify_pid',$v->toArray()['classify_id'])->select();
    	}
    	$this->assign('classify',$classify);
    	//商品系列
    	$series = Series::select();
    	$this->assign('series',$series);
       	return $this->fetch();
	}

	//邮箱验证码
	public function email_code(Request $request)
	{
		$data = $request->post();
		$validate = new Validate;
		if(!$validate->scene('email')->check($data))
		{
			exit(Message(2,$validate->getError()));
		}

		$user = new User;
		if(!$user->where('email',$data['email'])->find())
		{
			exit(Message(2,'该邮箱未注册'));
		}

		$stry="1234567890";
		$stry=substr(str_shuffle($stry),2,6);
		$to = array($data['email']);
    	$title = '曼斯威尔商城找回密码邮件';
    	$content = '您的验证码是'.$stry;
        
        $da = sendMail($to,$title,$content);
        if($da)
        {
        	exit(Message(2,'发送失败，请稍后再重试'));
        }
        else
        {
			Session::set('emailcode',$stry);

			exit(Message(1,'发送成功'));
        }
	}

	//重置密码
	public function retrieve_edit(Request $request)
	{
		$data = $request->post();
		$validate = new Validate;
		if(!$validate->scene('edit')->check($data))
		{
			exit(Message(2,$validate->getError()));
		}
		elseif($data['code']!=Session::get('emailcode'))
		{
			exit(Message(2,'验证码错误，请重新输入'));
		}
		elseif($data['password']!=$data['password2'])
		{
			exit(Message(2,'确认密码不正确'));
		}
		$data['password'] = md5(crypt($data['password'],'test'));
		
		$add = Db::table('user')->where('email',$data['email'])->update(['password'=>$data['password']]);
		if($add==1)
		{
			Session::delete('user_id');
			exit(Message(1,'重置密码成功','/index/Login/index'));
		}
		else
		{
			exit(Message(2,'重置失败，请稍后重试'));
		}
	}

	//退出登录
	public function logout()
	{
		Session::delete('user_id');
		//商品分类
    	$classify = Classify::where('classify_pid',0)->select();
    	foreach($classify as $k => $v)
    	{
    		$v['next'] = Classify::where('classify_pid',$v->toArray()['classify_id'])->select();
    	}
    	$this->assign('classify',$classify);
    	//商品系列
    	$series = Series::select();
    	$this->assign('series',$series);
		return $this->redirect('Login/index');
	}


}