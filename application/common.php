<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

use PHPMailer\PHPMailer\PHPMailer;


/**
  * 处理返回ajax接收的json数据
  * @param  code  	  int    前台判断 1成功 2失败
  * @param  message   string 前台弹窗提示
  * @param  url 	  string 跳转页面
  * @return info  	  json   前台ajax识别的json数据
  */
function Message($code,$message='',$url='')
{
	$info = [
		'code'=>$code,
		'message'=>$message,
		'url'=>$url,
	];

	return json_encode($info);
}


/**
  * 添加商品数据验证
  * @param  data  	  post提交的添加商品信息
  * @return info  	  报错信息
  */
function prodcuts($data)
{
	if($data['name']=='')
	{
		exit(Message(2,'商品名称不能为空'));
	}
	elseif(strlen($data['name']) > 20)
	{
		exit(Message(2,'商品名称不能超过20个字符'));
	}
	elseif($data['address']=='')
	{
		exit(Message(2,'商品产地不能为空'));
	}
	elseif(strlen($data['address']) > 20)
	{
		exit(Message(2,'商品产地不能超过20个字符'));
	}
	elseif($data['weight']=='')
	{
		exit(Message(2,'商品重量不能为空'));
	}
	elseif(!preg_match("/^[\d\.]*$/",$data['weight']))
	{
		exit(Message(2,'商品重量需要填写数字'));
	}
	elseif(strlen($data['weight']) > 10)
	{
		exit(Message(2,'商品重量数据错误，请填写正确重量'));
	}
	elseif(!isset($data['attribute']))
	{
		exit(Message(2,'至少选一种商品属性'));
	}
	$count = count($data['attribute']);
	for($i=0;$i<$count;$i++)
	{
		if($data['original_price'][$i]=='')
		{
			exit(Message(2,'请完善对应商品属性的原价'));
		}
		elseif(!preg_match("/^[\d\.]*$/",$data['original_price'][$i]))
		{
			exit(Message(2,'价格需要填写数字'));
		}
		elseif(strlen($data['original_price'][$i]) > 10)
		{
			exit(Message(2,'原价数据错误，请填写正确价格'));
		}
		if($data['price'][$i]=='')
		{
			exit(Message(2,'请完善对应商品属性的售价'));
		}
		elseif(!preg_match("/^[\d\.]*$/",$data['price'][$i]))
		{
			exit(Message(2,'价格需要填写数字'));
		}
		elseif(strlen($data['price'][$i]) > 10)
		{
			exit(Message(2,'售价数据错误，请填写正确价格'));
		}
	}
	if($data['imgpath'][0]=='')
	{
		exit(Message(2,'请上传商品展示图片'));
	}
	elseif(!isset($data['content']))
	{
		exit(Message(2,'商品内容不能为空'));
	}
}


/**
  * 制作水印图片
  * @param  tpye  int    水印方式：0：文字水印 1：图片水印
  * @param  path string 原图路径
  * @param  water 	  string 水印图片
  * @param  location  int    水印位置
  * @return thumbName string 成功返回文件路径
  */
function WaterImg($tpye,$path,$water='',$location=8)
{
	// 水印位置
	if($location == '') $location = 8;
	// 打开图片
	$image = \org\Image::open($path);
	// 判断水印方式
	if( $tpye == 0 ){
		// 文字水印
		$image->text('麦斯威尔','ziti.otf',100,'#ffffff',$location)->save($path);
	}else{
		// 图片水印
		$image->water($water,$location)->save($path);
	}

}	



/**
 * 发送邮件方法
 * @param $to ：接收者数组 $title：标题 $content：邮件内容
 */
