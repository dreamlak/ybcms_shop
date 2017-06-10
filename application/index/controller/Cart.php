<?php
/**
 * 购物车
 * ============================================================================
 * 版权所有 Ybcms开发团队，并保留所有权利
 * 网站地址: http://www.ybcms.com
 * ============================================================================
 * Author: ybcms(dreamlak@qq.com)      
 * UpDate: 2017-03-01
 */
namespace app\index\controller; 
use app\index\logic\CartLogic;
use app\index\model\Pickup;
use app\index\model\UserAddress;
use think\Controller;
use think\Db;
class Cart extends Base {
    
    public $cartLogic; // 购物车逻辑操作类
    public $userid = 0;
    public $user = array();    
    /**
     * 初始化函数
     */
    public function _initialize() {
        parent::_initialize();
        $this->cartLogic = new CartLogic();
        if(session('?user')){
        	$user = session('user');
            $user = Db::name('member')->where("userid", $user['userid'])->find();
            session('user',$user);  //覆盖session 中的 user
        	$this->user = $user;
        	$this->userid = $user['userid'];
        	$this->assign('user',$user); //存储用户信息
            // 给用户计算会员价 登录前后不一样
            if($user){
                $user[discount] = (empty($user[discount])) ? 1 : $user[discount];
                execute("update `__PREFIX__cart` set member_goods_price = goods_price * {$user[discount]} where (userid ={$user[userid]} or session_id = '{$this->session_id}') and prom_type = 0");
            }
        }                        
    }

    public function cart(){
        return $this->fetch();
    }
    
    public function index(){
    	return $this->fetch('cart');
    }

    /**
     * ajax 将商品加入购物车
     */
    function ajaxAddCart(){
        
        $goods_id = input("goods_id/d"); // 商品id
        $goods_num = input("goods_num/d");// 商品数量
        $goods_spec = input("goods_spec/a",array()); // 商品规格                
        $result = $this->cartLogic->addCart($goods_id, $goods_num, $goods_spec,$this->session_id,$this->userid); // 将商品加入购物车                     
        exit(json_encode($result));       
    }
    
    /**
     * ajax 删除购物车的商品
     */
    public function ajaxDelCart(){       
        $ids = input("ids"); // 商品 ids        
        $result = Db::name("cart")->where("id", "in", $ids)->delete(); // 删除id为5的用户数据
        $return_arr = array('status'=>1,'msg'=>'删除成功','result'=>''); // 返回结果状态       
        exit(json_encode($return_arr));
    }
    
    
    /*
     * ajax 请求获取购物车列表
     */
    public function ajaxCartList(){
        $post_goods_num = input("goods_num/a",array()); // goods_num 购物车商品数量
        $post_cart_select = input("cart_select/a",array()); // 购物车选中状态
        $where['session_id'] = $this->session_id;// 默认按照 session_id 查询
        // 如果这个用户已经等了则按照用户id查询
        if($this->userid){
            unset($where);
            $where['userid'] = $this->userid;
        }
        $cartList = Db::name('Cart')->where($where)->column("id,goods_num,selected,prom_type,prom_id");
        if($post_goods_num){
            // 修改购物车数量 和勾选状态
            foreach($post_goods_num as $key => $val){   
                $data['goods_num'] = $val < 1 ? 1 : $val;
                
                if($cartList[$key]['prom_type'] == 1){ //限时抢购 不能超过购买数量
                    $flash_sale = Db::name('flash_sale')->where("id", $cartList[$key]['prom_id'])->find();
                    $data['goods_num'] = $data['goods_num'] > $flash_sale['buy_limit'] ? $flash_sale['buy_limit'] : $data['goods_num'];
                }
                
                $data['selected'] = $post_cart_select[$key] ? 1 : 0 ;                               
                if(($cartList[$key]['goods_num'] != $data['goods_num']) || ($cartList[$key]['selected'] != $data['selected'])) 
                    Db::name('cart')->where("id", $key)->update($data);
            }
            $this->assign('select_all', input('post.select_all')); // 全选框
        }
                
        $result = $this->cartLogic->cartList($this->user, $this->session_id,1,1); // 选中的商品        
        if(empty($result['total_price']))
            $result['total_price'] = Array( 'total_fee' =>0, 'cut_fee' =>0, 'num' => 0);
        
        $this->assign('cartList', $result['cartList']); // 购物车的商品                
        $this->assign('total_price', $result['total_price']); // 总计
        return $this->fetch('ajax_cart_list');
    }
    /**
     * 购物车第二步确定页面
     */
    public function cart2(){        
        if($this->userid == 0){
            $this->error('请先登陆',url('index/User/login'));
		}
        if($this->cartLogic->cart_count($this->userid,1) == 0 ){
            $this->error ('你的购物车没有选中商品','Cart/cart');
		}
        $result = $this->cartLogic->cartList($this->user, $this->session_id,1,1); // 获取购物车商品        
        $shippingList = Db::name('plugin')->where("`type` = 'shipping' and status = 1")->cache(true,CACHE_TIME)->select();// 物流公司                
        
        //$Model = new \think\Model(); // 找出这个用户的优惠券 没过期的  并且 订单金额达到 condition 优惠券指定标准的               
        $sql = "select c1.name,c1.money,c1.condition, c2.* from __PREFIX__coupon as c1 inner join __PREFIX__coupon_list as c2  on c2.cid = c1.id and c1.type in(0,1,2,3) and order_id = 0  where c2.uid = :userid  and ".time()." < c1.use_end_time and c1.condition <= :total_fee";
        $couponList = query($sql,['userid'=>$this->userid,'total_fee'=>$result['total_price']['total_fee']]);
               
        $this->assign('couponList', $couponList); // 优惠券列表
        $this->assign('shippingList', $shippingList); // 物流公司
        $this->assign('cartList', $result['cartList']); // 购物车的商品                
        $this->assign('total_price', $result['total_price']); // 总计                               
        return $this->fetch();
    }
   
