<?php
/**
 * 首页
 * ============================================================================
 * 版权所有 Ybcms开发团队，并保留所有权利
 * 网站地址: http://www.ybcms.com
 * ============================================================================
 * Author: ybcms(dreamlak@qq.com)      
 * UpDate: 2017-03-01
 */
namespace app\mobile\controller;
use app\home\logic\UsersLogic;
use Think\Db;
class Index extends MobileBase {

    public function index(){
        $hot_goods = Db::name('goods')->where("is_hot=1 and is_on_sale=1")->order('goods_id DESC')->limit(20)->cache(true,CACHE_TIME)->select();//首页热卖商品
        $thems = Db::name('goods_category')->where('level=1')->order('sort_order')->limit(9)->cache(true,CACHE_TIME)->select();
        $this->assign('thems',$thems);
        $this->assign('hot_goods',$hot_goods);
        $favourite_goods = Db::name('goods')->where("is_recommend=1 and is_on_sale=1")->order('goods_id DESC')->limit(20)->cache(true,CACHE_TIME)->select();//首页推荐商品

        //秒杀商品
        $now_time = time();  //当前时间
        if(is_int($now_time/7200)){      //双整点时间，如：10:00, 12:00
            $start_time = $now_time;
        }else{
            $start_time = floor($now_time/7200)*7200; //取得前一个双整点时间
        }
        $end_time = $start_time+7200;   //结束时间
        $seckill_list=query("select * from __PREFIX__goods as g inner join __PREFIX__flash_sale as f on g.goods_id = f.goods_id where start_time = $start_time and end_time = $end_time limit 3");     //获取秒杀商品
		//dump($seckill_list);die;
        $this->assign('seckill_list',$seckill_list);
        $this->assign('start_time',$start_time);
        $this->assign('end_time',$end_time);
        $this->assign('favourite_goods',$favourite_goods);
        return $this->fetch();
    }
    /**
     * 分类列表显示
     */
    public function categoryList(){
        return $this->fetch();
    }
    /**
     * 商品列表页
     */
    public function goodsList(){
        $id = input('id/d',0); // 当前分类id
        $lists = getCatGrandson($id);
        $this->assign('lists',$lists);
        return $this->fetch();
    }
    
    public function ajaxGetMore(){
    	$p = input('p/d',1);
    	$favourite_goods = Db::name('goods')->where("is_recommend=1 and is_on_sale=1")->order('goods_id DESC')->page($p,10)->cache(true,CACHE_TIME)->select();//首页推荐商品
     	$this->assign('favourite_goods',$favourite_goods);
    	return $this->fetch();
    }
}