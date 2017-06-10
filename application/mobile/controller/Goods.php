<?php
/**
 * 商品 
 * ============================================================================
 * 版权所有 Ybcms开发团队，并保留所有权利
 * 网站地址: http://www.ybcms.com
 * ============================================================================
 * Author: ybcms(dreamlak@qq.com)      
 * UpDate: 2017-03-01
 */
namespace app\mobile\controller;
use think\AjaxPage;
use think\Page;
use think\Db;
class Goods extends MobileBase {
    /**
     * 分类列表显示
     */
    public function categoryList(){
        return $this->fetch();
    }

    /**
     * 商品列表页
     */
    public function goodsList(){
    	
    	$id = input('id/d',1); // 当前分类id
    	$brand_id = input('brand_id/d',0);
    	$spec = input('spec',0); // 规格
    	$attr = input('attr',''); // 属性
    	$sort = !empty(input('sort'))?input('sort'):'goods_id'; // 排序
    	$sort_asc = !empty(input('sort_asc'))?input('sort_asc'):'desc'; // 排序
    	$price = input('price',''); // 价钱
    	$start_price = trim(input('start_price','0')); // 输入框价钱
    	$end_price = trim(input('end_price','0')); // 输入框价钱
    	if($start_price && $end_price) $price = $start_price.'-'.$end_price; // 如果输入框有价钱 则使用输入框的价钱   	 
    	
    	$filter_param = []; // 帅选数组
    	$filter_param['id'] = $id; //加入帅选条件中
    	$brand_id  && ($filter_param['brand_id'] = $brand_id); //加入帅选条件中
    	$spec  && ($filter_param['spec'] = $spec); //加入帅选条件中
    	$attr  && ($filter_param['attr'] = $attr); //加入帅选条件中
    	$price  && ($filter_param['price'] = $price); //加入帅选条件中
         
    	$goodsLogic = new \app\home\logic\GoodsLogic(); // 前台商品操作逻辑类
    	// 分类菜单显示
    	$goodsCate = Db::name('goods_category')->where("id", $id)->find();// 当前分类
    	$cateArr = $goodsLogic->get_goods_cate($goodsCate);
    	 
    	// 帅选 品牌 规格 属性 价格
    	$cat_id_arr = getCatGrandson($id);
        
    	$filter_goods_id = Db::name('goods')->where("is_on_sale=1")->where("cat_id", "in" ,implode(',', $cat_id_arr))->cache(true)->column("goods_id");
    	
    	// 过滤帅选的结果集里面找商品
    	if($brand_id || $price){// 品牌或者价格
    		$goods_id_1 = $goodsLogic->getGoodsIdByBrandPrice($brand_id,$price); // 根据 品牌 或者 价格范围 查找所有商品id
    		$filter_goods_id = array_intersect($filter_goods_id,$goods_id_1); // 获取多个帅选条件的结果 的交集
    	}
    	if($spec){// 规格
    		$goods_id_2 = $goodsLogic->getGoodsIdBySpec($spec); // 根据 规格 查找当所有商品id
    		$filter_goods_id = array_intersect($filter_goods_id,$goods_id_2); // 获取多个帅选条件的结果 的交集
    	}
    	if($attr){// 属性
    		$goods_id_3 = $goodsLogic->getGoodsIdByAttr($attr); // 根据 规格 查找当所有商品id
    		$filter_goods_id = array_intersect($filter_goods_id,$goods_id_3); // 获取多个帅选条件的结果 的交集
    	}

        //筛选网站自营,入驻商家,货到付款,仅看有货,促销商品
        $sel =input('sel');
        if($sel){
            $goods_id_4 = $goodsLogic->get_filter_selected($sel,$cat_id_arr);
            $filter_goods_id = array_intersect($filter_goods_id,$goods_id_4);
        }
    	 
    	$filter_menu  = $goodsLogic->get_filter_menu($filter_param,'goodsList'); // 获取显示的帅选菜单
    	$filter_price = $goodsLogic->get_filter_price($filter_goods_id,$filter_param,'goodsList'); // 帅选的价格期间
    	$filter_brand = $goodsLogic->get_filter_brand($filter_goods_id,$filter_param,'goodsList',1); // 获取指定分类下的帅选品牌
    	$filter_spec  = $goodsLogic->get_filter_spec($filter_goods_id,$filter_param,'goodsList',1); // 获取指定分类下的帅选规格
    	$filter_attr  = $goodsLogic->get_filter_attr($filter_goods_id,$filter_param,'goodsList',1); // 获取指定分类下的帅选属性
    	
    	$count = count($filter_goods_id);
    	$page = new Page($count,4);
		
    	if($count > 0){
    		$goods_list = Db::name('goods')->where('goods_id','in',implode(',',$filter_goods_id))->order([$sort=>$sort_asc])->limit($page->firstRow.','.$page->listRows)->select();
    		
    		$filter_goods_id2 = get_arr_column($goods_list, 'goods_id');
    		if($filter_goods_id2){
    			$goods_images = Db::name('goods_images')->where('goods_id','in',implode(',', $filter_goods_id2))->cache(true)->select();
			}
			//dump($goods_list);die;
		}
    	$goods_category = Db::name('goods_category')->where('is_show=1')->cache(true)->column('id,name,parent_id,level'); // 键值分类数组
    	
    	$this->assign('goods_list',$goods_list);
    	$this->assign('goods_category',$goods_category);
    	$this->assign('goods_images',$goods_images);  // 相册图片
    	$this->assign('filter_menu',$filter_menu);  // 帅选菜单
    	$this->assign('filter_spec',$filter_spec);  // 帅选规格
    	$this->assign('filter_attr',$filter_attr);  // 帅选属性
    	$this->assign('filter_brand',$filter_brand);// 列表页帅选属性 - 商品品牌
    	$this->assign('filter_price',$filter_price);// 帅选的价格期间
    	$this->assign('goodsCate',$goodsCate);
    	$this->assign('cateArr',$cateArr);
    	$this->assign('filter_param',$filter_param); // 帅选条件
    	$this->assign('cat_id',$id);
    	$this->assign('page',$page);// 赋值分页输出
    	$this->assign('sort_asc', $sort_asc == 'asc' ? 'desc' : 'asc');
    	config('TOKEN_ON',false);
        if(input('is_ajax')){
            return $this->fetch('ajaxGoodsList');
        }else{
            return $this->fetch();
        }
    }

