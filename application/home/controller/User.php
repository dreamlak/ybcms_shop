<?php
/**
 * 用户
 * ============================================================================
 * 版权所有 Ybcms开发团队，并保留所有权利
 * 网站地址: http://www.ybcms.com
 * ============================================================================
 * Author: ybcms(dreamlak@qq.com)      
 * UpDate: 2017-03-01
 */
namespace app\home\controller; 
use app\home\logic\UsersLogic;
use app\home\logic\CartLogic;
use app\home\model\Message;
use think\Controller;
use think\Validate;
use think\Url;
use think\Page;
use think\Config;
use think\Verify;
use think\Db;
class User extends Base{
	public $userid = 0;
	public $user = array();
	public function _initialize() {      
        parent::_initialize();
        if(session('?user')){
        	$user = session('user');
            $user = Db::name('member')->where("userid", $user['userid'])->find();
            session('user',$user);  //覆盖session 中的 user               
        	$this->user = $user;
        	$this->userid = $user['userid'];
        	$this->assign('user',$user); //存储用户信息
        	$this->assign('userid',$this->userid);
        }else{
        	$nologin = array(
    			'login','pop_login','do_login','logout','verify','set_pwd','finished',
    			'verifyHandle','reg','send_sms_reg_code','identity','check_validate_code',
    			'forget_pwd','check_captcha','check_username','send_validate_code','regprotocol',
        	);
        	if(!in_array($this->request->action(),$nologin)){
                $this->redirect('Home/User/login');
        		exit;
        	}
        }
        //用户中心面包屑导航
        $navigate_user = navigate_user();
        $this->assign('navigate_user',$navigate_user);        
    }
    //用户中心首页
    public function index(){
        $logic = new UsersLogic();
        $user = $logic->get_info($this->userid);
        $user = $user['result'];
        $level = Db::name('member_level')->select();
        $level = convert_arr_key($level,'id');
        $this->assign('level',$level);
        $this->assign('user',$user);
		
		$map=[];
		$map['userid']=$this->userid;
		$order_list=Db::name('order')->where($map)->select();
		$model = new UsersLogic();
		$scount=['nopay'=>0,'nofan'=>0,'nosou'=>0,'noping'=>0];
		foreach($order_list as $k=>$v){
			$order_list[$k] = set_btn_order_status($v);  //添加属性  包括按钮显示属性 和 订单状态显示属性
			$data = $model->get_order_goods($v['order_id']);
			$order_list[$k]['goods_list'] = $data['result'];
			if($order_list[$k]['order_prom_type'] == 4){
                $pre_sell_item =  Db::name('goods_activity')->where(['act_id'=>$order_list[$k]['order_prom_id']])->find();
                $pre_sell_item = array_merge($pre_sell_item,unserialize($pre_sell_item['ext_info']));
                $order_list[$k]['pre_sell_is_finished'] = $pre_sell_item['is_finished'];
                $order_list[$k]['pre_sell_retainage_start'] = $pre_sell_item['retainage_start'];
                $order_list[$k]['pre_sell_retainage_end'] = $pre_sell_item['retainage_end'];
            }else{
                $order_list[$k]['pre_sell_is_finished'] = -1;//没有参与预售的订单
            }
		}
		$this->assign('order_list',$order_list);
		
		foreach($order_list as $ks=>$vs){
			if($vs['order_status_code']=='WAITPAY') $scount['nopay']+=1;
			if($vs['order_status_code']=='WAITSEND') $scount['nofan']+=1;
			if($vs['order_status_code']=='WAITRECEIVE') $scount['nosou']+=1;
			if($vs['order_status_code']=='WAITCCOMMENT') $scount['noping']+=1;
		}
		$this->assign('scount',$scount);
		
        return $this->fetch();
    }
    //账户资金==========================================================================================================
    public function account(){
    	$logic = new UsersLogic();
        $user = $logic->get_info($this->userid);
		$user=$user['result'];
		$this->assign('user',$user);
		//dump($user);die;

        //充值记录
        $count = Db::name('member_recharge')->where('userid',$this->userid)->count();
	   	$Page = new Page($count,10);
	   	$show = $Page->show();
	   	$recharge_list = Db::name('member_recharge')->where('userid',$this->userid)->order('addtime desc')->limit($Page->firstRow.','.$Page->listRows)->select();
	   	$this->assign('page',$show);
	   	$this->assign('recharge_list',$recharge_list);
		
	   	//消费记录
	   	$count2 = Db::name('member_account_log')->where(['userid'=>$this->userid,'mymoney'=>['>',0]])->count();
	   	$Page2 = new Page($count2,10);
	   	$consume_list = Db::name('member_account_log')->where(['userid'=>$this->userid,'mymoney'=>['>',0]])->order('addtime desc')->limit($Page2->firstRow.','.$Page2->listRows)->select();
	   	$this->assign('consume_list',$consume_list);
	   	$this->assign('page2',$Page2->show());
		
        return $this->fetch();
    }
    //充值
   	public  function recharge(){
   		if(request()->isPost()||request()->isAjax()){
   			$user = session('user');
   			$data['userid'] = $this->userid;
   			$data['nickname'] = $user['nickname'];
   			$data['account'] = input('account');
   			$data['ordersn'] = 'recharge'.get_rand_str(10,0,1);
   			$data['addtime'] = time();
   			$order_id = Db::name('member_recharge')->insertGetId($data);
   			if($order_id){
   				$pay_radio = empty($_REQUEST['pay_radio'])?input('pay_radio'):$_REQUEST['pay_radio'];
   				$url = url('payment/getpay',['pay_radio'=>$pay_radio,'order_id'=>$order_id]);
                $this->redirect($url);
   			}else{
   				$this->error('提交失败,参数有误!');
   			}
   		}
   		
	   	$paymentList = Db::name('plugin')->where("`type`='payment' and code!='cod' and status = 1 and  scene in(0,2)")->select();
	   	$paymentList = convert_arr_key($paymentList, 'code');	   	
	   	foreach($paymentList as $key => $val){
	   		$val['config_value'] = unserialize($val['config_value']);
	   		if($val['config_value']['is_bank'] == 2){
	   			$bankCodeList[$val['code']] = unserialize($val['bank_code']);
	   		}
	   	}
	   	$bank_img = include APP_PATH.'home/bank.php'; //银行对应图片
	   	$this->assign('paymentList',$paymentList);
	   	$this->assign('bank_img',$bank_img);
	   	$this->assign('bankCodeList',$bankCodeList);
	   	
	   	return $this->fetch();
   	}
	//积分
	public function points(){
		$mypoints=Db::name('member')->where('userid',$this->userid)->value('mypoints');
		$this->assign('mypoints',$mypoints);
		
		$map=[];
		$map['userid']=$this->userid;
		$map['mypoints']=['>',0];
		
		$count = Db::name('member_account_log')->where($map)->count();
	   	$Page = new Page($count,10);
	   	$show = $Page->show();
	   	$account_log = Db::name('member_account_log')->where($map)->limit($Page->firstRow.','.$Page->listRows)->select();
	   	$this->assign('page',$show);
	   	$this->assign('account_log',$account_log);
        
		return $this->fetch();
	} 
	//申请提现记录
    public function withdrawals(){
    	if(request()->isPost()||request()->isAjax()){
            $this->verifyHandle('withdrawals');                
    		$data = input('post.');
    		$data['userid'] = $this->userid;    		    		
    		$data['create_time'] = time();                
            $distribut_min = config('config.distribut_min'); // 最少提现额度
            if($data['money'] < $distribut_min){
                $this->error('每次最少提现额度'.$distribut_min);
                exit;
            }
            if($data['money'] > $this->user['user_money']){
                $this->error("你最多可提现{$this->user['user_money']}账户余额.");
                exit;
            }     
    		if(Db::name('withdrawals')->insert($data)){
    			$this->success("已提交申请");
                exit;
    		}else{
    			$this->error('提交失败,联系客服!');
                exit;
    		}
    	}
        
        $where['userid'] = $this->userid;
        $count = Db::name('withdrawals')->where($where)->count();
        $page = new Page($count,10);
        $show = $page->show();
        $list = Db::name('withdrawals')->where($where)->order("id desc")->limit("{$page->firstRow},{$page->listRows}")->select();   
        $this->assign('show',$show);// 赋值分页输出
        $this->assign('list',$list); // 下线
        return $this->fetch();
    }
    //优惠券列表
    public function coupon(){
        $logic = new UsersLogic();
        $data = $logic->get_coupon($this->userid,I('type'));
        $coupon_list = $data['result'];
        $this->assign('coupon_list',$coupon_list);
        $this->assign('page',$data['show']);
        $this->assign('active','coupon');
        return $this->fetch();
    }
    //登录==========================================================================================================
    public function login(){
        if($this->userid > 0){
            $this->redirect('Home/User/index');
        }
        $referurl = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : U("Home/User/index");
        $this->assign('referurl',$referurl);
        return $this->fetch();
    }
    public function do_login(){
    	$username = trim(input('username'));
    	$password = trim(input('password'));
    	$verify_code = input('verify_code');
     	if(!captcha_check($verify_code)){
	    	return $this->error("验证码错误");
   		}
    	         
    	$logic = new UsersLogic();
    	$res = $logic->login($username,$password);        
        
    	if($res['status'] == 1){
    		if(strstr(input('referurl'),'Home/index/index')||input('referurl')==''){
    			$res['url'] =  url('Home/User/index');
    		}else{
    			$res['url'] =  urldecode(input('referurl'));
    		}
    		session('user',$res['result']);
    		setcookie('userid',$res['result']['userid'],null,'/');
    		setcookie('is_distribut',$res['result']['is_distribut'],null,'/');
    		$username = empty($res['result']['username']) ? $username : $res['result']['username'];
            setcookie('uname',urlencode($username),null,'/');
            setcookie('cn',0,time()-3600,'/');
    		$cartLogic = new CartLogic();
    		$cartLogic->login_cart_handle($this->session_id,$res['result']['userid']);  //用户登录后 需要对购物车 一些操作
    	}
    	exit(json_encode($res));
    }
	//退出
    public function logout(){
    	setcookie('uname','',time()-3600,'/');
    	setcookie('cn','',time()-3600,'/');
    	setcookie('userid','',time()-3600,'/');
        session_unset();
        session_destroy();
        $this->redirect('Home/Index/index');
        exit;
    }
	//注册协议
	public function regprotocol(){
		return $this->fetch();
	}
    //注册
    public function reg(){
    	if($this->userid > 0){
            $this->redirect('Home/User/index');
        }
    	
        if(request()->isPost()||request()->isAjax()){
            $logic = new UsersLogic();
            //验证码检验
            $username = input('username','');
            $password = input('password','');
            $password2 = input('password2','');
            $tel_verify_code = input('tel_verify_code','');
            $scene = input('scene', 1);
            $session_id = session_id();
            
            //是否开启注册验证码机制
            if(check_mobile($username)){
                $reg_sms_enable = config('config.sms_is_reg');
                if($reg_sms_enable){
                    //短信功能开启时
                   	$check_code = $logic->check_validate_code($tel_verify_code, $username, 'phone', $session_id, $scene);
					
	                if($check_code['status'] != 1){
	                    return $this->error($check_code['msg']);
	                }
				}
            }
			
	     	if(!captcha_check(input('verify_code'))){
		    	return $this->error("验证码错误");
	   		}
			
            $data = $logic->reg($username,$password,$password2);
            if($data['status'] != 1){
                $this->error($data['msg']);
            }
            session('user',$data['result']);
    		setcookie('userid',$data['result']['userid'],null,'/');
    		setcookie('is_distribut',$data['result']['is_distribut'],null,'/');
            $nickname = empty($data['result']['nickname']) ? $username : $data['result']['nickname'];
            setcookie('uname',$nickname,null,'/');
			
            $cartLogic = new CartLogic();
            $cartLogic->login_cart_handle($this->session_id,$data['result']['userid']);  //用户登录后 需要对购物车 一些操作
            
            //$this->success($data['msg'],url('Home/User/index'));
			return ['status'=>1,'msg'=>$data['msg'],'url'=>url('Home/User/index')];
            exit;
        }
        $this->assign('regis_sms_enable',config('config.sms_is_reg')); //注册启用短信：
        $sms_time_out = config('config.sms_time_out')>0 ? config('config.sms_time_out') : 120;//手机短信超时时间
        $this->assign('sms_time_out', $sms_time_out); 
        return $this->fetch();
    }
    //订单列表==========================================================================================================
    public function order_list(){
        $where = ' userid=:userid';
        $bind['userid']=$this->userid;
        //条件搜索
       	if(input('type')){
           	$where .= config(strtoupper(input('type')));
       	}
       	// 搜索订单 根据商品名称 或者 订单编号
       	$search_key = trim(input('search_key'));       
       	if($search_key){
          	$where .= " and (order_sn like :search_key1 or order_id in (select order_id from `".config('database.prefix')."order_goods` where goods_name like :search_key2) ) ";
           	$bind['search_key1'] = "%$search_key%";
           	$bind['search_key2'] = "%$search_key%";
       	}
       
        $count = Db::name('order')->where($where)->bind($bind)->count();
        $Page = new Page($count,10);

        $show = $Page->show();
        $order_str = "order_id DESC";
        $order_list = Db::name('order')->order($order_str)->where($where)->bind($bind)->limit($Page->firstRow.','.$Page->listRows)->select();
		
        //获取订单商品
        $model = new UsersLogic();
        foreach($order_list as $k=>$v){
            $order_list[$k] = set_btn_order_status($v);  // 添加属性  包括按钮显示属性 和 订单状态显示属性
            $data = $model->get_order_goods($v['order_id']);
            $order_list[$k]['goods_list'] = $data['result'];
            if($order_list[$k]['order_prom_type'] == 4){
                $pre_sell_item =  Db::name('goods_activity')->where(array('act_id'=>$order_list[$k]['order_prom_id']))->find();
                $pre_sell_item = array_merge($pre_sell_item,unserialize($pre_sell_item['ext_info']));
                $order_list[$k]['pre_sell_is_finished'] = $pre_sell_item['is_finished'];
                $order_list[$k]['pre_sell_retainage_start'] = $pre_sell_item['retainage_start'];
                $order_list[$k]['pre_sell_retainage_end'] = $pre_sell_item['retainage_end'];
            }else{
                $order_list[$k]['pre_sell_is_finished'] = -1;//没有参与预售的订单
            }
        }
		//dump($order_list);die;
		$scount=get_order_count($this->userid);

		$this->assign('scount',$scount);
        $this->assign('order_status',config('ORDER_STATUS'));
        $this->assign('shipping_status',config('SHIPPING_STATUS'));
        $this->assign('pay_status',config('PAY_STATUS'));
        $this->assign('page',$show);
        $this->assign('lists',$order_list);
        $this->assign('active','order_list');
        $this->assign('active_status',input('get.type'));
        return $this->fetch();
    }
    //订单详情
    public function order_detail(){
        $id = input('id/d');
        $map['order_id'] = $id;
        $map['userid'] = $this->userid;
        $order_info = Db::name('order')->where($map)->find();
        $order_info = set_btn_order_status($order_info);  // 添加属性  包括按钮显示属性 和 订单状态显示属性
        
        if(!$order_info){
            $this->error('没有获取到订单信息');
            exit;
        }
        //获取订单商品
        $model = new UsersLogic();
        $data = $model->get_order_goods($order_info['order_id']);
        $order_info['goods_list'] = $data['result'];
        if($order_info['order_prom_type'] == 4){ //活动(0 普通订单,1 限时抢购, 2 团购 , 3 促销优惠,4预售)
            $pre_sell_item =  Db::name('goods_activity')->where(array('act_id'=>$order_info['order_prom_id']))->find();
            $pre_sell_item = array_merge($pre_sell_item,unserialize($pre_sell_item['ext_info']));
            $order_info['pre_sell_is_finished'] = $pre_sell_item['is_finished'];
            $order_info['pre_sell_retainage_start'] = $pre_sell_item['retainage_start'];
            $order_info['pre_sell_retainage_end'] = $pre_sell_item['retainage_end'];
            $order_info['pre_sell_deliver_goods'] = $pre_sell_item['deliver_goods'];
        }else{
            $order_info['pre_sell_is_finished'] = -1;//没有参与预售的订单
        }
		
        //获取订单进度条
        $sql = "SELECT action_id,log_time,status_desc,order_status FROM ((SELECT * FROM __PREFIX__order_action WHERE order_id = :id AND status_desc <>'' ORDER BY action_id) AS a) GROUP BY status_desc ORDER BY action_id";
        $bind['id'] = $id;
        $items = query($sql,$bind);
        $items_count = count($items);
        //$region_list = get_region_list();
        $invoice_no = Db::name('delivery_doc')->where("order_id", $id)->column('invoice_no');
        $order_info['invoice_no'] = implode(' , ', $invoice_no);
		//dump($order_info);die;
        //获取订单操作记录
        $order_action = Db::name('order_action')->where(array('order_id'=>$id))->order('action_id DESC')->select();
        $this->assign('order_status',config('ORDER_STATUS'));
        $this->assign('shipping_status',config('SHIPPING_STATUS'));
        $this->assign('pay_status',config('PAY_STATUS'));
        //$this->assign('region_list',$region_list);
        $this->assign('order_info',$order_info);
        $this->assign('order_action',$order_action);
        $this->assign('active','order_list');
        return $this->fetch();
    }
    //订单确认
    public function order_confirm(){
        $id = input('id/d',0);                      
        $data = confirm_order($id,$this->userid);
        if(!$data['status']){
            $this->error($data['msg']);
		}else{	
	   		$this->success($data['msg']);
		}
    }
    //取消订单
    public function cancel_order(){
        $id = input('get.id/d');
        //检查是否有积分，余额支付
        $logic = new UsersLogic();
        $data = $logic->cancel_order($this->userid,$id);
        if($data['status'] < 0)
            $this->error($data['msg']);
        $this->success($data['msg']);
    }
    //用户地址列表==========================================================================================================
    public function address_list(){
        $address_lists = get_user_address_list($this->userid);
        $this->assign('lists',$address_lists);
        $this->assign('active','address_list');

        return $this->fetch();
    }
    //添加地址
    public function add_address(){
        header("Content-type:text/html;charset=utf-8");
		$call_back = input('call_back');
        if(request()->isPost()||request()->isAjax()){
            $logic = new UsersLogic();
			$post=input('post.');
			unset($post['call_back']);
            $data = $logic->add_address($this->userid,0,$post);
            if($data['status'] != 1){
                exit('<script>alert("'.$data['msg'].'");history.go(-1);</script>');
			}
            echo "<script>parent.{$call_back}('success');</script>";
            exit(); // 成功 回调closeWindow方法 并返回新增的id
        }
        $p = Db::name('areas')->where(array('pid'=>0,'level'=> 1))->select();
        $this->assign('province',$p);
		$this->assign('call_back',$call_back);
        return $this->fetch('edit_address');

    }
    //地址编辑
    public function edit_address(){
        header("Content-type:text/html;charset=utf-8");
        $id = input('id/d');
        $address = Db::name('member_address')->where(array('id'=>$id,'userid'=> $this->userid))->find();
		$call_back = input('call_back');
        if(request()->isPost()||request()->isAjax()){
            $logic = new UsersLogic();
			$post=input('post.');
			unset($post['call_back']);
            $data = $logic->add_address($this->userid,$id,$post);
            if($data['status'] != 1){
                exit('<script>alert("'.$data['msg'].'");history.go(-1);</script>');
			}
            echo "<script>parent.{$call_back}('success');</script>";
            exit(); // 成功 回调closeWindow方法 并返回新增的id
        }

        //获取省份
        $p = Db::name('areas')->where(array('pid'=>0))->field('id,name')->select();
        $c = Db::name('areas')->where(array('pid'=>$address['province']))->field('id,name')->select();
        $d = Db::name('areas')->where(array('pid'=>$address['city']))->field('id,name')->select();
        if($address['twon']){
        	$e = Db::name('areas')->where(array('pid'=>$address['district']))->field('id,name')->select();
        	$this->assign('twon',$e);
        }
		
        $this->assign('province',$p);
        $this->assign('city',$c);
        $this->assign('district',$d);
        $this->assign('address',$address);
		$this->assign('call_back',$call_back);
        return $this->fetch();
    }
    //设置默认收货地址
    public function set_default(){
        $id = input('id/d');
        Db::name('member_address')->where('userid',$this->userid)->update(['is_default'=>0]);
        $row = Db::name('member_address')->where(['userid'=>$this->userid,'id'=>$id])->update(['is_default'=>1]);
        if($row===false){
            $this->error('操作失败');
        }
        $this->success("操作成功");
    }
    //地址删除
    public function del_address(){
        $id = input('id/d');
        
        $address = Db::name('member_address')->where("id", $id)->find();
        $row = Db::name('member_address')->where(['userid'=>$this->userid,'id'=>$id])->delete();
        // 如果删除的是默认收货地址 则要把第一个地址设置为默认收货地址
        if($address['is_default'] == 1){
            $address2 = Db::name('member_address')->where("userid", $this->userid)->find();
            $address2 && Db::name('member_address')->where("id", $address2['id'])->save(array('is_default'=>1));
        }
   
        if($row===false){
            $this->error('操作失败',url('User/address_list'));
        }else{
            $this->success("操作成功",url('User/address_list'));
		}
    }
	//自提点==========================================================================================================
    public function save_pickup(){
        $post = input('post.');
        if (empty($post['consignee'])) {
            return array('status' => -1, 'msg' => '收货人不能为空', 'result' => '');
        }
        if (!$post['province'] || !$post['city'] || !$post['district']) {
            return array('status' => -1, 'msg' => '所在地区不能为空', 'result' => '');
        }
        if(!check_mobile($post['mobile'])){
            return array('status'=>-1,'msg'=>'手机号码格式有误','result'=>'');
        }
        if(!$post['pickup_id']){
            return array('status'=>-1,'msg'=>'请选择自提点','result'=>'');
        }

        $user_logic = new UsersLogic();
        $res = $user_logic->add_pick_up($this->userid, $post);
        if($res['status'] != 1){
            exit('<script>alert("'.$res['msg'].'");history.go(-1);</script>');
        }
        $call_back = $_REQUEST['call_back'];
        echo "<script>parent.{$call_back}({$post['province']},{$post['city']},{$post['district']});</script>";
        exit(); // 成功 回调closeWindow方法 并返回新增的id
    }
    //评论晒单==========================================================================================================
    public function comment(){
        $userid = $this->userid;
        $status = input('status',-1);
        $logic = new UsersLogic();
        $data = $logic->get_comment($userid,$status); //获取评论列表
        $this->assign('page',$data['show']);// 赋值分页输出
        $this->assign('comment_list',$data['result']);
        $this->assign('active','comment');
        return $this->fetch();
    }
    //订单商品评价列表
    public function comment_list(){
        $order_id = input('order_id/d');
        $good_id = input('goods_id/d');
        if (empty($order_id) || empty($good_id)) {
            $this->error("参数错误");
        } else {
            //查找订单
            $order_comment_where['order_id'] = $order_id;
            $order_info = Db::name('order')->field('order_sn,order_id,add_time') ->where($order_comment_where)->find();
            //查找评价商品
            $order_comment_where['goods_id'] = $good_id;
            $order_goods = Db::name('order_goods')
                ->field('goods_id,is_comment,goods_name,goods_num,goods_price,spec_key_name')
                ->where($order_comment_where)
                ->find();
            $order_info = array_merge($order_info,$order_goods);
            $this->assign('order_info', $order_info);
            return $this->fetch();
        }
    }
    //添加评论
    public function add_comment(){          
        $user_info = session('user');
        $comment_img = serialize(input('comment_img/a')); // 上传的图片文件
        $add['goods_id'] = input('goods_id/d');
        $add['email'] = $user_info['email'];
        $add['username'] = deal_emoji($user_info['nickname']);
        $add['order_id'] = input('order_id/d');
        $add['service_rank'] = input('service_rank');
        $add['deliver_rank'] = input('deliver_rank');
        $add['goods_rank'] = input('goods_rank');
        $add['content'] = input('content');
        $add['img'] = $comment_img;
        $add['add_time'] = time();
        $add['ip_address'] = $_SERVER['REMOTE_ADDR'];
        $add['userid'] = $this->userid;
        $logic = new UsersLogic();
        //添加评论
        $row = $logic->add_comment($add);            
        exit(json_encode($row));        
    }
    //个人信息==========================================================================================================
    public function info(){
        $userLogic = new UsersLogic();
        $user_info = $userLogic->get_info($this->userid); // 获取用户信息
        $user_info = $user_info['result'];
		
        if(request()->isPost()||request()->isAjax()){
        	$data = input('post.');
			//数据验证
        	$validate = new Validate([
            	'nickname|昵称' => 'require',
        	]);
        	if(!$validate->check($data)){
            	$msg=$validate->getError();
            	$this->error($msg);
        	}
			
			input('birthday') ? $data['birthday'] = strtotime(input('birthday')) : 0;  //生日
			
			$city=[];
			input('province') ? $city[] = input('province') : false;  //省份
            input('city') ? $city[] = input('city') : false;  // 城市
            input('district') ? $city[] = input('district') : false;  //地区
			input('twon') ? $city[] = input('twon') : false;  //镇
			unset($data['province']);
			unset($data['city']);
			unset($data['district']);
			unset($data['twon']);
			$data['cityid']=implode(',', $city);
			
            if(!$userLogic->update_info($this->userid,$data)){
            	$this->error("保存失败");
            }
            $this->success("操作成功");
            exit;
        }
		if($user_info['birthday']>0){
			$user_info['birthday']=date('Y-m-d',$user_info['birthday']);
		}else{
			$user_info['birthday']='';
		}
		
		$areas=explode(',',$user_info['cityid']);
        $this->assign('province',$areas[0]);
        $this->assign('city',$areas[1]);
        $this->assign('district',$areas[2]);
		$this->assign('twon',$areas[3]);
		
        $this->assign('user',$user_info);

        return $this->fetch();
    }
	//账号安全
	public function safetyCenter(){
		$userLogic = new UsersLogic();
        $user_info = $userLogic->get_info($this->userid); // 获取用户信息
        $user_info = $user_info['result'];
		$user_info['rank']=2;
		if($user_info['email_validated']==1){
			$user_info['rank']+=1;
		}elseif($user_info['mobile_validated']==1){
			$user_info['rank']+=1;
		}elseif($user_info['paypwd']!=''){
			$user_info['rank']+=1;
		}elseif($user_info['relname']!='' && $user_info['idcard']!='' && $user_info['idcard_img']!=''){
			$user_info['rank']+=1;
		}
		$ranklevel=['2'=>'差级','3'=>'低级','4'=>'初级','5'=>'中级','6'=>'高级'];
		$this->assign('ranklevel',$ranklevel);
		$this->assign('user',$user_info);
		return $this->fetch();
	}
    //邮箱验证
    public function email_validate(){
        $userLogic = new UsersLogic();
        $user_info = $userLogic->get_info($this->userid); // 获取用户信息
        $user_info = $user_info['result'];
        $step = input('get.step',1);
        if(request()->isPost()||request()->isAjax()){
            $email = input('post.email');
            $old_email = input('post.old_email',''); //旧邮箱
            $code = input('post.code');
            $info = session('validate_code');
            if(!$info)
                $this->error('非法操作');
            if($info['time']<time()){
            	session('validate_code',null);
            	$this->error('验证超时，请重新验证');
            }
            //检查原邮箱是否正确
            if($user_info['email_validated'] == 1 && $old_email != $user_info['email'])
                $this->error('原邮箱匹配错误');
            //验证邮箱和验证码
            if($info['sender'] == $email && $info['code'] == $code){
                session('validate_code',null);
                if(!$userLogic->update_email_mobile($email,$this->userid))
                    $this->error('邮箱已存在');
                $this->success('绑定成功',U('Home/User/index'));
                exit;
            }
            $this->error('邮箱验证码不匹配');
        }
        $this->assign('user_info',$user_info);
        $this->assign('step',$step);
        return $this->fetch();
    }
    //手机验证
    public function mobile_validate(){
        $userLogic = new UsersLogic();
        $user_info = $userLogic->get_info($this->userid); //获取用户信息
        $user_info = $user_info['result'];
        $config = config('config');
        $sms_time_out = $config['sms_time_out'];
        $step = input('get.step', 1);
        if (request()->isPost()||request()->isAjax()) {
            $mobile = input('post.mobile');
            $old_mobile = input('post.old_mobile');
            $code = input('post.code');
            $scene = input('post.scene', 6);
            $session_id = input('unique_id', session_id());

            $logic = new UsersLogic();
            $res = $logic->check_validate_code($code, $mobile, 'phone', $session_id, $scene);

            if (!$res && $res['status'] != 1) $this->error($res['msg']);
            //检查原手机是否正确
            if ($user_info['mobile_validated'] == 1 && $old_mobile != $user_info['mobile']){
                $this->error('原手机号码错误');
			}
            //验证手机和验证码
            if ($res['status'] == 1) {
                //验证有效期
                if (!$userLogic->update_email_mobile($mobile, $this->userid, 2))
                    $this->error('手机已存在');
                $this->success('绑定成功', url('Home/User/index'));
                exit;
            } else {
                $this->error($res['msg']);
            }

        }
        $this->assign('time', $sms_time_out);
        $this->assign('step', $step);
        $this->assign('user_info', $user_info);
        return $this->fetch();
    }
    //发送手机注册验证码
    public function send_sms_reg_code(){
        $mobile = input('mobile');
        $userLogic = new UsersLogic();
        if(!check_mobile($mobile))
            exit(json_encode(array('status'=>-1,'msg'=>'手机号码格式有误')));
        $code =  rand(1000,9999);
        $send = $userLogic->sms_log($mobile,$code,$this->session_id);
        if($send['status'] != 1)
            exit(json_encode(array('status'=>-1,'msg'=>$send['msg'])));
        exit(json_encode(array('status'=>1,'msg'=>'验证码已发送，请注意查收')));
    }
	//支付密码
	public function paypassword(){
		return $this->fetch();
	}
	//实名认证
	public function certification(){
		return $this->fetch();
	}
    //商品收藏==========================================================================================================
    public function goods_collect(){
        $userLogic = new UsersLogic();
        $data = $userLogic->get_goods_collect($this->userid);
        $this->assign('page',$data['show']);// 赋值分页输出
        $this->assign('lists',$data['result']);
        $this->assign('active','goods_collect');
        return $this->fetch();
    }
    //删除一个收藏商品
    public function del_goods_collect(){
        $id = input('id/d');
        if(!$id)
            $this->error("缺少ID参数");
        $row = Db::name('goods_collect')->where(array('collect_id'=>$id,'userid'=>$this->userid))->delete();
        if(!$row)
            $this->error("删除失败");
        $this->success('删除成功');
    }
    //密码修改==========================================================================================================
    public function password(){
        //检查是否第三方登录用户
        $logic = new UsersLogic();
        $data = $logic->get_info($this->userid);
        $user = $data['result'];
        if($user['tel'] == ''&& $user['email'] == '')
            $this->error('请先绑定手机或邮箱',url('Home/User/safetycenter'));
        if(request()->isPost()||request()->isAjax()){
            $userLogic = new UsersLogic();
            $data = $userLogic->password($this->userid,input('old_password'),input('new_password'),input('confirm_password')); // 获取用户信息
            if($data['status'] == -1){
                $this->error($data['msg']);
			}
            $this->success($data['msg']);
            exit;
        }
        return $this->fetch();
    }
	//找回密码
    public function forget_pwd(){
        if ($this->userid > 0) {
            header("Location: " . url('Home/User/Index'));
        }
        if (request()->isPost()||request()->isAjax()) {
            $username = input('username');
            if (!empty($username)) {
                $field = 'mobile';
                if (check_email($username)) {
                    $field = 'email';
                }
                $user = Db::name('member')->where("email", $username)->whereOr('tel', $username)->find();
                if($user){
                	$data=[
	                	'userid' => $user['userid'],
	                	'username' => $username,
	                	'email' => $user['email'],
	                	'tel' => $user['tel'],
	                	'type' => $field
                	];
                    session('find_password', $data);
   					$this->redirect('User/identity');
                    exit;
                } else {
                   echo "用户名不存在，请检查";
                    $this->error("用户名不存在，请检查");
                }
            }
        }
        return $this->fetch();
    }
	//找回密码2
    public function identity(){
        if ($this->userid > 0) {
            header("Location: " . url('Home/User/Index'));
        }
        $user = session('find_password');
        if (empty($user)) {
            $this->error("请先验证用户名", url('User/forget_pwd'));
        }
        $this->assign('userinfo', $user);
        return $this->fetch();
    }
	//找回密码3
    public function set_pwd(){
    	if($this->userid > 0){
            $this->redirect('Home/User/Index');
    	}
		//验证码再次检测
    	$check = session('validate_code');
    	$logic = new UsersLogic();
    	if(empty($check)){
            $this->redirect('Home/User/forget_pwd');
    	}elseif($check['is_check']==0){
    		$this->error('验证码还未验证通过',url('Home/User/forget_pwd'));
    	}
		//修改密码
    	if(request()->isPost()||request()->isAjax()){
    		$password = input('post.password');
    		$password2 = input('post.password2');
    		if($password2 != $password){
    			$this->error('两次密码不一致',url('Home/User/forget_pwd'));
    		}
    		if($check['is_check']==1){
                $user = Db::name('member')->where("tel|email", '=', $check['sender'])->find();
				$encrypt=GetRandStr(6);
				$new_password=md5($password.$encrypt);//加密后的新密码
    			Db::name('member')->where("userid", $user['userid'])->update(['password'=>$new_password,'encrypt'=>$encrypt]);
    			session('validate_code',null);
				$sendemail=send_email($user['email'],'找回密码','您好，您的密码重设为：<b>'.$password.'</b>，请牢记您新设置的密码!');
                $this->redirect('Home/User/finished');
    		}else{
    			$this->error('验证码还未验证通过',U('Home/User/forget_pwd'));
    		}
    	}
    	return $this->fetch();
    }
    //找回密码4
    public function finished(){
    	if($this->userid > 0){
            $this->redirect('Home/User/Index');
    	}
    	return $this->fetch();
    }   
    //验证码验证
    public function check_captcha(){
		$verify_code = input('verify_code');
    	if (!captcha_check($verify_code)) {
    		exit(json_encode(0));
    	}else{
    		exit(json_encode(1));
    	}
    }
    //用户名验证
    public function check_username(){
    	$username = input('username');
    	if(!empty($username)){
    		$count = Db::name('member')->where("email", $username)->whereOr('tel', $username)->count();
    		exit(json_encode(intval($count)));
    	}else{
    		exit(json_encode(0));
    	}  	
    }
    //验证码验证（废弃）
    private function verifyHandle($id){
    	$verify_code = input('verify_code');
     	if(!captcha_check($verify_code)){
	    	return $this->error("验证码错误");
   		}
    }
    //申请退货==========================================================================================================
    public function return_goods(){
        $order_id = input('order_id/d',0);
        $order_sn = input('order_sn',0);
        $goods_id = input('goods_id/d',0);
	    $spec_key = input('spec_key');
        
        $c = Db::name('order')->where("order_id", $order_id)->where('userid', $this->userid)->count();
        if($c == 0){
            $this->error('非法操作');
            exit;
        }         
        
        $return_goods = Db::name('return_goods')->where(['order_id'=>$order_id,'goods_id'=>$goods_id,'spec_key'=>$spec_key])->find();
        if(!empty($return_goods)){
            $this->success('已经提交过退货申请!',url('Home/User/return_goods_info',array('id'=>$return_goods['id'])));
            exit;
        }       
        if(request()->isPost()||request()->isAjax()){
            $data['order_id'] = $order_id; 
            $data['order_sn'] = $order_sn; 
            $data['goods_id'] = $goods_id; 
            $data['addtime'] = time(); 
            $data['userid'] = $this->userid;            
            $data['type'] = input('type'); // 服务类型  退货 或者 换货
            $data['reason'] = input('reason'); // 问题描述
            $imgArr=input('imgs/a');
            $data['imgs'] = implode(',', $imgArr); // 用户拍照的相片
            $data['spec_key'] = input('spec_key'); // 商品规格			
            Db::name('return_goods')->insert($data);            
            $this->success('申请成功,客服第一时间会帮你处理',url('Home/User/order_list'));
            exit;
        }
        $region_id[] = config('config.province');        
        $region_id[] = config('config.city');        
        $region_id[] = config('config.district');
        $region_id[] = 0;        
        $return_address = Db::name('areas')->where("id in (".implode(',', $region_id).")")->column('id,name');
        $this->assign('return_address', $return_address);
        
        $goods = Db::name('order_goods')->where("goods_id", $goods_id)->find();
        $this->assign('goods',$goods);
        $this->assign('order_id',$order_id);
        $this->assign('order_sn',$order_sn);
        $this->assign('goods_id',$goods_id);
        return $this->fetch();
    }
    //退换货列表
    public function return_goods_list(){        
        $count = Db::name('return_goods')->where("userid", $this->userid)->count();
        $page = new Page($count,10);
        $list = Db::name('return_goods')->where("userid", $this->userid)->order("id desc")->limit("{$page->firstRow},{$page->listRows}")->select();
        $goods_id_arr = get_arr_column($list, 'goods_id');
        if(!empty($goods_id_arr)){
            $goodsList = Db::name('goods')->where("goods_id","in", implode(',',$goods_id_arr))->column('goods_id,goods_name');
		}
        $this->assign('goodsList', $goodsList);
        $this->assign('list', $list);
        $this->assign('page', $page->show());// 赋值分页输出
        return $this->fetch();
    }
    //退货详情
    public function return_goods_info(){
        $id = input('id/d',0);
        $return_goods = Db::name('return_goods')->where("id", $id)->find();
        if($return_goods['imgs']){
            $return_goods['imgs'] = explode(',', $return_goods['imgs']);
		}
        $goods = Db::name('order_goods')->where("goods_id", $return_goods['goods_id'])->find();
        $this->assign('goods',$goods);
        $this->assign('return_goods',$return_goods);
        return $this->fetch();
    }
    //用户消息通知==========================================================================================================
    public function message_notice(){
    	model('Message')->checkPublicMessage();//更新消息
		
		$map=[];
		$map['um.userid']=$this->userid;
		if(input('status')!='')$map['um.status']=input('status');//0未查看，1已查看
		
		//总数	
		$totalCount = DB::name('member_message')
            ->alias('um')
            ->field('um.id')
            ->join('__MESSAGE__ m','um.msgid = m.id','LEFT')
            ->where($map)
            ->count();
		//分页
        $Page = new Page($totalCount,10);
        $show = $Page->show();
		//列表
        $msglist = DB::name('member_message')
            ->alias('um')
            ->field('um.id, um.msgid, um.status, m.title, m.message, m.addtime')
            ->join('__MESSAGE__ m','um.msgid = m.id','LEFT')
            ->where($map)
			->order('um.status desc,m.addtime desc')
			->limit($Page->firstRow.','.$Page->listRows)->select();
		
		$this->assign('messages', $msglist);
		
        return $this->fetch();
    }
	//设为已 读
	public function ajax_readmsg(){
		$id = input('id');
        $rs=Db::name('member_message')->where(['id' =>$id])->setField('status',1);
		if($rs!==false){
			return ['status'=>1,'msg'=>'成功设为已读!'];
		}else{
			return ['status'=>0,'msg'=>'设为已读失败!'];
		}
	}
}