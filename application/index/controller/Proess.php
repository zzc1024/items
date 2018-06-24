<?php

namespace app\index\controller;

use app\index\controller\Base;
use think\Request;
use app\index\model\Classify;
use app\index\model\Series;
use app\index\model\Address;
use app\index\model\ProductAttributes;
use app\index\model\ShopCart;
use app\index\model\OrderGoods;
use app\index\model\OrderDetail;
use app\index\model\User;
use think\Db;
use think\Session;


/**
 * 订单表数据order_goods
 * @param $data['address_id'] 			 ：地址id
 * @param $data['amount_total'] 		 ：订单总价
 * @param $data['order_no'] 			 ：订单编号
 * @param $data['order_status'] 		 ：订单状态
 *  0 未付款；1 已付款未发货；2 已发货未签收；3 待评价； 4 完成；5 退货申请；6 退货中；7 已退款；8 已关闭
 * @param $data['pay_channel']  		 ：付款方式
 * @param $data['create_time'] 			 ：订单创建时间
 * @param $data['user_id'] 				 ：用户id
 * @param $data['deliver_goods_time'] 	 ：发货时间
 * @param $data['order_settlement_time'] ：结束时间
 * @param $data['words'] 				 ：用户留言
 */

/**
 * 订单商品详情表数据order_detail
 * @param $data['product_count'] 			：商品数量
 * @param $data['product_name'] 		 	：商品名称
 * @param $data['product_original_price'] 	：商品原价
 * @param $data['product_price'] 			：商品售价
 * @param $data['product_id']  				：商品id
 * @param $data['attribute_id'] 			：属性id
 * @param $data['product_path'] 			：缩略图
 * @param $data['amount'] 	 				：总价
 * @param $data['order_goods'] 				：所属订单id
 */