    /**
     * 商品列表页 ajax 翻页请求 搜索
     */
    public function ajaxGoodsList() {
        $where ='';

        $cat_id  = input("id/d",0); // 所选择的商品分类id
        if($cat_id > 0)
        {
            $grandson_ids = getCatGrandson($cat_id);
            $where .= " WHERE cat_id in(".  implode(',', $grandson_ids).") "; // 初始化搜索条件
        }

        $result = query("select count(1) as count from __PREFIX__goods $where ");
        $count = $result[0]['count'];
        $page = new AjaxPage($count,10);

        $order = " order by goods_id desc"; // 排序
        $limit = " limit ".$page->firstRow.','.$page->listRows;
        $list = query("select *  from __PREFIX__goods $where $order $limit");

        $this->assign('lists',$list);
        $html = $this->fetch('ajaxGoodsList'); //return $this->fetch('ajax_goods_list');
       exit($html);
    }

    /**
     * 商品详情页
     */
    public function goodsInfo(){
        config('TOKEN_ON',true);        
        $goodsLogic = new \app\home\logic\GoodsLogic();
        $goods_id = input("id/d");
        $goods = Db::name('Goods')->where("goods_id", $goods_id)->find();
        if(empty($goods)){
            $this->error('此商品不存在或者已下架');
        }
        if($goods['brand_id']){
            $brnad = Db::name('brand')->where("id", $goods['brand_id'])->find();
            $goods['brand_name'] = $brnad['name'];
        }
        $goods_images_list = Db::name('goods_images')->where("goods_id", $goods_id)->select(); // 商品 图册
        $goods_attribute = Db::name('goods_attribute')->column('attr_id,attr_name'); // 查询属性
        $goods_attr_list = Db::name('goods_attr')->where("goods_id", $goods_id)->select(); // 查询商品属性表

		$filter_spec = $goodsLogic->get_spec($goods_id);//商品规格
        $spec_goods_price  = Db::name('spec_goods_price')->where("goods_id", $goods_id)->column("key,price,store_count"); // 规格 对应 价格 库存表
        $commentStatistics = $goodsLogic->commentStatistics($goods_id);// 获取某个商品的评论统计     
        $this->assign('spec_goods_price', json_encode($spec_goods_price,true)); // 规格 对应 价格 库存表
      	$goods['sale_num'] = Db::name('order_goods')->where(['goods_id'=>$goods_id,'is_send'=>1])->count();

        //商品促销
        if($goods['prom_type'] == 1){
        	$prom_goods = Db::name('prom_goods')->where(['id'=>$goods['prom_id'],'is_close'=>0])->find();
        	$this->assign('prom_goods',$prom_goods);// 商品促销
            $goods['flash_sale'] = get_goods_promotion($goods['goods_id']);                        
            $flash_sale = Db::name('flash_sale')->where("id", $goods['prom_id'])->find();
            $this->assign('flash_sale',$flash_sale);
			if($goods['shop_price']>0){
            	$goods['discount'] = round($goods['flash_sale']['price']/$goods['shop_price'],2)*10;
			}else{
				$goods['discount'] = 0;
			}
        }else{
        	if($goods['market_price']>0){
        		$goods['discount'] = round($goods['shop_price']/$goods['market_price'],2)*10;
        	}else{
        		$goods['discount'] = 0;
        	}
        }

        //当前用户收藏
        $userid = cookie('userid');
        $collect = Db::name('goods_collect')->where(["goods_id"=>$goods_id ,"userid"=>$userid])->count();
        $goods_collect_count = Db::name('goods_collect')->where("goods_id",$goods_id)->count(); //商品收藏数
        $this->assign('collect',$collect);
        $this->assign('commentStatistics',$commentStatistics);//评论概览
        $this->assign('goods_attribute',$goods_attribute);//属性值     
        $this->assign('goods_attr_list',$goods_attr_list);//属性列表
        $this->assign('filter_spec',$filter_spec);//规格参数
        $this->assign('goods_images_list',$goods_images_list);//商品缩略图
        $this->assign('goods',$goods);
        $this->assign('goods_collect_count',$goods_collect_count); //商品收藏人数
        return $this->fetch();
    }

