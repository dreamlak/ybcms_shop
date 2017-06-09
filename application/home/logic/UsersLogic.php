<?php
/**
 * 用户类
 * ============================================================================
 * 版权所有 Ybcms开发团队，并保留所有权利
 * 网站地址: http://www.ybcms.com
 * ============================================================================
 * Author: ybcms(dreamlak@qq.com)      
 * UpDate: 2017-03-01
 */

namespace app\home\logic;
use app\home\model\UserAddress;
use think\Model;
use think\Page;
use think\db;
class UsersLogic extends Model{
    /*
     * 登陆
     */
    public function login($username,$password){
    	$result = array();
        if(!$username || !$password){
           $result= array('status'=>0,'msg'=>'请填写账号或密码');
		}
		
        $user = Db::name('member')->where("username|tel|email","=",$username)->find();
		$password=md5($password.$user['encrypt']);
		//dump($password);die;
        if(!$user){
           $result = array('status'=>-1,'msg'=>'账号不存在!');
        }elseif($password != $user['password']){
           $result = array('status'=>-2,'msg'=>'密码错误!');
        }elseif($user['status'] == 0){
           $result = array('status'=>-3,'msg'=>'账号异常已被锁定！！！');
        }else{
            //查询用户信息之后, 查询用户的登记昵称
            $levelId = $user['level'];
            $levelName = Db::name("member_level")->where("id", $levelId)->value("name");
            $user['level_name'] = $levelName;
          	//addUserLog('登陆成功',authType(),$user['userid'],$user['username']);//添加用户日志
           	$result = array('status'=>1,'msg'=>'登陆成功','result'=>$user);
        }
        return $result;
    }

