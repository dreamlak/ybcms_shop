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
namespace app\mobile\controller;
use think\Db;
class Cart extends MobileBase {
    
    public $cartLogic; // 购物车逻辑操作类    
    public $userid = 0;
    public $user = array();        
    /**
     * 析构流函数
     */
    public function  __construct() {   
        parent::__construct();                
        $this->cartLogic = new \app\home\logic\CartLogic();
        if(session('?user')){
        	$user = session('user');
            $user = Db::name('member')->where("userid", $user['userid'])->find();
            session('user',$user);  //覆盖session 中的 user               			                
        	$this->user = $user;
        	$this->userid = $user['userid'];
        	$this->assign('user',$user); //存储用户信息
            // 给用户计算会员价 登录前后不一样
            if($user){
                $user['discount'] = (empty($user['discount'])) ? 1 : $user['discount'];//会员折扣，默认1不享受
                execute("update `__PREFIX__cart` set member_goods_price = goods_price * {$user[discount]} where (userid ={$user[userid]} or session_id = '{$this->session_id}') and prom_type = 0");
            }
         }            
    }
    
    public function cart(){
        //获取热卖商品
        $hot_goods = Db::name('Goods')->where('is_hot=1 and is_on_sale=1')->limit(20)->cache(true,CACHE_TIME)->select();
        $this->assign('hot_goods',$hot_goods);
        return $this->fetch('cart');
    }
    /**
     * 将商品加入购物车
     */
    function addCart(){
        $goods_id = input("goods_id/d"); // 商品id
        $goods_num = input("goods_num/d");// 商品数量
        $goods_spec = input("goods_spec"); // 商品规格                
        $goods_spec = json_decode($goods_spec,true); //app 端 json 形式传输过来
        $unique_id = input("unique_id"); // 唯一id  类似于 pc 端的session id
        $userid = input("userid/d",0); // 用户id        
        $result = $this->cartLogic->addCart($goods_id, $goods_num, $goods_spec,$unique_id,$userid); // 将商品加入购物车
        exit(json_encode($result)); 
    }
    /**
     * ajax 将商品加入购物车
     */
    function ajaxAddCart(){
        $goods_id = input("goods_id/d"); // 商品id
        $goods_num = input("goods_num/d");// 商品数量
        $goods_spec = input("goods_spec/a",array()); //商品规格
        $result = $this->cartLogic->addCart($goods_id, $goods_num, $goods_spec,$this->session_id,$this->userid); // 将商品加入购物车
        exit(json_encode($result));
    }

    /*
     * 请求获取购物车列表
     */
    public function cartList(){
        $cart_form_data = input('cart_form_data'); // goods_num 购物车商品数量
        $cart_form_data = json_decode($cart_form_data,true); //app 端 json 形式传输过来

        $unique_id = input("unique_id"); // 唯一id  类似于 pc 端的session id
        $userid = input("userid/d"); // 用户id
        $where['session_id'] = $unique_id; // 默认按照 $unique_id 查询
        if($userid){
            $where['userid'] = $userid;
        }
        $cartList = Db::name('cart')->where($where)->column("id,goods_num,selected");

        if($cart_form_data){
            // 修改购物车数量 和勾选状态
            foreach($cart_form_data as $key => $val)
            {
                $data['goods_num'] = $val['goodsNum'];
                $data['selected'] = $val['selected'];
                $cartID = $val['cartID'];
                if(($cartList[$cartID]['goods_num'] != $data['goods_num']) || ($cartList[$cartID]['selected'] != $data['selected']))
                    Db::name('cart')->where("id", $cartID)->save($data);
            }
        }

        $result = $this->cartLogic->cartList($this->user, $unique_id,0);
        exit(json_encode($result));
    }

