<?php
// 递归删除文件夹
function delFile($dir,$file_type='') {
	if(is_dir($dir)){
		$files = scandir($dir);
		//打开目录 //列出目录中的所有文件并去掉 . 和 ..
		foreach($files as $filename){
			if($filename!='.' && $filename!='..'){
				if(!is_dir($dir.'/'.$filename)){
					if(empty($file_type)){
						unlink($dir.'/'.$filename);
					}else{
						if(is_array($file_type)){
							//正则匹配指定文件
							if(preg_match($file_type[0],$filename)){
								unlink($dir.'/'.$filename);
							}
						}else{
							//指定包含某些字符串的文件
							if(false!=stristr($filename,$file_type)){
								unlink($dir.'/'.$filename);
							}
						}
					}
				}else{
					delFile($dir.'/'.$filename);
					rmdir($dir.'/'.$filename);
				}
			}
		}
	}else{
		if(file_exists($dir)){
			unlink($dir);
			//数据库删除
			$url= str_replace('./uploads','/uploads',$dir);
			Db::name('attachment')->where('url',$url)->delete();
		}
	}
}
//执行SQL
function query($sql,$arr=[]){
	$sql = str_replace('__PREFIX__', config('database.prefix'), $sql);
	if(!empty($arr)){
		return \think\Db::query($sql,$arr);
	}else{
		return \think\Db::query($sql);
	}
}

//执行SQL
function execute($sql,$arr=[]){
	$sql = str_replace('__PREFIX__', config('database.prefix'), $sql);
	if(!empty($arr)){
		return \think\Db::execute($sql,$arr);
	}else{
		return \think\Db::execute($sql);
	}
}

/**
 * 获取数组中的某一列
 * @param type $arr 数组
 * @param type $key_name  列名
 * @return type  返回那一列的数组
 */
function get_arr_column($arr, $key_name){
	$arr2 = array();
	foreach($arr as $key => $val){
		$arr2[] = $val[$key_name];        
	}
	return $arr2;
}
/**
 * @param $arr
 * @param $key_name
 * @return array
 * 将数据库中查出的列表以指定的 id 作为数组的键名 
 */
function convert_arr_key($arr, $key_name){
	$arr2 = array();
	foreach($arr as $key => $val){
		$arr2[$val[$key_name]] = $val;        
	}
	return $arr2;
}

/**
 * 获取某个商品分类的 儿子 孙子  重子重孙 的 id
 * @param type $cat_id
 */
function getCatGrandson ($cat_id){
    $GLOBALS['catGrandson'] = array();
    $GLOBALS['category_id_arr'] = array();
    // 先把自己的id 保存起来
    $GLOBALS['catGrandson'][] = $cat_id;
    // 把整张表找出来
    $GLOBALS['category_id_arr'] = db('goods_category')->cache(true,CACHE_TIME)->column('id,parent_id');
    // 先把所有儿子找出来
    $son_id_arr = db('goods_category')->where("parent_id", $cat_id)->cache(true,CACHE_TIME)->column('id');
    foreach($son_id_arr as $k => $v){
        getCatGrandson2($v);
    }
    return $GLOBALS['catGrandson'];
}
/**
 * 递归调用找到 重子重孙
 * @param type $cat_id
 */
function getCatGrandson2($cat_id){
    $GLOBALS['catGrandson'][] = $cat_id;
    foreach($GLOBALS['category_id_arr'] as $k => $v){
        // 找到孙子
        if($v == $cat_id){
            getCatGrandson2($k); // 继续找孙子
        }
    }
}
/**
 * @param $arr
 * @param $key_name
  * @param $key_name2
 * @return array
 * 将数据库中查出的列表以指定的 id 作为数组的键名 数组指定列为元素 的一个数组
 */
function get_id_val($arr, $key_name,$key_name2){
	$arr2 = array();
	foreach($arr as $key => $val){
		$arr2[$val[$key_name]] = $val[$key_name2];
	}
	return $arr2;
}
/**
 * 刷新商品库存, 如果商品有设置规格库存, 则商品总库存 等于 所有规格库存相加
 * @param type $goods_id  商品id
 */
function refresh_stock($goods_id){
    $count = db("spec_goods_price")->where("goods_id", $goods_id)->count();
    if($count == 0) return false; // 没有使用规格方式 没必要更改总库存

    $store_count = db("spec_goods_price")->where("goods_id", $goods_id)->sum('store_count');
    db("Goods")->where("goods_id", $goods_id)->update(array('store_count'=>$store_count)); // 更新商品的总库存
}
/**
 * 多个数组的笛卡尔积
*
* @param unknown_type $data
*/
function combineDika() {
	$data = func_get_args();
	$data = current($data);
	$cnt = count($data);
	$result = array();
    $arr1 = array_shift($data);
	foreach($arr1 as $key=>$item) {
		$result[] = array($item);
	}		

	foreach($data as $key=>$item) {                                
		$result = combineArray($result,$item);
	}
	return $result;
}


