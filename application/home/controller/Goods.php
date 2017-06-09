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
namespace app\home\controller; 
use app\home\logic\CartLogic;
use app\home\logic\GoodsLogic;
use think\AjaxPage;
use think\Controller;
use think\Url;
use think\Config;
use think\Page;
use think\Verify;
use think\Db;
class Goods extends Base {
    public function index(){      
        return $this->fetch();
    }

   /**
    * 商品详情页
    */ 
    public function goodsInfo(){
        //form表单提交
        $goodsLogic = new \app\home\logic\GoodsLogic();
        //echo '=='.input('get.id');
        $goods_id = input("id/d");
        $goods = Db::name('goods')->where("goods_id",$goods_id)->find();
        
        if(empty($goods) || ($goods['is_on_sale'] == 0)){
        	$this->error('该商品已经下架',url('Index/index'));
        }
     
        if($goods['brand_id']){
            $brnad = Db::name('brand')->where("id",$goods['brand_id'])->find();
            $goods['brand_name'] = $brnad['name'];
        }  
        $goods_images_list = Db::name('goods_images')->where("goods_id", $goods_id)->select(); // 商品 图册
        $goods_attribute = Db::name('goods_attribute')->column('attr_id,attr_name'); // 查询属性
        $goods_attr_list = Db::name('goods_attr')->where("goods_id", $goods_id)->select(); // 查询商品属性表
	    $filter_spec = $goodsLogic->get_spec($goods_id);
        //dump($goods_images_list);die;
        //商品是否正在促销中        
        if($goods['prom_type'] == 1){
            $goods['flash_sale'] = get_goods_promotion($goods['goods_id']);                        
            $flash_sale = Db::name('flash_sale')->where("id", $goods['prom_id'])->find();
            $this->assign('flash_sale',$flash_sale);
        }
       
        $freight_free = config('config.freight_free'); // 全场满多少免运费
        $spec_goods_price  = Db::name('spec_goods_price')->where("goods_id", $goods_id)->column("key,price,store_count"); // 规格 对应 价格 库存表
        Db::name('Goods')->where("goods_id", $goods_id)->update(array('click_count'=>$goods['click_count']+1 )); //统计点击数
        $commentStatistics = $goodsLogic->commentStatistics($goods_id);// 获取某个商品的评论统计
        $point_rate = config('config.point_rate');
        $this->assign('freight_free', $freight_free);// 全场满多少免运费
        $this->assign('spec_goods_price', json_encode($spec_goods_price,true)); // 规格 对应 价格 库存表
        $this->assign('navigate_goods',navigate_goods($goods_id,1));// 面包屑导航
        $this->assign('commentStatistics',$commentStatistics);//评论概览
        $this->assign('goods_attribute',$goods_attribute);//属性值     
        $this->assign('goods_attr_list',$goods_attr_list);//属性列表
        $this->assign('filter_spec',$filter_spec);//规格参数
        $this->assign('goods_images_list',$goods_images_list);//商品缩略图
        $this->assign('siblings_cate',$goodsLogic->get_siblings_cate($goods['cat_id']));//相关分类
        $this->assign('look_see',$goodsLogic->get_look_see($goods));//看了又看      
        $this->assign('goods',$goods);
        $this->assign('point_rate',$point_rate);        
        return $this->fetch();        
    }

    
    public function goodsInfo2(){
        $this->fetch();
    }
    
    /**
     * 获取可发货地址
     */
    public function getRegion(){
        $goodsLogic = new GoodsLogic();
        $region_list = $goodsLogic->getRegionList();//获取配送地址列表
        $region_list['status'] = 1;
        $this->ajaxReturn($region_list);
    }
    
