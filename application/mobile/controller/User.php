<?php
/**
 * 会员
 * ============================================================================
 * 版权所有 Ybcms开发团队，并保留所有权利
 * 网站地址: http://www.ybcms.com
 * ============================================================================
 * Author: ybcms(dreamlak@qq.com)      
 * UpDate: 2017-03-01
 */
namespace app\mobile\controller;
use app\home\logic\UsersLogic;
use app\home\model\Message;
use think\Page;
use think\Request;
use think\Verify;
use think\db;
class User extends MobileBase{
    public $userid = 0;
    public $user = array();

    /*
    * 初始化操作
    */
    public function _initialize(){
        parent::_initialize();
        if(session('?user')) {
            $user = session('user');
            $user = Db::name('member')->where("userid", $user['userid'])->find();
            session('user', $user);  //覆盖session 中的 user
            $this->user = $user;
            $this->userid = $user['userid'];
            $this->assign('user', $user); //存储用户信息
        }
        $nologin = array(
            'login', 'pop_login', 'do_login', 'logout', 'verify', 'set_pwd', 'finished',
            'verifyHandle', 'reg', 'send_sms_reg_code', 'find_pwd', 'check_validate_code',
            'forget_pwd', 'check_captcha', 'check_username', 'send_validate_code', 'express',
        );
        if (!$this->userid && !in_array($this->request->action(), $nologin)) {
            header("location:" . url('Mobile/User/login'));
            exit;
        }

        $order_status_coment = array(
            'WAITPAY' => '待付款 ', //订单查询状态 待支付
            'WAITSEND' => '待发货', //订单查询状态 待发货
            'WAITRECEIVE' => '待收货', //订单查询状态 待收货
            'WAITCCOMMENT' => '待评价', //订单查询状态 待评价
        );
        $this->assign('order_status_coment', $order_status_coment);
    }

    /*
     * 用户中心首页
     */
    public function index(){
        $goods_collect_count = Db::name('goods_collect')->where("userid", $this->userid)->count(); // 我的商品收藏
        $comment_count = Db::name('goods_comment')->where("userid", $this->userid)->count();   // 我的评论数
        $coupon_count = Db::name('coupon_list')->where("uid", $this->userid)->count(); // 我的优惠券数量
        $level_name = Db::name('member_level')->where("id", $this->user['level'])->value('name'); // 等级名称
        $order_count = Db::name('order')->where("userid", $this->userid)->count(); //我的全部订单 (改)
        $count_return = Db::name('return_goods')->where("userid=$this->userid and status<2")->count();   //退换货数量
        $wait_pay = Db::name('order')->where("userid=$this->userid and pay_status =0 and order_status = 0  and pay_code != 'cod'")->count(); //我的待付款 (改)
        $wait_receive = Db::name('order')->where("userid=$this->userid and order_status= 1 and shipping_status= 1")->count(); //我的待收货 (改)
        $comment = query("select COUNT(1) as comment from __PREFIX__order_goods as og left join __PREFIX__order as o on o.order_id = og.order_id where o.userid = $this->userid and og.is_send = 1 and og.is_comment = 0 ");  //我的待评论订单
        $wait_comment = $comment[0]['comment'];
        $count_sundry_status = array($wait_pay, $wait_receive, $wait_comment, $count_return);
        $this->assign('level_name', $level_name);
        $this->assign('order_count', $order_count); // 我的订单数 （改）
        $this->assign('goods_collect_count', $goods_collect_count);
        $this->assign('comment_count', $comment_count);
        $this->assign('coupon_count', $coupon_count);
        $this->assign('count_sundry_status', $count_sundry_status);  //各种数量
        return $this->fetch();
    }


    public function logout(){
        session_unset();
        session_destroy();
        setcookie('cn', '', time() - 3600, '/');
        setcookie('userid', '', time() - 3600, '/');
        header("Location:" . url('Mobile/Index/index'));
        exit();
    }

    /*
     * 账户资金
     */
    public function account(){
        $user = session('user');
        //获取账户资金记录
        $logic = new UsersLogic();
        $data = $logic->get_account_log($this->userid, input('type'));
        $account_log = $data['result'];

        $this->assign('user', $user);
        $this->assign('account_log', $account_log);
        $this->assign('page', $data['show']);

        if(input('is_ajax')==1) {
            return $this->fetch('ajax_account_list');
            exit;
        }
        return $this->fetch();
    }

    /**
     * 优惠券
     */
    public function coupon(){
        $logic = new UsersLogic();
        $data = $logic->get_coupon($this->userid, input('type'));
        $coupon_list = $data['result'];
        $this->assign('coupon_list', $coupon_list);
        $this->assign('page', $data['show']);
        if(input('is_ajax')){
            return $this->fetch('ajax_coupon_list');
            exit;
        }
        return $this->fetch();
    }

    /**
     * 确定订单的使用优惠券
     * @author lxl
     * @time 2017
     */
    public function checkcoupon(){
        $cartLogic = new \app\home\logic\CartLogic();
        // 找出这个用户的优惠券 没过期的  并且 订单金额达到 condition 优惠券指定标准的
        $result = $cartLogic->cartList($this->user, $this->session_id,1,1); // 获取购物车商品
        if(input('type') == ''){
            $where = " c2.uid = {$this->userid} and ".time()." < c1.use_end_time and c1.condition <= {$result['total_price']['total_fee']} ";
        }
        if(input('type') == '1'){
           $where = " c2.uid = {$this->userid} and c1.use_end_time < ".time()." or {$result['total_price']['total_fee']}  < c1.condition ";
        }

        $coupon_list = DB::name('coupon')
            ->alias('c1')
            ->field('c1.name,c1.money,c1.condition,c1.use_end_time, c2.*')
            ->join('coupon_list c2','c2.cid = c1.id and c1.type in(0,1,2,3) and order_id = 0','LEFT')
            ->where($where)
            ->select();
        $this->assign('coupon_list', $coupon_list); // 优惠券列表
        return $this->fetch();
    }