class Proess extends Base
{
	//提交订单步骤页面
	public function sp_base(Request $request)
	{
		//get proess判断是第几步骤
		$proess = $request->get('proess');
		$proess = isset($proess)?$proess:1;
		$this->assign('proess',$proess);

		//如果是第一步
		if($proess==1)
		{
			$this->first($request);
		}
		//第二步
		elseif($proess==2)
		{
			$this->second($request);
		}
		//付款成功后
		elseif($proess==3)
		{
			$this->third($request);
		}
		//购物车
		elseif($proess==4)
		{
			$this->shop_cair();
		}

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

	//购物车
	private function shop_cair()
	{
		$sc = ShopCart::join('product_attributes pa','shop_cart.product_attributes_id=pa.id','left')->where('user_id',Session::get('user_id'))->field('shop_cart.id,product_count,product_name,product_id,attribute_id,original_price,price,path,attribute_name')->select();
		$this->assign('shop_cair_pro',$sc);
	}

	//第一步展示商品信息
	private function first($request)
	{
		$data = $request->post();
		//点立即购买进入
		if(empty($data['check']))
		{	
			$this->assign('check',0);

			$pa = ProductAttributes::where('id',$data['curpro_id'])->field('product_name,product_id,attribute_id,original_price,price,path,attribute_name')->select();
			foreach($pa as $k => $v)
			{
				$v['id'] = 0;
				$v['product_count'] = $data['product_count'];
			}
			$this->assign('shop_cair_pro',$pa);
			$address = Address::where('user_id',Session::get('user_id'))->where('status','neq',1)->select();
			$this->assign('address',$address);
		}
		//通过购物车进入
		else
		{
			$check = $data['check'];
			$this->assign('check',$check);

			$sc = ShopCart::join('product_attributes pa','shop_cart.product_attributes_id=pa.id','left')->where('user_id',Session::get('user_id'))->field('shop_cart.id,product_count,product_name,product_id,attribute_id,original_price,price,path,attribute_name')->select();
			$this->assign('shop_cair_pro',$sc);
			$address = Address::where('user_id',Session::get('user_id'))->where('status','neq',1)->select();
			$this->assign('address',$address);
		}
	}

	//第二步提交订单
	private function second($request)
	{
		$balance = User::where('id',Session::get('user_id'))->field('balance')->find()['balance'];
		$this->assign('balance',$balance);

		//判断是否post提交
		if($request->isPost()){
			$data = $request->post();
			$OrderGoods = new OrderGoods;
			$OrderDetail = new ORderDetail;
			$ShopCart = new ShopCart;
			//订单表数据
			$order_goods = [
				'address_id'=>$data['address_id'],
				'amount_total'=>$data['amount_total'],
				'order_no'=>OrderNumber(),
				'order_status'=>0,
				'create_time'=>time(),
				'words'=>$data['words'],
				'product_name'=>$data['product_name'][0],
				'product_path'=>$data['path'][0],
				'user_id'=>Session::get('user_id'),
			];
			if($OrderGoods->save($order_goods)==1)
			{
				$id = $OrderGoods->order_goods_id;
				//重组订单商品详情表的数据
				$count = count($data['product_id']);
				$order_detail = [];
				$shop_cart_id = [];
				for($i=0;$i<$count;$i++)
				{
					$order_detail[$i] = ['product_count'=>$data['product_count'][$i],
										 'product_name'=>$data['product_name'][$i],
										 'product_price'=>$data['product_price'][$i],
										 'product_original_price'=>$data['product_original_price'][$i],
										 'product_id'=>$data['product_id'][$i],
										 'attribute_id'=>$data['attribute_id'][$i],
										 'product_path'=>$data['path'][$i],
										 'amount'=>$data['amount'][$i],
										 'order_goods_id'=>$id,
										];
					$shop_cart_id[$i] = $data['shop_cart_id'][$i];
				}
				if(!$OrderDetail->saveAll($order_detail))
				{
					$OrderGoods->destroy($id);
					echo '<p>创建订单失败</p>';die;
				}
				$ShopCart->destroy($shop_cart_id);
				$this->assign('price_amount',$data['amount_total']);
				$this->assign('subject',$order_detail[0]['product_name']);
				$this->assign('out_trade_no',$order_goods['order_no']);
			}
			else
			{
				echo '<p>创建订单失败</p>';die;
			}
		}
		else
		{
			$id = $request->get()['ordid'];

			$data = OrderGoods::where('order_goods_id',$id)->field('amount_total,product_name,order_no')->find();

			$this->assign('price_amount',$data['amount_total']);
			$this->assign('subject',$data['product_name']);
			$this->assign('out_trade_no',$data['order_no']);
		}
	}

	//付款成功后的跳转页面
	private function third($request)
	{
		$data = $request->get();
		OrderGoods::where('order_no',$data['out_trade_no'])->update(['order_status'=>1]);
		$ordid = OrderGoods::where('order_no',$data['out_trade_no'])->field('order_goods_id')->find()['order_goods_id'];

		$this->assign([
			'amount'=>$data['total_amount'],
			'ordid'=>$ordid,
		]);
	}

	//购物车修改商品数量
	public function edit_product_num(Request $request)
	{
		$data = $request->post();
		$data['product_count'] = $data['product_count']>99?99:$data['product_count'];
		if(Db::table('shop_cart')->update($data))
		{
			exit(Message(1));
		}
	}

	//购物车删除商品
	public function delete_product(Request $request)
	{
		$id = $request->post('id');
		if(ShopCart::destroy($id)==1)
		{
			exit(Message(1,'删除成功'));
		}
		else
		{
			exit(Message(2,'删除失败'));
		}
	}

	//支付宝付款
	public function zfbpay(Request $request)
	{
		$params = $request->post();
		\alipay\Pagepay::pay($params);
	}

	//余额付款
	public function balance_pay(Request $request)
	{
		$data = $request->post();
		$password = md5($data['paypass']);
		$balance = User::where('id',Session::get('user_id'))->where('paypassword',$password)->field('balance')->find();
		if($balance)
		{
			
			if(OrderGoods::where('order_no',$data['out_trade_no'])->update(['order_status'=>1]))
			{
				$balance = $balance['balance']-$data['price_amount'];
				User::where('id',Session::get('user_id'))->update(['balance'=>$balance]);
				exit(Message(1,$data['out_trade_no']));
			}
			else
			{	
				exit(Message(2,'支付失败，请稍后重试'));
			}
		}
		else
		{
			exit(Message(2,'支付密码错误'));
		}

	}
}