    /*
     * ajax 获取用户收货地址 用于购物车确认订单页面
     */
    public function ajaxAddress(){
        $address_list = Db::name('member_address')->where(['userid'=>$this->userid,'is_pickup'=>0])->select();
        if($address_list){
        	$area_id = array();
        	foreach ($address_list as $val){
        		$area_id[] = $val['province'];
                        $area_id[] = $val['city'];
                        $area_id[] = $val['district'];
                        $area_id[] = $val['twon'];                        
        	}    
                $area_id = array_filter($area_id);
        	$area_id = implode(',', $area_id);
        	$regionList = Db::name('areas')->where("id", "in", $area_id)->column('id,name');
        	$this->assign('regionList', $regionList);
        }
        $address_where['is_default'] = 1;
        $c = Db::name('member_address')->where(['userid'=>$this->userid,'is_default'=>1,'is_pickup'=>0])->count(); // 看看有没默认收货地址
        if((count($address_list) > 0) && ($c == 0)) // 如果没有设置默认收货地址, 则第一条设置为默认收货地址
            $address_list[0]['is_default'] = 1;
        $this->assign('address_list', $address_list);
        return $this->fetch('ajax_address');
    }

    public function test(){
        $userid = 18991;
        echo crc32($userid);
    }

    /**
     * @author dyr
     * @time 2016.08.22
     * 获取自提点信息
     */
    public function ajaxPickup(){
        $province_id = input('province_id/d');
        $city_id = input('city_id/d');
        $district_id = input('district_id/d');
        if (empty($province_id) || empty($city_id) || empty($district_id)) {
            exit("<script>alert('参数错误');</script>");
        }
        $user_address = new UserAddress();
        $address_list = $user_address->getUserPickup($this->userid);
        $pickup = new Pickup();
        $pickup_list = $pickup->getPickupItemByPCD($province_id, $city_id, $district_id);
        $this->assign('pickup_list', $pickup_list);
        $this->assign('address_list', $address_list);
        return $this->fetch('ajax_pickup');
    }

    /**
     * @author dyr
     * @time 2016.08.22
     * 更换自提点
     */
    public function replace_pickup()
    {
        $province_id = input('get.province_id/d');
        $city_id = input('get.city_id/d');
        $district_id = input('get.district_id/d');
        $region_model = Db::name('areas');
        $call_back = input('get.call_back');
        if (request()->isPost()||request()->isAjax()) {
            echo "<script>parent.{$call_back}('success');</script>";
            exit(); // 成功
        }
        $address = array('province' => $province_id, 'city' => $city_id, 'district' => $district_id);
        $p = $region_model->where(array('pid' => 0))->select();
        $c = $region_model->where(array('pid' => $province_id))->select();
        $d = $region_model->where(array('pid' => $city_id))->select();
        $this->assign('province', $p);
        $this->assign('city', $c);
        $this->assign('district', $d);
        $this->assign('address', $address);
        $this->assign('call_back', $call_back);
        return $this->fetch();
    }

    /**
     * @author dyr
     * @time 2016.08.22
     * 更换自提点
     */
    public function ajax_PickupPoint()
    {
        $province_id = input('province_id/d');
        $city_id = input('city_id/d');
        $district_id = input('district_id/d');
        $pick_up_model = new Pickup();
        $pick_up_list = $pick_up_model->getPickupListByPCD($province_id,$city_id,$district_id);
        exit(json_encode($pick_up_list));
    }