    /*
     * app端登陆
     */
    public function app_login($username,$password){
    	$result = array();
        if(!$username || !$password){
           $result= array('status'=>0,'msg'=>'请填写账号或密码');
		}
		
        $user = Db::name('member')->where("username|tel|email","=",$username)->find();
		$password=md5($password.$user['encrypt']);
        if(!$user){
           $result = array('status'=>-1,'msg'=>'账号不存在!');
        }elseif($password != $user['password']){
           $result = array('status'=>-2,'msg'=>'密码错误!');
        }elseif($user['status'] == 1){
           $result = array('status'=>-3,'msg'=>'账号异常已被锁定！！！');
        }else{
            //查询用户信息之后, 查询用户的登记昵称
            $levelId = $user['level'];
            $levelName = Db::name("member_level")->where("id", $levelId)->value("name");
            $user['level_name'] = $levelName;            

            Db::name('member')->where("userid", $user['userid'])->update(array('lastip'=>request()->ip(),'lasttime'=>time()));
            
            addUserLog('登陆成功','app',$user['userid'],$user['username']);
            $result = array('status'=>1,'msg'=>'登陆成功','result'=>$user);
        }
        return $result;
    }    
    
    
    //绑定账号
    public function oauth_bind($data = array()){
    	$user = session('user');
    	if(empty($user['openid'])){
    		if(Db::name('member')->where(array('openid'=>$data['openid']))->count()>0){
    			return array('status'=>-1,'msg'=>'您的'.$data['oauth'].'账号已经绑定过账号');
    		}else{
    			Db::name('member')->where(array('userid'=>$user['userid']))->update($data);
				addUserLog('绑定账号','pc');
    			return array('status'=>1,'msg'=>'绑定成功','result'=>$data);
    		}
    	}else{
    		return array('status'=>-1,'msg'=>'您的账号已绑定过，请不要重复绑定');
    	}
    }
    /*
     * 第三方登录
     */
    public function thirdLogin($data=array()){
        $openid = $data['openid']; //第三方返回唯一标识
        $oauth = $data['oauth']; //来源
        if(!$openid || !$oauth) return array('status'=>-1,'msg'=>'参数有误','result'=>'');
		
        //获取用户信息
        if(isset($data['unionid'])){
        	$map['unionid'] = $data['unionid'];
        	$user = get_user_info($data['unionid'],4,$oauth);
        }else{
        	$user = get_user_info($openid,3,$oauth);
        }
		
        if(!$user){
        	$encrypt=GetRandStr(6);
			$password=md5('ybcmscom'.$encrypt);//加密后的新密码
			$username='ybcms_'.$encrypt;
			$codes=base64_encode($username.$password);
			
            //账户不存在 注册一个
            $map['username'] = $username;
            $map['password'] = $password;
			$map['encrypt'] = $encrypt;
            $map['openid'] = $openid;
            $map['nickname'] = $data['nickname'];
            $map['regtime'] = time();
            $map['oauth'] = $oauth;
            $map['avatar'] = $data['avatar'];
            $map['sex'] = empty($data['sex']) ? 0 : $data['sex'];
            $map['token'] = hash('md5',$codes);
            $map['first_leader'] = cookie('first_leader'); // 推荐人id
            
            if($_GET['first_leader']){
                $map['first_leader'] = $_GET['first_leader']; // 微信授权登录返回时 get 带着参数的
            }
			
            // 如果找到他老爸还要找他爷爷他祖父等
            if($map['first_leader']){
                $first_leader = Db::name('member')->where("userid", $map['first_leader'])->find();
                $map['second_leader'] = $first_leader['first_leader']; //  第一级推荐人
                $map['third_leader'] = $first_leader['second_leader']; // 第二级推荐人
            }else{
				$map['first_leader'] = 0;
			}
			
			$map['regip'] = request()->ip();
            // 成为分销商条件  
            $distribut_condition = config('config.distribut_condition'); 
            if($distribut_condition == 0){  // 直接成为分销商, 每个人都可以做分销        
                $map['is_distribut']  = 1;
			}
			//添加用户
            $row_id = Db::name('member')->insertGetId($map);
			
			//会员注册送优惠券
			$coupon = Db::name('coupon')->where("send_end_time > ".time()." and ((createnum - send_num) > 0 or createnum = 0) and type = 2")->select();
			foreach ($coupon as $key => $val){
				// 送券            
				Db::name('coupon_list')->insert(array('cid'=>$val['id'],'type'=>$val['type'],'uid'=>$row_id,'send_time'=>time()));
				Db::name('Coupon')->where("id", $val['id'])->setInc('send_num'); // 优惠券领取数量加一
			}
            $user = Db::name('member')->where("userid", $row_id)->find();
        }else{
            Db::name('member')->where("userid", $user['userid'])->update(array('lastip'=>request()->ip(),'lasttime'=>time()));
        }
		addUserLog('登陆成功',$oauth,$user['userid'],$user['username']);
        return array('status'=>1,'msg'=>'登陆成功','result'=>$user);
    }