/**
 * 两个数组的笛卡尔积
 * @param unknown_type $arr1
 * @param unknown_type $arr2
*/
function combineArray($arr1,$arr2) {		 
	$result = array();
	foreach ($arr1 as $item1){
		foreach ($arr2 as $item2){
			$temp = $item1;
			$temp[] = $item2;
			$result[] = $temp;
		}
	}
	return $result;
}
/**
 *  商品缩略图 给于标签调用 拿出商品表的 original_img 原始图来裁切出来的
 * @param type $goods_id  商品id
 * @param type $width     生成缩略图的宽度
 * @param type $height    生成缩略图的高度
 */
function goods_thum_images($goods_id,$width,$height){
    if(empty($goods_id))
		return '';
	
    $original_img = db('goods')->where("goods_id", $goods_id)->value('original_img');
    if(empty($original_img)) return '';
    
    if(!file_exists('.'.$original_img)) return '';

    return thumb($original_img,$width,$height);
}
//商品原图
function goods_img($goods_id){
	$original_img = db('goods')->where("goods_id", $goods_id)->value('original_img');
	return $original_img;
}
/**
 * 获取商品库存
 * @param type $goods_id 商品id
 * @param type $key  库存 key
 */
function getGoodNum($goods_id,$key){
    if(!empty($key)){
        return db("spec_goods_price")->where(['goods_id' => $goods_id, 'key' => $key])->value('store_count');
    }else{
        return  db("goods")->where("goods_id", $goods_id)->value('store_count');
	}
}
/**
 * 计算订单金额
 * @param type $userid  用户id
 * @param type $order_goods  购买的商品
 * @param type $shipping  物流code
 * @param type $shipping_price 物流费用, 如果传递了物流费用 就不在计算物流费
 * @param type $province  省份
 * @param type $city 城市
 * @param type $district 县
 * @param type $pay_points 积分
 * @param type $user_money 余额
 * @param type $coupon_id  优惠券
 * @param type $couponCode  优惠码
 */