    /**
     *  登录
     */
    public function login(){
    	//dump($_SESSION);die;
        if ($this->userid > 0) {
			$this->redirect('Mobile/User/index');
        }
        $referurl = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : url("Mobile/User/index");
        $this->assign('referurl', $referurl);
        return $this->fetch();
    }

    /**
     * 登录
     */
    public function do_login(){
        $username = trim(input('username'));
        $password = trim(input('password'));
        //验证码验证
        $verify_code = input('verify_code');
		if(!captcha_check($verify_code)){
	    	return $this->error("验证码错误");
   		}
        $logic = new UsersLogic();
        $res = $logic->login($username, $password);
		
        if ($res['status'] == 1) {
            $res['url'] = urldecode(input('referurl'));
            session('user', $res['result']);
			
            setcookie('userid', $res['result']['userid'], null, '/');
            setcookie('is_distribut', $res['result']['is_distribut'], null, '/');
			
            $nickname = empty($res['result']['nickname']) ? $username : $res['result']['nickname'];
            setcookie('uname', $nickname, null, '/');
			
            setcookie('cn', 0, time() - 3600, '/');
			
            $cartLogic = new \app\home\logic\CartLogic();
            $cartLogic->login_cart_handle($this->session_id, $res['result']['userid']);  //用户登录后 需要对购物车 一些操作
        }
		//dump($_SESSION);die;
        exit(json_encode($res));
    }

    /**
     *  注册
     */
    public function reg(){
    	if($this->userid > 0) header("Location: ".url('Mobile/User/index'));
        $reg_sms_enable = tpCache('sms.regis_sms_enable');
        $reg_smtp_enable = tpCache('sms.regis_smtp_enable');

        if (request()->isPost()||request()->isAjax()) {
            $logic = new UsersLogic();
            //验证码检验
            //$this->verifyHandle('user_reg');
            $username = input('post.username', '');
            $password = input('post.password', '');
            $password2 = input('post.password2', '');
            //是否开启注册验证码机制
            $code = input('post.mobile_code', '');
            $scene = input('post.scene', 1);

            $session_id = session_id();
            
            if(check_mobile($username)){
                $check_code = $logic->check_validate_code($code, $username, 'phone', $session_id , $scene);
                if($check_code['status'] != 1){
                    $this->error($check_code['msg']);
                }
            }
            //是否开启注册邮箱验证码机制
            if(check_email($username)){
                $check_code = $logic->check_validate_code($code, $username);
                if($check_code['status'] != 1){
                    $this->error($check_code['msg']);
                }
            }

            $data = $logic->reg($username, $password, $password2);
            if ($data['status'] != 1){
                $this->error($data['msg']);
            }
            session('user', $data['result']);
            setcookie('userid', $data['result']['userid'], null, '/');
            setcookie('is_distribut', $data['result']['is_distribut'], null, '/');
            $cartLogic = new \app\home\logic\CartLogic();
            $cartLogic->login_cart_handle($this->session_id, $data['result']['userid']);  //用户登录后 需要对购物车 一些操作
            $this->success($data['msg'], url('Mobile/User/index'));
            exit;
        }
        $this->assign('regis_sms_enable',$reg_sms_enable); // 注册启用短信：
        $this->assign('regis_smtp_enable',$reg_smtp_enable); // 注册启用邮箱：
        $sms_time_out = tpCache('sms.sms_time_out')>0 ? tpCache('sms.sms_time_out') : 120;
        $this->assign('sms_time_out', $sms_time_out); // 手机短信超时时间
        return $this->fetch();
    }

    /*
     * 订单列表
     */
    public function order_list(){
        $where = ' userid=' . $this->userid;
        //条件搜索
       	if(input('type')){
            $where .= config(strtoupper(input('type')));
       	}   
        $count = Db::name('order')->where($where)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $order_str = "order_id DESC";
        $order_list = Db::name('order')->order($order_str)->where($where)->limit($Page->firstRow . ',' . $Page->listRows)->select();

        //获取订单商品
        $model = new UsersLogic();
        foreach ($order_list as $k => $v) {
            $order_list[$k] = set_btn_order_status($v);  // 添加属性  包括按钮显示属性 和 订单状态显示属性
            $data = $model->get_order_goods($v['order_id']);
            $order_list[$k]['goods_list'] = $data['result'];
        }
        //统计订单商品数量
        foreach ($order_list as $key => $value) {
            $count_goods_num = '';
            foreach ($value['goods_list'] as $kk => $vv) {
                $count_goods_num += $vv['goods_num'];
            }
            $order_list[$key]['count_goods_num'] = $count_goods_num;
        }
        $this->assign('order_status', config('ORDER_STATUS'));
        $this->assign('shipping_status', config('SHIPPING_STATUS'));
        $this->assign('pay_status', config('PAY_STATUS'));
        $this->assign('page', $show);
        $this->assign('lists', $order_list);
        $this->assign('active', 'order_list');
        $this->assign('active_status', input('get.type'));
        if (input('is_ajax')) {
            return $this->fetch('ajax_order_list');
            exit;
        }
        return $this->fetch();
    }


    /*
     * 订单列表
     */
    public function ajax_order_list()
    {

    }

