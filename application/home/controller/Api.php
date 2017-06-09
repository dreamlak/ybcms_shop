<?php
/**
 * API
 * ============================================================================
 * 版权所有 Ybcms开发团队，并保留所有权利
 * 网站地址: http://www.ybcms.com
 * ============================================================================
 * Author: ybcms(dreamlak@qq.com)      
 * UpDate: 2017-03-01
 */
namespace app\home\controller;
use app\home\logic\UsersLogic;
use think\Session;
use think\Controller;
use think\Verify;
use think\Db;
class Api extends Controller {
    public  $send_scene;
    
    public function _initialize() {
        Session::start();
    }
    /*
     * 获取地区
     */
    public function getRegion(){
        $parent_id = input('parent_id/d');
        $selected = input('selected',0);        
        $data = Db::name('areas')->where("pid",$parent_id)->select();
        $html = '';
        if($data){
            foreach($data as $h){
            	if($h['id'] == $selected){
            		$html .= "<option value='{$h['id']}' selected>{$h['name']}</option>";
            	}
                $html .= "<option value='{$h['id']}'>{$h['name']}</option>";
            }
        }
        echo $html;
    }
    

    public function getTwon(){
    	$parent_id = input('parent_id/d');
    	$data = Db::name('areas')->where("id",$parent_id)->select();
    	$html = '';
    	if($data){
    		foreach($data as $h){
    			$html .= "<option value='{$h['id']}'>{$h['name']}</option>";
    		}
    	}
    	if(empty($html)){
    		echo '0';
    	}else{
    		echo $html;
    	}
    }
    
    /*
     * 获取地区
     */
    public function get_category(){
        $parent_id = input('parent_id/d'); // 商品分类 父id
            $list = Db::name('goods_category')->where("parent_id", $parent_id)->select();
        
        foreach($list as $k => $v)
            $html .= "<option value='{$v['id']}'>{$v['name']}</option>";        
        exit($html);
    }  
    
    
    /**
     * 前端发送短信方法: APP/WAP/PC 共用发送方法
	 * 场景: 1注册验证码提示, 2找回密码 ,3手机认证, 4客户下单, 5客户支付, 6商家发货, 7支付通知
     */
    public function send_validate_code(){
        $type = input('type');//发关类型
        $scene = input('scene');//发送短信验证码使用场景
        $mobile = input('mobile');//手机号
        $sender = input('send');//发送号码
        $verify_code = input('verify_code');//验证号码
        $mobile = !empty($mobile)?$mobile:$sender;//发送号码取值
		
        $session_id = input('unique_id' , session_id());
        session("scene" , $scene);
        //注册时，有验证码时
        if($scene==1 && !empty($verify_code)){
			if(!captcha_check($verify_code)){
	    		return $this->error("验证码错误");
   			}
        }
		//邮件验证时
        if($type == 'email'){
            //发送邮件验证码
            $logic = new UsersLogic();
            $res = $logic->send_validate_code($sender);
            ajaxReturn($res);
        }else{
        	//短验证时
            //判断是否存在验证码
            $data = Db::name('sms_log')->where(['mobile'=>$mobile,'session_id'=>$session_id,'status'=>1])->order('id DESC')->find();
            //获取时间配置
            $sms_time_out = config('config.sms_time_out');
            $sms_time_out = $sms_time_out?$sms_time_out:120;
            //120秒以内不可重复发送
            if($data && (time() - $data['add_time']) < $sms_time_out){
                $return_arr = ['status'=>-1,'msg'=>$sms_time_out.'秒内不允许重复发送'];
                ajaxReturn($return_arr);
            }
            //随机一个验证码
            $code = rand(1000, 9999);
			$params = ['code'=>$code];
            $user = session('user');
            if ($scene == 3){//身份验证时
                if(!$user['userid']){
                    ajaxReturn(array('status'=>-1,'msg'=>'登录超时'));
                }
                if($user['nickname']){
                    $params['username'] = $user['nickname'];
                }
            }
            $params['code'] =$code;
            
            //发送短信
            $resp = sendSms($scene , $mobile , $params);
            if($resp['status'] == 1){
                //发送成功, 修改发送状态位成功
                Db::name('sms_log')->where(['mobile'=>$mobile,'code'=>$code,'session_id'=>$session_id,'status'=>0])->update(['status'=>1]);
                $return_arr = array('status'=>1,'msg'=>'发送成功,请注意查收');
            }else{
                $return_arr = array('status'=>-1,'msg'=>'发送失败'.$resp['msg']);
            }
            ajaxReturn($return_arr);
        }
    }
    
    /**
     * 验证短信验证码: APP/WAP/PC 共用发送方法
     */
    public function check_validate_code(){
          
        $code = input('post.code');
        $mobile = input('mobile');
        $send = input('send');
        $sender = empty($mobile) ? $send : $mobile; 
        $type = input('type');
        $session_id = input('unique_id', session_id());
        $scene = input('scene', -1);

        $logic = new UsersLogic();
        $res = $logic->check_validate_code($code, $sender, $type ,$session_id, $scene);
        ajaxReturn($res);
    }
    
    /**
     * 检测手机号是否已经存在
     */
    public function issetMobile(){
      	$username = input("username",'0');  
     	$users = Db::name('member')->where('tel',$username)->whereOr('nickname',$username)->whereOr('username',['like','%'.$username.'%'])->find();
      	if($users){
          	exit ('1');
      	}else{ 
          	exit ('0');
	  	}    
    }

    public function issetMobileOrEmail(){
        $username = input("username",'0');
		if(!check_email($username)){
			$emailArr = explode("@",$username);
			$username=$emailArr[0];
		}
        $users = Db::name('member')
        ->where("email",['like','%'.$username.'%'])
        ->whereOr('tel',$username)
        ->whereOr('nickname',$username)
        ->whereOr('username',['like','%'.$username.'%'])
        ->find();
		
        if($users){
          	exit ('1');
      	}else{ 
          	exit ('0');
	  	}
    }
    /**
     * 查询物流
     */
    public function queryExpress(){
        $shipping_code = input('shipping_code');
        $invoice_no = input('invoice_no');
        if(empty($shipping_code) || empty($invoice_no)){
            return json(['status'=>0,'message'=>'参数有误','result'=>'']);
        }
        return json(queryExpress($shipping_code,$invoice_no));
    }

    public function test(){
        $scene = session("scene");
        echo ' scene : '.$scene;
    }
    
    /**
     * 检查订单状态
     */
    public function check_order_pay_status(){
        $order_id = input('order_id/d');
        if(empty($order_id)){
            $res = ['message'=>'参数错误','status'=>-1,'result'=>''];
            exit(json_encode($res));
        }
        $order = Db::name('order')->field('pay_status')->where(['order_id'=>$order_id])->find();
        if($order['pay_status'] != 0){
            $res = ['message'=>'已支付','status'=>1,'result'=>$order];
        }else{
            $res = ['message'=>'未支付','status'=>0,'result'=>$order];
        }
        exit(json_encode($res));
    }

    /**
     * 广告位js
     */
    public function ad_show(){
        $pid = input('pid/d',1);
        $where = array(
            'pid'=>$pid,
            'enable'=>1,
            'start_time'=>array('lt',strtotime(date('Y-m-d H:00:00'))),
            'end_time'=>array('gt',strtotime(date('Y-m-d H:00:00'))),
        );
        $ad = Db::name("poster")->where($where)->order("orderby desc")->cache(true,CACHE_TIME)->find();
        $this->assign('ad',$ad);
        return $this->fetch();
    }
}