    /**
     * 商品列表页
     */
    public function goodsList(){ 
        $key = md5($_SERVER['REQUEST_URI'].input('start_price').'_'.input('end_price'));
        $html = S($key);
        if(!empty($html)){
            return $html;
        }
        
        $filter_param = array(); // 帅选数组                        
        $id = input('id/d',1); // 当前分类id
        $brand_id = input('brand_id/d',0);
        $spec = input('spec',0); // 规格 
        $attr = input('attr',''); // 属性        
        $sort = input('sort','goods_id'); // 排序
        $sort_asc = input('sort_asc','asc'); // 排序
        $price = input('price',''); // 价钱
        $start_price = trim(input('start_price','0')); // 输入框价钱
        $end_price = trim(input('end_price','0')); // 输入框价钱        
        if($start_price && $end_price) $price = $start_price.'-'.$end_price; // 如果输入框有价钱 则使用输入框的价钱
     
        $filter_param['id'] = $id; //加入帅选条件中                       
        $brand_id  && ($filter_param['brand_id'] = $brand_id); //加入帅选条件中
        $spec  && ($filter_param['spec'] = $spec); //加入帅选条件中
        $attr  && ($filter_param['attr'] = $attr); //加入帅选条件中
        $price  && ($filter_param['price'] = $price); //加入帅选条件中
                
        $goodsLogic = new GoodsLogic(); // 前台商品操作逻辑类
        
        // 分类菜单显示
        $goodsCate = Db::name('goods_category')->where("id", $id)->find();// 当前分类
        $cateArr = $goodsLogic->get_goods_cate($goodsCate);
		//dump($goodsCate);die;
        // 帅选 品牌 规格 属性 价格
        $cat_id_arr = getCatGrandson ($id);
        $filter_goods_id = Db::name('goods')->where(['is_on_sale'=>1,'cat_id'=>['in',implode(',', $cat_id_arr)]])->cache(true)->column("goods_id");
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
           
        $filter_menu  = $goodsLogic->get_filter_menu($filter_param,'goodsList'); // 获取显示的帅选菜单
        $filter_price = $goodsLogic->get_filter_price($filter_goods_id,$filter_param,'goodsList'); // 帅选的价格期间         
        $filter_brand = $goodsLogic->get_filter_brand($filter_goods_id,$filter_param,'goodsList',1); // 获取指定分类下的帅选品牌        
        $filter_spec  = $goodsLogic->get_filter_spec($filter_goods_id,$filter_param,'goodsList',1); // 获取指定分类下的帅选规格        
        $filter_attr  = $goodsLogic->get_filter_attr($filter_goods_id,$filter_param,'goodsList',1); // 获取指定分类下的帅选属性        
                                
        $count = count($filter_goods_id);
        $page = new Page($count,12);
        if($count > 0){
            $goods_list = Db::name('goods')->where("goods_id","in", implode(',', $filter_goods_id))->order("$sort $sort_asc")->limit($page->firstRow.','.$page->listRows)->select();
            $filter_goods_id2 = get_arr_column($goods_list, 'goods_id');
            if($filter_goods_id2)
            $goods_images = Db::name('goods_images')->where("goods_id", "in", implode(',', $filter_goods_id2))->cache(true)->select();
        }
        $goods_category = Db::name('goods_category')->where('is_show=1')->cache(true)->column('id,name,parent_id,level'); // 键值分类数组
        $navigate_cat = navigate_goods($id); // 面包屑导航         
        $this->assign('goods_list',$goods_list);
        $this->assign('navigate_cat',$navigate_cat);
        $this->assign('goods_category',$goods_category);                
        $this->assign('goods_images',$goods_images);  // 相册图片
        $this->assign('filter_menu',$filter_menu);  // 帅选菜单
        $this->assign('filter_spec',$filter_spec);  // 帅选规格
        $this->assign('filter_attr',$filter_attr);  // 帅选属性
        $this->assign('filter_brand',$filter_brand);  // 列表页帅选属性 - 商品品牌
        $this->assign('filter_price',$filter_price);// 帅选的价格期间
        $this->assign('goodsCate',$goodsCate);
        $this->assign('cateArr',$cateArr);
        $this->assign('filter_param',$filter_param); // 帅选条件
        $this->assign('cat_id',$id);
        $this->assign('page',$page);// 赋值分页输出        
        $html = $this->fetch();        
        S($key,$html);
        return $html;
    }    