    /*
     * 订单详情
     */
    public function order_detail(){
        $id = input('id/d');
        $map['order_id'] = $id;
        $map['userid'] = $this->userid;
        $order_info = Db::name('order')->where($map)->find();
        $order_info = set_btn_order_status($order_info);  // 添加属性  包括按钮显示属性 和 订单状态显示属性
        if (!$order_info) {
            $this->error('没有获取到订单信息');
            exit;
        }
        //获取订单商品
        $model = new UsersLogic();
        $data = $model->get_order_goods($order_info['order_id']);
        $order_info['goods_list'] = $data['result'];

        //$region_list = get_region_list();
        $invoice_no = Db::name('delivery_doc')->where("order_id", $id)->column('invoice_no');
        $order_info['invoice_no'] = implode(' , ', $invoice_no);
        //获取订单操作记录
        $order_action = Db::name('order_action')->where(array('order_id' => $id))->select();
        $this->assign('order_status', config('ORDER_STATUS'));
        $this->assign('shipping_status', config('SHIPPING_STATUS'));
        $this->assign('pay_status', config('PAY_STATUS'));
        //$this->assign('region_list', $region_list);
        $this->assign('order_info', $order_info);
        $this->assign('order_action', $order_action);

        if (input('waitreceive')) {  //待收货详情
            return $this->fetch('wait_receive_detail');
        }
        return $this->fetch();
    }

    public function express(){
        $order_id = input('order_id/d', 195);
        $order_goods = Db::name('order_goods')->where("order_id", $order_id)->select();
        $delivery = Db::name('delivery_doc')->where("order_id", $order_id)->find();
        $this->assign('order_goods', $order_goods);
        $this->assign('delivery', $delivery);
        return $this->fetch();
    }

    /*
     * 取消订单
     */
    public function cancel_order(){
        $id = input('id/d');
        //检查是否有积分，余额支付
        $logic = new UsersLogic();
        $data = $logic->cancel_order($this->userid, $id);
        if ($data['status'] < 0)
            $this->error($data['msg']);
        $this->success($data['msg']);
    }

    /*
     * 用户地址列表
     */
    public function address_list(){
        $address_lists = get_user_address_list($this->userid);
        //$region_list = get_region_list();
        $this->assign('region_list', $region_list);
        $this->assign('lists', $address_lists);
        return $this->fetch();
    }

    /*
     * 添加地址
     */
    public function add_address(){
        if (request()->isPost()||request()->isAjax()) {
            $logic = new UsersLogic();
			$postData=input('post.');
			unset($postData['source']);
            $data = $logic->add_address($this->userid, 0, $postData);
            if ($data['status'] != 1){
                $this->error($data['msg']);
            }elseif (input('source') == 'cart2') {
                header('Location:' . url('/Mobile/Cart/cart2', array('id' => $data['result'])));
                exit;
            }
            $this->success($data['msg'], url('/Mobile/User/address_list'));
            exit();
        }
        $p = Db::name('areas')->where(array('pid' => 0))->select();
        $this->assign('province', $p);
        return $this->fetch();
    }

    /*
     * 地址编辑
     */
    public function edit_address(){
        $id = input('id/d');
        $address = Db::name('member_address')->where(array('id' => $id, 'userid' => $this->userid))->find();
        if (request()->isPost()||request()->isAjax()) {
            $logic = new UsersLogic();
            $data = $logic->add_address($this->userid, $id, input('post.'));
            if ($_POST['source'] == 'cart2') {
                header('Location:' . url('/Mobile/Cart/cart2', array('address_id' => $id)));
                exit;
            } else
                $this->success($data['msg'], url('/Mobile/User/address_list'));
            exit();
        }
        //获取省份
        $p = Db::name('areas')->where(array('pid' => 0))->select();
        $c = Db::name('areas')->where(array('pid' => $address['province']))->select();
        $d = Db::name('areas')->where(array('pid' => $address['city']))->select();
        if ($address['twon']) {
            $e = Db::name('areas')->where(array('pid' => $address['district']))->select();
            $this->assign('twon', $e);
        }
        $this->assign('province', $p);
        $this->assign('city', $c);
        $this->assign('district', $d);
        $this->assign('address', $address);
        return $this->fetch();
    }

    /*
     * 设置默认收货地址
     */
    public function set_default(){
        $id = input('id/d');
        $source = input('source');
        Db::name('member_address')->where(['userid' => $this->userid])->setField('is_default',0);
        $row = Db::name('member_address')->where(['userid'=>$this->userid,'id'=>$id])->setField('is_default',1);
        if ($source == 'cart2') {
            header("Location:" . url('Mobile/Cart/cart2'));
            exit;
        } else {
            header("Location:" . url('Mobile/User/address_list'));
        }
    }

    /*
     * 地址删除
     */
    public function del_address(){
        $id = input('get.id/d');

        $address = Db::name('member_address')->where("id", $id)->find();
		
        $row = Db::name('member_address')->where(['userid'=>$this->userid,'id'=>$id])->delete();
        // 如果删除的是默认收货地址 则要把第一个地址设置为默认收货地址
        if ($address['is_default'] == 1) {
            $address2 = Db::name('member_address')->where("userid",$this->userid)->find();
            $address2 && Db::name('member_address')->where("id", $address2['id'])->setField('is_default',1);
        }
        if (!$row)
            $this->error('操作失败', url('User/address_list'));
        else
            $this->success("操作成功", url('User/address_list'));
    }

    /*
     * 评论晒单
     */
    public function comment(){
        $userid = $this->userid;
        $status = input('status');
        $logic = new UsersLogic();
        $result = $logic->get_comment($userid, $status); //获取评论列表
       
        $this->assign('comment_list', $result['result']);
        if (input('is_ajax')) {
            return $this->fetch('ajax_comment_list');
            exit;
        }
        return $this->fetch();
    }