    /**
     * 商品详情页
     */
    public function detail(){
        //  form表单提交
        config('TOKEN_ON',true);
        $goods_id = input("get.id/d");
        $goods = Db::name('Goods')->where("goods_id", $goods_id)->find();
        $this->assign('goods',$goods);
        return $this->fetch();
    }

    /*
     * 商品评论
     */
    public function comment(){
        $goods_id = input("goods_id/d",0);
        $this->assign('goods_id',$goods_id);
        return $this->fetch();
    }

    /*
     * ajax获取商品评论
     */
    public function ajaxComment(){        
        $goods_id = input("goods_id/d",0);
        $commentType = input('commentType','1'); // 1 全部 2好评 3中评 4差评
        if($commentType==5){
        	$where = "goods_id = :goods_id and parent_id = 0 and img !='' ";
        }else{
        	$typeArr = array('1'=>'0,1,2,3,4,5','2'=>'4,5','3'=>'3','4'=>'0,1,2');
        	$where = "goods_id = :goods_id and parent_id = 0 and ceil((deliver_rank + goods_rank + service_rank) / 3) in($typeArr[$commentType])";
        }
        $count = Db::name('goods_comment')->where($where)->bind(['goods_id'=>$goods_id])->count();
		$page_count = 5;
        $page = new AjaxPage($count,$page_count);
        $list = Db::name('goods_comment')
				->alias('c')
				->join('__MEMBER__ u','u.userid = c.userid','LEFT')
				->where($where)
				->bind(['goods_id'=>$goods_id])
				->order("add_time desc")
				->limit($page->firstRow.','.$page->listRows)
				->select();
		$replyList = Db::name('goods_comment')->where(['goods_id'=>$goods_id,'parent_id'=>['>',0]])->order("add_time desc")->select();
        foreach($list as $k => $v){
            $list[$k]['img'] = unserialize($v['img']); // 晒单图片            
        }
        //dump($list);die;
		$this->assign('goods_id',$goods_id);//商品id
        $this->assign('commentlist',$list);// 商品评论
		$this->assign('commentType',$commentType);// 1 全部 2好评 3 中评 4差评 5晒图
        $this->assign('replyList',$replyList); // 管理员回复
		$this->assign('count', $count);//总条数
		$this->assign('page_count', $page_count);//页数
		$this->assign('current_count', $page_count*input('p'));//当前条
		$this->assign('p', input('p'));//页数
        return $this->fetch();
    }
    
    /*
     * 获取商品规格
     */
    public function goodsAttr(){
        $goods_id = input("get.goods_id/d",0);
        $goods_attribute = Db::name('goods_attribute')->column('attr_id,attr_name'); // 查询属性
        $goods_attr_list = Db::name('goods_attr')->where("goods_id", $goods_id)->select(); // 查询商品属性表
        $this->assign('goods_attr_list',$goods_attr_list);
        $this->assign('goods_attribute',$goods_attribute);
        return $this->fetch();
    }

