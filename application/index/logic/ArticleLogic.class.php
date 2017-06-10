<?php
/**
 * 文章类
 * ============================================================================
 * 版权所有 Ybcms开发团队，并保留所有权利
 * 网站地址: http://www.ybcms.com
 * ============================================================================
 * Author: ybcms(dreamlak@qq.com)      
 * UpDate: 2017-03-01
 */
namespace index\Logic;
use think\Model\RelationModel;

class ArticleLogic extends RelationModel{

	public function getSiteArticle(){
		$syscate =  Db::name('ArticleCat')->where("cattype  = 1")->select();
		foreach($syscate as $v){
			$cats .= $v['catid'].',';
		}
		$cats = trim($cats,',');
		$result = Db::name('Article')->where("catid","in",$cats)->select();
		foreach ($result as $val){
			$arr[$val['catid']][] = $val;
		}
		
		foreach ($syscate as $v){
			$v['article'] = $arr[$v['catid']];
			$brr[] = $v;
		}
		return $brr;
	}
	
	public function getArticleDetail($article_id){
		$article = '';
		return $article;
	}
}