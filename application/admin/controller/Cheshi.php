<?php

namespace app\admin\controller;

use app\admin\controller\Base;
use \org\Upload;
use PHPMailer\SendEmail;

class Cheshi extends Base
{
	public function index()
	{
		return $this->fetch();
	}

	public function add()
	{
		$upload = new \org\Upload();//实例化上传类
		$upload->maxSize = 3145728;//设置附件上传大小
		$upload->exts = array('jpg','gif','png','jpeg');//设置附件上传类型
		$upload->rootPath = './static/img/';//设置附件上传目录
		$info = $upload->upload();
		if(!$info)	//上传错误提示错误信息
		{
			$this->error($upload->getError());
		}
		else 	//上传成功
		{
			echo json_encode($upload->rootPath.'public/'.$info['file_data']['savename']);//地址
		}
	}

	public function cheshi()
    {
    	$to = array('1095395035@qq.com');
    	$title = '这是一个测试邮件';
    	$content = '这是一个测试邮件';
        sendMail($to,$title,$content);
    }

    public function address()
    {
    	dump(url('Proess/sp_base'));
    }

    public function zfbpay()
	{
		$params = [
			'subject'=>'咖啡',
			'out_trade_no'=>'2018612162531788',
			'total_amount'=>'99.99',
		];
		\alipay\Pagepay::pay($params);
	}

}