    /**
     * 积分商城
     */
    public function integralMall(){
        $rank= input('get.rank');
        //以兑换量（购买量）排序
        if($rank == 'num'){
            $ranktype = 'sales_sum';
            $order = 'desc';
        }
        //以需要积分排序
        if($rank == 'integral'){
            $ranktype = 'exchange_integral';
            $order = 'desc';
        }
        $point_rate = config('config.point_rate');
        $goods_where = array(
            'is_on_sale' => 1,  //是否上架
        );
        //积分兑换筛选
        $exchange_integral_where_array = array(array('gt',0));

        // 分类id
        if (!empty($cat_id)) {
            $goods_where['cat_id'] = array('in', getCatGrandson($cat_id));
        }
        //我能兑换
        $userid = cookie('userid');
        if (!empty($userid)) {
            //获取用户积分
            $user_pay_points = intval(Db::name('member')->where(array('userid' => $userid))->value('mypoints'));
            if ($user_pay_points !== false) {
                array_push($exchange_integral_where_array, array('lt', $user_pay_points));
            }
        }
        $goods_where['exchange_integral'] =  $exchange_integral_where_array;  //拼装条件
        $goods_list_count = Db::name('goods')->where($goods_where)->count();   //总页数
        $page = new Page($goods_list_count, 15);
        $goods_list = Db::name('goods')->where($goods_where)->order($ranktype ,$order)->limit($page->firstRow . ',' . $page->listRows)->select();
        $goods_category = Db::name('goods_category')->where(array('level' => 1))->select();

        $this->assign('goods_list', $goods_list);
        $this->assign('page', $page->show());
        $this->assign('goods_list_count',$goods_list_count);
        $this->assign('goods_category', $goods_category);//商品1级分类
        $this->assign('point_rate', $point_rate);//兑换率
        $this->assign('totalPages',$page->totalPages);//总页数
        if(request()->isAjax()){
            return $this->fetch('ajaxIntegralMall'); //获取更多
        }
        return $this->fetch();
    }

     /**
     * 商品搜索列表页
     */
    public function search(){
    	$filter_param = []; // 帅选数组
    	$id 			= input('get.id/d',0); // 当前分类id
    	$brand_id 		= input('brand_id/d',0);//品牌ID
    	$sort 			= !empty(input('sort'))?input('sort'):'goods_id'; // 排序
    	$sort_asc 		= !empty(input('sort_asc'))?input('sort_asc'):'desc'; // 排序
    	$price 			= input('price',''); //价钱
    	$start_price 	= trim(input('start_price','0')); // 输入框价钱
    	$end_price 		= trim(input('end_price','0')); // 输入框价钱
    	$qtype 			= input('qtype','');
    	$q 				= urldecode(trim(input('q',''))); // 关键字搜索
    	//IIS环境下时，强制转编码为utf-8
    	if(php_sapi_name()=='cgi-fcgi'){
    		$q = iconv("gbk","utf-8",$q);  
    	}
    	//dump($q);die;
    	if($start_price && $end_price) $price = $start_price.'-'.$end_price; // 如果输入框有价钱 则使用输入框的价钱
    	$filter_param['id'] = $id;//加入帅选条件中
    	$brand_id  && ($filter_param['brand_id'] = $brand_id);//加入帅选条件中    	    	
    	$price  && ($filter_param['price'] = $price);//加入帅选条件中
        $q  && ($_GET['q'] = $filter_param['q'] = $q); //加入帅选条件中

        $where  = array('is_on_sale' => 1);
        if($qtype){
        	$filter_param['qtype'] = $qtype;
        	$where[$qtype] = 1;
        }
        if($q) $where['goods_name'] = array('like','%'.$q.'%');
		
    	$filter_goods_id = Db::name('goods')->where($where)->cache(true)->column("goods_id");
		$goodsLogic = new \app\home\logic\GoodsLogic(); // 前台商品操作逻辑类
    	// 过滤帅选的结果集里面找商品
    	if($brand_id>0 || $price>0){// 品牌或者价格
    		$goods_id_1 = $goodsLogic->getGoodsIdByBrandPrice($brand_id,$price); // 根据 品牌 或者 价格范围 查找所有商品id
    		$filter_goods_id = array_intersect($filter_goods_id,$goods_id_1); // 获取多个帅选条件的结果 的交集
    	}
		
        //筛选网站自营,入驻商家,货到付款,仅看有货,促销商品
        $sel = input('sel');
        if($sel){
            $goods_id_4 = $goodsLogic->get_filter_selected($sel);
            $filter_goods_id = array_intersect($filter_goods_id,$goods_id_4);
        }

    	$filter_menu  = $goodsLogic->get_filter_menu($filter_param,'search'); // 获取显示的帅选菜单
    	$filter_price = $goodsLogic->get_filter_price($filter_goods_id,$filter_param,'search'); // 帅选的价格期间
    	$filter_brand = $goodsLogic->get_filter_brand($filter_goods_id,$filter_param,'search',1); // 获取指定分类下的帅选品牌    	 
    	
    	$count = count($filter_goods_id);
    	$page = new Page($count,12);
    	if($count > 0){
    		$goods_list = Db::name('goods')->where("goods_id", "in", implode(',', $filter_goods_id))->order("$sort $sort_asc")->limit($page->firstRow.','.$page->listRows)->select();
    		$filter_goods_id2 = get_arr_column($goods_list, 'goods_id');
    		if($filter_goods_id2)
    			$goods_images = Db::name('goods_images')->where("goods_id", "in", implode(',', $filter_goods_id2))->cache(true)->select();
    	}
    	$goods_category = Db::name('goods_category')->where('is_show=1')->cache(true)->column('id,name,parent_id,level'); // 键值分类数组
    	$this->assign('goods_list',$goods_list);
    	$this->assign('goods_category',$goods_category);
    	$this->assign('goods_images',$goods_images);  // 相册图片
    	$this->assign('filter_menu',$filter_menu);  // 帅选菜单     
    	$this->assign('filter_brand',$filter_brand);// 列表页帅选属性 - 商品品牌
    	$this->assign('filter_price',$filter_price);// 帅选的价格期间    	
    	$this->assign('filter_param',$filter_param); // 帅选条件    	
    	$this->assign('page',$page);// 赋值分页输出
    	$this->assign('sort_asc', $sort_asc == 'asc' ? 'desc' : 'asc');
    	$this->assign('q',$q);// 赋值分页输出
    	config('TOKEN_ON',false);
        if(input('is_ajax')){
            return $this->fetch('ajaxGoodsList');
		}else{
            return $this->fetch();
		}
    }