    /*
     *添加评论
     */
    public function add_comment(){
        if (request()->isPost()||request()->isAjax()) {
            // 晒图片
            $files = request()->file('comment_img_file');
            $save_url = 'uploads/comment/' . date('Y', time()) . '/' . date('m-d', time());
            foreach ($files as $file) {
                // 移动到框架应用根目录/public/uploads/ 目录下
                $info = $file->rule('uniqid')->validate(['size' => 1024 * 1024 * 3, 'ext' => 'jpg,png,gif,jpeg'])->move($save_url);
                if ($info) {
                    // 成功上传后 获取上传信息
                    // 输出 jpg
                    $comment_img[] = '/'.$save_url . '/' . $info->getFilename();
                } else {
                    // 上传失败获取错误信息
                    $this->error($file->getError());
                }
            }
            if (!empty($comment_img)) {
                $add['img'] = serialize($comment_img);
            }

            $user_info = session('user');
            $logic = new UsersLogic();
            $add['goods_id'] = input('goods_id/d');
            $add['email'] = $user_info['email'];
            $hide_username = input('hide_username');
            if (empty($hide_username)) {
                $add['username'] = $user_info['nickname'];
            }
            $add['order_id'] = input('order_id/d');
            $add['service_rank'] = input('service_rank');
            $add['deliver_rank'] = input('deliver_rank');
            $add['goods_rank'] = input('goods_rank');
            $add['is_show'] = input('is_show/d',0);
            $add['content'] = input('content');
            $add['add_time'] = time();
            $add['ip_address'] = request()->ip();
            $add['userid'] = $this->userid;

            //添加评论
            $row = $logic->add_comment($add);
            if ($row['status'] == 1) {
                $this->success('评论成功', url('/Mobile/Goods/goodsInfo', array('id' => $add['goods_id'])));
                exit();
            } else {
                $this->error($row['msg']);
            }
        }
        $rec_id = input('rec_id/d');
        $order_goods = Db::name('order_goods')->where("rec_id", $rec_id)->find();
        $this->assign('order_goods', $order_goods);
        return $this->fetch();
    }

    /*
     * 个人信息
     */
    public function userinfo(){
        $userLogic = new UsersLogic();
        $user_info = $userLogic->get_info($this->userid); // 获取用户信息
        $user_info = $user_info['result'];
        if (request()->isPost()||request()->isAjax()) {
        	$post=[];
            input('nickname') ? $post['nickname'] = input('nickname') : false; //昵称
            input('qq') ? $post['qq'] = input('qq') : false;  //QQ号码
            input('head_pic') ? $post['head_pic'] = input('head_pic') : false; //头像地址
            input('sex') ? $post['sex'] = input('sex') : $post['sex'] = 0;  // 性别
            input('birthday') ? $post['birthday'] = strtotime(input('birthday')) : false;  // 生日
            input('province') ? $post['province'] = input('province') : false;  //省份
            input('city') ? $post['city'] = input('city') : false;  // 城市
            input('district') ? $post['district'] = input('district') : false;  //地区
            input('email') ? $post['email'] = input('email') : false; //邮箱
            input('mobile') ? $post['tel'] = input('mobile') : false; //手机

            $email = input('email');
            $mobile = input('mobile');
            $code = input('mobile_code', '');
            $scene = input('scene', 3);

            if (!empty($email)) {
                $c = Db::name('member')->where(['email' => input('email'), 'userid' => ['<>', $this->userid]])->count();
                $c && $this->error("邮箱已被使用");
            }
            if (!empty($mobile)) {
                $c = Db::name('member')->where(['tel' => input('mobile'), 'userid' => ['<>', $this->userid]])->count();
                $c && $this->error("手机已被使用");
                if(!$code){
                    $this->error('请输入验证码');
				}
                $check_code = $userLogic->check_validate_code($code, $mobile, 'phone', $this->session_id, $scene);
                if ($check_code['status'] != 1){
                    $this->error($check_code['msg']);
				}
            }
            if (!$userLogic->update_info($this->userid, $post)){
                $this->error("保存失败");
			}
            $this->success("操作成功");
            exit;
        }
		
        //获取省份
        $province = Db::name('areas')->where(array('pid' => 0))->select();
        //获取订单城市
        $city = Db::name('areas')->where(array('pid' => $user_info['province']))->select();
        //获取订单地区
        $area = Db::name('areas')->where(array('pid' => $user_info['city']))->select();
        $this->assign('province', $province);
        $this->assign('city', $city);
        $this->assign('area', $area);
        $this->assign('user', $user_info);
        $this->assign('sex', config('SEX'));
        //从哪个修改用户信息页面进来，
        $dispaly = input('action');
        if ($dispaly != '') {
            return $this->fetch("$dispaly");
            exit;
        }
        return $this->fetch();
    }

    /*
     * 邮箱验证
     */
    public function email_validate(){
        $userLogic = new UsersLogic();
        $user_info = $userLogic->get_info($this->userid); // 获取用户信息
        $user_info = $user_info['result'];
        $step = input('get.step', 1);
        //验证是否未绑定过
        if ($user_info['email_validated'] == 0){
            $step = 2;
		}
        //原邮箱验证是否通过
        if ($user_info['email_validated'] == 1 && session('email_step1') == 1){
            $step = 2;
		}
        if ($user_info['email_validated'] == 1 && session('email_step1') != 1){
            $step = 1;
		}
        if (request()->isPost()||request()->isAjax()) {
            $email = input('post.email');
            $code = input('post.code');
            $info = session('email_code');
            if (!$info){
                $this->error('非法操作');
			}
            if ($info['email'] == $email || $info['code'] == $code) {
                if ($user_info['email_validated'] == 0 || session('email_step1') == 1) {
                    session('email_code', null);
                    session('email_step1', null);
                    if (!$userLogic->update_email_mobile($email, $this->userid)){
                        $this->error('邮箱已存在');
					}
                    $this->success('绑定成功', url('Home/User/index'));
                } else {
                    session('email_code', null);
                    session('email_step1', 1);
                    redirect(url('Home/User/email_validate', array('step' => 2)));
                }
                exit;
            }
            $this->error('验证码邮箱不匹配');
        }
        $this->assign('step', $step);
        return $this->fetch();
    }

