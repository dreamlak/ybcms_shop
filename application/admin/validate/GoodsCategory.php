<?php
 /**
 * 商品分类验证
 * ============================================================================
 * 版权所有 Ybcms开发团队，并保留所有权利
 * 网站地址: http://www.ybcms.com
 * ============================================================================
 * Author: ybcms(dreamlak@qq.com)      
 * UpDate: 2017-03-01
 */
namespace app\admin\validate;
use think\Validate;
class GoodsCategory extends Validate {   
    // 验证规则
    protected $rule = [
        ['name','require','分类名称必须填写'],
        ['sort_order', 'number', '排序必须为数字'],     
    ];    
}
