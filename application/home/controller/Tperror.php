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
namespace app\home\controller;
use think\Controller;
class Tperror extends Controller {

	public function web404($msg='',$url=''){
		$msg = empty($msg) ? '您可能输入了错误的网址，或者该页面已经不存在了哦。' : $msg;
		$this->assign('error',$msg);		
		$tpshop_config = array();
		$tp_config = Db::name('config')->cache(true,CACHE_TIME)->select();
		foreach($tp_config as $k => $v){
			if($v['name'] == 'hot_keywords'){
				$tpshop_config['hot_keywords'] = explode('|', $v['value']);
			}
			$tpshop_config[$v['inc_type'].'_'.$v['name']] = $v['value'];
		}
		$this->assign('goods_category_tree', get_goods_category_tree());
		$brand_list = Db::name('brand')->cache(true,CACHE_TIME)->field('id,parent_cat_id,logo,is_hot')->where("parent_cat_id>0")->select();
		$this->assign('brand_list', $brand_list);
		$this->assign('tpshop_config', $tpshop_config);
		return $this->fetch('public/tp404');
	}
	
}