    /**
     * 商品搜索列表页
     */
    public function ajaxSearch()
    {
        return $this->fetch();
    }

    /**
     * 品牌街
     */
    public function brandstreet()
    {
        $getnum = 9;   //取出数量
        $goods=Db::name('goods')->where(array('is_recommend'=>1,'is_on_sale'=>1))->page(1,$getnum)->cache(true,TPSHOP_CACHE_TIME)->select(); //推荐商品
        for($i=0;$i<($getnum/3);$i++){
            //3条记录为一组
            $recommend_goods[] = array_slice($goods,$i*3,3);
        }
        $where = array(
            'is_hot' => 1,  //1为推荐品牌
        );
        $count = Db::name('brand')->where($where)->count(); // 查询满足要求的总记录数
        $Page = new Page($count,20);
        $show = $Page->show();// 分页显示输出
        $brand_list = Db::name('brand')->where($where)->limit($Page->firstRow.','.$Page->listRows)->order('sort desc')->select();
        $this->assign('Page',$show);  //赋值分页输出
        $this->assign('recommend_goods',$recommend_goods);  //品牌列表
        $this->assign('brand_list',$brand_list);            //推荐商品
        if(input('is_ajax')){
            return $this->fetch('ajaxBrandstreet');
        }
        return $this->fetch();
    }

    /**
     * 看相似
     * $author lxl
     * $time 2017-1
     */
//    public function similar(){
//        $good_id = input('id/d','');
//        $now_good = Db::name('goods')->where("goods_id=$good_id")->find();   //当前商品
//        $where_id_list = Db::name('goods')->field('cat_id,spec_type')->where("goods_id=$good_id")->find(); //相似条件，分类id,类型id
//        $count = Db::name('goods')->where($where_id_list) ->count();    //符合条件的总数
//        $pagesize = C('PAGESIZE');  //每页显示数
//        $Page = new Page($count,$pagesize);
//        $goods_list = Db::name('goods')->where($where_id_list) ->limit($Page->firstRow.','.$Page->listRows)->select();  //获取相似商品
//        $this->assign('goods_list',$goods_list);
//        $this->assign('now_good',$now_good);
//        if(input('is_ajax')){
//            return $this->fetch('ajax_similar');
//        }
//        return $this->fetch();
//    }
}