function calculate_price($userid = 0, $order_goods, $shipping_code = '', $shipping_price = 0, $province = 0, $city = 0, $district = 0, $pay_points = 0, $user_money = 0, $coupon_id = 0, $couponCode = ''){
    $cartLogic = new app\home\logic\CartLogic();
    $user = db('member')->where("userid", $userid)->find();// 找出这个用户

    if (empty($order_goods)){
        return array('status' => -9, 'msg' => '商品列表不能为空', 'result' => '');
    }

    $goods_id_arr = get_arr_column($order_goods, 'goods_id');
    $goods_arr = db('goods')->where("goods_id in(" . implode(',', $goods_id_arr) . ")")->cache(true,CACHE_TIME)->column('goods_id,weight,market_price,is_free_shipping'); // 商品id 和重量对应的键值对
    foreach ($order_goods as $key => $val) {
        // 如果传递过来的商品列表没有定义会员价
        if (!array_key_exists('member_goods_price', $val)) {
            $user['discount'] = $user['discount'] ? $user['discount'] : 1; // 会员折扣 不能为 0
            $order_goods[$key]['member_goods_price'] = $val['member_goods_price'] = $val['goods_price'] * $user['discount'];
        }
        //如果商品不是包邮的
        if ($goods_arr[$val['goods_id']]['is_free_shipping'] == 0){
            $goods_weight += $goods_arr[$val['goods_id']]['weight'] * $val['goods_num']; //累积商品重量 每种商品的重量 * 数量
		}
        $order_goods[$key]['goods_fee'] = $val['goods_num'] * $val['member_goods_price'];    // 小计
        $order_goods[$key]['store_count'] = getGoodNum($val['goods_id'], $val['spec_key']); // 最多可购买的库存数量
        if ($order_goods[$key]['store_count'] <= 0){
            return array('status' => -10, 'msg' => $order_goods[$key]['goods_name'] . "库存不足,请重新下单", 'result' => '');
        }
        $goods_price += $order_goods[$key]['goods_fee']; // 商品总价
        $cut_fee += $val['goods_num'] * $val['market_price'] - $val['goods_num'] * $val['member_goods_price']; // 共节约
        $anum += $val['goods_num']; // 购买数量
    }
    // 优惠券处理操作
    $coupon_price = 0;
    if ($coupon_id && $userid) {
        $coupon_price = $cartLogic->getCouponMoney($userid, $coupon_id, 1); // 下拉框方式选择优惠券
    }
    if ($couponCode && $userid) {
        $coupon_result = $cartLogic->getCouponMoneyByCode($couponCode, $goods_price); // 根据 优惠券 号码获取的优惠券
        if ($coupon_result['status'] < 0)
            return $coupon_result;
        $coupon_price = $coupon_result['result'];
    }
    // 处理物流
    if ($shipping_price == 0) {
        $freight_free = config('config.freight_free'); // 全场满多少免运费
        if ($freight_free > 0 && $goods_price >= $freight_free) {
            $shipping_price = 0;
        } else {
            $shipping_price = $cartLogic->cart_freight2($shipping_code, $province, $city, $district, $goods_weight);
        }
    }
    if ($pay_points && ($pay_points > $user['mypoints'])){
        return array('status' => -5, 'msg' => "你的账户可用积分为:" . $user['mypoints'], 'result' => ''); // 返回结果状态
	}
    if ($user_money && ($user_money > $user['mymoney'])){
        return array('status' => -6, 'msg' => "你的账户可用余额为:" . $user['mymoney'], 'result' => ''); // 返回结果状态
	}
    $order_amount = $goods_price + $shipping_price - $coupon_price; // 应付金额 = 商品价格 + 物流费 - 优惠券

    $user_money = ($user_money > $order_amount) ? $order_amount : $user_money;  // 余额支付原理等同于积分
    $order_amount = $order_amount - $user_money; //  余额支付抵应付金额
    
    /*判断能否使用积分
     1..积分低于多少时,不可使用
     2.在不使用积分的情况下, 计算商品应付金额
     3.原则上, 积分支付不能超过商品应付金额的50%, 该值可在平台设置
     @{ */
    $point_rate = config('config.point_rate'); //兑换比例: 如果拥有的积分小于该值, 不可使用
    $min_use_limit_point = config('config.point_min_limit'); //最低使用额度: 如果拥有的积分小于该值, 不可使用
    $use_percent_point = config('config.point_use_percent');     //最大使用限制: 最大使用积分比例, 例如: 为50时, 未50% , 那么积分支付抵扣金额不能超过应付金额的50%
    if($min_use_limit_point > 0 && $pay_points > 0 && $pay_points < $min_use_limit_point){
        return array('status'=>-1,'msg'=>"您使用的积分必须大于{$min_use_limit_point}才可以使用",'result'=>''); // 返回结果状态
    }
    // 计算该笔订单最多使用多少积分
    $limit = $order_amount * ($use_percent_point / 100) * $point_rate;
    if(($use_percent_point !=100 ) && $pay_points > $limit) {
        return array('status'=>-1,'msg'=>"该笔订单, 您使用的积分不能大于{$limit}",'result'=>'积分'); // 返回结果状态
    }
     
    $pay_points = ($pay_points / config('config.point_rate')); // 积分支付 100 积分等于 1块钱
    $pay_points = ($pay_points > $order_amount) ? $order_amount : $pay_points; // 假设应付 1块钱 而用户输入了 200 积分 2块钱, 那么就让 $pay_points = 1块钱 等同于强制让用户输入1块钱
    $order_amount = $order_amount - $pay_points; //  积分抵消应付金额
  
    $total_amount = $goods_price + $shipping_price;
    //订单总价  应付金额  物流费  商品总价 节约金额 共多少件商品 积分  余额  优惠券
    $result = array(
        'total_amount' => $total_amount, // 商品总价
        'order_amount' => $order_amount, // 应付金额
        'shipping_price' => $shipping_price, // 物流费
        'goods_price' => $goods_price, // 商品总价
        'cut_fee' => $cut_fee, // 共节约多少钱
        'anum' => $anum, // 商品总共数量
        'integral_money' => $pay_points,  // 积分抵消金额
        'user_money' => $user_money, // 使用余额
        'coupon_price' => $coupon_price,// 优惠券抵消金额
        'order_goods' => $order_goods, // 商品列表 多加几个字段原样返回
    );
    return array('status' => 1, 'msg' => "计算价钱成功", 'result' => $result); // 返回结果状态
}
/**
 * 支付完成修改订单
 * @param $order_sn 订单号
 * @param array $ext 额外参数
 * @return bool|void
 */