    /**
     * ajax 获取订单商品价格 或者提交 订单
     */
    public function cart3(){
        if($this->userid == 0){
            exit(json_encode(array('status'=>-100,'msg'=>"登录超时请重新登录!",'result'=>null))); // 返回结果状态
		}
        $address_id = input("address_id/d"); //  收货地址id
        $shipping_code =  input("shipping_code"); //  物流编号        
        $invoice_title = input('invoice_title'); // 发票
        $coupon_id =  input("coupon_id/d"); //  优惠券id
        $couponCode =  input("couponCode"); //  优惠券代码
        $pay_points =  input("pay_points/d",0); //  使用积分
        $user_money =  input("user_money/f",0); //  使用余额        
        $user_money = $user_money ? $user_money : 0;

        if($this->cartLogic->cart_count($this->userid,1) == 0 ) exit(json_encode(array('status'=>-2,'msg'=>'你的购物车没有选中商品','result'=>null))); // 返回结果状态
        if(!$address_id) exit(json_encode(array('status'=>-3,'msg'=>'请先填写收货人信息','result'=>null))); // 返回结果状态
        if(!$shipping_code) exit(json_encode(array('status'=>-4,'msg'=>'请选择物流信息','result'=>null))); // 返回结果状态
		
		$address = Db::name('member_address')->where("id", $address_id)->find();
		$order_goods = Db::name('cart')->where(['userid'=>$this->userid,'selected'=>1])->select();
        $result = calculate_price($this->userid,$order_goods,$shipping_code,0,$address[province],$address[city],$address[district],$pay_points,$user_money,$coupon_id,$couponCode);
		if($result['status'] < 0){
			exit(json_encode($result));      	
		}
		// 订单满额优惠活动		                
        $order_prom = get_order_promotion($result['result']['order_amount']);
        $result['result']['order_amount'] = $order_prom['order_amount'] ;
        $result['result']['order_prom_id'] = $order_prom['order_prom_id'] ;
        $result['result']['order_prom_amount'] = $order_prom['order_prom_amount'] ;
        
        $car_price = array(
            'postFee'      => $result['result']['shipping_price'], // 物流费
            'couponFee'    => $result['result']['coupon_price'], // 优惠券            
            'balance'      => $result['result']['user_money'], // 使用用户余额
            'pointsFee'    => $result['result']['integral_money'], // 积分支付            
            'payables'     => number_format($result['result']['order_amount'], 2, '.', ''), // 应付金额
            'goodsFee'     => $result['result']['goods_price'],// 商品价格            
            'order_prom_id' => $result['result']['order_prom_id'], // 订单优惠活动id
            'order_prom_amount' => $result['result']['order_prom_amount'], // 订单优惠活动优惠了多少钱
        );
       
        // 提交订单        
        if($_REQUEST['act'] == 'submit_order'){  
            if(empty($coupon_id) && !empty($couponCode))
               $coupon_id = Db::name('coupon_list')->where("code", $couponCode)->value('id');
            $result = $this->cartLogic->addOrder($this->userid,$address_id,$shipping_code,$invoice_title,$coupon_id,$car_price); // 添加订单                        
            exit(json_encode($result));            
        }
            $return_arr = array('status'=>1,'msg'=>'计算成功','result'=>$car_price); // 返回结果状态
            exit(json_encode($return_arr));           
    }	
    /**
     * ajax 获取订单商品价格 或者提交 订单
	 * 已经用心方法 这个方法 cart9  准备作废
     */
   
