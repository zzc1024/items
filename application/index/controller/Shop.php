<?php

namespace app\index\controller;

use app\index\controller\Base;
use think\Request;
use think\Db;
use app\index\model\Classify;
use app\index\model\Series;
use app\index\model\ProductAttributes;
use app\index\model\Product;
use app\index\model\ShopCart;
use think\Session;

class Shop extends Base
{
	//展示商品、查询商品
	public function shop_list(Request $request)
	{   
		//根据分类或系列查找商品
		$post = $request->param();
		$ProductAttributes = new ProductAttributes;

		if(isset($post['seriesid']))
		{
			$this->assign('seriesid',$post['seriesid']);
			$ProductAttributes->where('series',$post['seriesid']);
		}
		else
		{
			$this->assign('seriesid',999);
		}
		if(isset($post['classifyid']))
		{
			$ProductAttributes->where('classify','like',"%\"".$post['classifyid']."\"%");
		}
		if(isset($post['search']))
		{
			$count = count($post['search']);
			for($i=0;$i<$count;$i++)
			{
				$ProductAttributes->where('classify','like',"%\"".$post['search'][$i]."\"%");
			}
		}
		if(isset($post['price'])&&$post['price']!='')
		{
			$ProductAttributes->where('price','>',$post['price']);
		}
		if(isset($post['price2'])&&$post['price2']!='')
		{
			$ProductAttributes->where('price','<',$post['price2']);
		}
		if(isset($post['contents'])&&$post['contents']!='')
		{
			$ProductAttributes->where("product_name|attribute_name","like","%".$post['contents']."%");
		}

		$product_attr = $ProductAttributes->where('statu',1)->order('id desc')->group('product_id')->paginate(16,false,['query'=>$request->param()]);
		$this->assign('producta',$product_attr);
		if($request->isPost())
		{
			$list = $this->fetch('product');
			$info = [
				'code'=>1,
				'html'=>$list
			];
			exit(json_encode($info));
		}

		//商品数据
		$this->assign('producta',$product_attr);

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

	//商品详情页
	public function shop_goods(Request $request)
	{
		$get = $request->get();
		$product = new Product;
		$productattributes = new ProductAttributes;
		//查商品表
		$products = $product->get($get['pro']);
		$products['classify']=json_decode($products['classify']);
		$products['thumbnail']=ltrim($products['thumbnail'],".");
		//看了又看
		$see = $productattributes->where('statu',1)->where('see',1)->order('id desc')->limit(3)->group('product_id')->select();
		$this->assign('see',$see);
		//热门产品
		$hotgoods = $productattributes->where('statu',1)->where('hotgoods',1)->order('id desc')->limit(5)->group('product_id')->select();
		$this->assign('hotgoods',$hotgoods);
		//热门关注
		$hotfollow = $productattributes->where('statu',1)->where('hotfollow',1)->order('id desc')->limit(5)->group('product_id')->select();
		$this->assign('hotfollow',$hotfollow);

		//查SKU表
		$productattribute = $productattributes->where('product_id',$get['pro'])->select();
		$this->assign('products',$products);
		$this->assign('producta',$productattribute);
		if(empty($get['att']))
		{			
			$curpro = $productattributes->where('product_id',$get['pro'])->find();
			$this->assign('curpro',$curpro);
			$this->assign('att',999);
		}
		else
		{
			$curpro = $productattributes->where('product_id',$get['pro'])->where('attribute_id',$get['att'])->find();
			$this->assign('curpro',$curpro);
			$this->assign('att',$get['att']);
		}
		
		//查图片
		$products['imgpath']=json_decode($products['imgpath']);
		$count = count($products['imgpath']);
		$productimg=[];
		for($i=0;$i<$count;$i++)
		{
			$productimg[$i]=Db::table('product_files')->where('id',$products['imgpath'][$i])->find()['path'];
		}
		$this->assign('img',$productimg);

		//商品评价
		$comment = Db::table('product_comment')->alias('p')->where('p.product_id',$get['pro'])->where('p.status','neq',1)->join('user u','u.id=p.user_id')->field('p.content,p.score,p.create_time,u.name')->select();
		$this->assign('comment',$comment);

		//总销量
		$sales = Db::table('order_detail')->where('product_id',$get['pro'])->field('product_count')->select();
		$sales_num = 0;
		if($sales)
		{
			foreach ($sales as $k => $v) 
			{
				$sales_num = $sales_num+$v['product_count'];
			}
		}
		$this->assign('sales_num',$sales_num);

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

	//点击属性，无刷新更新价格，名称
	public function namesprice(Request $request)
	{
		$data = $request->post();
		$producta = ProductAttributes::where('product_id',$data['pid'])->where('attribute_id',$data['aid'])->find();
		$name = $producta['product_name'].$producta['attribute_name'];
		$o_price = '￥'.$producta['original_price'];
		$price = '￥'.$producta['price'];
		
		$info = [
			'name'=>$name."<input type='hidden' name='curpro_id' value=".$producta['id'].">",
			'o_price'=>$o_price,
			'price'=>$price,
		];
		exit(json_encode($info));
	}

	//加入购物车
	public function add_shop_cart(Request $request)
	{
		$data = $request->post();
		$data['product_count'] = $data['product_count']>99?99:$data['product_count'];
		$data['user_id']=Session::get('user_id');

		$pa = new ProductAttributes;
		$sc = new ShopCart;
		$sc_id = $sc->where('product_attributes_id',$data['product_attributes_id'])->where('user_id',$data['user_id'])->find();
		if(isset($sc_id))
		{
			$product_count = intval($data['product_count'])+intval($sc_id['product_count']);
			if($sc->where('id',$sc_id['id'])->update(['product_count'=>$product_count])==1)
			{
				$count = count($sc->where('user_id',Session::get('user_id'))->select());
				exit(Message(1,$count));
			}
			else
			{
				exit(Message(2));
			}
		}
		else
		{
			if($sc->save($data)==1)
			{
				$count = count($sc->where('user_id',Session::get('user_id'))->select());
				exit(Message(1,$count));
			}
			else
			{
				exit(Message(2));
			}
		}

	}

}