function update_pay_status($order_sn,$ext=array()){
	if(stripos($order_sn,'recharge') !== false){
		//用户在线充值
		$count = db('recharge')->where("order_sn = :order_sn and pay_status = 0")->bind(['order_sn'=>$order_sn])->count();   // 看看有没已经处理过这笔订单  支付宝返回不重复处理操作
		if($count == 0) return false;
		$order = db('recharge')->where("order_sn", $order_sn)->find();
		db('recharge')->where("order_sn",$order_sn)->save(array('pay_status'=>1,'pay_time'=>time()));
		accountLog($order['userid'],$order['account'],0,'会员在线充值');
	}else{
		// 如果这笔订单已经处理过了
		$count = db('order')->where("order_sn = :order_sn and pay_status = 0 OR pay_status = 2")->bind(['order_sn'=>$order_sn])->count();   // 看看有没已经处理过这笔订单  支付宝返回不重复处理操作
		if($count == 0) return false;
		// 找出对应的订单
        $order = db('order')->where("order_sn",$order_sn)->find();
        //预售订单
        if ($order['order_prom_type'] == 4) {
            // 预付款支付 有订金支付 修改支付状态  部分支付
            if($order['total_amount'] != $order['order_amount'] && $order['pay_status'] == 0){
                //支付订金
                db('order')->where("order_sn", $order_sn)->update(array('order_sn'=> date('YmdHis').mt_rand(1000,9999) ,'pay_status' => 2, 'pay_time' => time(),'paid_money'=>$order['order_amount']));
            }else{
                //全额支付 无订金支付 支付尾款
                db('order')->where("order_sn", $order_sn)->update(array('pay_status' => 1, 'pay_time' => time()));
            }
            $orderGoodsArr = db('order_Goods')->where(['order_id'=>$order['order_id']])->find();
            db('goods_activity')->where(array('act_id'=>$order['order_prom_id']))->setInc('act_count',$orderGoodsArr['goods_num']);

        } else {
            // 修改支付状态  已支付
            db('order')->where("order_sn", $order_sn)->update(array('pay_status'=>1,'pay_time'=>time()));
        }
		// 减少对应商品的库存
		minus_stock($order['order_id']);
		// 给他升级, 根据order表查看消费记录 给他会员等级升级 修改他的折扣 和 总金额
		update_user_level($order['userid']);
		// 记录订单操作日志
        if(array_key_exists('admin_id',$ext)){
            logOrder($order['order_id'],$ext['note'],'付款成功',$ext['admin_id']);
        }else{
            logOrder($order['order_id'],'订单付款成功','付款成功',$order['userid']);
        }
		//分销设置
		db('rebate_log')->where("order_id" ,$order['order_id'])->update(array('status'=>1));
		// 成为分销商条件
		$distribut_condition = config('config.distribut_condition');
		if($distribut_condition == 1)  // 购买商品付款才可以成为分销商
			db('member')->where("userid", $order['userid'])->update(array('is_distribut'=>1));
		
		//用户支付, 发送短信给商家
		$sender = config("config.site_mobile");
		if(empty($sender))return;
		$params = ['order_sn'=>$order_sn];
		$s=sendSms("5", $sender, $params);
	}
}
/**
 * 记录帐户变动
 * @param   int     $userid        用户id
 * @param   float   $mymoney     可用余额变动
 * @param   int     $mypoints     消费积分变动
 * @param   string  $desc    变动说明
 * @param   float   getmoney 分佣金额
 * @return  bool
 */
function accountLog($userid, $mymoney = 0,$mypoints = 0, $content = '',$getmoney = 0){
    /* 插入帐户变动记录 */
    $account_log = array(
        'userid'       => $userid,
        'mymoney'    => $mymoney,
        'mypoints'    => $mypoints,
        'addtime'   => time(),
        'content'   => $content,
    );
    /* 更新用户信息 */
	$update_data = [
        'mymoney'    => ['exp','mymoney+'.$mymoney],
        'mypoints'   => ['exp','mypoints+'.$mypoints],
        'getmoney'   => ['exp','getmoney+'.$getmoney]
    ];
	if(($mymoney+$mypoints+$getmoney) == 0){
		return false;
	}
	$update = db('member')->where('userid',$userid)->update($update_data);
    if($update){
    	db('member_account_log')->insert($account_log);
        return true;
    }else{
        return false;
    }
}

/**
 * 订单操作日志
 * 参数示例
 * @param type $order_id  订单id
 * @param type $action_note 操作备注
 * @param type $status_desc 操作状态  提交订单, 付款成功, 取消, 等待收货, 完成
 * @param type $userid  用户id 默认为管理员
 * @return boolean
 */
function logOrder($order_id,$action_note,$status_desc,$userid = 0){
    $status_desc_arr = array('提交订单', '付款成功', '取消', '等待收货', '完成','退货');
    $order = db('order')->where("order_id", $order_id)->find();
    $action_info = array(
        'order_id'        =>$order_id,
        'action_user'     =>$userid,
        'order_status'    =>$order['order_status'],
        'shipping_status' =>$order['shipping_status'],
        'pay_status'      =>$order['pay_status'],
        'action_note'     => $action_note,
        'status_desc'     =>$status_desc, //''
        'log_time'        =>time(),
    );
    return db('order_action')->insert($action_info);
}
/**
 * 更新会员等级,折扣，消费总额
 * @param $userid  用户ID
 * @return boolean
 */
