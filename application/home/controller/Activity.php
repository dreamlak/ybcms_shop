<?php
/**
 * 活动
 * ============================================================================
 * 版权所有 Ybcms开发团队，并保留所有权利
 * 网站地址: http://www.ybcms.com
 * ============================================================================
 * Author: ybcms(dreamlak@qq.com)      
 * UpDate: 2017-03-01
 */
namespace app\home\controller;

use app\home\logic\CartLogic;
use app\home\logic\GoodsLogic;
use think\AjaxPage;
use think\Controller;
use think\Url;
use think\Config;
use think\Page;
use think\Verify;
use think\Db;

class Activity extends Base{
    /**
     * 商品详情页
     */
    public function group(){
        //form表单提交
        config('TOKEN_ON', true);

        $goodsLogic = new GoodsLogic();
        $goods_id = input("id/d");

        $group_buy_info = Db::name('group_buy')->where(['goods_id' => $goods_id, 'start_time' => ['<=', time()], 'end_time' => ['>=', time()]])->find(); // 找出这个商品
        if (empty($group_buy_info)) {
            $this->error("此商品没有团购活动", url('Home/Goods/goodsInfo', array('id' => $goods_id)));
            exit;
        }

        $goods = Db::name('Goods')->where("goods_id", $goods_id)->find();
        $goods_images_list = Db::name('goods_images')->where("goods_id", $goods_id)->select(); // 商品 图册

        $goods_attribute = Db::name('goods_attribute')->column('attr_id,attr_name'); // 查询属性
        $goods_attr_list = Db::name('goods_attr')->where("goods_id", $goods_id)->select(); // 查询商品属性表
		
        // 商品规格 价钱 库存表 找出 所有 规格项id
        $keys = Db::name('spec_goods_price')->where("goods_id", $goods_id)->value("GROUP_CONCAT(`key` SEPARATOR '_') ");
        if($keys) {
            $specImage = Db::name('spec_image')->where("goods_id = :goods_id and src != '' ")->bind(['goods_id' => $goods_id])->column("spec_image_id,src");// 规格对应的 图片表， 例如颜色
            $keys = str_replace('_', ',', $keys);
            $sql = "SELECT a.name,a.order,b.* FROM __PREFIX__spec AS a INNER JOIN __PREFIX__spec_item AS b ON a.id = b.spec_id WHERE b.id IN($keys) ORDER BY a.order";
            $filter_spec2 = query($sql);
            foreach ($filter_spec2 as $key => $val) {
                $filter_spec[$val['name']][] = array(
                    'item_id' => $val['id'],
                    'item' => $val['item'],
                    'src' => $specImage[$val['id']],
                );
            }
        }
		
        $spec_goods_price = Db::name('spec_goods_price')->where("goods_id", $goods_id)->column("key,price,store_count"); // 规格 对应 价格 库存表
        Db::name('goods')->where("goods_id", $goods_id)->update(array('click_count' => $goods['click_count'] + 1)); // 统计点击数
        $commentStatistics = $goodsLogic->commentStatistics($goods_id);// 获取某个商品的评论统计
        $navigate_goods = navigate_goods($goods_id, 1); // 面包屑导航
        $point_rate = config('config.point_rate');
        $this->assign('point_rate', $point_rate);
        $this->assign('group_buy_info', $group_buy_info);
        $this->assign('spec_goods_price', json_encode($spec_goods_price, true)); // 规格 对应 价格 库存表
        $this->assign('navigate_goods', $navigate_goods);
        $this->assign('commentStatistics', $commentStatistics);
        $this->assign('goods_attribute', $goods_attribute);
        $this->assign('goods_attr_list', $goods_attr_list);
        $this->assign('filter_spec', $filter_spec);
        $this->assign('goods_images_list', $goods_images_list);
        $this->assign('goods', $goods);
        $this->assign('look_see', $goodsLogic->get_look_see($goods)); //看了又看 2017-2-28 lxl
        return $this->fetch();
    }


    /**
     * 团购活动列表
     */
    public function group_list(){
        $count = Db::name('group_buy')->where(time() . " >= start_time and " . time() . " <= end_time ")->count();// 查询满足要求的总记录数
        $Page = new Page($count, 20);// 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();// 分页显示输出
        $this->assign('page', $show);// 赋值分页输出
        $list = Db::name('group_buy')->where(time() . " >= start_time and " . time() . " <= end_time ")->limit($Page->firstRow . ',' . $Page->listRows)->select(); // 找出这个商品
        $this->assign('list', $list);
        return $this->fetch();
    }

