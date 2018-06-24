<?php

namespace app\admin\controller;

use app\admin\controller\Base;
use app\admin\model\OrderGoods;
use app\admin\model\OrderDeliver;
use app\index\model\Address;
use app\index\model\OrderGoods as OrderGood;
use app\index\model\OrderDetail;
use app\admin\validate\Order as validate;
use think\Request;
use think\Db;

class Orders extends Base
{
	//订单列表
	public function index()
	{
		$result = Db::table('order_goods')->alias('o')->join('user u','o.user_id=u.id','left')->join('address a','o.address_id=a.id','left')->field('o.order_goods_id id,o.order_no,u.name user_name,a.name address_name,a.phone,a.region,a.address,o.create_time,o.order_status')->order('o.order_goods_id desc')->paginate(10);
		$this->assign('result',$result);
		return $this->fetch();
	}

	//搜索订单
	public function search(Request $request)
	{
		$data = Request::instance();
		$search = $data->post('search');
		$where = $search==''?"%%":"%".$search."%";
		$result = Db::table('order_goods')->alias('o')->join('user u','o.user_id=u.id','left')->join('address a','o.address_id=a.id','left')->field('o.order_goods_id id,o.order_no,u.name user_name,a.name address_name,a.phone,a.region,a.address,o.create_time,o.order_status')->where('o.order_no','like',$where)->order('o.order_goods_id desc')->paginate(10,false,['query'=>$data->post()]);
		$this->assign('result',$result);
		return $this->fetch('index');
	}

	//填写发货信息
	public function deliver_info(Request $request)
	{
		$this->assign('id',$request->get('id'));
		return $this->fetch();
	}

	//订单发货
	public function deliver_goods(Request $request)
	{
		$data = $request->post();
		$validate = new validate;
		$order_deliver = new OrderDeliver;
		$order_goods = new OrderGoods;
		if(!$validate->check($data))
		{
			exit(Message(2,$validate->getError()));
		}

		$data['create_time'] = time();

		if($order_deliver->save($data)==1)
		{
			if($order_goods->save(['order_status'=>2],['order_goods_id'=>$data['order_goods_id']]))
			{
				exit(Message(1,'发货成功'));
			}
			else
			{
				exit(Message(2,'发货失败，请稍后重试'));
			}

		}
		else
		{
			exit(Message(2,'发货失败，请稍后重试'));
		}			
	}

	//商品清单
	public function goods_list(Request $request)
	{
		$order_goods_id = $request->get('id');
        //订单信息
        $order_goods = OrderGood::where('order_goods_id',$order_goods_id)->field('order_no,address_id,order_status,amount_total,deliver_goods_time,order_settlement_time,words')->find();
        $order_goods['order_status'] = OrderStatus($order_goods['order_status']);
        //商品信息
        $order_product = OrderDetail::where('order_goods_id',$order_goods_id)->select();
        $this->assign(['order_goods'=>$order_goods,
                       'order_product'=>$order_product
                        ]);
		return $this->fetch();
	}
	
}