function update_user_level($userid){
    $level_info = db('member_level')->order('id')->select();
    $total_amount = db('order')->where("userid=:userid AND pay_status=1 and order_status not in (3,5)")->bind(['userid'=>$userid])->sum('order_amount');
    if($level_info){
    	foreach($level_info as $k=>$v){
    		if($total_amount >= $v['amount']){
    			$level = $level_info[$k]['level_id'];
    			$discount = $level_info[$k]['discount']/100;
    		}
    	}
    	$user = session('user');
    	$updata['spendmoney'] = $total_amount;//更新累计修复额度
    	//累计额度达到新等级，更新会员折扣
    	if(isset($level) && $level>$user['level']){
    		$updata['level'] = $level;
    		$updata['discount'] = $discount;
    	}
    	db('member')->where("userid", $userid)->update($updata);
    }
}
/**
 * 根据 order_goods 表扣除商品库存
 * @param type $order_id  订单id
 */
function minus_stock($order_id){
    $orderGoodsArr = db('order_goods')->where("order_id", $order_id)->select();
    foreach($orderGoodsArr as $key => $val)
    {
        // 有选择规格的商品
        if(!empty($val['spec_key']))
        {   // 先到规格表里面扣除数量 再重新刷新一个 这件商品的总数量
            db('spec_goods_price')->where("goods_id = :goods_id and `key` = :key")->bind(['goods_id'=>$val['goods_id'],'key'=>$val['spec_key']])->setDec('store_count',$val['goods_num']);
            refresh_stock($val['goods_id']);
        }else{
            db('goods')->where("goods_id", $val['goods_id'])->setDec('store_count',$val['goods_num']); // 直接扣除商品总数量
        }
        db('goods')->where("goods_id", $val['goods_id'])->setInc('sales_sum',$val['goods_num']); // 增加商品销售量
        //更新活动商品购买量
        if($val['prom_type']==1 || $val['prom_type']==2){
        	$prom = get_goods_promotion($val['goods_id']);
        	if($prom['is_end']==0){
        		$tb = $val['prom_type']==1 ? 'flash_sale' : 'group_buy';
        		db($tb)->where("id", $val['prom_id'])->setInc('buy_num',$val['goods_num']);
        		db($tb)->where("id", $val['prom_id'])->setInc('order_num');
        	}
        }
    }
}
/**
 * 查看商品是否有活动
 * @param goods_id 商品ID
 */

function get_goods_promotion($goods_id,$userid=0){
	$now = time();
	$goods = db('goods')->where("goods_id", $goods_id)->find();
    $where = [
        'end_time' => ['gt', $now],
        'start_time' => ['lt', $now],
        'id' => $goods['prom_id'],
    ];
	
	$prom['price'] = $goods['shop_price'];
	$prom['prom_type'] = $goods['prom_type'];
	$prom['prom_id'] = $goods['prom_id'];
	$prom['is_end'] = 0;
	
	if($goods['prom_type'] == 1){//抢购
		$prominfo = db('flash_sale')->where($where)->find();
		if(!empty($prominfo)){
			if($prominfo['goods_num'] == $prominfo['buy_num']){
				$prom['is_end'] = 2;//已售馨
			}else{
				//核查用户购买数量
				$where = "userid = :userid and order_status!=3 and  add_time>".$prominfo['start_time']." and add_time<".$prominfo['end_time'];
				$order_id_arr = db('order')->where($where)->bind(['userid'=>$userid])->column('order_id');
				if($order_id_arr){
					$goods_num = db('order_goods')->where("prom_id={$goods['prom_id']} and prom_type={$goods['prom_type']} and order_id in (".implode(',', $order_id_arr).")")->sum('goods_num');
					if($goods_num < $prominfo['buy_limit']){
						$prom['price'] = $prominfo['price'];
					}
				}else{
					$prom['price'] = $prominfo['price'];
				}
			} 				
		}
	}
	
	if($goods['prom_type']==2){//团购
		$prominfo = db('group_buy')->where($where)->find();
		if(!empty($prominfo)){			
			if($prominfo['goods_num'] == $prominfo['buy_num']){
				$prom['is_end'] = 2;//已售馨
			}else{
				$prom['price'] = $prominfo['price'];
			}				
		}
	}
	if($goods['prom_type'] == 3){//优惠促销
		$parse_type = array('0'=>'直接打折','1'=>'减价优惠','2'=>'固定金额出售','3'=>'买就赠优惠券','4'=>'买M件送N件');
		$prominfo = db('prom_goods')->where($where)->find();
		if(!empty($prominfo)){
			if($prominfo['type'] == 0){
				$prom['price'] = $goods['shop_price']*$prominfo['expression']/100;//打折优惠
			}elseif($prominfo['type'] == 1){
				$prom['price'] = $goods['shop_price']-$prominfo['expression'];//减价优惠
			}elseif($prominfo['type']==2){
				$prom['price'] = $prominfo['expression'];//固定金额优惠
			}
		}
	}
	
	if(!empty($prominfo)){
		$prom['start_time'] = $prominfo['start_time'];
		$prom['end_time'] = $prominfo['end_time'];
	}else{
		$prom['prom_type'] = $prom['prom_id'] = 0 ;//活动已过期
		$prom['is_end'] = 1;//已结束
	}
	
	if($prom['prom_id'] == 0){
		db('goods')->where("goods_id", $goods_id)->update($prom);
	}
	return $prom;
}
/**
 * 查看订单是否满足条件参加活动
 * @param order_amount 订单应付金额
 */
