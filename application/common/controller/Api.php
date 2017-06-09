<?php
/*
 * @name  API
 * @time on 2016/12/11
 * @Author  dreamlak   dreamlak@qq.com
 */
namespace app\common\controller;
use think\Controller;
use \think\Db;
class Api extends Controller{
	 /*
     * 获取商品分类
     */
    public function get_category(){
        $parent_id = input('parent_id'); // 商品分类 父id
            $list = Db::name('goods_category')->where("parent_id", $parent_id)->select();
        $html='';
        foreach($list as $k => $v)
            $html .= "<option value='{$v['id']}'>{$v['name']}</option>";        
        exit($html);
    }  
}
?>