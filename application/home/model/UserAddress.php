<?php
/**
 * 收货地址
 * ============================================================================
 * 版权所有 Ybcms开发团队，并保留所有权利
 * 网站地址: http://www.ybcms.com
 * ============================================================================
 * Author: ybcms(dreamlak@qq.com)      
 * UpDate: 2017-03-01
 */

namespace app\home\model;
use think\model;
use think\Db;
class UserAddress extends model{
    protected $tableName = 'user_address';

    /**
     * 获取用户自提点
     * @time 2016/08/23
     * @author
     * @param $user_id
     * @return mixed
     */
    public function getUserPickup($userid)
    {
        $user_pickup_where = array(
            'ua.userid' => $userid,
            'ua.is_pickup' => 1
        );
        $user_pickup_list = Db::name('member_address')
            ->alias('ua')
            ->field('ua.*,r1.name AS province_name,r2.name AS city_name,r3.name AS district_name')
            ->join('__AREAS__ r1','r1.id = ua.province','LEFT')
            ->join('__AREAS__ r2','r2.id = ua.city','LEFT')
            ->join('__AREAS__ r3', 'r3.id = ua.district','LEFT')
            ->where($user_pickup_where)
            ->find();
        return $user_pickup_list;
    }

}