    /*
    * 手机验证
    */
    public function mobile_validate(){
        $userLogic = new UsersLogic();
        $user_info = $userLogic->get_info($this->userid); // 获取用户信息
        $user_info = $user_info['result'];
        $step = input('get.step', 1);
        //验证是否未绑定过
        if ($user_info['mobile_validated'] == 0){
            $step = 2;
		}
        //原手机验证是否通过
        if ($user_info['mobile_validated'] == 1 && session('mobile_step1') == 1){
            $step = 2;
		}
        if ($user_info['mobile_validated'] == 1 && session('mobile_step1') != 1){
            $step = 1;
		}
        if (request()->isPost()||request()->isAjax()) {
            $mobile = input('post.mobile');
            $code = input('post.code');
            $info = session('mobile_code');
            if (!$info){
                $this->error('非法操作');
			}
            if ($info['email'] == $mobile || $info['code'] == $code) {
                if ($user_info['email_validated'] == 0 || session('email_step1') == 1) {
                    session('mobile_code', null);
                    session('mobile_step1', null);
                    if (!$userLogic->update_email_mobile($mobile, $this->userid, 2)){
                        $this->error('手机已存在');
					}
                    $this->success('绑定成功', url('Home/User/index'));
                } else {
                    session('mobile_code', null);
                    session('email_step1', 1);
                    redirect(url('Home/User/mobile_validate', array('step' => 2)));
                }
                exit;
            }
            $this->error('验证码手机不匹配');
        }
        $this->assign('step', $step);
        return $this->fetch();
    }

    /**
     * 用户收藏列表
     */
    public function collect_list(){
        $userLogic = new UsersLogic();
        $data = $userLogic->get_goods_collect($this->userid);
        $this->assign('page', $data['show']);// 赋值分页输出
        $this->assign('goods_list', $data['result']);
        if (request()->isAjax()||input('is_ajax')==1) {      //ajax加载更多
            return $this->fetch('ajax_collect_list');
            exit;
        }
        return $this->fetch();
    }

    /*
     *取消收藏
     */
    public function cancel_collect(){
        $collect_id = input('collect_id/d');
        $userid = $this->userid;
        if (Db::name('goods_collect')->where(['collect_id' => $collect_id, 'userid' => $userid])->delete()) {
            $this->success("取消收藏成功", url('User/collect_list'));
        } else {
            $this->error("取消收藏失败", url('User/collect_list'));
        }
    }

    /**
     * 我的留言
     */
    public function message_list(){
        config('TOKEN_ON', true);
        if (request()->isPost()||request()->isAjax()) {
            $this->verifyHandle('message');

            $data = input('post.');
            $data['userid'] = $this->userid;
            $user = session('user');
            $data['user_name'] = $user['nickname'];
            $data['msg_time'] = time();
            if (Db::name('shop_feedback')->insert($data)) {
                $this->success("留言成功", url('User/message_list'));
                exit;
            } else {
                $this->error('留言失败', url('User/message_list'));
                exit;
            }
        }
        $msg_type = array(0 => '留言', 1 => '投诉', 2 => '询问', 3 => '售后', 4 => '求购');
        $count = Db::name('shop_feedback')->where("userid", $this->userid)->count();
        $Page = new Page($count, 100);
        $Page->rollPage = 2;
        $message = Db::name('shop_feedback')->where("userid", $this->userid)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $showpage = $Page->show();
        header("Content-type:text/html;charset=utf-8");
        $this->assign('page', $showpage);
        $this->assign('message', $message);
        $this->assign('msg_type', $msg_type);
        return $this->fetch();
    }

