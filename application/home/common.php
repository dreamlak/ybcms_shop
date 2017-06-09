<?php
/**
 * 面包屑导航  用于前台用户中心
 * 根据当前的控制器名称 和 action 方法
 */
function navigate_user()
{    
    $navigate = include APP_PATH.'home/navigate.php';    
    $location = strtolower('Home/'.request()->controller());
    $arr = array(
        '首页'=>'/',
        $navigate[$location]['name']=>url('/Home/'.request()->controller()),
        $navigate[$location]['action'][request()->action()]=>'javascript:void();',
    );
    return $arr;
}

/**
*  面包屑导航  用于前台商品
 * @param type $id 商品id  或者是 商品分类id
 * @param type $type 默认0是传递商品分类id  id 也可以传递 商品id type则为1
 */
function navigate_goods($id,$type = 0){
    $cat_id = $id; //
    // 如果传递过来的是
    if($type == 1){
        $cat_id = db('goods')->where("goods_id", $id)->value('cat_id');
    }
    $categoryList = db('goods_category')->column("id,name,parent_id");

    // 第一个先装起来
    $arr[$cat_id] = $categoryList[$cat_id]['name'];
    while (true){
        $cat_id = $categoryList[$cat_id]['parent_id'];
        if($cat_id > 0){
            $arr[$cat_id] = $categoryList[$cat_id]['name'];
        }else{
            break;
		}
    }
    $arr = array_reverse($arr,true);
    return $arr;
}
//跟据用户获取订单数
function get_order_count($userid){
	$map=['userid'=>$userid];
	$order_list = db('order')->order($order_str)->where($map)->select();
	$scount=['all'=>count($order_list),'nopay'=>0,'nofan'=>0,'nosou'=>0,'noping'=>0];
	foreach($order_list as $k=>$v){
		$order_list[$k] = set_btn_order_status($v);
	}
	foreach($order_list as $ks=>$vs){
		if($vs['order_status_code']=='WAITPAY') $scount['nopay']+=1;
		if($vs['order_status_code']=='WAITSEND') $scount['nofan']+=1;
		if($vs['order_status_code']=='WAITRECEIVE') $scount['nosou']+=1;
		if($vs['order_status_code']=='WAITCCOMMENT') $scount['noping']+=1;
	}
	return $scount;
}
