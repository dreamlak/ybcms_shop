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
namespace app\index\controller; 
use think\Request;
use think\Db;
class Payment extends Base {
    
    public $payment; //  具体的支付类
    public $pay_code; //  具体的支付code
 
    /**
     * 析构流函数
     */
    public function  __construct() {   
        parent::__construct();           
        //订单支付提交
        $pay_radio = empty($_REQUEST['pay_radio'])?input('pay_radio'):$_REQUEST['pay_radio'];
		//dump($pay_radio);die;
        if(!empty($pay_radio)) {                         
            $pay_radio = parse_url_param($pay_radio);
            $this->pay_code = $pay_radio['pay_code']; // 支付 code
        }else{ // 第三方 支付商返回
            $this->pay_code = input('pay_code');
            unset($_GET['pay_code']); // 用完之后删除, 以免进入签名判断里面去 导致错误
        }                        
        //获取通知的数据
        $xml = $GLOBALS['HTTP_RAW_POST_DATA'];      
        
        if(empty($this->pay_code)){
            exit('pay_code 不能为空');
		}
        // 导入具体的支付类文件                
        include_once  "plugins/payment/{$this->pay_code}/{$this->pay_code}.class.php"; // D:\wamp\www\svn_tpshop\www\plugins\payment\alipay\alipayPayment.class.php                       
        $code = '\\'.$this->pay_code; // \alipay
        $this->payment = new $code();
    }
   
    /**
     * 提交支付方式
     */
    public function getCode(){        
            //C('TOKEN_ON',false); // 关闭 TOKEN_ON
            header("Content-type:text/html;charset=utf-8");            
            $order_id = input('order_id/d'); // 订单id
            session('order_id',$order_id); // 最近支付的一笔订单 id
            //修改订单的支付方式
			$data=[];
			$data['pay_code']=$this->pay_code;
			$data['pay_name']=Db::name('plugin')->where(['code'=>$this->pay_code,'type'=>'payment'])->value('name');
            Db::name('order')->where("order_id",$order_id)->update($data);
            
            $order = Db::name('order')->where("order_id", $order_id)->find();
            if($order['pay_status'] == 1){
            	$this->error('此订单，已完成支付!');
            }
            
            //订单支付提交
			$pay_radio = empty($_REQUEST['pay_radio'])?input('pay_radio'):$_REQUEST['pay_radio'];
            $config_value = parse_url_param($pay_radio); // 类似于 pay_code=alipay&bank_code=CCB-DEBIT 参数
            
            //微信JS支付
           	if($this->pay_code == 'weixin' && $_SESSION['openid'] && strstr($_SERVER['HTTP_USER_AGENT'],'MicroMessenger')){
               	$code_str = $this->payment->getJSAPI($order,$config_value);
               	exit($code_str);
           	}else{
           		$code_str = $this->payment->get_code($order,$config_value);
           	}
			
           	$this->assign('code_str', $code_str); 
           	$this->assign('order_id', $order_id);
			
           	return $this->fetch('payment');  // 分跳转 和不 跳转 
    }
	//充值
    public function getPay(){
    	header("Content-type:text/html;charset=utf-8"); 
    	$order_id = input('order_id/d'); // 订单id
        session('order_id',$order_id); // 最近支付的一笔订单 id

    	$data=[];
		$data['paycode']=$this->pay_code;
		$data['paytype']=Db::name('plugin')->where(['code'=>$this->pay_code,'type'=>'payment'])->value('name');
    	Db::name('member_recharge')->where("id", $order_id)->update($data);
		
    	$order = Db::name('member_recharge')->where("id", $order_id)->find();
    	if($order['paystatus'] == 1){
    		$this->error('此订单，已完成支付!');
    	}

    	$pay_radio = empty($_REQUEST['pay_radio'])?input('pay_radio'):$_REQUEST['pay_radio'];
    	$config_value = parse_url_param($pay_radio); // 类似于 pay_code=alipay&bank_code=CCB-DEBIT 参数
        $order['order_amount'] = $order['account'];//应付款金额
    	$code_str = $this->payment->get_code($order,$config_value);
		
    	//微信JS支付
    	if($this->pay_code == 'weixin' && $_SESSION['openid'] && strstr($_SERVER['HTTP_USER_AGENT'],'MicroMessenger')){
    		$code_str = $this->payment->getJSAPI($order,$config_value);
    		exit($code_str);
    	}
		
    	$this->assign('code_str', $code_str);
    	$this->assign('order_id', $order_id);
		
    	return $this->fetch('recharge'); //分跳转 和不 跳转
    }
    
    // 服务器点对点 /index/Payment/notifyUrl        
    public function notifyUrl(){            
        $this->payment->response();            
        exit();
    }

    // 页面跳转/index/Payment/returnUrl        
    public function returnUrl(){
    	//充值
        $result = $this->payment->respond2(); // $result['order_sn'] = '201512241425288593';
        if(stripos($result['order_sn'],'recharge') !== false){
            $order = Db::name('member_recharge')->where("ordersn", $result['order_sn'])->find();
            $this->assign('order', $order);
            if($result['status'] == 1){
                return $this->fetch('recharge_success');   
            }else{
                return $this->fetch('recharge_error');
			}
            exit();            
        }
		
        //订单
        $order = Db::name('order')->where("order_sn", $result['order_sn'])->find();
        if(empty($order)){ // order_sn 找不到 根据 order_id 去找
            $order_id = session('order_id'); // 最近支付的一笔订单 id        
            $order = Db::name('order')->where("order_id", $order_id)->find();
        }
        
        $this->assign('order', $order);
        if($result['status'] == 1){
            return $this->fetch('success');   
        }else{
            return $this->fetch('error');
		}
    }                
}
