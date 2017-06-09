<?php
/**
 * 文章
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

class Article extends Base {
    public function index(){       
        $article_id = input('article_id/d',38);
    	$article = Db::name('article')->where("artid", $article_id)->find();
    	$this->assign('article',$article);
        return $this->fetch();
    }
 
    /**
     * 文章内列表页
     */
    public function articleList(){
        $article_cat = Db::name('article_cat')->where("pid  = 0")->select();
        $this->assign('article_cat',$article_cat);
        return $this->fetch();
    }    
    /**
     * 文章内容页
     */
    public function detail(){
    	$article_id = input('article_id/d',1);
    	$article = Db::name('article')->where("artid", $article_id)->find();
    	if($article){
    		$parent = Db::name('article_cat')->where("catid",$article['catid'])->find();
    		$this->assign('catname',$parent['catname']);
    		$this->assign('article',$article);
    	}
        return $this->fetch();
    } 
   
}