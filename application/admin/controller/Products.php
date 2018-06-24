<?php

namespace app\admin\controller;

use app\admin\controller\Base;
use think\Db;
use think\Request;
use app\admin\model\Classify;
use app\admin\model\Series;
use app\admin\model\Attribute;
use app\admin\model\Product;
use app\admin\model\Water;
use app\admin\model\ProductAttributes;


class Products extends Base
{
//商品展示
	public function index()
	{
        $data = Product::order('id desc')->paginate(5);  //每页显示5条数据

        foreach($data as $k=>$v)
        {
            $v['thumbnail'] = ltrim($v['thumbnail'], ".");
            $series = Series::get($v['series']);
            $v['series'] = $series['name'];
        }
        $this->assign('data',$data);
		return $this->fetch();
	}

//商品上下架
    public function product_statu(Request $request)
    {
        $data = $request->post();
        $id = $data['id'];
        if(Product::where('id',$data['id'])->update($data))
        {
            unset($data['id']);
            ProductAttributes::where('product_id',$id)->update($data);
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

//添加商品页面
	public function add()
	{
		//查商品分类表
		$classify = Classify::where('classify_pid',0)->select();
		foreach($classify as $k => $v)
		{
			$v['next'] = Classify::where('classify_pid',$v->toArray()['classify_id'])->select();
		}
		$this->assign('classify',$classify);
		//查商品系列表
		$series = Series::select();
		$this->assign('series',$series);
		//查商品属性表
		$attribute = Attribute::select();
		$this->assign('attribute',$attribute);
        //查图片水印表
        $water = Water::select();
        $this->assign('water',$water);
		return $this->fetch();
	}

//商品图片上传
    public function product_add_images()
    {

        $upload = new  \org\Upload();// 实例化上传类    
        $upload->maxSize   =     3145728 ;// 设置附件上传大小    
        $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型    
        $upload->rootPath  =      './static/img/'; // 设置附件上传目录    // 上传文件
        $upload->saveName=time().rand(1111,9999);
        $date=date("Y-m-d",time());//已上传日期为子目录名
        $upload->saveExt="png";//上传的文件后缀
          $info   =   $upload->upload();   
          if(!$info) {// 上传错误提示错误信息  

              $this->error($upload->getError());  

           }else{// 上传成功 
            
            $data['path']='/static/img/'.'public'."/".$upload->saveName.".".$upload->saveExt;
            $result=Db::table('product_files')->insertGetId($data);
            $file=['id'=>$result,'imagepath'=>$data['path']];
            echo json_encode($file);

           }
    }

//商品图片删除
    public function product_img_delete(){
        $result=Db::table('product_files')->delete($_GET['id']);
        if($result){
            echo 1;
        }else{
            echo 0;
        }
    }

//添加商品
    public function product_add(Request $request)
    {
        $product = new Product;

    	$data = $request->post();

        if(empty($data['hotgoods']))
        {
            $data['hotgoods']=0;
        }
        if(empty($data['hotfollow']))
        {
            $data['hotfollow']=0;
        }
        if(empty($data['see']))
        {
            $data['see']=0;
        }

    	//把classify转成数组
    	$data['classify'] = str_replace("classify=","",$data['classify']);
    	$data['classify'] = explode('&', $data['classify']);
    	//把imgpath转成数组
    	$data['imgpath'] = str_replace("imgpath=","",$data['imgpath']);
    	$data['imgpath'] = explode('&', $data['imgpath']);



        prodcuts($data);//验证信息

    	//将第一张图片做成缩略图
    	$imgid = $data['imgpath'][count($data['imgpath'])-1];
    	$imgpath = Db::table('product_files')->field('path')->where('id',$imgid)->find();
        $img123 = $imgpath['path'];
        $imgpath['path'] = '.'.$imgpath['path'];    //路径前面要加.	
    	$image = \org\Image::open($imgpath['path']);
    	//保存缩略图的文件路径，不存在的话自动创建
    	$thumpath = './static/img/thum';
    	$thumname = str_replace("./static/img/public/","",$imgpath['path']);
    	if(!file_exists($thumpath))
    	{
    		mkdir($thumpath,0777,true);
    	}
    	$thum_path = $thumpath.'/'.$thumname; //缩略图路径
    	$image->thumb(36,35)->save($thum_path); //上传图片大小小于36，会报错
        $data['thumbnail'] = $thum_path;
        
        //给图片添加水印
        $water_path = Water::get($data["water"])['path'];
        $water_path ='.'.$water_path;
        $water_path =str_replace('\\', '/',$water_path);
        // dump($water_path);die;
        foreach($data['imgpath'] as $key => $vo)
        {
            $img_path = Db::table('product_files')->where('id',$vo)->find()['path'];
            $img_path = '.'.$img_path;
            WaterImg($data["is_water"],$img_path,$water_path,$data["water_d"]);
        }

        //将数组数据转json
        $data['classify']=json_encode($data['classify']);
        $attributearray = $data['attribute'];
        $data['attribute']=json_encode($data['attribute']);
        $data['imgpath']=json_encode($data['imgpath']);

        $productadd = $product->save($data);  //添加商品返回自增id
        $id = $product->id;
    	//添加成功的话，根据自增id，添加商品编号
        if($productadd!=0)
        {   
            
            $number = mt_rand(100000,999999);//生成6位数商品编号

            //添加商品属性关联表
            // path = $imgpath  主图路径
            // product_name = $data['name']   商品名称
            // product_id = $number     商品编号
            // attribute_id = $data['attribute'][$i] 属性id
            // attribute_name = $data['attribute_name'][$i] 属性名称
            // original_price = $data['original_price'][$i] 原价
            // price = $data['price'][$i] 售价
            $count = count($attributearray);
            $product_attr = [];
            for($i=0;$i<$count;$i++)
            {
                $product_attr[$i] =['product_name'=>$data['name'],
                                    'product_id'=>$id,
                                    'attribute_id'=>$attributearray[$i],
                                    'attribute_name'=>$data['attribute_name'][$i],
                                    'original_price'=>$data['original_price'][$i],
                                    'price'=>$data['price'][$i],
                                    'path'=>$img123,
                                    'series'=>$data['series'],
                                    'classify'=>$data['classify'],
                                    'statu'=>$data['statu'],
                                    'hotgoods'=>$data['hotgoods'],
                                    'hotfollow'=>$data['hotfollow'],
                                    'see'=>$data['see']
                                    ];
            }
            $ProductAttributes = new ProductAttributes;
            $product_attributes = $ProductAttributes->saveAll($product_attr);
            if(!$product_attributes)
            {
                Product::destroy($id); //编号添加失败删除之前添加的商品信息
                exit(Message(2,'添加失败'));
            }

            //修改商品表的商品编号
            $thumbnail = $product->save(['number' => $number],['id' => $id]);
            if($thumbnail!=0)
            {
                exit(Message(1,'添加成功'));
            }
            else
            {
                Product::destroy($id); //编号添加失败删除之前添加的商品信息
                ProductAttributes::destroy(['product_number'=>$number]); //删除商品属性关联表
                exit(Message(2,'添加失败'));
            }
        }
        else
        {
                exit(Message(2,'添加失败'));
        }
    }

//商品编辑页面
    public function edit(Request $request)
    {
        $data = $request->param();
        $product = Product::get($data['id']);
        $this->assign('product',$product);

        $producta = ProductAttributes::where('product_id',$data['id'])->select();
        $this->assign('producta',$producta);

        //商品图片
        $image = [];
        foreach($product['imgpath'] as $v)
        {
            array_push($image,Db::table('product_files')->find($v));
        }
        $this->assign('image',$image);


        //查商品分类表
        $classify = Classify::where('classify_pid',0)->select();
        foreach($classify as $k => $v)
        {
            $v['next'] = Classify::where('classify_pid',$v->toArray()['classify_id'])->select();
        }
        $this->assign('classify',$classify);
        //查商品系列表
        $series = Series::select();
        $this->assign('series',$series);
        //查商品属性表
        $attribute = Attribute::select();
        $this->assign('attribute',$attribute);
        //查图片水印表
        $water = Water::select();
        $this->assign('water',$water);
        return $this->fetch();
    }

//修改商品信息
    public function product_edit(Request $request)
    {
        $product = new Product;

        $data = $request->post();
        $product_id = $data['id'];
        unset($data['id']);
        //把classify转成数组
        $data['classify'] = str_replace("classify=","",$data['classify']);
        $data['classify'] = explode('&', $data['classify']);
        //把imgpath转成数组
        $data['imgpath'] = str_replace("imgpath=","",$data['imgpath']);
        $data['imgpath'] = explode('&', $data['imgpath']);
        
        prodcuts($data);//验证信息

        //将第一张图片做成缩略图
        // $image = new  \org\Image();

        if(empty($data['hotgoods']))
        {
            $data['hotgoods']=0;
        }
        if(empty($data['hotfollow']))
        {
            $data['hotfollow']=0;
        }
        if(empty($data['see']))
        {
            $data['see']=0;
        }

        $imgid = $data['imgpath'][count($data['imgpath'])-1];
        $imgpath = Db::table('product_files')->field('path')->where('id',$imgid)->find();
        $img123 = $imgpath['path'];
        $imgpath['path'] = '.'.$imgpath['path'];    //路径前面要加.
        
        $image = \org\Image::open($imgpath['path']);

        //保存缩略图的文件路径，不存在的话自动创建
        $thumpath = './static/img/thum';
        $thumname = str_replace("./static/img/public/","",$imgpath['path']);
        if(!file_exists($thumpath))
        {
            mkdir($thumpath,0777,true);
        }
        $thum_path = $thumpath.'/'.$thumname; //缩略图路径
        $image->thumb(36,35)->save($thum_path);
        $data['thumbnail'] = $thum_path;
        
        //给图片添加水印
        if($data["is_water"]!=3){
            $water_path = Water::get($data["water"])['path'];
            $water_path ='.'.$water_path;
            $water_path =str_replace('\\', '/',$water_path);
            foreach($data['imgpath'] as $key => $vo)
            {
                $img_path = Db::table('product_files')->where('id',$vo)->find()['path'];
                $img_path = '.'.$img_path;
                WaterImg($data["is_water"],$img_path,$water_path,$data["water_d"]);
            }
        }

        //将数组数据转json
        $data['classify']=json_encode($data['classify']);
        $attributearray = $data['attribute'];
        $data['attribute']=json_encode($data['attribute']);
        $data['imgpath']=json_encode($data['imgpath']);

        $productadd = $product->save($data,['id'=>$product_id]);  //修改商品
        if($productadd!=0)
        { 

            ProductAttributes::destroy(['product_id'=>$product_id]);
            $count = count($attributearray);
            $product_attr = [];
            for($i=0;$i<$count;$i++)
            {
                $product_attr[$i] =['product_name'=>$data['name'],
                                    'product_id'=>$product_id,
                                    'attribute_id'=>$attributearray[$i],
                                    'attribute_name'=>$data['attribute_name'][$i],
                                    'original_price'=>$data['original_price'][$i],
                                    'price'=>$data['price'][$i],
                                    'path'=>$img123,
                                    'series'=>$data['series'],
                                    'classify'=>$data['classify'],
                                    'statu'=>$data['statu'],
                                    'hotgoods'=>$data['hotgoods'],
                                    'hotfollow'=>$data['hotfollow'],
                                    'see'=>$data['see']
                                    ];
            }
            $ProductAttributes = new ProductAttributes;
            $product_attributes = $ProductAttributes->saveAll($product_attr);
            if(!$product_attributes)
            {
                exit(Message(2,'修改失败'));
            }
            exit(Message(1,'修改成功'));
        }
        else
        {
            exit(Message(2,'修改失败'));
        }
    }

//删除产品
    public function product_delete(Request $request)
    {
        $id = $request->post();
        
        if(Product::destroy($id['id']))
        {
            ProductAttributes::destroy(['product_id'=>$id['id']]);
            exit(Message(1,'删除成功'));
        }
        else
        {
            exit(Message(2,'删除失败'));
        }
    }

//批量删除
    public function product_deletes(Request $request)
    {
        $data = $request->post()['checkID'];
        $count = count($data);
        if(Product::destroy($data))
        {
            for($i=0;$i<$count;$i++)
            {
                ProductAttributes::destroy(['product_id'=>$data[$i]]);
            }
            exit(Message(1,'删除成功'));
        }
        else
        {
            exit(Message(2,'删除失败'));
        }
    }

//商品分类查询
    public function product_list()
    {
        $data = Request::instance();
        $classify_id = input('id');
        $where = "%\"".$classify_id."\"%";

        $data = Product::where("classify","like",$where)->order('id desc')->paginate(5,false,['query'=>$data->param()]);
        foreach($data as $k=>$v)
        {
            $v['thumbnail'] = ltrim($v['thumbnail'], ".");
            $series = Series::get($v['series']);
            $v['series'] = $series['name'];
        }
        $this->assign('data',$data);
        return $this->fetch('index');        
    }

//搜产品
    public function product_search()
    {
        $data = Request::instance();    //这里要用Request静态调用，不然后面paginate传的参数会无效
        $id = $data->post('search');
        $where = $id==''?"%%":"%$id%";                               
        $data = Product::where("number|name","like",$where)->order('id desc')->paginate(5,false,['query'=>$data->param()]);
        foreach($data as $k=>$v)
        {
            $v['thumbnail'] = ltrim($v['thumbnail'], ".");
            $series = Series::get($v['series']);
            $v['series'] = $series['name'];
        }
        $this->assign('data',$data);
        return $this->fetch('index');     
    }
}

