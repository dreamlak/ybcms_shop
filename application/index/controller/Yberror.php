<?php
/**
 * 404错误
 * ============================================================================
 * 版权所有 Ybcms开发团队，并保留所有权利
 * 网站地址: http://www.ybcms.com
 * ============================================================================
 * Author: ybcms(dreamlak@qq.com)      
 * UpDate: 2017-03-01
 */
namespace app\index\controller;
use think\Controller;
class Yberror extends Controller {

	public function web404($msg='',$url=''){
		$msg = empty($msg) ? '您可能输入了错误的网址，或者该页面已经不存在了哦。' : $msg;
		$this->assign('error',$msg);		
		$config = config('config');

		$this->assign('goods_category_tree', get_goods_category_tree());
		$brand_list = Db::name('brand')->cache(true,CACHE_TIME)->field('id,parent_cat_id,logo,is_hot')->where("parent_cat_id>0")->select();
		$this->assign('brand_list', $brand_list);
		$this->assign('config', $config);
		return $this->fetch('public/web404');
	}
	
}