    /**
     * 购物车第二步确定页面
     */
    public function cart2(){
        if($this->userid == 0){
            $this->error('请先登陆',url('Mobile/User/login'));
		}
        $address_id = input('address_id/d');
        if($address_id){
            $address = Db::name('member_address')->where("id", $address_id)->find();
		}else{
            $address = Db::name('member_address')->where(['userid'=>$this->userid,'is_default'=>1])->find();
		}
        if(empty($address)){
        	header("Location: ".url('Mobile/User/add_address',array('source'=>'cart2')));
        }else{
        	$this->assign('address',$address);
        }

        if($this->cartLogic->cart_count($this->userid,1) == 0 ){
            $this->error ('你的购物车没有选中商品','Cart/cart');
		}
        $result = $this->cartLogic->cartList($this->user, $this->session_id,1,1); // 获取购物车商品
        $shippingList = Db::name('Plugin')->where("`type` = 'shipping' and status = 1")->cache(true,CACHE_TIME)->select();// 物流公司

        // 找出这个用户的优惠券 没过期的  并且 订单金额达到 condition 优惠券指定标准的
        $sql = "select c1.name,c1.money,c1.condition, c2.* from __PREFIX__coupon as c1 inner join __PREFIX__coupon_list as c2  on c2.cid = c1.id and c1.type in(0,1,2,3) and order_id = 0  where c2.uid = {$this->userid} and ".time()." < c1.use_end_time and c1.condition <= {$result['total_price']['total_fee']}";
        $couponList = query($sql);
        if(input('cid/d') != ''){
            $cid = input('cid/d');
            $checkconpon = Db::name('coupon')->field('id,name,money')->where("id = $cid")->find();    //要使用的优惠券
            $checkconpon['lid'] = input('lid/d');
        }
        $this->assign('couponList', $couponList); // 优惠券列表
        $this->assign('shippingList', $shippingList); // 物流公司
        $this->assign('cartList', $result['cartList']); // 购物车的商品
        $this->assign('total_price', $result['total_price']); // 总计
        $this->assign('checkconpon', $checkconpon); // 使用的优惠券
        return $this->fetch();
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
        $couponTypeSelect =  input("couponTypeSelect"); //  优惠券类型  1 下拉框选择优惠券 2 输入框输入优惠券代码
        $coupon_id =  input("coupon_id/d"); //  优惠券id
        $couponCode =  input("couponCode"); //  优惠券代码
        $pay_points =  input("pay_points/d",0); //  使用积分
        $user_money =  input("user_money/f",0); //  使用余额
        $user_note = trim(input('user_note'));   //买家留言
        $user_money = $user_money ? $user_money : 0;
		
        if($this->cartLogic->cart_count($this->userid,1) == 0 ) exit(json_encode(array('status'=>-2,'msg'=>'你的购物车没有选中商品','result'=>null))); // 返回结果状态
        if(!$address_id) exit(json_encode(array('status'=>-3,'msg'=>'请先填写收货人信息','result'=>null))); // 返回结果状态
        if(!$shipping_code) exit(json_encode(array('status'=>-4,'msg'=>'请选择物流信息','result'=>null))); // 返回结果状态
		
		$address = Db::name('member_address')->where("id", $address_id)->find();
		$order_goods = Db::name('cart')->where(['userid'=>$this->userid,'selected'=>1])->select();
        $result = calculate_price($this->userid,$order_goods,$shipping_code,0,$address['province'],$address['city'],$address['district'],$pay_points,$user_money,$coupon_id,$couponCode);
        //dump($result['result']);die;
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
            'payables'     => $result['result']['order_amount'], // 应付金额
            'goodsFee'     => $result['result']['goods_price'],// 商品价格
            'order_prom_id' => $result['result']['order_prom_id'], // 订单优惠活动id
            'order_prom_amount' => $result['result']['order_prom_amount'], // 订单优惠活动优惠了多少钱            
        );
       