    /**
     * 注册
     * @param $username  邮箱或手机
     * @param $password  密码
     * @param $password2 确认密码
     * @return array
     */
    public function reg($username,$password,$password2){
    	$map=[];
    	$is_validated = 0 ;
        if(check_email($username)){
        	//邮箱注册
            $is_validated = 1;
            $map['email_validated'] = 1;
            $map['email'] = $username; 
			$emailArr = explode("@",$username);
			$map['nickname'] = $emailArr[0];
			$map['username'] = 'ybcms_'.$emailArr[0];
        }elseif(check_mobile($username)){
        	//手机注册
            $is_validated = 1;
            $map['mobile_validated'] = 1;
			$map['tel'] = $map['nickname'] = $username;
            $map['username'] = 'ybcms_'.$username; 
        }else{
        	//普通注册
        	$map['nickname'] = $map['username'] = $username; 
        }
        if($is_validated != 1){
            return array('status'=>-1,'msg'=>'请用手机号或邮箱注册');
		}
        if(!$username || !$password){
            return array('status'=>-1,'msg'=>'请输入用户名或密码');
		}
        //验证两次密码是否匹配
        if($password2 != $password){
            return array('status'=>-1,'msg'=>'两次输入密码不一致');
		}
        //验证是否存在用户名
        $usercount = Db::name('member')->where("username|tel|email","=",$username)->count();
        if($usercount>0){
            return array('status'=>-1,'msg'=>'账号已存在');
        }
		
		$encrypt=GetRandStr(6);
		$password=md5($password.$encrypt);//加密后的新密码
        $map['password'] = $password;
		$map['encrypt'] = $encrypt;
        $map['regtime']=$map['lasttime'] = time();
		$map['regip']=$map['lastip'] = request()->ip();
        $map['first_leader'] = cookie('first_leader'); // 推荐人id
        // 如果找到他老爸还要找他爷爷他祖父等
        if($map['first_leader']){
            $first_leader = Db::name('member')->where("userid", $map['first_leader'])->find();
            $map['second_leader'] = $first_leader['first_leader'];
            $map['third_leader'] = $first_leader['second_leader'];
        }else{
			$map['first_leader'] = 0;
		}              
        
        // 成为分销商条件  
        $distribut_condition = config('config.distribut_condition'); 
        if($distribut_condition == 0){  // 直接成为分销商, 每个人都可以做分销        
            $map['is_distribut']  = 1;        
        }
        $codes=base64_encode($username.$password);
        $map['token'] = hash('md5',$codes);
		//添加用户
        $userid = Db::name('member')->insertGetId($map);
		
        if($userid === false){
            return array('status'=>-1,'msg'=>'注册失败');
		}
        $pay_points = config('config.reg_integral'); //会员注册赠送积分
        // 记录日志流水
        if($pay_points > 0){
            accountLog($userid,0,$pay_points, '会员注册赠送积分'); 
		}
        // 会员注册送优惠券
        $coupon = Db::name('coupon')->where("send_end_time > ".time()." and ((createnum - send_num) > 0 or createnum = 0) and type = 2")->select();
        if(!empty($coupon)){
        	foreach ($coupon as $key => $val){
        		Db::name('coupon_list')->insert(array('cid'=>$val['id'],'type'=>$val['type'],'uid'=>$userid,'send_time'=>time()));
        		Db::name('Coupon')->where("id", $val['id'])->setInc('send_num'); // 优惠券领取数量加一
        	}
        }
        $user = Db::name('member')->where("userid", $userid)->find();
		
		//addUserLog('注册成功','pc',$user['userid'],$user['username']);
        return array('status'=>1,'msg'=>'注册成功','result'=>$user);
    }

     /*
      * 获取当前登录用户信息
      */
     public function get_info($userid){
        if(!$userid > 0){
            return array('status'=>-1,'msg'=>'缺少参数','result'=>'');
		}
        $user_info = Db::name('member')->where("userid", $userid)->find();
        if(!$user_info){
            return false;
		}
         $user_info['coupon_count'] = Db::name('coupon_list')->where(['uid'=>$userid,'use_time'=>0])->count(); //获取优惠券列表
         $user_info['collect_count'] = Db::name('goods_collect')->where(array('userid'=>$userid))->count(); //获取收藏数量         
                                    
         $user_info['waitPay']     = Db::name('order')->where("userid = :userid ".config('WAITPAY'))->bind(['userid'=>$userid])->count(); //待付款数量
         $user_info['waitSend']    = Db::name('order')->where("userid = :userid ".config('WAITSEND'))->bind(['userid'=>$userid])->count(); //待发货数量
         $user_info['waitReceive'] = Db::name('order')->where("userid = :userid ".config('WAITRECEIVE'))->bind(['userid'=>$userid])->count(); //待收货数量
         $user_info['order_count'] = $user_info['waitPay'] + $user_info['waitSend'] + $user_info['waitReceive'];
         return array('status'=>1,'msg'=>'获取成功','result'=>$user_info);
     }
     
    /*
     * 获取最近一笔订单
     */
    public function get_last_order($userid){
        $last_order = Db::name('order')->where("userid", $userid)->order('order_id DESC')->find();
        return $last_order;
    }