    /**账户明细*/
    public function points(){
        $type = input('type', 'all');    //获取类型
        $this->assign('type', $type);
        if ($type == 'recharge') {
            //充值明细
            $count = Db::name('recharge')->where("userid", $this->userid)->count();
            $Page = new Page($count, 15);
            $account_log = Db::name('recharge')->where("userid", $this->userid)->order('order_id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        } else if ($type == 'points') {
            //积分记录明细
            $count = Db::name('member_account_log')->where(['userid'=>$this->userid,'mypoints'=>['<>', 0]])->count();
            $Page = new Page($count, 15);
            $account_log = Db::name('member_account_log')->where(['userid'=>$this->userid,'mypoints'=> ['<>', 0]])->order('logid desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        } else {
            //全部
            $count = Db::name('member_account_log')->where(['userid'=>$this->userid])->count();
            $Page = new Page($count, 15);
            $account_log = Db::name('member_account_log')->where(['userid'=> $this->userid])->order('logid desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        }
        $showpage = $Page->show();
        $this->assign('account_log', $account_log);
        $this->assign('page', $showpage);
        if (input('is_ajax')) {
            return $this->fetch('ajax_points');
            exit;
        }
        return $this->fetch();
    }

    /*
     * 密码修改
     */
    public function password(){
        //检查是否第三方登录用户
        $logic = new UsersLogic();
        $data = $logic->get_info($this->userid);
        $user = $data['result'];
        if ($user['tel'] == '' && $user['email'] == ''){
            $this->error('请先到电脑端绑定手机', url('/Mobile/User/index'));
		}
        if (request()->isPost()||request()->isAjax()) {
            $userLogic = new UsersLogic();
            $data = $userLogic->password($this->userid, input('post.old_password'), input('post.new_password'), input('post.confirm_password')); // 获取用户信息
            if ($data['status'] == -1)
                $this->error($data['msg']);
            $this->success($data['msg']);
            exit;
        }
        return $this->fetch();
    }

    function forget_pwd(){
        if ($this->userid > 0) {
            $this->redirect("User/index");
        }
        $username = input('username');
        if (request()->isPost()||request()->isAjax()) {
            if (!empty($username)) {
                $this->verifyHandle('forget');
                $field = 'mobile';
                if (check_email($username)) {
                    $field = 'email';
                }
                $user = Db::name('member')->where("email", $username)->whereOr('tel', $username)->find();
                if ($user) {
                    session('find_password',['userid'=>$user['userid'],'username'=>$username,'email'=>$user['email'],'tel' =>$user['tel'],'type' =>$field]);
                    header("Location: " . url('User/find_pwd'));
                    exit;
                } else {
                    $this->error("用户名不存在，请检查");
                }
            }
        }
        return $this->fetch();
    }

    function find_pwd(){
        if ($this->userid > 0) {
            header("Location: " . url('User/index'));
        }
        $user = session('find_password');
        if (empty($user)) {
            $this->error("请先验证用户名", url('User/forget_pwd'));
        }
        $this->assign('user', $user);
        return $this->fetch();
    }

	//修改密码
    public function set_pwd(){
        if ($this->userid > 0) {
            $this->redirect('Mobile/User/index');
        }
        $check = session('validate_code');
        if (empty($check)) {
            header("Location:" . url('User/forget_pwd'));
        } elseif ($check['is_check'] == 0) {
            $this->error('验证码还未验证通过', url('User/forget_pwd'));
        }
        if (request()->isPost()||request()->isAjax()) {
            $password = input('post.password');
            $password2 = input('post.password2');
            if ($password2 != $password) {
                $this->error('两次密码不一致', url('User/forget_pwd'));
            }
            if ($check['is_check'] == 1) {
                $user = Db::name('users')->where("mobile", $check['sender'])->whereOr('email', $check['sender'])->find();
				
				$encrypt=GetRandStr(6);
				$new_password=md5($password.$encrypt);//加密后的新密码
                Db::name('member')->where("userid", $user['userid'])->update(['password' => $new_password,'encrypt'=>$encrypt]);
                session('validate_code', null);
                $this->success('新密码已设置行牢记新密码', url('User/index'));
                exit;
            } else {
                $this->error('验证码还未验证通过', url('User/forget_pwd'));
            }
        }
        $is_set = input('is_set', 0);
        $this->assign('is_set', $is_set);
        return $this->fetch();
    }
 
    /**
     * 验证码验证
     * $id 验证码标示
     */
    private function verifyHandle($id){
        /*$verify = new Verify();
        if (!$verify->check(input('post.verify_code'), $id ? $id : 'user_login')) {
            $this->error("验证码错误");
        }*/
		$verify_code = input('verify_code');
		if(!captcha_check($verify_code)){
	    	return $this->error("验证码错误");
   		}
    }

    /**
     * 验证码获取
     */
    public function verify(){
        //验证码类型
        $type = input('type') ? input('type') : 'user_login';
        $config = array(
            'fontSize' => 40,
            'length' => 4,
            'useCurve' => true,
            'useNoise' => false,
        );
        $Verify = new Verify($config);
        $Verify->entry($type);
    }

    /**
     * 账户管理
     */
    public function accountManage(){
        return $this->fetch();
    }

    /**
     * 确定收货成功
     */
    public function order_confirm(){
        $id = input('id/d', 0);
        $data = confirm_order($id, $this->userid);
		
        if (!$data['status']) {
            $this->error($data['msg']);
        } else {
            $model = new UsersLogic();
            $order_goods = $model->get_order_goods($id);
            $this->assign('order_goods', $order_goods);
            return $this->fetch();
            exit;
        }
    }

    /**
     * 申请退货
     */
    public function return_goods(){
        $order_id = input('order_id/d', 0);
        $order_sn = input('order_sn', 0);
        $goods_id = input('goods_id/d', 0);
        $good_number = input('good_number/d', 0); //申请数量
        $spec_key = input('spec_key');
        $c = Db::name('order')->where(['order_id' => $order_id, 'userid' => $this->userid])->count();
        if ($c == 0) {
            $this->error('非法操作');
            exit;
        }

        $return_goods = Db::name('return_goods')
            ->where(['order_id' => $order_id, 'goods_id' => $goods_id, 'spec_key' => $spec_key])
            ->find();
        if (!empty($return_goods)) {
            $this->success('已经提交过退货申请!', url('Mobile/User/return_goods_info', array('id' => $return_goods['id'])));
            exit;
        }
        if (request()->isPost()||request()->isAjax()) {
            // 晒图片
            if (count($_FILES['return_imgs']['tmp_name'])>0) {
                $files = request()->file('return_imgs');
                $save_url = 'public/upload/return_goods/' . date('Y', time()) . '/' . date('m-d', time());
                foreach ($files as $file) {
                    // 移动到框架应用根目录/public/uploads/ 目录下
                    $info = $file->rule('uniqid')->validate(['size' => 1024 * 1024 * 3, 'ext' => 'jpg,png,gif,jpeg'])->move($save_url);
                    if ($info) {
                        // 成功上传后 获取上传信息
                        $return_imgs[] = '/'.$save_url . '/' . $info->getFilename();
                    } else {
                        // 上传失败获取错误信息
                        $this->error($file->getError());
                    }
                }
                if (!empty($return_imgs)) {
                    $data['imgs'] = implode(',', $return_imgs);
                }
            }
            $data['order_id'] = $order_id;
            $data['order_sn'] = $order_sn;
            $data['goods_id'] = $goods_id;
            $data['addtime'] = time();
            $data['userid'] = $this->userid;
            $data['type'] = input('type'); // 服务类型  退货 或者 换货
            $data['reason'] = input('reason'); // 问题描述     
            $data['spec_key'] = input('spec_key'); // 商品规格						       
            $res = Db::name('return_goods')->add($data);
            $data['return_id'] = $res;  //退换货id
            $this->assign('data',$data);
            return $this->fetch('return_good_success'); //申请成功
            exit;
        } 
        
        $region_id[] = tpCache('shop_info.province');        
        $region_id[] = tpCache('shop_info.city');        
        $region_id[] = tpCache('shop_info.district');
        $region_id[] = 0;        
        $return_address = Db::name('areas')->where("id in (".implode(',', $region_id).")")->getField('id,name');
        $this->assign('return_address', $return_address);
        
        $goods = Db::name('order_goods')->where("goods_id", $goods_id)->find();
        //查找订单收货地址
        $region = Db::name('order')->field('consignee,country,province,city,district,twon,address,mobile')->where("order_id = $order_id")->find();
        $region_list = get_region_list();
        $this->assign('region_list', $region_list);
        $this->assign('region', $region);
        $this->assign('goods', $goods);
        $this->assign('order_id', $order_id);
        $this->assign('order_sn', $order_sn);
        $this->assign('goods_id', $goods_id);

        return $this->fetch();
    }

    /**
     * 退换货列表
     */
    public function return_goods_list(){
        //退换货商品信息
        $count = Db::name('return_goods')->where("userid", $this->userid)->count();
        $pagesize = config('paginate.list_rows');
        $page = new Page($count, $pagesize);
        $list = Db::name('return_goods')->where("userid", $this->userid)->order("id desc")->limit("{$page->firstRow},{$page->listRows}")->select();
        $goods_id_arr = get_arr_column($list, 'goods_id');  //获取商品ID
        if (!empty($goods_id_arr)){
            $goodsList = Db::name('goods')->where("goods_id", "in", implode(',', $goods_id_arr))->getField('goods_id,goods_name');
        }

        $this->assign('goodsList', $goodsList);
        $this->assign('list', $list);
        $this->assign('page', $page->show());// 赋值分页输出
        if (input('is_ajax')) {
            return $this->fetch('ajax_return_goods_list');
            exit;
        }
        return $this->fetch();
    }

    /**
     *  退货详情
     */
    public function return_goods_info(){
        $id = input('id/d', 0);
        $return_goods = Db::name('return_goods')->where("id = $id")->find();
        if ($return_goods['imgs'])
            $return_goods['imgs'] = explode(',', $return_goods['imgs']);
        $goods = Db::name('goods')->where("goods_id = {$return_goods['goods_id']} ")->find();
        $this->assign('goods', $goods);
        $this->assign('return_goods', $return_goods);
        return $this->fetch();
    }

	//充值
    public function recharge(){
        $order_id = input('order_id/d');
        $paymentList = Db::name('plugin')->where("`type`='payment' and code!='cod' and status = 1 and  scene in(0,1)")->select();
        //微信浏览器
        if (strstr($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger')) {
            $paymentList = Db::name('Plugin')->where("`type`='payment' and status = 1 and code='weixin'")->select();
        }
        $paymentList = convert_arr_key($paymentList, 'code');

        foreach ($paymentList as $key => $val) {
            $val['config_value'] = unserialize($val['config_value']);
            if ($val['config_value']['is_bank'] == 2) {
                $bankCodeList[$val['code']] = unserialize($val['bank_code']);
            }
        }
        $bank_img = include APP_PATH . 'home/bank.php'; // 银行对应图片
        $payment = Db::name('Plugin')->where("`type`='payment' and status = 1")->select();
        $this->assign('paymentList', $paymentList);
        $this->assign('bank_img', $bank_img);
        $this->assign('bankCodeList', $bankCodeList);

        if ($order_id > 0) {
            $order = Db::name('recharge')->where("order_id", $order_id)->find();
            $this->assign('order', $order);
        }
        return $this->fetch();
    }

    /**
     * 申请提现记录
     */
    public function withdrawals(){
        config('TOKEN_ON', true);
        if (request()->isPost()) {
            $this->verifyHandle('withdrawals');//验证码
            $data = input('post.');
            $data['userid'] = $this->userid;
            $data['create_time'] = time();
            $distribut_min = config('config.distribut_min'); // 最少提现额度
            if ($data['money'] < $distribut_min) {
                $this->error('每次最少提现额度' . $distribut_min);
                exit;
            }
            if ($data['money'] > $this->user['mymoney']) {
                $this->error("你最多可提现{$this->user['mymoney']}账户余额.");
                exit;
            }
            $withdrawal = Db::name('withdrawals')->where(['userid' => $this->userid, 'status' => 0])->sum('money');
            if ($this->user['mymoney'] < ($withdrawal + $data['money'])) {
                $this->error('您有提现申请待处理，本次提现余额不足');
            }
			unset($data['verify_code']);
            if(Db::name('withdrawals')->insert($data)) {
                $this->success("已提交申请");
                exit;
            } else {
                $this->error('提交失败,联系客服!');
                exit;
            }
        }

        $withdrawals_where['userid'] = $this->userid;
        $count = Db::name('withdrawals')->where($withdrawals_where)->count();
        $pagesize = config('paginate.list_rows');
        $page = new Page($count, $pagesize);
        $list = Db::name('withdrawals')->where($withdrawals_where)->order("id desc")->limit("{$page->firstRow},{$page->listRows}")->select();
        $this->assign('page', $page->show());// 赋值分页输出
        $this->assign('list', $list); // 下线
        if(input('is_ajax')==1){
            return $this->fetch('ajax_withdrawals_list');
            exit;
        }
		
        //$order_count = Db::name('order')->where("userid", $this->userid)->count(); // 我的订单数
        //$goods_collect_count = Db::name('goods_collect')->where("userid", $this->userid)->count(); // 我的商品收藏
        //$comment_count = Db::name('goods_comment')->where("userid", $this->userid)->count();//  我的评论数
        //$coupon_count = Db::name('coupon_list')->where("uid", $this->userid)->count(); // 我的优惠券数量
        //$level_name = Db::name('member_level')->where("id", $this->user['level'])->value('name'); // 等级名称
        //$this->assign('level_name', $level_name);
        //$this->assign('order_count', $order_count);
        //$this->assign('goods_collect_count', $goods_collect_count);
        //$this->assign('comment_count', $comment_count);
        //$this->assign('coupon_count', $coupon_count);
        $this->assign('mymoney', $this->user['mymoney']);    //用户余额
        return $this->fetch();
    }

    /**
     * 申请记录列表
     */
    public function withdrawals_list(){
        $withdrawals_where['userid'] = $this->userid;
        $count = Db::name('withdrawals')->where($withdrawals_where)->count();
        $pagesize = config('paginate.list_rows');
        $page = new Page($count, $pagesize);
        $list = Db::name('withdrawals')->where($withdrawals_where)->order("id desc")->limit("{$page->firstRow},{$page->listRows}")->select();

        $this->assign('page', $page->show());// 赋值分页输出
        $this->assign('list', $list); // 下线
        if (input('is_ajax')) {
            return $this->fetch('ajax_withdrawals_list');
            exit;
        }
        return $this->fetch();
    }

    /**
     * 删除已取消的订单
     */
    public function order_del(){
        $userid = $this->userid;
        $order_id = input('get.order_id/d');
        $order = Db::name('order')->where(array('order_id' => $order_id, 'userid' => $userid))->find();
        if (empty($order)) {
            return $this->error('订单不存在');
            exit;
        }
        $res = Db::name('order')->where("order_id=$order_id and order_status=3")->delete();
        $result = Db::name('order_goods')->where("order_id=$order_id")->delete();
        if ($res && $result) {
            return $this->success('成功', "mobile/User/order_list");
            exit;
        } else {
            return $this->error('删除失败');
            exit;
        }
    }

    /**
     * 我的关注
     * $author lxl
     * $time   2017/1
     */
    public function myfocus()
    {
        return $this->fetch();
    }

    /**
     * 待收货列表
     * $author lxl
     * $time   2017/1
     */
    public function wait_receive(){
        $where = ' userid=' . $this->userid;
        //条件搜索
        if (input('type') == 'WAITRECEIVE') {
            $where .= config(strtoupper(input('type')));
        }
        $count = Db::name('order')->where($where)->count();
        $pagesize = config('paginate.list_rows');
        $Page = new Page($count, $pagesize);
        $show = $Page->show();
        $order_str = "order_id DESC";
        $order_list = Db::name('order')->order($order_str)->where($where)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        //获取订单商品
        $model = new UsersLogic();
        foreach ($order_list as $k => $v) {
            $order_list[$k] = set_btn_order_status($v);  // 添加属性  包括按钮显示属性 和 订单状态显示属性
            //$order_list[$k]['total_fee'] = $v['goods_amount'] + $v['shipping_fee'] - $v['integral_money'] -$v['bonus'] - $v['discount']; //订单总额
            $data = $model->get_order_goods($v['order_id']);
            $order_list[$k]['goods_list'] = $data['result'];
        }

        //统计订单商品数量
        foreach ($order_list as $key => $value) {
            $count_goods_num = '';
            foreach ($value['goods_list'] as $kk => $vv) {
                $count_goods_num += $vv['goods_num'];
            }
            $order_list[$key]['count_goods_num'] = $count_goods_num;
            //订单物流单号
            $invoice_no = Db::name('DeliveryDoc')->where("order_id", $value['order_id'])->value('invoice_no');
            $order_list[$key]['invoice_no'] = implode(' , ', $invoice_no);
        }
        $this->assign('page', $show);
        $this->assign('order_list', $order_list);
        if ($_GET['is_ajax']) {
            return $this->fetch('ajax_wait_receive');
            exit;
        }
        return $this->fetch();
    }

    /**
     *  用户消息通知
     * @author dyr
     * @time 2016/09/01
     */
    public function message_notice()
    {
        return $this->fetch('user/message_notice');
    }

    /**
     * ajax用户消息通知请求
     * @author dyr
     * @time 2016/09/01
     */
    public function ajax_message_notice()
    {
        $type = input('type', 0);
        $user_logic = new UsersLogic();
        $message_model = new Message();
        if ($type == 1) {
            //系统消息
            $user_sys_message = $message_model->getUserMessageNotice();
            $user_logic->setSysMessageForRead();
        } else if ($type == 2) {
            //活动消息：后续开发
            $user_sys_message = array();
        } else {
            //全部消息：后续完善
            $user_sys_message = $message_model->getUserMessageNotice();
        }
        $this->assign('messages', $user_sys_message);
        return $this->fetch('user/ajax_message_notice');

    }

    /**
     * 设置消息通知
     */
    public function set_notice(){
        //暂无数据
        return $this->fetch();
    }

}