        // 提交订单        
        if($_REQUEST['act'] == 'submit_order'){  
            if(empty($coupon_id) && !empty($couponCode)){
                $coupon_id = Db::name('coupon_list')->where("code", $couponCode)->value('id');
            }
            $result = $this->cartLogic->addOrder($this->userid,$address_id,$shipping_code,$invoice_title,$coupon_id,$car_price,$user_note); // 添加订单
            exit(json_encode($result));
        }
            $return_arr = array('status'=>1,'msg'=>'计算成功','result'=>$car_price); // 返回结果状态
            exit(json_encode($return_arr));
    }	
    /*
     * 订单支付页面
     */
    public function cart4(){
        $order_id = input('order_id/d');
        $order = Db::name('order')->where("order_id", $order_id)->find();
        // 如果已经支付过的订单直接到订单详情页面. 不再进入支付页面
        if($order['pay_status'] == 1){
            $order_detail_url = url("Mobile/User/order_detail",['id'=>$order_id]);
            header("Location: $order_detail_url");
            exit;
        }
		$payment_where=['type'=>'payment'];
        if(strstr($_SERVER['HTTP_USER_AGENT'],'MicroMessenger')){
            //微信浏览器
            if($order['order_prom_type'] == 4){
                //预售订单
                $payment_where['code'] = 'weixin';
            }else{
                $payment_where['code'] = array('in',array('weixin','cod'));
            }
        }else{
            if($order['order_prom_type'] == 4){
                //预售订单
                $payment_where['code'] = array('neq','cod');
            }
            $payment_where['scene'] = array('in',array('0','1'));
        }
		$payment_where['status']=1;
        $paymentList = Db::name('plugin')->where($payment_where)->select();
        $paymentList = convert_arr_key($paymentList, 'code');

        foreach($paymentList as $key => $val){
            $val['config_value'] = unserialize($val['config_value']);
            if($val['config_value']['is_bank'] == 2){
                $bankCodeList[$val['code']] = unserialize($val['bank_code']);
            }
            //判断当前浏览器显示支付方式
            if(($key == 'weixin' && !is_weixin()) || ($key == 'alipayMobile' && is_weixin())){
                unset($paymentList[$key]);
            }
        }

        $bank_img = include APP_PATH.'home/bank.php'; // 银行对应图片
        //$payment = Db::name('plugin')->where("`type`='payment' and status = 1")->select();
        $this->assign('paymentList',$paymentList);
        $this->assign('bank_img',$bank_img);
        $this->assign('order',$order);
        $this->assign('bankCodeList',$bankCodeList);
        $this->assign('pay_date',date('Y-m-d', strtotime("+1 day")));
        return $this->fetch();
    }


    /*
    * ajax 请求获取购物车列表
    */
    public function ajaxCartList(){
        $post_goods_num = input("goods_num/a"); // goods_num 购物车商品数量
        $post_cart_select = input("cart_select/a"); // 购物车选中状态
        
        $where['session_id'] = $this->session_id; // 默认按照 session_id 查询
        // 如果这个用户已经等了则按照用户id查询
        if($this->userid){
            unset($where);
            $where['userid'] = $this->userid;
        }
        $cartList = Db::name('cart')->where($where)->column("id,goods_num,selected,prom_type,prom_id");
		
        if($post_goods_num){
            // 修改购物车数量 和勾选状态
            foreach($post_goods_num as $key => $val) {                
                $data['goods_num'] = $val < 1 ? 1 : $val;
                if($cartList[$key]['prom_type'] == 1){ //限时抢购 不能超过购买数量
                    $flash_sale = Db::name('flash_sale')->where("id", $cartList[$key]['prom_id'])->find();
                    $data['goods_num'] = $data['goods_num'] > $flash_sale['buy_limit'] ? $flash_sale['buy_limit'] : $data['goods_num'];
                }
                
                $data['selected'] = $post_cart_select[$key] ? 1 : 0 ;
                if(($cartList[$key]['goods_num'] != $data['goods_num']) || ($cartList[$key]['selected'] != $data['selected'])){
                    Db::name('Cart')->where("id", $key)->update($data);
				}
            }
            //$this->assign('select_all', input('select_all')); // 全选框
        }
		
        $result = $this->cartLogic->cartList($this->user, $this->session_id,1,1);        
        if(empty($result['total_price'])){
            $result['total_price'] = [ 'total_fee' =>0, 'cut_fee' =>0, 'num' => 0, 'atotal_fee' =>0, 'acut_fee' =>0, 'anum' => 0];
        }
        $this->assign('cartList', $result['cartList']); // 购物车的商品                
        $this->assign('total_price', $result['total_price']); // 总计
        
        return $this->fetch('ajax_cart_list');
    }

    /*
 	* ajax 获取用户收货地址 用于购物车确认订单页面
 	*/
    public function ajaxAddress(){
		//地区列表
        $regionList = Db::name('areas')->column('id,name');

        $address_list = Db::name('member_address')->where("userid", $this->userid)->select();
        $c = Db::name('member_address')->where("userid = {$this->userid} and is_default = 1")->count(); // 看看有没默认收货地址
        if((count($address_list) > 0) && ($c == 0)) // 如果没有设置默认收货地址, 则第一条设置为默认收货地址
            $address_list[0]['is_default'] = 1;

        $this->assign('regionList', $regionList);
        $this->assign('address_list', $address_list);
        return $this->fetch('ajax_address');
    }

    /**
     * ajax 删除购物车的商品
     */
    public function ajaxDelCart(){
        $ids = input("ids"); // 商品 ids
        $result = Db::name("cart")->where("id","in",$ids)->delete(); // 删除id为5的用户数据
        $return_arr = array('status'=>1,'msg'=>'删除成功','result'=>''); // 返回结果状态
        exit(json_encode($return_arr));
    }

}