    /**
     *  查询配送地址，并执行回调函数
     */
    public function region(){
        $fid = input('fid/d');
        $callback = input('callback');
        $parent_region = Db::name('areas')->field('id,name')->where('id',$fid)->cache(true)->select();
        echo $callback.'('.json_encode($parent_region).')';
        exit;
    }
    /**
     * 商品物流配送和运费
     */
    public function dispatching(){        
        $goods_id = input('goods_id/d');//143
        $region_id = input('region_id/d');//28242
        $goods_logic = new GoodsLogic();
        $dispatching_data = $goods_logic->getGoodsDispatching($goods_id,$region_id);
        $this->ajaxReturn($dispatching_data);
    }
    /**
     * 商品搜索列表页
     */
    public function search(){
       //C('URL_MODEL',0);
        $filter_param = array(); // 帅选数组                        
        $id = input('get.id/d',0); // 当前分类id 
        $brand_id = input('brand_id/d',0);         
        $sort = input('sort','goods_id'); // 排序
        $sort_asc = input('sort_asc','asc'); // 排序
        $price = input('price',''); // 价钱
        $start_price = trim(input('start_price','0')); // 输入框价钱
        $end_price = trim(input('end_price','0')); // 输入框价钱
        if($start_price && $end_price) $price = $start_price.'-'.$end_price; // 如果输入框有价钱 则使用输入框的价钱
        $q = urldecode(trim(input('q',''))); // 关键字搜索
        empty($q) && $this->error('请输入搜索词');
        $id && ($filter_param['id'] = $id); //加入帅选条件中                       
        $brand_id  && ($filter_param['brand_id'] = $brand_id); //加入帅选条件中        
        $price  && ($filter_param['price'] = $price); //加入帅选条件中
        $q  && ($_GET['q'] = $filter_param['q'] = $q); //加入帅选条件中
        $goodsLogic = new GoodsLogic(); // 前台商品操作逻辑类
        $where  = array(
            'is_on_sale' => 1
        );
        //引入
        if(file_exists(PLUGIN_PATH.'coreseek/sphinxapi.php')){
            require_once(PLUGIN_PATH.'coreseek/sphinxapi.php');
            $cl = new \SphinxClient();
            $cl->SetServer(config('SPHINX_HOST').'', config(config('SPHINX_PORT')));
            $cl->SetConnectTimeout(10);
            $cl->SetArrayResult(true);
            $cl->SetMatchMode(SPH_MATCH_ANY);
            $res = $cl->Query($q, "mysql");
            if($res){
                $goods_id_array = array();
                foreach ($res['matches'] as $key => $value) {
                    $goods_id_array[] = $value['id'];
                }
                if(!empty($goods_id_array)){
                    $where['goods_id'] = array('in',$goods_id_array);
                }else{
                    $where['goods_id'] = 0;
                }
            }else{
                $where['goods_name'] = array('like','%'.$q.'%');
            }
        }else{
            $where['goods_name'] = array('like','%'.$q.'%');
        }

        if($id){
            $cat_id_arr = getCatGrandson ($id);
            $where['cat_id'] = array('in',implode(',', $cat_id_arr));
        }
        $search_goods = Db::name('goods')->where($where)->column('goods_id,cat_id');
        $filter_goods_id = array_keys($search_goods);
        $filter_cat_id = array_unique($search_goods); // 分类需要去重
        if($filter_cat_id)        {
            $cateArr = Db::name('goods_category')->where("id","in",implode(',', $filter_cat_id))->select();
            $tmp = $filter_param;
            foreach($cateArr as $k => $v)            {
                $tmp['id'] = $v['id'];
                $cateArr[$k]['href'] = url("/Home/Goods/search",$tmp);                            
            }                
        }                        
        // 过滤帅选的结果集里面找商品        
        if($brand_id || $price){
        	// 品牌或者价格
            $goods_id_1 = $goodsLogic->getGoodsIdByBrandPrice($brand_id,$price); // 根据 品牌 或者 价格范围 查找所有商品id    
            $filter_goods_id = array_intersect($filter_goods_id,$goods_id_1); // 获取多个帅选条件的结果 的交集
        }
        
        $filter_menu  = $goodsLogic->get_filter_menu($filter_param,'search'); // 获取显示的帅选菜单
        $filter_price = $goodsLogic->get_filter_price($filter_goods_id,$filter_param,'search'); // 帅选的价格期间         
        $filter_brand = $goodsLogic->get_filter_brand($filter_goods_id,$filter_param,'search',1); // 获取指定分类下的帅选品牌        
                                
        $count = count($filter_goods_id);
        $page = new Page($count,20);
        if($count > 0){
            $goods_list = Db::name('goods')->where(['is_on_sale'=>1,'goods_id'=>['in',implode(',', $filter_goods_id)]])->order("$sort $sort_asc")->limit($page->firstRow.','.$page->listRows)->select();
            $filter_goods_id2 = get_arr_column($goods_list, 'goods_id');
            if($filter_goods_id2)
            $goods_images = Db::name('goods_images')->where("goods_id", "in",implode(',', $filter_goods_id2))->select();
        }    
                
        $this->assign('goods_list',$goods_list);  
        $this->assign('goods_images',$goods_images);  // 相册图片
        $this->assign('filter_menu',$filter_menu);  // 帅选菜单
        $this->assign('filter_brand',$filter_brand);  // 列表页帅选属性 - 商品品牌
        $this->assign('filter_price',$filter_price);// 帅选的价格期间
        $this->assign('cateArr',$cateArr);
        $this->assign('filter_param',$filter_param); // 帅选条件
        $this->assign('cat_id',$id);
        $this->assign('page',$page);// 赋值分页输出
        $this->assign('q',input('q'));
        C('TOKEN_ON',false);
        return $this->fetch();
    }
    