    /*
     * 获取订单商品
     */
    public function get_order_goods($order_id){
        $sql = "SELECT og.* FROM __PREFIX__order_goods og LEFT JOIN __PREFIX__goods g ON g.goods_id = og.goods_id WHERE order_id = :order_id";
        $bind['order_id'] = $order_id;
        $goods_list = query($sql,$bind);
		foreach($goods_list as $k=>$v){
			//退换货信息
			$rdata=Db::name('return_goods')->where(['order_id'=>$order_id,'goods_id'=>$v['goods_id']])->find();
			if(!empty($rdata)){
				$goods_list[$k]['return_id']=$rdata['id'];
				$goods_list[$k]['return_type']=$rdata['type'];
				$goods_list[$k]['return_typen']=$rdata['type']==0?'退货':'换货';
				$goods_list[$k]['return_status']=$rdata['status'];
			}else{
				$goods_list[$k]['return_id']='';
				$goods_list[$k]['return_type']='';
				$goods_list[$k]['return_typen']='';
				$goods_list[$k]['return_status']=-1;
			}
		}
		
        $return['status'] = 1;
        $return['msg'] = '';
        $return['result'] = $goods_list;
        return $return;
    }

    /*
     * 获取账户资金记录
     */
    public function get_account_log($userid,$type=0){
        //查询条件
        if($type == 1){
            //收入
            $where = 'mymoney > 0 OR mypoints > 0 AND userid=:userid';
        }
        if($type == 2){
            //支出
            $where = 'mymoney < 0 OR mypoints < 0 AND userid=:userid';
        }
        $count = Db::name('member_account_log')->where($where ? $where : 'userid = :userid')->bind(['userid'=>$userid])->count();
        $Page = new Page($count,16);
        $logs = Db::name('member_account_log')->where($where ? $where : 'userid = :userid')->bind(['userid'=>$userid])->order('addtime desc')->limit($Page->firstRow.','.$Page->listRows)->select();

        $return['status'] = 1;
        $return['msg'] = '';
        $return['result'] = $logs;
        $return['show'] = $Page->show();

        return $return;
    }
    /*
     * 获取优惠券
     */
    public function get_coupon($userid,$type =0 ){
        //查询条件
        $where = ' AND l.order_id = 0 AND c.use_end_time > '.time(); // 未使用
        if($type == 1){
            //已使用
            $where = ' AND l.order_id > 0 AND l.use_time > 0 ';
        }
        if($type == 2){
            //已过期
            $where = ' AND '.time().' > c.use_end_time ';
        }        
        //获取数量
        $sql = "SELECT count(l.id) as total_num FROM __PREFIX__coupon_list".
            " l LEFT JOIN __PREFIX__coupon".
            " c ON l.cid =  c.id WHERE l.uid = {$userid} {$where}";
        $count = query($sql);
        $count = $count[0]['total_num'];

        $Page = new Page($count,10);
		
		if(input('p')==''){
			$pe=$Page->firstRow;
		}else{
			$pe=input('p');
		}
        $sql = "SELECT l.*,c.name,c.money,c.use_end_time,c.condition FROM __PREFIX__coupon_list".
            " l LEFT JOIN __PREFIX__coupon".
            " c ON l.cid =  c.id WHERE l.uid = {$userid} {$where}  ORDER BY l.send_time DESC,l.use_time LIMIT {$pe},{$Page->listRows}";

        $logs = query($sql);

        $return['status'] = 1;
        $return['msg'] = '获取成功';
        $return['result'] = $logs;
        $return['show'] = $Page->show();
        return $return;
    }

    /**
     * 获取商品收藏列表
     * @param $userid  用户id
     */
    public function get_goods_collect($userid){
        $count = Db::name('goods_collect')->where(array('userid'=>$userid))->count();
        $page = new Page($count,10);
        $show = $page->show();
        //获取我的收藏列表
        $sql = "SELECT c.collect_id,c.add_time,g.goods_id,g.goods_name,g.shop_price,g.original_img,g.is_on_sale FROM __PREFIX__goods_collect c ".
            "inner JOIN __PREFIX__goods g ON g.goods_id = c.goods_id ".
            "WHERE c.userid = ".$userid.
            " ORDER BY c.add_time DESC LIMIT {$page->firstRow},{$page->listRows}";
        $result = query($sql);
        $return['status'] = 3;
        $return['msg'] = '获取成功';
        $return['result'] = $result;
        $return['show'] = $show;        
        return $return;
    }