function get_order_promotion($order_amount){
	$parse_type = array('0'=>'满额打折','1'=>'满额优惠金额','2'=>'满额送倍数积分','3'=>'满额送优惠券','4'=>'满额免运费');
	$now = time();
	$prom = db('prom_order')->where("type<2 and end_time>$now and start_time<$now and money<=$order_amount")->order('money desc')->find();
	$res = array('order_amount'=>$order_amount,'order_prom_id'=>0,'order_prom_amount'=>0);
	if($prom){
		if($prom['type'] == 0){
			$res['order_amount']  = round($order_amount*$prom['expression']/100,2);//满额打折
			$res['order_prom_amount'] = $order_amount - $res['order_amount'] ;
			$res['order_prom_id'] = $prom['id'];
		}elseif($prom['type'] == 1){
			$res['order_amount'] = $order_amount- $prom['expression'];//满额优惠金额
			$res['order_prom_amount'] = $prom['expression'];
			$res['order_prom_id'] = $prom['id'];
		}
	}
	return $res;		
}
/**
 * 获取商品一二三级分类
 * @return type
 */
function get_goods_category_tree(){
	$arr = $result = array();
	$cat_list = db('goods_category')->where("is_show = 1")->order('sort_order')->cache(true)->select();//所有分类
	$tree=[];
	foreach ($cat_list as $val){
		if($val['level'] == 2){
			$arr[$val['parent_id']][] = $val;
		}
		if($val['level'] == 3){
			$crr[$val['parent_id']][] = $val;
		}
		if($val['level'] == 1){
			$tree[] = $val;
		}
	}

	foreach ($arr as $k=>$v){
		foreach ($v as $kk=>$vv){
			$arr[$k][$kk]['sub_menu'] = empty($crr[$vv['id']]) ? array() : $crr[$vv['id']];
		}
	}
	
	foreach ($tree as $val){
		$val['tmenu'] = empty($arr[$val['id']]) ? array() : $arr[$val['id']];
		$result[$val['id']] = $val;
	}
	return $result;
}
/**
 * 给订单送券送积分 送东西
 */
function order_give($order)
{
	$order_goods = db('order_goods')->where("order_id",$order['order_id'])->cache(true)->select();
	//查找购买商品送优惠券活动
	foreach ($order_goods as $val)
    {
		if($val['prom_type'] == 3)
        {
			$prom = db('prom_goods')->where('type=3 and id=:id')->bind(['id'=>$val['prom_id']])->find();
			if($prom){
				$coupon = db('coupon')->where("id", $prom['expression'])->find();//查找优惠券模板
				if($coupon && $coupon['createnum']>0){					                                        
                    $remain = $coupon['createnum'] - $coupon['send_num'];//剩余派发量
                    if($remain > 0)                                            
                    {
                        $data = array('cid'=>$coupon['id'],'type'=>$coupon['type'],'uid'=>$order['userid'],'send_time'=>time());
                        db('coupon_list')->add($data);       
                        db('Coupon')->where("id", $coupon['id'])->setInc('send_num'); // 优惠券领取数量加一
                    }
				}
		 	}
		 }
	}
	
	//查找订单满额送优惠券活动
	$pay_time = $order['pay_time'];
	$prom = db('prom_order')
        ->where("type>1 and end_time>:pay_time1 and start_time<:pay_time2 and money<=:money")
        ->bind(['pay_time1'=>$pay_time,'pay_time2'=>$pay_time,'money'=>$order['order_amount']])
        ->order('money desc')
        ->find();
	if($prom){
		if($prom['type']==3){
			$coupon = db('coupon')->where("id",$prom['expression'])->find();//查找优惠券模板
			if($coupon){
				if($coupon['createnum']>0){
					$remain = $coupon['createnum'] - $coupon['send_num'];//剩余派发量
                    if($remain > 0)
                    {
                       $data = array('cid'=>$coupon['id'],'type'=>$coupon['type'],'uid'=>$order['userid'],'send_time'=>time());
                       db('coupon_list')->add($data);           
                       db('Coupon')->where("id",$coupon['id'])->setInc('send_num'); // 优惠券领取数量加一
                    }				
				}
			}
		}else if($prom['type']==2){
			accountLog($order['userid'], 0 , $prom['expression'] ,"订单活动赠送积分");
		}
	}
    $points = db('order_goods')->where("order_id", $order['order_id'])->sum("give_integral * goods_num");
    $points && accountLog($order['userid'], 0,$points,"下单赠送积分");
}
/**
 * 订单确认收货
 * @param $id   订单id
 */