function sendMail(array $to,$title,$content){
    //配置（强烈建议写进配置文件，这里我仅是为了方便）
    $config = array(
        // 配置邮件发送服务器
        'MAIL_DEBUG'     =>  1,   // 是否启用smtp的debug进行调试
        'MAIL_HOST'      =>  'smtp.qq.com',   // SMTP服务器地址
        'MAIL_HOSTNAME'  =>  'http://lsgozj.cn',   // 设置发件人的主机域
        'MAIL_PORT'      =>  465,  //设置ssl连接smtp服务器的远程服务器端口号 可选465或587
        'MAIL_SMTPAUTH'  =>  TRUE, //启用smtp认证
        'MAIL_USERNAME'  =>  '1095395035@qq.com',  // 用户名
        'MAIL_FROM'      =>  '1095395035@qq.com',  // 邮箱地址
        'MAIL_FROMNAME'  =>  '曼斯威尔咖啡',  // 发件人姓名
        'MAIL_PASSWORD'  =>  'svfixdlzrkgobacf',  //smtp登录的密码 使用生成的授权码
        'MAIL_CHARSET'   =>  'UTF-8',   // 字符集
        'MAIL_ISHTML'    =>  TRUE, // 是否HTML格式邮件
        'MAIL_REPLYTO'   =>  '1095395035@qq.com',   //用户回复邮件时的接收邮箱，可以与原始邮箱分开
        //抄送就是 你写的这封邮件除了传送给收件人，还会传送给你在抄送一栏里写的邮箱地址，并且收件人>知道你把这封邮件发给了他和抄送一栏里输入的邮件地址的人
        //密送就是 你写的这封邮件除了传送给收件人，还会传送给你在暗送一栏里写的邮箱地址，但是收件人>不知道你把这封邮件发给了暗送一栏里输入的邮件地址的人
        'MAIL_CC'        =>  '',    //抄送者
        'MAIL_BCC'       =>  '',    //密送着
    );

    //实例化PHPMailer核心类
    //这里由于 index.php 文件中已经 include "vendor/autoload.php"，这里就不用引入了
    $mail = new PHPMailer;
    //使用smtp鉴权方式发送邮件
    $mail->isSMTP();
    //链接qq域名邮箱的服务器地址
    $mail->Host = $config['MAIL_HOST'];
    //smtp需要鉴权 这个必须是true
    $mail->SMTPAuth = $config['MAIL_SMTPAUTH'];
    //smtp登录的账号 这里填入字符串格式的qq号即可
    $mail->Username = $config['MAIL_USERNAME'];
    //smtp登录的密码 使用生成的授权码
    $mail->Password = $config['MAIL_PASSWORD'];
    //设置使用ssl加密方式登录鉴权
    $mail->SMTPSecure = 'ssl';
    //设置ssl连接smtp服务器的远程服务器端口号 可选465或587
    $mail->Port = $config['MAIL_PORT'];
    //设置发送的邮件的编码 可选GB2312 我喜欢utf-8 据说utf8在某些客户端收信下会乱码
    $mail->CharSet = $config['MAIL_CHARSET'];
    $mail->setFrom($config['MAIL_FROM'], $config['MAIL_FROMNAME']);
    //设置收件人邮箱地址 该方法有两个参数 第一个参数为收件人邮箱地址 第二参数为给该地址设置的昵称 不同的邮箱系统会自动进行处理变动 这里第二个参数的意义不大
    //添加多个收件人 则多次调用方法即可
    // $mail->addAddress('xxx@163.com','晶晶在线用户');
    foreach($to as $val){
        $mail->addAddress($val);
    }

    //设置用户回复的邮箱
    $mail->addReplyTo($config['MAIL_REPLYTO']);
    //设置用户回复的邮箱
    $mail->addReplyTo($config['MAIL_REPLYTO']);

    //设置抄送人
    $mail->addCC($config['MAIL_CC']);
    //密送者，Mail Header不会显示密送者信息
    $mail->addBCC($config['MAIL_BCC']);

//    $mail->addAttachment('/var/tmp/file.tar.gz');         // 添加附件
//    $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
    //邮件正文是否为html编码 注意此处是一个方法 不再是属性 true或false
    $mail->isHTML($config['MAIL_ISHTML']);

    //添加该邮件的主题
    $mail->Subject = $title;
    //添加邮件正文 上方将isHTML设置成了true，则可以是完整的html字符串 如：使用file_get_contents函数>读取本地的html文件
    $mail->Body = $content;
    //添加邮件正文 上方将isHTML设置成了false时调用
    $mail->AltBody = strip_tags($content);

    if (!$mail->send()) {
        throw new \Exception('邮件发送失败！请检查相关配置！');
    }
}



/**
 * 生成24位唯一订单号码，格式：YYYY-MMDD-HHII-SS-NNNN,NNNN-CC，其中：YYYY=年份，MM=月份，DD=日期，HH=24格式小时，II=分，SS=秒，NNNNNNNN=随机数，CC=检查码
 * @return $order_id ：唯一订单号码
 */

function OrderNumber()
{
	  $order_date = date('Y-m-d');
 
	  //订单号码主体（YYYYMMDDHHIISSNNNNNNNN）
	 
	  $order_id_main = date('YmdHis') . rand(10000000,99999999);
	 
	  //订单号码主体长度
	 
	  $order_id_len = strlen($order_id_main);
	 
	  $order_id_sum = 0;
	 
	  for($i=0; $i<$order_id_len; $i++){
	 
	  $order_id_sum += (int)(substr($order_id_main,$i,1));
	 
	  }
	 
	  //唯一订单号码（YYYYMMDDHHIISSNNNNNNNNCC）
	 
	  $order_id = $order_id_main . str_pad((100 - $order_id_sum % 100) % 100,2,'0',STR_PAD_LEFT);

	  return $order_id;
}



/**
 * 订单状态转换文字
 * @param $data ：数据库存的订单状态数值
 * @return $result : 返回转换后的文字
 */
function OrderStatus($data)
{
	switch ($data) {
            case 0:
                $result='待付款';
                break;
            
            case 1:
                $result='已付款';
                break;
            
            case 2:
                $result='已发货';
                break;
            
            case 3:
                $result='待评价';
                break;
            
            case 4:
                $result='完成';
                break;
            
            case 5:
                $result='申请退款中';
                break;
            
            case 6:
                $result='退货中';
                break;
            
            case 7:
                $result='已退款';
                break;
            
            case 8:
                $result='关闭';
                break;
        }
        return $result;
}