    /**
     * 获取评论列表
     * @param $userid 用户id
     * @param $status  状态 0 未评论 1 已评论
     * @return mixed
     */
    public function get_comment($userid,$status=2){
        if($status == 1){
            //已评论
            $count2 =query("select count(*) as count from `__PREFIX__goods_comment` as c inner join __PREFIX__order_goods as g on c.goods_id = g.goods_id and c.order_id = g.order_id where c.userid = $userid");
            $count2 = $count2[0]['count'];
            
            $page = new Page($count2,10);
            $sql = "select c.*,g.*,(select order_sn from  __PREFIX__order where order_id = c.order_id ) as order_sn  from  __PREFIX__goods_comment as c inner join __PREFIX__order_goods as g on c.goods_id = g.goods_id and c.order_id = g.order_id where c.userid = $userid order by c.add_time desc LIMIT {$page->firstRow},{$page->listRows}";
        }else{        	
        	$countsql = " select count(1) as comment_count from __PREFIX__order_goods as og
        	left join __PREFIX__order as o on o.order_id = og.order_id where o.userid = $userid  and og.is_send = 1 ";
        	$where = '';
        	if($status == 0){
        		$countsql .= $where = " and og.is_comment = 0 ";
        	}
        	$comment = query($countsql);
        	$count1 = $comment[0][comment_count]; // 待评价
            $page = new Page($count1,3);
            $sql =" select *  from __PREFIX__order_goods as og 
            left join __PREFIX__order as o on o.order_id = og.order_id  where o.userid = $userid and og.is_send = 1  
            $where order by o.order_id desc  LIMIT {$page->firstRow},{$page->listRows}";            
        }
        
        $show = $page->show();
        $comment_list = query($sql);
        if($comment_list){
        	$return['result'] = $comment_list;
        	$return['show'] = $show; //分页
        	return $return;
        }else{
        	return array();
        }
    }

    /**
     * 添加评论
     * @param $add
     * @return array
     */
    public function add_comment($add){
        if(!$add['order_id'] || !$add['goods_id']){
            return array('status'=>-1,'msg'=>'非法操作','result'=>'');
		}
        //检查订单是否已完成
        $order = Db::name('order')->where("order_id", $add['order_id'])->where('userid', $add['userid'])->find();
        if($order['order_status'] != 2){
            return array('status'=>-1,'msg'=>'该笔订单还未确认收货','result'=>'');
		}
        //检查是否已评论过
        $goods = Db::name('goods_comment')->where("order_id", $add['order_id'])->where('goods_id', $add['goods_id'])->find();
        if($goods){
            return array('status'=>-1,'msg'=>'您已经评论过该商品','result'=>'');        
		}
        $row = Db::name('goods_comment')->insert($add);
        if($row){
            //更新订单商品表状态
            Db::name('order_goods')->where(['goods_id'=>$add['goods_id'],'order_id'=>$add['order_id']])->update(['is_comment'=>1]);
            Db::name('goods')->where(array('goods_id'=>$add['goods_id']))->setInc('comment_count',1); // 评论数加一
            // 查看这个订单是否全部已经评论,如果全部评论了 修改整个订单评论状态            
            $comment_count   = Db::name('order_goods')->where("order_id", $add['order_id'])->where('is_comment', 0)->count();
            if($comment_count == 0){
				// 如果所有的商品都已经评价了 订单状态改成已评价
                Db::name('order')->where("order_id",$add['order_id'])->update(['order_status'=>4]);
            }
            return array('status'=>1,'msg'=>'评论成功','result'=>'');
        }
        return array('status'=>-1,'msg'=>'评论失败','result'=>'');
    }