function confirm_order($id,$userid = 0){
    $order = db('order')->where(['order_id'=>$id,'userid'=>$userid])->find();
    if($order['order_status'] != 1){
        return array('status'=>-1,'msg'=>'该订单不能收货确认');
	}
	
    $data['order_status'] = 2; // 已收货        
    $data['pay_status'] = 1; // 已付款        
    $data['confirm_time'] = time(); //收货确认时间
    if($order['pay_code'] == 'cod'){
    	$data['pay_time'] = time();
    }
    $row = db('order')->where('order_id',$id)->update($data);
    if(!$row){
        return array('status'=>-3,'msg'=>'操作失败');
	}
    order_give($order);// 调用送礼物方法, 给下单这个人赠送相应的礼物
    
    //分销设置
    db('rebate_log')->where("order_id", $id)->update(array('status'=>2,'confirm'=>time()));
    
    return array('status'=>1,'msg'=>'操作成功');
}
/**
 * 给订单数组添加属性  包括按钮显示属性 和 订单状态显示属性
 * @param type $order
 */
function set_btn_order_status($order){
    $order_status_arr = [
        'WAITPAY' => '待支付',
        'WAITSEND'=>'待发货',
        'WAITRECEIVE'=>'待收货',
        'WAITCCOMMENT'=> '待评价',
        'REPLACED'=>'已换货',
        'RETURNED'=>'已退货',
        'CANCEL'=> '已取消',
        'FINISH'=> '已完成', //
        'CANCELLED'=> '已作废'
    ];
    $order['order_status_code'] = $order_status_code = orderStatusDesc(0, $order); // 订单状态显示给用户看的
    $order['order_status_desc'] = $order_status_arr[$order_status_code];
    $orderBtnArr = orderBtn(0, $order);
    return array_merge($order,$orderBtnArr); // 订单该显示的按钮
}
/**
 * 获取订单状态的 显示按钮
 * @param type $order_id  订单id
 * @param type $order     订单数组
 * @return array()
 */
function orderBtn($order_id = 0, $order = array()){
    if(empty($order))
        $order = db('Order')->where("order_id", $order_id)->find();
    /**
     *  订单用户端显示按钮
    	去支付     AND pay_status=0 AND order_status=0 AND pay_code ! ="cod"
    	取消按钮  AND pay_status=0 AND shipping_status=0 AND order_status=0
    	确认收货  AND shipping_status=1 AND order_status=0
    	评价      AND order_status=1
    	查看物流  if(!empty(物流单号))
     */
    $btn_arr = array(
        'pay_btn' => 0, // 去支付按钮
        'cancel_btn' => 0, // 取消按钮
        'receive_btn' => 0, // 确认收货
        'comment_btn' => 0, // 评价按钮
        'shipping_btn' => 0, // 查看物流
        'return_btn' => 0, // 退货按钮 (联系客服)
    );


    //货到付款
    if($order['pay_code'] == 'cod'){
    	//待发货
        if(($order['order_status']==0 || $order['order_status']==1) && $order['shipping_status'] == 0){ 
            $btn_arr['cancel_btn'] = 1; // 取消按钮 (联系客服)
        }
		//待收货
        if($order['shipping_status'] == 1 && $order['order_status'] == 1){ 
            $btn_arr['receive_btn'] = 1;  // 确认收货
            $btn_arr['return_btn'] = 1; // 退货按钮 (联系客服)
        }       
    }else{
	//非货到付款
        if($order['pay_status'] == 0 && $order['order_status'] == 0){ // 待支付
            $btn_arr['pay_btn'] = 1; // 去支付按钮
            $btn_arr['cancel_btn'] = 1; // 取消按钮
        }
		//待发货
        if($order['pay_status'] == 1 && in_array($order['order_status'],array(0,1)) && $order['shipping_status'] == 0){
            $btn_arr['return_btn'] = 1; // 退货按钮 (联系客服)
        }
		//待收货
        if($order['pay_status'] == 1 && $order['order_status'] == 1  && $order['shipping_status'] == 1) {
            $btn_arr['receive_btn'] = 1;  // 确认收货
            $btn_arr['return_btn'] = 1; // 退货按钮 (联系客服)
        }
    }
	//已收货时
    if($order['order_status'] == 2){
        $btn_arr['comment_btn'] = 1;  // 评价按钮
        $btn_arr['return_btn'] = 1; // 退货按钮 (联系客服)
    }
	//已发货、部分发货时
    if($order['shipping_status'] != 0){
        $btn_arr['shipping_btn'] = 1; // 查看物流
    }
	//已发货、部分发货时
    if($order['shipping_status'] == 2 && $order['order_status'] == 1){            
        $btn_arr['return_btn'] = 1; // 退货按钮 (联系客服)
    }

    return $btn_arr;
}
/**
 * 获取订单状态的 中文描述名称
 * @param type $order_id  订单id
 * @param type $order     订单数组
 * @return string
 * order_status=订单状态(0待确认 1已确认 2已收货 3已取消 4已完成 5已作废)
 * shipping_status=发货状态(0未发货 1已发货 2部分发货)
 * pay_status=支付状态(0未支付 1已支付)
 */
