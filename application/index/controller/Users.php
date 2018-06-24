<?php

namespace app\index\controller;

use app\index\controller\Base;
use think\Request;
use think\Db;
use app\index\model\Classify;
use app\index\model\Series;
use app\index\model\Address;
use app\index\model\OrderGoods;
use app\index\model\OrderDetail;
use app\index\validate\Address as validate;
use think\Session;

class Users extends Base
{
	//会员中心展示页面
	public function index()
	{	
        //订单信息	
        $order_goods = OrderGoods::join('address','address_id=order_goods.address_id')->where('order_goods.user_id',Session::get('user_id'))->field('order_goods_id,order_no,address_id,order_status,create_time,name')->order('order_goods_id desc')->find();
        if($order_goods)
        {
            $order_goods['order_status'] = OrderStatus($order_goods['order_status']);
            //订单商品信息
            $order_product = OrderDetail::where('order_goods_id',$order_goods['order_goods_id'])->select();
            $this->assign(['order_goods'=>$order_goods,'order_product'=>$order_product]);
        }
        else
        {
            $this->assign(['order_goods'=>0,'order_product'=>0]);
        }


        $statu0 = OrderGoods::where('user_id',Session::get('user_id'))->where('order_status',0)->field('COUNT(order_status) has')->find();
        $statu2 = OrderGoods::where('user_id',Session::get('user_id'))->where('order_status',2)->field('COUNT(order_status) has')->find();
        $statu3 = OrderGoods::where('user_id',Session::get('user_id'))->where('order_status',3)->field('COUNT(order_status) has')->find();
        $statu0 = empty($statu0)?0:$statu0;$statu2 = empty($statu2)?0:$statu2;$statu3 = empty($statu3)?0:$statu3;
        $this->assign(['statu0'=>$statu0['has'],'statu2'=>$statu2['has'],'statu3'=>$statu3['has'],]);

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

    //地址管理页面
    public function address()
    {
        $request = Request::instance();
        //查地址表
        $address = Address::where('user_id',Session::get('user_id'))->where('status','neq',1)->order('id desc')->paginate(5,false,['query'=>$request->param()]);
        $this->assign('address',$address);

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

    //添加地址页面
    public function addressadd()
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

    //添加地址
    public function a_add(Request $request)
    {
        $data = $request->post();
        //验证信息
        $validate = new validate;
        if(!$validate->check($data))
        {
            exit(Message(2,$validate->getError()));
        }

        $data['user_id']=Session::get('user_id');
        $address = new Address;
        if($address->save($data)==1)
        {
            exit(Message(1,'添加成功','/index/Users/address'));
        }
        else
        {
            exit(Message(2,'添加失败'));
        }
    }

    //删除地址
    public function a_delete(Request $request)
    {
        $id = $request->post('id');
        if(Address::where('id',$id)->update(['status'=>1]))
        {
            exit(Message(1,'删除成功','/index/Users/address'));
        }
        else
        {
            exit(Message(2,'删除失败'));
        }
    }

    //订单列表
    public function order()
    {
        $OrderGoods = new OrderGoods;
        $OrderDetail = new OrderDetail;
        $ordsta = 99;
        $this->assign('ordsta',$ordsta);
        //查订单表
        $order_goods = $OrderGoods->where('user_id',Session::get('user_id'))->order('order_goods_id desc')->paginate(5);
        $this->assign('order_goods',$order_goods);

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

    //订单搜索
    public function order_search(Request $request)
    {
        $OrderGoods = new OrderGoods;
        $OrderDetail = new OrderDetail;
        $order_condition = $request->param();
        $ordsta = 99;
        if(isset($order_condition['ordsta']))
        {
            $ordsta = $order_condition['ordsta'];
            $OrderGoods->where('order_status',$ordsta);
        }
        elseif(!empty($order_condition['order_no']))
        {
            $OrderGoods->where('order_no','like','%'.$order_condition['order_no'].'%');
        }
        elseif(!empty($order_condition['time1']))
        {
            $OrderGoods->where('create_time','>',strtotime($order_condition['time1']));
        }
        elseif(!empty($order_condition['time2']))
        {
            $OrderGoods->where('create_time','<',strtotime($order_condition['time2']));
        }


        $order_goods = $OrderGoods->where('user_id',Session::get('user_id'))->order('order_goods_id desc')->paginate(5,false,['query'=>$order_condition]);

        $this->assign('ordsta',$ordsta);
        $this->assign('order_goods',$order_goods);

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
        return $this->fetch('order');
    }

    //订单详情表
    public function order_detail(Request $request)
    {

        $order_goods_id = $request->get('ordid');
        //订单信息
        $order_goods = OrderGoods::where('order_goods_id',$order_goods_id)->field('order_no,address_id,order_status,amount_total,deliver_goods_time,order_settlement_time,words')->find();

        if($order_goods['order_status']>1)
        {
            $deliver = Db::table('order_deliver')->where('order_goods_id',$order_goods_id)->find();
            $this->assign('deliver',$deliver);
            $this->assign('p',2);
        }
        else
        {
            $this->assign('p',1);
        }

        $order_goods['order_status'] = OrderStatus($order_goods['order_status']);

        //地址信息
        $order_address = Address::where('id',$order_goods['address_id'])->find();
        //商品信息
        $order_product = OrderDetail::where('order_goods_id',$order_goods_id)->select();
        $this->assign(['order_goods'=>$order_goods,
                       'order_address'=>$order_address,
                       'order_product'=>$order_product
                        ]);

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

    //确认收货
    public function confirm(Request $request)
    {
        $order_goods_id = $request->post('id');
        if(OrderGoods::where('order_goods_id',$order_goods_id)->update(['order_status'=>3])==1)
        {
            exit(Message(1,'已确认收货'));
        }
        else
        {
            exit(Message(2,'错误，请稍后重试'));
        } 
    }

    //发表评价页面
    public function comment(Request $request)
    {
        $order_goods_id = $request->get('ordid');

        //商品信息
        $order_product = OrderDetail::where('order_goods_id',$order_goods_id)->group('product_id')->select();
        
        $this->assign('order_product',$order_product);
        $this->assign('order_goods_id',$order_goods_id);

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

    //发表评价
    public function add_comment(Request $request)
    {
        $data = $request->post();
        $count = count($data['product_id']);
        $comment = [];
        $user_id = Session::get('user_id');
        $order_goods_id = $data['order_goods_id'];
        for($i=0;$i<$count;$i++)
        {
            $comment[$i] = [
                'user_id'=>$user_id,
                'product_id'=>$data['product_id'][$i],
                'content'=>$data['content'][$i],
                'score'=>$data['score'],
                'create_time'=>time(),
            ];
        }

        if(Db::table('product_comment')->insertAll($comment))
        {
            OrderGoods::where('order_goods_id',$order_goods_id)->update(['order_status'=>4]);
            exit(Message(1,'发表评价成功','/index/Users/order'));
        }
        else
        {
            exit(Message(2,'错误，请稍后重试'));
        }
    }
}