    /**
     * 邮箱或手机绑定
     * @param $email_mobile  邮箱或者手机
     * @param int $type  1 为更新邮箱模式  2 手机
     * @param int $userid  用户id
     * @return bool
     */
    public function update_email_mobile($email_mobile,$userid,$type=1){
        //检查是否存在邮件
        if($type == 1){
            $field = 'email';
		}
        if($type == 2){
            $field = 'mobile';
		}
		$condition=[];
        $condition['userid'] = ['neq',$userid];
        $condition[$field] = $email_mobile;

        $is_exist = Db::name('member')->where($condition)->find();
        if($is_exist){
            return false;
		}
        unset($condition[$field]);
        $condition['userid'] = $userid;
        $validate = $field.'_validated';
        Db::name('member')->where($condition)->update([$field=>$email_mobile,$validate=>1]);
		
        return true;
    }

    /**
     * 更新用户信息
     * @param $userid
     * @param $post  要更新的信息
     * @return bool
     */
    public function update_info($userid,$post=array()){
        $row = Db::name('member')->where("userid", $userid)->update($post);
        if($row === false){
           return false;
		}
        return true;
    }

    /**
     * 地址添加/编辑
     * @param $userid 用户id
     * @param $userid 地址id(编辑时需传入)
     * @return array
     */
    public function add_address($userid,$address_id=0,$data){
        $post = $data;
        if($address_id == 0){
            $c = Db::name('member_address')->where("userid", $userid)->count();
            if($c >= 20){
                return ['status'=>-1,'msg'=>'最多只能添加20个收货地址','result'=>''];
			}
        }        

        if($post['consignee'] == ''){
            return ['status'=>-1,'msg'=>'收货人不能为空','result'=>''];
		}
        if(!$post['province'] || !$post['city'] || !$post['district']){
            return ['status'=>-1,'msg'=>'所在地区不能为空','result'=>''];
		}
        if(!$post['address']){
            return ['status'=>-1,'msg'=>'地址不能为空','result'=>''];
		}
		//检查手机格式
        if(!check_mobile($post['mobile'])){
            return ['status'=>-1,'msg'=>'手机号码格式有误','result'=>''];
		}
        //编辑模式
        if($address_id > 0){
            $address = Db::name('member_address')->where(array('id'=>$address_id,'userid'=> $userid))->find();
            if($post['is_default'] == 1 && $address['is_default'] != 1){
                Db::name('member_address')->where(array('userid'=>$userid))->update(array('is_default'=>0));
			}
            $row = Db::name('member_address')->where(array('id'=>$address_id,'userid'=> $userid))->update($post);
            if($row===false){
                return array('status'=>-1,'msg'=>'操作完成','result'=>'');
			}
            return array('status'=>1,'msg'=>'编辑成功','result'=>'');
        }
        //添加模式
        $post['userid'] = $userid;
        
        // 如果目前只有一个收货地址则改为默认收货地址
        $c = Db::name('member_address')->where("userid", $post['userid'])->count();
        if($c == 0)  $post['is_default'] = 1;
        
        $address_id = Db::name('member_address')->insert($post);
        //如果设为默认地址
        $insert_id = DB::name('member_address')->getLastInsID();
        $map['userid'] = $userid;
        $map['id'] = array('neq',$insert_id);
        if($post['is_default'] == 1){
            Db::name('member_address')->where($map)->setField('is_default',0);
		}
        if($address_id===false){
            return ['status'=>-1,'msg'=>'添加失败','result'=>''];
		}
        
        return ['status'=>1,'msg'=>'添加成功','result'=>$address_id];
    }

    /**
     * 添加自提点
     * @author dyr
     * @param $userid
     * @param $post
     * @return array
     */
    public function add_pick_up($userid, $post){
        //检查用户是否已经有自提点
        $user_pickup_address_id = Db::name('member_address')->where(['userid'=>$userid,'is_pickup'=>1])->value('id');
        $pick_up = Db::name('pick_up')->where(['pickup_id' => $post['pickup_id']])->find();
        $post['address'] = $pick_up['pickup_address'];
        $post['is_pickup'] = 1;
        $post['userid'] = $userid;
        $user_address = new UserAddress();
        if (!empty($user_pickup_address_id)) {
            //更新自提点
            $user_address_save_result = $user_address->allowField(true)->validate(true)->update($post,['address_id'=>$user_pickup_address_id]);
        } else {
            //添加自提点
            $user_address_save_result = $user_address->allowField(true)->validate(true)->update($post);
        }
        if (false === $user_address_save_result) {
            return ['status' => -1, 'msg' => '保存失败', 'result' => $user_address->getError()];
        } else {
            return ['status' => 1, 'msg' => '保存成功', 'result' => ''];
        }
    }