    /*
     * 订单支付页面
     */
    public function cart4(){
        
        $order_id = input('order_id/d');
        $order = Db::name('Order')->where("order_id", $order_id)->find();
        
        // 如果已经支付过的订单直接到订单详情页面. 不再进入支付页面
        if($order['pay_status'] == 1){            
            $order_detail_url = url("index/User/order_detail",array('id'=>$order_id));
            header("Location: $order_detail_url");
            exit;
        }
        //如果是预售订单，支付尾款
        if($order['pay_status'] == 2 && $order['order_prom_type'] == 4){
            $pre_sell_info = Db::name('goods_activity')->where(array('act_id'=>$order['order_prom_id']))->find();
            $pre_sell_info = array_merge($pre_sell_info,unserialize($pre_sell_info['ext_info']));
            if($pre_sell_info['retainage_start'] > time()){
                $this->error('还未到支付尾款时间'.date('Y-m-d H:i:s',$pre_sell_info['retainage_start']));
            }
            if($pre_sell_info['retainage_end'] < time()){
                $this->error('对不起，该预售商品已过尾款支付时间'.date('Y-m-d H:i:s',$pre_sell_info['retainage_start']));
            }
        }
        $payment_where = array(
            'type'=>'payment',
            'status'=>1,
            'scene'=>array('in',array(0,2))
        );
        if($order['order_prom_type'] == 4){
            $payment_where['code'] = array('neq','cod');
        }
        $paymentList = Db::name('plugin')->where($payment_where)->select();
        $paymentList = convert_arr_key($paymentList, 'code');
        
        foreach($paymentList as $key => $val){
            $val['config_value'] = unserialize($val['config_value']);            
            if($val['config_value']['is_bank'] == 2)
            {
                $bankCodeList[$val['code']] = unserialize($val['bank_code']);        
            }                
        }                
        
        $bank_img = include APP_PATH.'index/bank.php'; // 银行对应图片        
        $this->assign('paymentList',$paymentList);        
        $this->assign('bank_img',$bank_img);
        $this->assign('order',$order);
        $this->assign('bankCodeList',$bankCodeList);        
        $this->assign('pay_date',date('Y-m-d', strtotime("+1 day")));

        return $this->fetch();
    }
 
    
    //ajax 请求购物车列表
    public function header_cart_list(){
    	$cart_result = $this->cartLogic->cartList($this->user, $this->session_id,0,1);
    	if(empty($cart_result['total_price']))
    		$cart_result['total_price'] = Array( 'total_fee' =>0, 'cut_fee' =>0, 'num' => 0);
    	
    	$this->assign('cartList', $cart_result['cartList']); // 购物车的商品
    	$this->assign('cart_total_price', $cart_result['total_price']); // 总计
        $template = input('template','header_cart_list');    	 
        return $this->fetch($template);		 
    }
	
	//ajax 请求购物车数量
	public function ajax_cart_num(){
		$cnum=getcartnum();
		return $cnum;
	}
    /**
     * 预售商品下单流程
     */
    public function pre_sell_cart()
    {
        $act_id = input('act_id/d');
        $goods_num = input('goods_num/d');
        if(empty($act_id)){
            $this->error('没有选择需要购买商品');
        }
        if(empty($goods_num)){
            $this->error('购买商品数量不能为0', url('index/Activity/pre_sell', array('act_id' => $act_id)));
        }
        if($this->userid == 0){
            $this->error('请先登陆');
        }
        $pre_sell_info = Db::name('goods_activity')->where(array('act_id' => $act_id, 'act_type' => 1))->find();
        if(empty($pre_sell_info)){
            $this->error('商品不存在或已下架',U('index/Activity/pre_sell_list'));
        }
        $pre_sell_info = array_merge($pre_sell_info, unserialize($pre_sell_info['ext_info']));
        if ($pre_sell_info['act_count'] + $goods_num > $pre_sell_info['restrict_amount']) {
            $buy_num = $pre_sell_info['restrict_amount'] - $pre_sell_info['act_count'];
            $this->error('预售商品库存不足，还剩下' . $buy_num . '件', U('index/Activity/pre_sell', array('id' => $act_id)));
        }
        $pre_count_info = Db::name('goods_activity')->getPreCountInfo($pre_sell_info['act_id'], $pre_sell_info['goods_id']);//预售商品的订购数量和订单数量
        $pre_sell_price['cut_price'] = Db::name('goods_activity')->getPrePrice($pre_count_info['total_goods'], $pre_sell_info['price_ladder']);//预售商品价格
        $pre_sell_price['goods_num'] = $goods_num;
        $pre_sell_price['deposit_price'] = floatval($pre_sell_info['deposit']);
        // 提交订单
        if ($_REQUEST['act'] == 'submit_order') {
            $invoice_title = input('invoice_title'); // 发票
            $shipping_code =  input("shipping_code"); //  物流编号
            $address_id = input("address_id/d"); //  收货地址id
            if(empty($address_id)){
                exit(json_encode(array('status'=>-3,'msg'=>'请先填写收货人信息','result'=>null))); // 返回结果状态
            }
            if(empty($shipping_code)){
                exit(json_encode(array('status'=>-4,'msg'=>'请选择物流信息','result'=>null))); // 返回结果状态
            }
            $cart_logic = new CartLogic();
            $result = $cart_logic->addPreSellOrder($this->userid, $address_id, $shipping_code, $invoice_title, $act_id, $pre_sell_price); // 添加订单
            exit(json_encode($result));
        }
        $shippingList = Db::name('Plugin')->where("`type` = 'shipping' and status = 1")->select();// 物流公司
        $this->assign('pre_sell_info', $pre_sell_info);// 购物车的预售商品
        $this->assign('shippingList', $shippingList); // 物流公司
        $this->assign('pre_sell_price',$pre_sell_price);
        return $this->fetch();
    }
}