    /**
     * 商品咨询ajax分页
     */
    public function ajax_consult(){        
        $goods_id = input("goods_id/d", 0);
        $consult_type = input('consult_type','0'); // 0全部咨询  1 商品咨询 2 支付咨询 3 配送 4 售后
                 
        $where  = ['is_show'=>1,'parent_id'=>0,'goods_id'=>$goods_id];
        if($consult_type > 0){
            $where['consult_type'] = $consult_type;
        }
        $count = Db::name('goods_consult')->where($where)->count();
        $page = new AjaxPage($count,5);
        $show = $page->show();        
        $list = Db::name('goods_consult')->where($where)->order("id desc")->limit($page->firstRow.','.$page->listRows)->select();
        $replyList = Db::name('goods_consult')->where("parent_id > 0")->order("id desc")->select();
        
        $this->assign('consultCount',$count);// 商品咨询数量
        $this->assign('consultList',$list);// 商品咨询
        $this->assign('replyList',$replyList); // 管理员回复
        $this->assign('page',$show);// 赋值分页输出        
        return $this->fetch();        
    }
    
    /**
     * 商品评论ajax分页
     */
    public function ajaxComment(){        
        $goods_id = input("goods_id/d",'0');        
        $commentType = input('commentType','1'); // 1 全部 2好评 3 中评 4差评
        $where = ['is_show'=>1,'goods_id'=>$goods_id,'parent_id'=>0];
        if($commentType==5){
            $where['img'] = ['<>',''];
        }else{
        	$typeArr = array('1'=>'0,1,2,3,4,5','2'=>'4,5','3'=>'3','4'=>'0,1,2');
            $where['ceil((deliver_rank + goods_rank + service_rank) / 3)'] = ['in',$typeArr[$commentType]];
        }
        $count = Db::name('goods_comment')->where($where)->count();                
        
        $page = new AjaxPage($count,2);
        $show = $page->show();   
       
        $list = Db::name('goods_comment')->alias('c')->join('__MEMBER__ u','u.userid = c.userid','LEFT')->where($where)->order("add_time desc")->limit($page->firstRow.','.$page->listRows)->select();
         
        $replyList = Db::name('goods_comment')->where(['is_show'=>1,'goods_id'=>$goods_id,'parent_id'=>['>',0]])->order("add_time desc")->select();
        
        foreach($list as $k => $v){
            $list[$k]['img'] = unserialize($v['img']); // 晒单图片            
        }        
        $this->assign('commentlist',$list);// 商品评论
        $this->assign('replyList',$replyList); // 管理员回复
        $this->assign('page',$show);// 赋值分页输出        
        return $this->fetch();        
    }    
    