    /**
     * 设置默认收货地址
     * @param $userid
     * @param $address_id
     */
    public function set_default($userid,$address_id){
        Db::name('member_address')->where(['userid'=>$userid])->update(array('is_default'=>0)); //改变以前的默认地址地址状态
        $row = Db::name('member_address')->where(['userid'=>$userid,'id'=>$address_id])->update(array('is_default'=>1));
        if(!$row){
            return false;
		}
		//addUserLog('设置默认收货地址','pc',$userid,'');
        return true;
    }

    /**
     * 修改密码
     * @param $userid  用户id
     * @param $old_password  旧密码
     * @param $new_password  新密码
     * @param $confirm_password 确认新 密码
     */
    public function password($userid,$old_password,$new_password,$confirm_password,$is_update=true){
        $data = $this->get_info($userid);
        $user = $data['result'];
		$encrypt=$user['encrypt'];
		
        if(strlen($new_password) < 6){
            return ['status'=>-1,'msg'=>'密码不能低于6位字符','result'=>''];
		}
        if($new_password != $confirm_password){
            return ['status'=>-1,'msg'=>'两次密码输入不一致','result'=>''];
		}
        //验证原密码
        if($is_update && ($user['password'] != '' && md5($old_password.$encrypt) != $user['password'])){
            return ['status'=>-1,'msg'=>'原密码错误','result'=>''];
		}
		$new_encrypt=GetRandStr(6);
        $row = Db::name('member')->where("userid", $userid)->update(['password'=>md5($new_password.$new_encrypt),'encrypt'=>$new_encrypt]);
        if(!$row){
            return ['status'=>-1,'msg'=>'修改失败','result'=>''];
		}
		//addUserLog('密码修改成功','pc',$user['userid'],$user['username']);
        return ['status'=>1,'msg'=>'修改成功','result'=>''];
    }

    /**
     * 取消订单
     */
    public function cancel_order($userid,$order_id){
        $order = Db::name('order')->where(array('order_id'=>$order_id,'userid'=>$userid))->find();
        //检查是否未支付订单 已支付联系客服处理退款
        if(empty($order)){
            return array('status'=>-1,'msg'=>'订单不存在','result'=>'');
		}
        //检查是否未支付的订单
        if($order['pay_status'] > 0 || $order['order_status'] > 0){
            return array('status'=>-1,'msg'=>'支付状态或订单状态不允许','result'=>'');
		}
        //获取记录表信息
        //$log = Db::name('account_log')->where(array('order_id'=>$order_id))->find();
        //有余额支付的情况
        if($order['user_money'] > 0 || $order['integral'] > 0){
            accountLog($userid,$order['user_money'],$order['integral'],"订单取消，退回{$order['user_money']}元,{$order['integral']}积分");
        }

        $row = Db::name('order')->where(array('order_id'=>$order_id,'userid'=>$userid))->update(['order_status'=>3]);
				
        $data['order_id'] = $order_id;
        $data['action_user'] = $userid;
        $data['action_note'] = '您取消了订单';
        $data['order_status'] = 3;
        $data['pay_status'] = $order['pay_status'];
        $data['shipping_status'] = $order['shipping_status'];
        $data['log_time'] = time();
        $data['status_desc'] = '用户取消订单';        
        Db::name('order_action')->insert($data);//订单操作记录

        if(!$row){
            return array('status'=>-1,'msg'=>'操作失败','result'=>'');
		}
        return array('status'=>1,'msg'=>'操作成功','result'=>'');
    }
  
