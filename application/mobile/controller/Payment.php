<?php
/**
 * 支付  
 * ============================================================================
 * 版权所有 Ybcms开发团队，并保留所有权利
 * 网站地址: http://www.ybcms.com
 * ============================================================================
 * Author: ybcms(dreamlak@qq.com)      
 * UpDate: 2017-03-01
 */
namespace app\mobile\controller;
use think\Request;
use think\db;
class Payment extends MobileBase {
    
    public $payment; //  具体的支付类
    public $pay_code; //  具体的支付code
 
    /**
     * 析构流函数
     */
    public function  __construct() {   
        parent::__construct();      
        // tpshop 订单支付提交
        $pay_radio = $_REQUEST['pay_radio'];
        if(!empty($pay_radio)) {                         
            $pay_radio = parse_url_param($pay_radio);
            $this->pay_code = $pay_radio['pay_code']; // 支付 code
        }else{ 
			// 第三方 支付商返回
            $this->pay_code = input('pay_code');
            unset($_GET['pay_code']); // 用完之后删除, 以免进入签名判断里面去 导致错误
        }                        
        //获取通知的数据
        $xml = $GLOBALS['HTTP_RAW_POST_DATA'];    
        if(empty($this->pay_code))
            exit('pay_code 不能为空');        
        // 导入具体的支付类文件                
        include_once  "plugins/payment/{$this->pay_code}/{$this->pay_code}.class.php";
        $code = '\\'.$this->pay_code; // \alipay
        $this->payment = new $code();
    }
   
    /**
     * 提交支付方式
     */
    public function getCode(){     
        //header("Content-type:text/html;charset=utf-8");
        $order_id = input('order_id/d'); //订单id
        //修改订单的支付方式
		$pay_name=Db::name('plugin')->where('code',$this->pay_code)->value('name');
        Db::name('order')->where("order_id", $order_id)->update(['pay_code'=>$this->pay_code,'pay_name'=>$pay_name]);
        
        $order = Db::name('order')->where("order_id", $order_id)->find();
        if($order['pay_status'] == 1){
        	$this->error('此订单，已完成支付!');
        }
        //订单支付提交
        $pay_radio = $_REQUEST['pay_radio'];
        $config_value = parse_url_param($pay_radio); // 获取类似于 pay_code=alipay&bank_code=CCB-DEBIT 的参数
        
        //微信JS支付
       	if($this->pay_code == 'weixin' && $_SESSION['openid'] && strstr($_SERVER['HTTP_USER_AGENT'],'MicroMessenger')){
           	$code_str = $this->payment->getJSAPI($order);
           	exit($code_str);
       	}else{
       		$code_str = $this->payment->get_code($order,$config_value);
       	}
       	
        $this->assign('code_str', $code_str); 
        $this->assign('order_id', $order_id); 
        return $this->fetch('payment');  // 分跳转 和不 跳转
    }
	//手机端在线充值
    public function getPay(){
        header("Content-type:text/html;charset=utf-8");
        $order_id = input('order_id/d'); //订单id
        $user = session('user');
        $data['account'] = input('account');
        if($order_id>0){
        	Db::name('recharge')->where(['order_id'=>$order_id,'userid'=>$user['userid']])->update($data);//更新充值金额
        }else{
        	$data['userid'] = $user['userid'];
        	$data['nickname'] = $user['nickname'];
        	$data['order_sn'] = 'recharge'.get_rand_str(10,0,1);
        	$data['ctime'] = time();
        	$order_id = Db::name('recharge')->insertGetId($data);
        }
        if($order_id){
        	$order = Db::name('recharge')->where("order_id", $order_id)->find();
        	if(is_array($order) && $order['pay_status']==0){
        		$order['order_amount'] = $order['account'];
        		$pay_radio = $_REQUEST['pay_radio'];
        		$config_value = parse_url_param($pay_radio); // 类似于 pay_code=alipay&bank_code=CCB-DEBIT 参数
        		$payment_arr = Db::name('plugin')->where("`type` = 'payment'")->column("code,name");
        		Db::name('recharge')->where("order_id", $order_id)->update(['pay_code'=>$this->pay_code,'pay_name'=>$payment_arr[$this->pay_code]]);
        		//微信JS支付
        		if($this->pay_code == 'weixin' && $_SESSION['openid'] && strstr($_SERVER['HTTP_USER_AGENT'],'MicroMessenger')){
        			$code_str = $this->payment->getJSAPI($order);
        			exit($code_str);
        		}else{
        			$code_str = $this->payment->get_code($order,$config_value);
        		}
        	}else{
        		$this->error('此充值订单，已完成支付!');
        	}
        }else{
        	$this->error('提交失败,参数有误!');
        }
        $this->assign('code_str', $code_str); 
        $this->assign('order_id', $order_id); 
    	return $this->fetch('recharge'); //分跳转 和不 跳转
    }

    // 服务器点对点 /index.php/Home/Payment/notifyUrl
    public function notifyUrl(){            
        $this->payment->response();            
        exit();
    }

    // 页面跳转 /Home/Payment/returnUrl        
    public function returnUrl(){
        $result = $this->payment->respond2(); // $result['order_sn'] = '201512241425288593';  
        if(stripos($result['order_sn'],'recharge') !== false){
        	$order = Db::name('recharge')->where("order_sn", $result['order_sn'])->find();
        	$this->assign('order', $order);
        	if($result['status'] == 1)
        		return $this->fetch('recharge_success');
        	else
        		return $this->fetch('recharge_error');
        	exit();
        }          
        $order = Db::name('order')->where("order_sn", $result['order_sn'])->find();
        $this->assign('order', $order);
        if($result['status'] == 1){
            return $this->fetch('success');
        }else{
            return $this->fetch('error');
		}
    }                
}