    /**
     *  商品咨询
     */
    public function goodsConsult(){
        //  form表单提交
        config('TOKEN_ON',true);        
        $goods_id = input("goods_id/d",'0'); // 商品id
        $consult_type = input("consult_type",'1'); // 商品咨询类型
        $username = input("username",'匿名用户'); // 网友咨询
        $content = input("content"); // 咨询内容
        
        //验证码验证
		if(!captcha_check(input('verify_code'))){
	    	return $this->error("验证码错误");
   		}
        $result = $this->validate(input('post.'),['__token__'=>'require|token'],['__token__'=>'你已经提交过了']);
        if (true !== $result) {
            $this->error($result, url('/Home/Goods/goodsInfo',array('id'=>$goods_id)));             
            exit;
        }                
       
        $goodsConsult = Db::name('goods_consult');       
        $data = array(
            'goods_id'=>$goods_id,
            'consult_type'=>$consult_type,
            'username'=>$username,
            'content'=>$content,
            'add_time'=>time(),
        );        
        $goodsConsult->insert($data);        
        $this->success('咨询已提交!', url('/Home/Goods/goodsInfo',['id'=>$goods_id]));
    }
    
    /**
     * 用户收藏某一件商品
     * @param type $goods_id
     */
    public function collect_goods(){
        $goods_id = input('goods_id/d');
        $goodsLogic = new GoodsLogic();        
        $result = $goodsLogic->collect_goods(cookie('userid'),$goods_id);
        exit(json_encode($result));
    }
    
    /**
     * 加入购物车弹出
     */
    public function open_add_cart(){        
         return $this->fetch();
    }

    /**
     * 积分商城
     */
    public function integralMall(){
        $cat_id = input('get.id/d');
        $minValue = input('get.minValue');
        $maxValue = input('get.maxValue');
        $brandType = input('get.brandType');
        $point_rate = config('config.point_rate');
        $is_new = input('get.is_new',0);
        $exchange = input('get.exchange',0);
        $goods_where = array(
            'is_on_sale' => 1,  //是否上架
        );
        //积分兑换筛选
        $exchange_integral_where_array = array(array('gt',0));
        // 分类id
        if (!empty($cat_id)) {
            $goods_where['cat_id'] = array('in', getCatGrandson($cat_id));
        }
        //积分截止范围
        if (!empty($maxValue)) {
            array_push($exchange_integral_where_array, array('elt', $maxValue));
        }
        //积分起始范围
        if (!empty($minValue)) {
            array_push($exchange_integral_where_array, array('egt', $minValue));
        }
        //积分+金额
        if ($brandType == 1) {
            array_push($exchange_integral_where_array, array('exp', ' < shop_price* ' . $point_rate));
        }
        //全部积分
        if ($brandType == 2) {
            array_push($exchange_integral_where_array, array('exp', ' = shop_price* ' . $point_rate));
        }
        //新品
        if($is_new == 1){
            $goods_where['is_new'] = $is_new;
        }
        //我能兑换
        $userid = cookie('userid');
        if ($exchange == 1 && !empty($userid)) {
            $user_pay_points = intval(Db::name('member')->where(array('userid' => $userid))->value('mypoints'));
            if ($user_pay_points !== false) {
                array_push($exchange_integral_where_array, array('lt', $user_pay_points));
            }
        }

        $goods_where['exchange_integral'] =  $exchange_integral_where_array;
        $goods_list_count = Db::name('goods')->where($goods_where)->count();   //总页数
        $page = new Page($goods_list_count, 15);
        $goods_list = Db::name('goods')->where($goods_where)->limit($page->firstRow . ',' . $page->listRows)->select();
        $goods_category = Db::name('goods_category')->where(array('level' => 1))->select();

        $this->assign('goods_list', $goods_list);
        $this->assign('page', $page->show());
        $this->assign('goods_list_count',$goods_list_count);
        $this->assign('goods_category', $goods_category);//商品1级分类
        $this->assign('point_rate', $point_rate);//兑换率
        $this->assign('nowPage',$page->nowPage);// 当前页
        $this->assign('totalPages',$page->totalPages);//总页数
        return $this->fetch();
    }
}