    /**
     * 发送邮件验证码
     * @param $sender 接收人
     * @param $type 发送类型
     * @return json
     */
    public function send_validate_code($sender,$type='email'){
    	//获取上一次的发送时间
    	$send = session('validate_code');
    	if(!empty($send) && $send['time'] > time() && $send['sender'] == $sender){
    		//在有效期范围内 相同号码不再发送
    		$res = array('status'=>-1,'msg'=>'您发操作太快了，请休息2分钟吧！');
            return $res;
    	}
    	$code =  mt_rand(1000,9999);

		//检查是否邮箱格式
		if(!check_email($sender)){
			$res = array('status'=>-1,'msg'=>'邮箱格式有误');
            return $res;
		}
		$send = send_email($sender,'验证码','您好，你的验证码是：'.$code);
    	
    	if($send['status']==1){
    		$info['code'] = $code;
    		$info['sender'] = $sender;
    		$info['is_check'] = 0;
    		$info['time'] = time() + 120; //有效验证时间
    		session('validate_code',$info);
    		$res = array('status'=>1,'msg'=>'验证码已发送，请注意查收');
    	}else{
    		$res = array('status'=>-1,'msg'=>'验证码发送失败,请联系管理员');
    	}
    	return $res;
    }
    
    /**
     * 检查短信/邮件验证码验证码
     * @param $code 验证码
     * @param $sender 邮件地址（手机时是手机号码）
	 * @param $type 类型（手机或邮箱）
     * @param unknown $session_id
     * @return $scene 手机时短信模板ID
     */
    public function check_validate_code($code, $sender, $type ='email', $session_id=0 ,$scene = -1){
        $timeOut = time();
        $inValid = true;  //验证码失效

        //短信发送否开启
        //-1:用户没有发送短信
        //空:发送验证码关闭
        //$sms_status = checkEnableSendSms($scene);

        if($type == 'email'){
        	//邮件证码是否开启
        	$reg_smtp_enable = config('config.smtp_open');
            if(!$reg_smtp_enable){//发生邮件功能关闭
                $validate_code = session('validate_code');
                $validate_code['sender'] = $sender;
                $validate_code['is_check'] = 1;//标示验证通过
                session('validate_code',$validate_code);
                return ['status'=>1,'msg'=>'邮件验证码功能关闭, 无需校验验证码'];
            }            
            if(!$code)return ['status'=>-1,'msg'=>'请输入邮件验证码'];                
            //邮件
            $data = session('validate_code');
            $timeOut = $data['time'];
            if($data['code'] != $code || $data['sender']!=$sender){
            	$inValid = false;
            }  
        }else{
            if($scene == -1){
                return ['status'=>-1,'msg'=>'参数错误, 请传递合理的scene参数'];
            }
            
            if(!$code)return ['status'=>-1,'msg'=>'请输入短信验证码'];
            //短信
            $sms_time_out = config('config.sms_time_out');
            $sms_time_out = $sms_time_out ? $sms_time_out : 120;
            $data = Db::name('sms_log')->where(['mobile'=>$sender,'session_id'=>$session_id , 'status'=>1])->order('id DESC')->find();
            if(is_array($data) && $data['code'] == $code){
            	$data['sender'] = $sender;
            	$timeOut = $data['add_time']+ $sms_time_out;
            }else{
            	$inValid = false;
            }           
        }
        
       if(empty($data)){
           $res = ['status'=>-1,'msg'=>'请先获取验证码'];
       }elseif($timeOut < time()){
           $res = ['status'=>-1,'msg'=>'验证码已超时失效'];
       }elseif(!$inValid){
           $res = ['status'=>-1,'msg'=>'验证失败,验证码有误'];
       }else{
            $data['is_check'] = 1; //标示验证通过
            session('validate_code',$data);
            $res = ['status'=>1,'msg'=>'验证成功'];
        }
        return $res;
    }
     
    
    /**
     * @time 2016/09/01
     * @author dyr
     * 设置用户系统消息已读  (不用了)
     */
    public function setSysMessageForRead(){
        $user_info = session('user');
        if (!empty($user_info['userid'])) {
            $data['status'] = 1;
            Db::name('member_message')->where(array('userid' => $user_info['userid'], 'category' => 0))->update($data);
        }
    }
}