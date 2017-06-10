<?php
/*
 * @name  单页
 * @time on 2016/09/20
 * @Author  dreamlak   dreamlak@qq.com
 */
namespace app\mobile\controller;
use think\Controller;
use think\Validate;
use think\Db;
class Page extends MobileBase{
    public function index(){
    	$catid=input('catid');
		
		$catinfo=Db::name('article_cat')->where('catid',$catid)->find();
		if($catinfo['ischild']==1){
			$catid=Db::name('article_cat')->where(['pid'=>$catid,'modelid'=>4])->order('sort')->limit('1')->value('catid');
		}
		
		$info=Db::name('page')->where('catid',$catid)->find();
		$template=$catinfo['pagetpl'];
		if($template==''){
			$template='index';
		}
		$this->assign('info',$info);
		
		if($info['title']==''){
			$title=$catinfo['catname'];
		}else{
			$title=$info['title'];
		}
		$this->setModName($title);
		
		Db::name('page')->where('catid',$catid)->setInc('hot',1);
		
		//二级栏目
		$catlist=model('Article')->getCatLists();
		$this->assign("catlist", $catlist);
		
    	return $this->fetch('page/'.$template);
    }
}