    /**
     * 预售列表页
     */
    public function pre_sell_list(){
        $model = Db::name('goods_activity');
        $pre_sell_list = $model->where(array('act_type' => 1, 'is_finished' => 0))->select();
        foreach ($pre_sell_list as $key => $val) {
            $pre_sell_list[$key] = array_merge($pre_sell_list[$key]->toArray(), unserialize($pre_sell_list[$key]['ext_info']));
            $pre_sell_list[$key]['act_status'] = $model->getPreStatusAttr($pre_sell_list[$key]);
            $pre_count_info = $model->getPreCountInfo($pre_sell_list[$key]['act_id'], $pre_sell_list[$key]['goods_id']);
            $pre_sell_list[$key] = array_merge($pre_sell_list[$key], $pre_count_info);
            $pre_sell_list[$key]['price'] = $model->getPrePrice($pre_sell_list[$key]['total_goods'], $pre_sell_list[$key]['price_ladder']);
        }
        $this->assign('pre_sell_list', $pre_sell_list);
        return $this->fetch();
    }

    /**
     *   预售详情页
     */
    public function pre_sell(){
        $id = input('id/d', 0);
        $pre_sell_info = Db::name('goods_activity')->where(array('act_id' => $id, 'act_type' => 1))->find();
        if (empty($pre_sell_info)) {
            $this->error('对不起，该预售商品不存在或者已经下架了', url('Home/Activity/pre_sell_list'));
            exit();
        }
        $goods = Db::name('goods')->where(array('goods_id' => $pre_sell_info['goods_id']))->find();
        if (empty($goods)) {
            $this->error('对不起，该预售商品不存在或者已经下架了', url('Home/Activity/pre_sell_list'));
            exit();
        }

        $pre_sell_info = array_merge($pre_sell_info, unserialize($pre_sell_info['ext_info']));
        $pre_count_info = Db::name('goods_activity')->getPreCountInfo($pre_sell_info['act_id'], $pre_sell_info['goods_id']);//预售商品的订购数量和订单数量
        $pre_sell_info['price'] = Db::name('goods_activity')->getPrePrice($pre_count_info['total_goods'], $pre_sell_info['price_ladder']);//预售商品价格
        $pre_sell_info['amount'] = Db::name('goods_activity')->getPreAmount($pre_count_info['total_goods'], $pre_sell_info['price_ladder']);//预售商品数额ing
        if ($goods['brand_id']) {
            $brand = Db::name('brand')->where(array('id' => $goods['brand_id']))->find();
            $goods['brand_name'] = $brand['name'];
        }
        $goods_images_list = Db::name('GoodsImages')->where(array('goods_id' => $goods['goods_id']))->select(); // 商品 图册
        $goods_attribute = Db::name('GoodsAttribute')->column('attr_id,attr_name'); // 查询属性
        $goods_attr_list = Db::name('GoodsAttr')->where(array('goods_id' => $goods['goods_id']))->select(); // 查询商品属性表
        $goodsLogic = new GoodsLogic();
        $filter_spec = $goodsLogic->get_spec($goods['goods_id']);
        $spec_goods_price = Db::name('spec_goods_price')->where(array('goods_id' => $goods['goods_id']))->column("key,price,store_count"); // 规格 对应 价格 库存表
        $commentStatistics = $goodsLogic->commentStatistics($goods['goods_id']);// 获取某个商品的评论统计
        $this->assign('pre_count_info', $pre_count_info);//预售商品的订购数量和订单数量
        $this->assign('commentStatistics', $commentStatistics);//评论概览
        $this->assign('goods_attribute', $goods_attribute);//属性值
        $this->assign('goods_attr_list', $goods_attr_list);//属性列表
        $this->assign('filter_spec', $filter_spec);//规格参数
        $this->assign('goods_images_list', $goods_images_list);//商品缩略图
        $this->assign('spec_goods_price', json_encode($spec_goods_price, true)); // 规格 对应 价格 库存表\
        $this->assign('siblings_cate', $goodsLogic->get_siblings_cate($goods['cat_id']));//相关分类
        $this->assign('look_see', $goodsLogic->get_look_see($goods));//看了又看
        $this->assign('pre_sell_info', $pre_sell_info);
        $this->assign('goods', $goods);
        return $this->fetch();
    }
}