function orderStatusDesc($order_id = 0, $order = array()){
    if(empty($order)){
        $order = db('Order')->where("order_id", $order_id)->find();
	}
    // 货到付款
    if($order['pay_code'] == 'cod'){
        if(in_array($order['order_status'],array(0,1)) && $order['shipping_status'] == 0){
            return 'WAITSEND'; //'待发货',
        }
    }else{// 非货到付款
        if($order['pay_status'] == 0 && $order['order_status'] == 0){
            return 'WAITPAY'; //'待支付',
        }
        if($order['pay_status'] == 1 &&  in_array($order['order_status'],array(0,1)) && $order['shipping_status'] != 1){
            return 'WAITSEND'; //'待发货',
        }
    }
    if(($order['shipping_status'] == 1) && ($order['order_status'] == 1)){
        return 'WAITRECEIVE'; //'待收货',
    }
    if($order['order_status'] == 2){
    	$is_send1=db('order_goods')->where(['order_id'=>$order['order_id'],'is_send'=>2])->count();
		if($is_send1>0){
        	return 'RETURNED'; //'已退货',
		}
		$is_send2=db('order_goods')->where(['order_id'=>$order['order_id'],'is_send'=>3])->count();
		if($is_send2>0){
        	return 'REPLACED'; //'已换货',
		}
    	$is_comment=db('order_goods')->where(['order_id'=>$order['order_id'],'is_comment'=>1])->count();
		if($is_comment==0){
        	return 'WAITCCOMMENT'; //'待评价',
		}
    }
    if($order['order_status'] == 3){
        return 'CANCEL'; //'已取消',
    }
    if($order['order_status'] == 4){
        return 'FINISH'; //'已完成',
    }
    if($order['order_status'] == 5){
    	return 'CANCELLED'; //'已作废',
    }
    return 'OTHER';
}
/*
 * 获取地区列表
 */
function get_region_list(){
    //获取地址列表 缓存读取
    if(!S('region_list')){
        //$region_list = db('areas')->select();
		$region_list=json_decode(file_get_contents(ROOT_PATH.'/data/areaCache.json'), true);
        $region_list = convert_arr_key($region_list,'id');        
        S('region_list',$region_list);
    }

    return $region_list ? $region_list : S('region_list');
}
/*
 * 获取指定地址信息
 */
function get_user_address_info($userid,$address_id){
    $data = db('member_address')->where(array('userid'=>$userid,'id'=>$address_id))->find();
    return $data;
}
/*
 * 获取用户默认收货地址
 */
function get_user_default_address($userid){
    $data = db('member_address')->where(array('userid'=>$userid,'is_default'=>1))->find();
    return $data;
}
/*
 * 获取用户地址列表
 */
function get_user_address_list($userid){
    $lists = db('member_address')->where(array('userid'=>$userid))->select();
    return $lists;
}
/**
 * 查看某个用户购物车中商品的数量
 * @param type $userid
 * @param type $session_id
 * @return type 购买数量
 */
function cart_goods_num($userid = 0,$session_id = ''){
    $where = " session_id = :session_id ";
    $bind['session_id'] = $session_id;
    if($userid){
        $where .= " or userid = :userid ";
        $bind['userid'] = $userid;
    }
    // 查找购物车数量
    $cart_count =  db('cart')->where($where)->bind($bind)->sum('goods_num');
    $cart_count = $cart_count ? $cart_count : 0;
    return $cart_count;
}
//查看购物车中商品的数量
function getcartnum(){
	$map=[];
	if(session('?user')){
		$user = session('user');
		$map['userid']=$user['userid'];
	}else{
		$map['session_id'] = session_id();
        $map['userid'] = 0;
	}
	// 查找购物车数量
    $cart_count =  db('cart')->where($map)->sum('goods_num');
    $cart_count = $cart_count ? $cart_count : 0;
    return $cart_count;
}
//返回JSON
function ajaxReturn($data){
    header('Content-Type:application/json; charset=utf-8');
    exit(json_encode($data));
}
/**
 * 获取url 中的各个参数  类似于 pay_code=alipay&bank_code=ICBC-DEBIT
 * @param type $str
 * @return type
 */
function parse_url_param($str){
    $data = array();
    $str = explode('?',$str);
    $str = end($str);
    $parameter = explode('&',$str);
    foreach($parameter as $val){
        $tmp = explode('=',$val);
        $data[$tmp[0]] = $tmp[1];
    }
    return $data;
}
//获取供应商
function getSuppliers($sid,$filed='suppliers_name'){
    $filed_value = db('suppliers')->where('suppliers_id',$sid)->value($filed);
	if($filed_value==''){
		$filed_value=config('config.site_name');
	}
    return $filed_value;
}
?>