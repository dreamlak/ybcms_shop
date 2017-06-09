<?php
/**
 * 订单管理
 * -----------------------------------------
 * CopyRight @Ybcms开发团队，并保留所有权利
 * Url: http://www.ybcms.com
 * -----------------------------------------
 * Author: ybcms(dreamlak@qq.com)      
 * UpDate: 2017-03-01
 */

namespace app\admin\controller;
use app\admin\logic\OrderLogic;
use think\Validate;
use \think\Db;
class Order extends AdminBase{
	public  $order_status;
    public  $pay_status;
    public  $shipping_status;
	public function _initialize(){
		parent::_initialize();
		config('TOKEN_ON',false);//关闭表单令牌验证
		$this->order_status = ['0'=>'待确认','1'=>'已确认','2'=>'已收货','3'=>'已取消','4'=>'已完成','5'=>'已作废'];
        $this->pay_status = ['0'=>'未支付','1'=>'已支付','2'=>'部分支付'];
        $this->shipping_status = ['0' =>'未发货','1'=>'已发货','2'=>'部分发货'];
    	//订单 支付 发货状态
        $this->assign('order_status',$this->order_status);
        $this->assign('pay_status',$this->pay_status);
        $this->assign('shipping_status',$this->shipping_status);
	}
	public function index(){
		$map=[];
		$begin = strtotime(input('add_time_begin'));
        $end = strtotime(input('add_time_end'));
		if($begin && $end){
        	$map['add_time'] = ['between',[$begin,$end]];
        }
		input('pay_status')!=''?$map['pay_status']=input('pay_status'):0;
		input('pay_code')!=''?$map['pay_code']=input('pay_code'):0;
		input('shipping_status')!=''?$map['shipping_status']=input('shipping_status'):0;
		input('order_status')!=''?$map['order_status']=input('order_status'):0;
		input('keywords')!=''?$map['order_sn|consignee|mobile']=['like','%'.input('keywords').'%']:'';
		input('userid') ? $map['userid'] = trim(input('userid')):false;
		$totalCount=Db::name('order')->where($map)->count();
		$pagecount=config('paginate.list_admin');
		$data=Db::name('order')->where($map)->order('order_id DESC')->paginate($pagecount,$totalCount,['query'=>request()->param()]);
		$lists = $data->all();
		$this->assign('lists',$lists);
		$this->assign('total',$data->total());
		$this->assign('listRows',$data->listRows());
		$this->assign('currentPage',$data->currentPage());
		$this->assign('lastPage',$data->lastPage());
		$this->assign('pages',$data->render());
		
		return $this->fetch();
	}
	/**
     * 订单删除
     * @param int $id 订单id
     */
    public function delete_order(){
    	$order_id=input('order_id');
		$order_sn=input('order_sn');
    	$orderLogic = new OrderLogic();
		if(empty(input('order_id'))){//批量删除
			$ids=input('ids/a');
			$rd=['status'=>0,'msg'=>'删除失败!'];
			$names='';
	    	if(is_array($ids)){
				foreach($ids as $id){
					//删除前获取该删除的领导信箱名
					$names.=Db::name('order')->where('order_id',$id)->value('order_sn').'，';
					//删除
					$del = $orderLogic->delOrder($id);
					if($del){
						$rd=['status'=>1,'msg'=>'删除成功!'];
					}else{
						return $rd;
					}
		        }
				if($rd['status']==1){
					addAdminLog('成功删除订单:'.$names);
				}
			}
			return $rd;
		}else{
	    	$del = $orderLogic->delOrder($order_id);
	        if($del){
	        	addAdminLog('成功删除订单:'.$order_sn);
	            return ['status'=>1,'msg'=>'成功删除订单!'];
	        }else{
	        	addAdminLog('试图删除订单失败:'.$order_sn);
	        	return ['status'=>1,'msg'=>'删除订单失败!'];
	        }
		}
    }
	/**
     * 订单详情
     * @param int $id 订单id
     */
    public function detail($order_id){
        $orderLogic = new OrderLogic();
        $order = $orderLogic->getOrderInfo($order_id);
        $orderGoods = $orderLogic->getOrderGoods($order_id);
        $button = $orderLogic->getOrderButton($order);
        // 获取操作记录
        $action_log = Db::name('order_action')->where('order_id',$order_id)->order('log_time desc')->select();
        $userIds = [];
        //查找用户昵称
        foreach ($action_log as $k => $v){
            $userIds[$k] = $v['action_user'];
        }
		
        if($userIds && count($userIds) > 0){
            $users = Db::name("member")->where("userid in (".implode(",",$userIds).")")->column("userid,nickname");
        }
        $this->assign('users',$users);
        $this->assign('order',$order);
        $this->assign('action_log',$action_log);
        $this->assign('orderGoods',$orderGoods);
		
        $split = count($orderGoods)>1?1:0;
        foreach ($orderGoods as $val){
        	if($val['goods_num']>1){
        		$split = 1;
        	}
        }
        $this->assign('split',$split);
        $this->assign('button',$button);
		
        return $this->fetch();
    }
	
