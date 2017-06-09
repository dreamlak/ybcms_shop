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
namespace app\home\controller; 
use think\Controller;
use think\Url;
use think\Config;
use think\Page;
use think\Verify;
use think\Db;
class Index extends Base {
    public function index(){
       	//如果是手机跳转到 手机模块
        if(true == isMobile()){
            header("Location: ".url('Mobile/Index/index'));
        }
        //二级分类下热卖商品
        $hot_goods = $hot_cate = $cateList = array();
        $sql = "select a.goods_name,a.goods_id,a.shop_price,a.market_price,a.cat_id,b.parent_id_path,b.name from ".config('database.prefix')."goods as a left join ";
        $sql .= config('database.prefix')."goods_category as b on a.cat_id=b.id where a.is_hot=1 and a.is_on_sale=1 order by a.sort";       
        $index_hot_goods = S('index_hot_goods');
        if(empty($index_hot_goods)){
            $index_hot_goods = query($sql);//首页热卖商品
            S('index_hot_goods',$index_hot_goods,CACHE_TIME);
        }
       
        if($index_hot_goods){
            foreach($index_hot_goods as $val){
                $cat_path = explode('_', $val['parent_id_path']);
                $hot_goods[$cat_path[1]][] = $val;
            }
        }
		//热门三级分类
        $hot_category = Db::name('goods_category')->where("is_hot=1 and level=3 and is_show=1")->cache(true,CACHE_TIME)->select();
        foreach ($hot_category as $v){
        	$cat_path = explode('_', $v['parent_id_path']);
        	$hot_cate[$cat_path[1]][] = $v;
        }
        
        foreach ($this->cateTrre as $k=>$v){
            if($v['is_hot']==1){
        		$v['hot_goods'] = empty($hot_goods[$k]) ? '' : $hot_goods[$k];
        		$v['hot_cate'] = empty($hot_cate[$k]) ? '' : $hot_cate[$k];
        		$cateList[] = $v;
        	}
        }
        $this->assign('cateList',$cateList);
        return $this->fetch();
    }
 
    /**
     *  公告详情页
     */
    public function notice(){
        return $this->fetch();
    }
    
    // 二维码
    public function qr_code(){        
     	vendor('phpqrcode.phpqrcode'); 
        error_reporting(E_ERROR);            
        $url = urldecode(input('data'));
        \QRcode::png($url);
		exit;        
    }
    
    // 验证码
    public function verify(){
        //验证码类型
        $type = input('get.type') ? input('get.type') : '';
        $fontSize = input('get.fontSize') ? input('get.fontSize') : '40';
        $length = input('get.length') ? input('get.length') : '4';
        
        $config = array(
            'fontSize' => $fontSize,
            'length' => $length,
            'useCurve' => true,
            'useNoise' => false,
        );
        $Verify = new Verify($config);
        $Verify->entry($type);        
    }
    
    // 促销活动页面
    public function promoteList(){
        $goodsList = query("select * from __PREFIX__goods as g inner join __PREFIX__flash_sale as f on g.goods_id = f.goods_id   where ".time()." > start_time  and ".time()." < end_time");
        $brandList = Db::name('brand')->column("id,name,logo");
        $this->assign('brandList',$brandList);
        $this->assign('goodsList',$goodsList);
        return $this->fetch();
    }
    
    function truncate_tables_ (){
        $tables = query("show tables");
        $table = array('ybcms_admin','ybcms_config','ybcms_areas','ybcms_system_module','ybcms_admin_role','ybcms_system_menu','ybcms_article_cat','ybcms_wx_user_tp');
        foreach($tables as $key => $val){                                    
            if(!in_array($val['Tables_in_ybcms'], $table))                             
                echo "truncate table ".$val['Tables_in_ybcms'].' ; ';
                echo "<br/>";         
        }                
    }

    /**
     * 猜你喜欢
     * @author lxl
     * @time 17-2-15
     */
    public function ajax_favorite(){
        $p = input('p/d',1);
        $i = input('i',5); //显示条数
        //首页推荐商品
        $favourite_goods = Db::name('goods')->where("is_recommend=1 and is_on_sale=1")->order('goods_id DESC')->page($p,$i)->cache(true,CACHE_TIME)->select();
        $this->assign('favourite_goods',$favourite_goods);
        return $this->fetch();
    }
}