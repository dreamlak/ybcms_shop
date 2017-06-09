<?php
/**
 * 配送区域模型
 * -----------------------------------------
 * CopyRight @Ybcms开发团队，并保留所有权利
 * Url: http://www.ybcms.com
 * -----------------------------------------
 * Author: ybcms(dreamlak@qq.com)      
 * UpDate: 2017-03-01
 */

namespace app\admin\model;
use think\model;
use think\Db;
class ShippingArea extends model {

    /**
     * 获取配送区域
     * @return mixed
     */
    public function getShippingArea(){
        $shipping_areas = Db::name('shipping_area')->select();
        foreach($shipping_areas as $key => $val){
            $shipping_areas[$key]['config'] = unserialize($shipping_areas[$key]['config']);
        }
        return $shipping_areas;
    }

}