	/**
     * 订单编辑
     * @param int $id 订单id
     */
    public function edit_order(){
    	$order_id = input('order_id');
        $orderLogic = new OrderLogic();
        $order = $orderLogic->getOrderInfo($order_id);//获取订单信息
        if($order['shipping_status'] != 0){
            $this->error('已发货订单不允许编辑');
            exit;
        }
        $orderGoods = $orderLogic->getOrderGoods($order_id);//获取订单商品详情
       	if(request()->isPost() || request()->isAjax()){
            $order['consignee'] = input('consignee');// 收货人
            $order['province'] = input('province'); // 省份
            $order['city'] = input('city'); // 城市
            $order['district'] = input('district'); // 县
            $order['address'] = input('address'); // 收货地址
            $order['mobile'] = input('mobile'); // 手机           
            $order['invoice_title'] = input('invoice_title');// 发票
            $order['admin_note'] = input('admin_note'); // 管理员备注
            $order['admin_note'] = input('admin_note'); //                  
            $order['shipping_code'] = input('shipping');// 物流方式
            $order['shipping_name'] = Db::name('plugin')->where(['status'=>1,'type'=>'shipping','code'=>input('shipping')])->value('name');            
            $order['pay_code'] = input('payment');// 支付方式            
            $order['pay_name'] = Db::name('plugin')->where(['status'=>1,'type'=>'payment','code'=>input('payment')])->value('name');                            
            
            $goods_id_arr = input("goods_id/a");
            $new_goods = $old_goods_arr = array();
            //订单添加商品
            if($goods_id_arr){
            	$new_goods = $orderLogic->get_spec_goods($goods_id_arr);//根据商品型号获取商品
            	foreach($new_goods as $key => $val){
            		$val['order_id'] = $order_id;
            		$rec_id = Db::name('order_goods')->insert($val);//订单添加商品
            		if(!$rec_id){
            			$this->error('添加失败');
					}
            	}
            }
            //订单修改删除商品
            $old_goods = input('old_goods/a');
            foreach ($orderGoods as $val){
            	if(empty($old_goods[$val['rec_id']])){
            		Db::name('order_goods')->where("rec_id=".$val['rec_id'])->delete();//删除商品
            	}else{
            		//修改商品数量
            		if($old_goods[$val['rec_id']] != $val['goods_num']){
            			$val['goods_num'] = $old_goods[$val['rec_id']];
            			Db::name('order_goods')->where("rec_id=".$val['rec_id'])->update(array('goods_num'=>$val['goods_num']));
            		}
            		$old_goods_arr[] = $val;
            	}
            }
            //计算订单金额
            $goodsArr = array_merge($old_goods_arr,$new_goods);
            $result = calculate_price($order['userid'],$goodsArr,$order['shipping_code'],0,$order['province'],$order['city'],$order['district'],0,0,0,0);
            if($result['status'] < 0){
            	$this->error($result['msg']);
            }
			
            //修改订单费用
            $order['goods_price']    = $result['result']['goods_price']; // 商品总价
            $order['shipping_price'] = $result['result']['shipping_price'];//物流费
            $order['order_amount']   = $result['result']['order_amount']; // 应付金额
            $order['total_amount']   = $result['result']['total_amount']; // 订单总价
            unset($order['address2']);
            $o = Db::name('order')->where('order_id='.$order_id)->update($order);
            
            $l = $orderLogic->orderActionLog($order_id,'edit','修改订单');//操作日志
            if($o && $l){
            	addAdminLog('成功订单编辑:');
				return ['status'=>1,'msg'=>'修改成功','url'=>url('editprice',['order_id'=>$order_id])];
            }else{
				return ['status'=>0,'msg'=>'修改失败','url'=>url('detail',['order_id'=>$order_id])];
            }
            exit;
        }

        // 获取省份
        $province = Db::name('areas')->where(['pid'=>0])->select();
        //获取订单城市
        $city =  Db::name('areas')->where(['pid'=>$order['province']])->select();
        //获取订单地区
        $area =  Db::name('areas')->where(['pid'=>$order['city']])->select();
        //获取支付方式
        $payment_list = Db::name('plugin')->where(['status'=>1,'type'=>'payment'])->select();
        //获取配送方式
        $shipping_list = Db::name('plugin')->where(['status'=>1,'type'=>'shipping'])->select();
        
        $this->assign('order',$order);
        $this->assign('province',$province);
        $this->assign('city',$city);
        $this->assign('area',$area);
        $this->assign('orderGoods',$orderGoods);
        $this->assign('shipping_list',$shipping_list);
        $this->assign('payment_list',$payment_list);
        return $this->fetch();
    }
	/**
     * 选择搜索商品
     */
    public function search_goods(){
    	$brandList =  Db::name("brand")->select();
    	$categoryList =  Db::name("goods_category")->select();
    	$this->assign('categoryList',$categoryList);
    	$this->assign('brandList',$brandList);   	
    	$where = ' is_on_sale = 1 ';//搜索条件
    	input('intro')  && $where = "$where and ".input('intro')." = 1";
    	if(input('cat_id')){
    		$this->assign('cat_id',input('cat_id'));    		
            $grandson_ids = getCatGrandson(input('cat_id')); 
            $where = " $where  and cat_id in(".  implode(',', $grandson_ids).") "; // 初始化搜索条件
                
    	}
        if(input('brand_id')){
            $this->assign('brand_id',input('brand_id'));
            $where = "$where and brand_id = ".input('brand_id');
        }
    	if(!empty($_REQUEST['keywords'])){
    		$this->assign('keywords',input('keywords'));
    		$where = "$where and (goods_name like '%".input('keywords')."%' or keywords like '%".input('keywords')."%')" ;
    	}  	
    	$goodsList =  Db::name('goods')->where($where)->order('goods_id DESC')->limit(10)->select();
        
        foreach($goodsList as $key => $val){
            $spec_goods = Db::name('spec_goods_price')->where("goods_id = {$val['goods_id']}")->select();
            $goodsList[$key]['spec_goods'] = $spec_goods;            
        }
        if($goodsList){
            //计算商品数量
            foreach ($goodsList as $value){
                if($value['spec_goods']){
                    $count += count($value['spec_goods']);
                }else{
                    $count++;
                }
            }
            $this->assign('totalSize',$count);
        }
        
    	$this->assign('goodsList',$goodsList);
    	return $this->fetch();
    }
	/*
     * 拆分订单
     */
    public function split_order(){
    	$order_id = input('order_id');
    	$orderLogic = new OrderLogic();
    	$order = $orderLogic->getOrderInfo($order_id);
    	if($order['shipping_status'] != 0){
    		$this->error('已发货订单不允许编辑');
    		exit;
    	}
    	$orderGoods = $orderLogic->getOrderGoods($order_id);
    	if(request()->isPost() || request()->isAjax()){
    		$data = input('post.');
    		//################################先处理原单剩余商品和原订单信息
    		$old_goods = input('old_goods/a');
    		
    		foreach ($orderGoods as $val){
    			if(empty($old_goods[$val['rec_id']])){
    				Db::name('order_goods')->where("rec_id=".$val['rec_id'])->delete();//删除商品
    			}else{
    				//修改商品数量
    				if($old_goods[$val['rec_id']] != $val['goods_num']){
    					$val['goods_num'] = $old_goods[$val['rec_id']];
    					Db::name('order_goods')->where("rec_id=".$val['rec_id'])->update(array('goods_num'=>$val['goods_num']));
    				}
    				$oldArr[] = $val;//剩余商品
    			}
    			$all_goods[$val['rec_id']] = $val;//所有商品信息
    		}
    		$result = calculate_price($order['userid'],$oldArr,$order['shipping_code'],0,$order['province'],$order['city'],$order['district'],0,0,0,0);
    		if($result['status'] < 0){
    			$this->error($result['msg']);
    		}
    		//修改订单费用
    		$res['goods_price']    = $result['result']['goods_price']; // 商品总价
    		$res['order_amount']   = $result['result']['order_amount']; // 应付金额
    		$res['total_amount']   = $result['result']['total_amount']; // 订单总价
    		
    		Db::name('order')->where("order_id=".$order_id)->update($res);
			//################################原单处理结束
			
    		//################################新单处理
    		for($i=1;$i<20;$i++){
                $temp = $this->request->param($i.'_old_goods/a');
    			if(!empty($temp)){
    				$split_goods[] = $temp;
    			}
    		}

    		foreach ($split_goods as $key=>$vrr){
    			foreach ($vrr as $k=>$v){
    				$all_goods[$k]['goods_num'] = $v;
    				$brr[$key][] = $all_goods[$k];
    			}
    		}

    		foreach($brr as $goods){
    			$result = calculate_price($order['userid'],$goods,$order['shipping_code'],0,$order['province'],$order['city'],$order['district'],0,0,0,0);
    			if($result['status'] < 0){
    				$this->error($result['msg']);
    			}
    			$new_order = $order;
    			$new_order['order_sn'] = date('YmdHis').mt_rand(1000,9999);
    			$new_order['parent_sn'] = $order['order_sn'];
    			//修改订单费用
    			$new_order['goods_price']    = $result['result']['goods_price']; // 商品总价
    			$new_order['order_amount']   = $result['result']['order_amount']; // 应付金额
    			$new_order['total_amount']   = $result['result']['total_amount']; // 订单总价
    			$new_order['add_time'] = time();
    			unset($new_order['order_id']);
				unset($new_order['address2']);
    			$new_order_id = DB::name('order')->insertGetId($new_order);//插入订单表
    			foreach ($goods as $vv){
    				$vv['order_id'] = $new_order_id;
    				unset($vv['rec_id']);
    				$nid = Db::name('order_goods')->insert($vv);//插入订单商品表
    			}
    		}
    		//################################新单处理结束
    		addAdminLog('成功拆分订单:');
			return ['status'=>1,'msg'=>'操作成功','url'=>url('detail',['order_id'=>$order_id])];
            exit;
    	}
    	
    	foreach ($orderGoods as $val){
    		$brr[$val['rec_id']] = array('goods_num'=>$val['goods_num'],'goods_name'=>getSubstr($val['goods_name'], 0, 35).$val['spec_key_name']);
    	}
    	$this->assign('order',$order);
    	$this->assign('goods_num_arr',json_encode($brr));
    	$this->assign('orderGoods',$orderGoods);
    	return $this->fetch();
    }
	/**
     * 订单打印
     * @param int $id 订单id
     */
    public function order_print(){
    	$order_id = input('order_id');
        $orderLogic = new OrderLogic();
        $order = $orderLogic->getOrderInfo($order_id);
        $order['province'] = get_id_areaName($order['province']);
        $order['city'] = get_id_areaName($order['city']);
        $order['district'] = get_id_areaName($order['district']);
        $order['full_address'] = $order['province'].' '.$order['city'].' '.$order['district'].' '. $order['address'];
		$order['invoice_no']='';
        $orderGoods = $orderLogic->getOrderGoods($order_id);
        $shop = [
			  "site_url" => config('config.site_domain'),
			  "store_name" => config('config.site_name'),
			  "email" => config('config.smtp_user'),
			  "address" => config('config.site_address'),
			  "mobile" => config('config.site_mobile'),
			  "province" =>config('config.province'),
			  "city" => config('config.city'),
			  "district" => config('config.district'),
			];
        $this->assign('order',$order);
        $this->assign('shop',$shop);
        $this->assign('orderGoods',$orderGoods);
        $template = input('template','print');
        return $this->fetch($template);
    }
	/*
     * 价钱修改
     */
    public function editprice(){
    	$order_id = input('order_id');
        $orderLogic = new OrderLogic();
        $order = $orderLogic->getOrderInfo($order_id);
        $this->editable($order);
        if(request()->isPost() || request()->isAjax()){
        	$admin_id = is_login();
            if(empty($admin_id)){
                $this->error('非法操作');
                exit;
            }
            $update['discount'] = input('post.discount');
            $update['shipping_price'] = input('post.shipping_price');
			$update['order_amount'] = $order['goods_price'] + $update['shipping_price'] - $update['discount'] - $order['user_money'] - $order['integral_money'] - $order['coupon_price'];
            $row = Db::name('order')->where(array('order_id'=>$order_id))->update($update);
            if(!$row){
				return ['status'=>0,'msg'=>'没有更新数据','url'=>url('editprice',['order_id'=>$order_id])];
            }else{
            	addAdminLog('成功修改订单价钱:');
				return ['status'=>1,'msg'=>'操作成功','url'=>url('detail',['order_id'=>$order_id])];
            }
            exit;
        }
        $this->assign('order',$order);
        return $this->fetch();
    }
	 /**
     * 检测订单是否可以编辑
     * @param $order
     */
    private function editable($order){
        if($order['shipping_status'] != 0){
            $this->error('已发货订单不允许编辑');
            exit;
        }
        return;
    }
	/**
     * 订单取消付款
     */
    public function pay_cancel($order_id){
    	if(request()->isPost() || request()->isAjax()||input('remark')){
    		$data = input('post.');
    		$note = ['退款到用户余额','已通过其他方式退款','不处理，误操作项'];
    		if($data['refundType'] == 0 && $data['amount']>0){
    			accountLog($data['userid'], $data['amount'], 0,  '退款到用户余额');
    		}
    		$orderLogic = new OrderLogic();
            $orderLogic->orderProcessHandle($data['order_id'],'pay_cancel');
    		$d = $orderLogic->orderActionLog($data['order_id'],'pay_cancel',$data['remark'].':'.$note[$data['refundType']]);
    		if($d){
    			addAdminLog('成功订单取消付款:');
    			exit("<script>window.parent.pay_callback(1);</script>");
    		}else{
    			exit("<script>window.parent.pay_callback(0);</script>");
    		}
    	}else{
    		$order = Db::name('order')->where("order_id=$order_id")->find();
    		$this->assign('order',$order);
    		return $this->fetch();
    	}
    }
	//确认发货
	public function delivery_info(){
    	$order_id = input('order_id');
    	$orderLogic = new OrderLogic();
    	$order = $orderLogic->getOrderInfo($order_id);
    	$orderGoods = $orderLogic->getOrderGoods($order_id);
		$delivery_record = Db::name('delivery_doc')->alias('d')->join('__ADMIN__ a','a.adminid = d.admin_id')->where('d.order_id='.$order_id)->select();
		if($delivery_record){
			$order['invoice_no'] = $delivery_record[count($delivery_record)-1]['invoice_no'];
		}
		$this->assign('order',$order);
		$this->assign('orderGoods',$orderGoods);
		$this->assign('delivery_record',$delivery_record);//发货记录
    	return $this->fetch();
    }
	/**
     * 生成发货单
     */
    public function deliveryHandle(){
        $orderLogic = new OrderLogic();
		$data = input('post.');
		$res = $orderLogic->deliveryHandle($data);
		if($res){
			addAdminLog('成功生成发货单:');
			return ['status'=>1,'msg'=>'操作成功','url'=>url('delivery_info',['order_id'=>$order_id])];
		}else{
			return ['status'=>0,'msg'=>'操作失败','url'=>url('delivery_info',['order_id'=>$order_id])];
		}
    }
	/**
     * 订单操作
     * @param $id
     */
    public function order_action(){    	
        $orderLogic = new OrderLogic();
        $action = input('type');
        $order_id = input('order_id');
		if(input('note')!='')return ['status'=>0,'msg'=>'请填写说明！','url'=>url('index')];
        if($action && $order_id){
            if($action !=='pay'){
                $res = $orderLogic->orderActionLog($order_id,$action,input('note'));
            }
        	$a = $orderLogic->orderProcessHandle($order_id,$action,['note'=>input('note'),'admin_id'=>is_login()]);
        	if($res !== false && $a !== false){
                if ($action == 'remove') {
                	addAdminLog('成功移出订单操作:');
                 	return ['status'=>1,'msg'=>'操作成功','url'=>url('index')];
                }
				addAdminLog('成功操作处理订单:');
				return ['status'=>1,'msg'=>'操作成功','url'=>url('index')];
        	}else{
                if ($action == 'remove') {
					return ['status'=>0,'msg'=>'操作失败','url'=>url('index')];
                }
        	 	return ['status'=>0,'msg'=>'操作失败','url'=>url('index')];
        	}
        }else{
			return ['status'=>0,'msg'=>'参数错误','url'=>url('detail',['order_id'=>$order_id])];
        }
    }
	/**
     * 发货单列表
     */
    public function delivery_list(){
        $map=[];
		$map['order_status'] = ['in','1,2,4'];
		if(input('order_sn')!='')$map['order_sn']=['like','%'.trim(input('order_sn')).'%'];
		if(input('consignee')!='')$map['consignee']=['like','%'.trim(input('consignee')).'%'];
		if(input('shipping_status'))$map['shipping_status']=input('shipping_status');
		
		$totalCount=Db::name('order')->where($map)->count();
		$pagecount=config('paginate.list_admin');
		$data=Db::name('order')->where($map)->order('add_time DESC')->paginate($pagecount,$totalCount,['query'=>request()->param()]);
		$lists = $data->all();
		
		$this->assign('lists',$lists);
		$this->assign('total',$data->total());
		$this->assign('listRows',$data->listRows());
		$this->assign('currentPage',$data->currentPage());
		$this->assign('lastPage',$data->lastPage());
		$this->assign('pages',$data->render());
		
		return $this->fetch();
    }
	/**
     * 快递单打印
     */
    public function shipping_print(){
        $order_id = input('order_id');
        $orderLogic = new OrderLogic();
        $order = $orderLogic->getOrderInfo($order_id);
        //查询是否存在订单及物流
        $shipping = Db::name('plugin')->where(array('code'=>$order['shipping_code'],'type'=>'shipping'))->find();        
        if(!$shipping){
        	$this->error('物流插件不存在');
        }
		if(empty($shipping['config_value'])){
			$this->error('请设置'.$shipping['name'].'打印模板');
		}
		//获取网站信息
		$cof=config('config');
        $cof['province'] = empty($cof['province']) ? '' : get_id_areaName($cof['province']);
        $cof['city'] = empty($cof['city']) ? '' : get_id_areaName($cof['city']);
        $cof['district'] = empty($cof['district']) ? '' : get_id_areaName($cof['district']);

        $order['province'] = get_id_areaName($order['province']);
        $order['city'] = get_id_areaName($order['city']);
        $order['district'] = get_id_areaName($order['district']);
		
        if(empty($shipping['config'])){
        	$config = array('width'=>840,'height'=>480,'offset_x'=>0,'offset_y'=>0);
        	$this->assign('config',$config);
        }else{
        	$this->assign('config',unserialize($shipping['config']));
        }
        $template_var = ["发货点-名称", "发货点-联系人", "发货点-电话", "发货点-省份", "发货点-城市",
    		"发货点-区县", "发货点-手机", "发货点-详细地址", "收件人-姓名", "收件人-手机", "收件人-电话", 
    		"收件人-省份", "收件人-城市", "收件人-区县", "收件人-邮编", "收件人-详细地址", "时间-年", "时间-月", 
    		"时间-日","时间-当前日期","订单-订单号", "订单-备注","订单-配送费用"
    	];
				
        $content_var = [$cof['site_name'],$cof['site_contact'],$cof['site_tel'],$cof['province'],$cof['city'],
        	$cof['district'],$cof['site_mobile'],$cof['site_address'],$order['consignee'],$order['mobile'],$order['phone'],
        	$order['province'],$order['city'],$order['district'],$order['zipcode'],$order['address'],date('Y'),date('M'),
        	date('d'),date('Y-m-d'),$order['order_sn'],$order['admin_note'],$order['shipping_price'],
        ];
        $shipping['config_value'] = str_replace($template_var,$content_var, $shipping['config_value']);
        $this->assign('shipping',$shipping);
		
        return $this->fetch("plugins/print_express");
    }

	/*
     * ajax 退货订单列表
     */
    public function return_list(){
    	$map=[];
		if(input('order_sn')!='')$map['order_sn']=['like','%'.trim(input('order_sn')).'%'];
		if(input('status'))$map['status']=input('status');
		
		$totalCount=Db::name('return_goods')->where($map)->count();
		$pagecount=config('paginate.list_admin');
		$data=Db::name('return_goods')->where($map)->order('id DESC')->paginate($pagecount,$totalCount,['query'=>request()->param()]);
		$lists = $data->all();
		$goods_id_arr = get_arr_column($lists, 'goods_id');
		if(!empty($goods_id_arr)){
            $goods_list = Db::name('goods')->where("goods_id in (".implode(',', $goods_id_arr).")")->column('goods_id,goods_name');
        }
		$this->assign('goods_list',$goods_list);
		$this->assign('lists',$lists);
		$this->assign('total',$data->total());
		$this->assign('listRows',$data->listRows());
		$this->assign('currentPage',$data->currentPage());
		$this->assign('lastPage',$data->lastPage());
		$this->assign('pages',$data->render());
		
		return $this->fetch();
    }
	 /**
     * 删除某个退换货申请
     */
    public function return_del(){
        $id = input('id');
        Db::name('return_goods')->where("id = $id")->delete(); 
        addAdminLog('成功删除退货申请:');
		return ['status'=>1,'msg'=>'成功删除'];
    }
    /**
     * 退换货操作
     */
    public function return_info(){
        $id = input('id');
        $return_goods = Db::name('return_goods')->where("id= $id")->find();
        if($return_goods['imgs']){
            $return_goods['imgs'] = explode(',',$return_goods['imgs']);
		}
        $user = Db::name('member')->where("userid",$return_goods['userid'])->find();
        $goods = Db::name('goods')->where("goods_id",$return_goods['goods_id'])->find();
        $type_msg = ['退换','换货'];
        $status_msg = ['未处理','处理中','已完成'];
        if(request()->isPost() || request()->isAjax()){
        	$data=[];
            $data['type'] = input('type');
            $data['status'] = input('status');
            $data['remark'] = input('remark');                                    
            $note ="退换货:{$type_msg[$data['type']]}, 状态:{$status_msg[$data['status']]},处理备注：{$data['remark']}";
            $result = Db::name('return_goods')->where('id',$id)->update($data);
            if($result){
            	$type = $data['type']==0 ? 2 : 3;
				$where=['order_id'=>$return_goods['order_id'],'goods_id'=>$return_goods['goods_id']];
            	Db::name('order_goods')->where($where)->update(array('is_send'=>$type));//更改商品状态        
                $orderLogic = new OrderLogic();
                $log = $orderLogic->orderActionLog($return_goods['order_id'],'refund',$note);
				addAdminLog('成功退货处理:order_id='.$return_goods['order_id']);
				return ['status'=>1,'msg'=>'修改成功','url'=>url('return_list')];
                exit;
            }  
        }        
        
        $this->assign('id',$id); // 用户
        $this->assign('user',$user); //用户
        $this->assign('goods',$goods);//商品
        $this->assign('return_goods',$return_goods);//退换货 
        return $this->fetch();
    }
    
    /**
     * 管理员生成申请退货单
     */
    public function add_return_goods(){                
            $order_id = input('order_id'); 
            $goods_id = input('goods_id');
                
            $return_goods = Db::name('return_goods')->where("order_id = $order_id and goods_id = $goods_id")->find();            
            if(!empty($return_goods)){
                $this->error('已经提交过退货申请!',url('Admin/Order/return_list'));
                exit;
            }
            $order = Db::name('order')->where("order_id = $order_id")->find();
            
            $data['order_id'] = $order_id; 
            $data['order_sn'] = $order['order_sn']; 
            $data['goods_id'] = $goods_id; 
            $data['addtime'] = time(); 
            $data['userid'] = $order[userid];            
            $data['remark'] = '管理员申请退换货'; // 问题描述            
            Db::name('return_goods')->insert($data);            
            addAdminLog('成功生成申请退货单:'.$order['order_sn']);
			return ['status'=>1,'msg'=>'申请成功,现在去处理退货','url'=>url('return_list')];
            exit;
    }
	//订单日志
	public function order_log(){
		$map=[];
		$begin = strtotime(input('add_time_begin'));
        $end = strtotime(input('add_time_end'));
		if($begin && $end){
        	$map['log_time'] = ['between',[$begin,$end]];
        }
		if(input('admin_id'))$map['action_user'] = input('admin_id');
		if(input('order_sn')!='')$map['order_sn'] = input('order_sn');
		
		$totalCount=Db::name('order_action')->where($map)->count();
		$pagecount=config('paginate.list_admin');
		$data=Db::name('order_action')->where($map)->order('action_id DESC')->paginate($pagecount,$totalCount,['query'=>request()->param()]);
		$lists = $data->all();
		
		$this->assign('goods_list',$goods_list);
		$this->assign('lists',$lists);
		$this->assign('total',$data->total());
		$this->assign('listRows',$data->listRows());
		$this->assign('currentPage',$data->currentPage());
		$this->assign('lastPage',$data->lastPage());
		$this->assign('pages',$data->render());
		 	
    	$admin = Db::name('admin')->column('adminid,adminname');
    	$this->assign('admin',$admin);    	
    	return $this->fetch();
    }
	//导出
	public function export_order(){
		$map=[];
		$begin = strtotime(input('add_time_begin'));
        $end = strtotime(input('add_time_end'));
		if($begin && $end){
        	$map['add_time'] = ['between',[$begin,$end]];
        }
		input('pay_status')!=''?$map['pay_status']=input('pay_status'):0;
		input('pay_code')!=''?$map['pay_code']=input('pay_code'):0;
		input('shipping_status')!=''?$map['shipping_status']=input('shipping_status'):0;
		input('order_status')!=''?$map['order_status']=input('order_status'):0;
		input('keywords')!=''?$map['order_sn|consignee|mobile']=['like','%'.input('keywords').'%']:'';
		input('userid') ? $map['userid'] = trim(input('userid')):false;
		$orderList=Db::name('order')->where($map)->order('order_id')->select();
		//$sql = "select *,FROM_UNIXTIME(add_time,'%Y-%m-%d') as create_time from __PREFIX__order $where order by order_id";
    	//$orderList = query($sql);
		//dump($orderList);die;
    	$strTable ='<table width="500" border="1">';
    	$strTable .= '<tr>';
    	$strTable .= '<td style="text-align:center;font-size:12px;width:120px;">订单编号</td>';
    	$strTable .= '<td style="text-align:center;font-size:12px;" width="100">日期</td>';
    	$strTable .= '<td style="text-align:center;font-size:12px;" width="*">收货人</td>';
    	$strTable .= '<td style="text-align:center;font-size:12px;" width="*">收货地址</td>';
    	$strTable .= '<td style="text-align:center;font-size:12px;" width="*">电话</td>';
    	$strTable .= '<td style="text-align:center;font-size:12px;" width="*">订单金额</td>';
    	$strTable .= '<td style="text-align:center;font-size:12px;" width="*">实际支付</td>';
    	$strTable .= '<td style="text-align:center;font-size:12px;" width="*">支付方式</td>';
    	$strTable .= '<td style="text-align:center;font-size:12px;" width="*">支付状态</td>';
    	$strTable .= '<td style="text-align:center;font-size:12px;" width="*">发货状态</td>';
    	$strTable .= '<td style="text-align:center;font-size:12px;" width="*">商品信息</td>';
    	$strTable .= '</tr>';
	    if(is_array($orderList)){
	    	$region	= Db::name('areas')->column('id,name');
	    	foreach($orderList as $k=>$val){
	    		$strTable .= '<tr>';
	    		$strTable .= '<td style="text-align:center;font-size:12px;">&nbsp;'.$val['order_sn'].'</td>';
	    		$strTable .= '<td style="text-align:left;font-size:12px;">'.date('Y-m-d H:i:s',$val['add_time']).' </td>';	    		
	    		$strTable .= '<td style="text-align:left;font-size:12px;">'.$val['consignee'].'</td>';
                $strTable .= '<td style="text-align:left;font-size:12px;">'."{$region[$val['province']]},{$region[$val['city']]},{$region[$val['district']]},{$region[$val['twon']]}{$val['address']}".' </td>';                        
	    		$strTable .= '<td style="text-align:left;font-size:12px;">'.$val['mobile'].'</td>';
	    		$strTable .= '<td style="text-align:left;font-size:12px;">'.$val['goods_price'].'</td>';
	    		$strTable .= '<td style="text-align:left;font-size:12px;">'.$val['order_amount'].'</td>';
	    		$strTable .= '<td style="text-align:left;font-size:12px;">'.$val['pay_name'].'</td>';
	    		$strTable .= '<td style="text-align:left;font-size:12px;">'.$this->pay_status[$val['pay_status']].'</td>';
	    		$strTable .= '<td style="text-align:left;font-size:12px;">'.$this->shipping_status[$val['shipping_status']].'</td>';
	    		$orderGoods = Db::name('order_goods')->where('order_id',$val['order_id'])->select();
	    		$strGoods="";
	    		foreach($orderGoods as $goods){
	    			$strGoods .= "商品编号：".$goods['goods_sn']." 商品名称：".$goods['goods_name'];
	    			if ($goods['spec_key_name'] != '') $strGoods .= " 规格：".$goods['spec_key_name'];
	    			$strGoods .= "<br />";
	    		}
	    		unset($orderGoods);
	    		$strTable .= '<td style="text-align:left;font-size:12px;">'.$strGoods.' </td>';
	    		$strTable .= '</tr>';
	    	}
	    }
    	$strTable .='</table>';
    	unset($orderList);
		addAdminLog('成功导出订单:');
    	downloadExcel($strTable,'order');
    	exit();
    }
	/**
     * 添加一笔订单
     */
    public function add_order(){
        $order = array();
        //  获取省份
        $province = Db::name('areas')->where(array('pid'=>0))->select();
        //  获取订单城市
        //$city =  Db::name('areas')->where(array('pid'=>$order['province']))->select();
        //  获取订单地区
        //$area =  Db::name('areas')->where(array('pid'=>$order['city']))->select();
        //  获取配送方式
        $shipping_list = Db::name('plugin')->where(array('status'=>1,'type'=>'shipping'))->select();
        //  获取支付方式
        $payment_list = Db::name('plugin')->where(array('status'=>1,'type'=>'payment'))->select();
        if(request()->isPost() || request()->isAjax()){
            $order['userid'] = input('userid');// 用户id 可以为空
            $order['consignee'] = input('consignee');// 收货人
            $order['province'] = input('province'); // 省份
            $order['city'] = input('city'); // 城市
            $order['district'] = input('district'); // 县
            $order['address'] = input('address'); // 收货地址
            $order['mobile'] = input('mobile'); // 手机           
            $order['invoice_title'] = input('invoice_title');// 发票
            $order['admin_note'] = input('admin_note'); // 管理员备注            
            $order['order_sn'] = date('YmdHis').mt_rand(1000,9999); // 订单编号;
            $order['admin_note'] = input('admin_note'); // 
            $order['add_time'] = time(); //                    
            $order['shipping_code'] = input('shipping');// 物流方式
            $order['shipping_name'] = Db::name('plugin')->where(array('status'=>1,'type'=>'shipping','code'=>input('shipping')))->value('name');            
            $order['pay_code'] = input('payment');// 支付方式            
            $order['pay_name'] = Db::name('plugin')->where(array('status'=>1,'type'=>'payment','code'=>input('payment')))->value('name');            
                            
            $goods_id_arr = input("goods_id/a");
            $orderLogic = new OrderLogic();
            $order_goods = $orderLogic->get_spec_goods($goods_id_arr);          
            $result = calculate_price($order['userid'],$order_goods,$order['shipping_code'],0,$order[province],$order[city],$order[district],0,0,0,0);      
            if($result['status'] < 0){
                 $this->error($result['msg']);      
            } 
           
           $order['goods_price']    = $result['result']['goods_price']; // 商品总价
           $order['shipping_price'] = $result['result']['shipping_price']; //物流费
           $order['order_amount']   = $result['result']['order_amount']; // 应付金额
           $order['total_amount']   = $result['result']['total_amount']; // 订单总价
           
            // 添加订单
            $order_id = Db::name('order')->insert($order);
            $order_insert_id = DB::getLastInsID();
            if($order_id){
                foreach($order_goods as $key => $val){
                    $val['order_id'] = $order_id;
                    $rec_id = Db::name('order_goods')->insert($val);
                    if(!$rec_id)
                        $this->error('添加失败');                                  
                }
				addAdminLog('成功添加订单:'.$order_insert_id);
				return ['status'=>1,'msg'=>'添加商品成功','url'=>url("detail",array('order_id'=>$order_insert_id))];
                exit();
            }
            else{
                $this->error('添加失败');
            }                
        }     
        $this->assign('shipping_list',$shipping_list);
        $this->assign('payment_list',$payment_list);
        $this->assign('province',$province);
        //$this->assign('city',$city);
        //$this->assign('area',$area);        
        return $this->fetch();
    }
}
?>