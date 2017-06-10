<?php
//文章栏目URL
function get_caturl($catid){
	if($catid=='') return false;
	$cat=db('article_cat')->where('catid',$catid)->field('catid,modelid,url')->find();
	$url='';

	if($cat['modelid']==4){
		$url=url('Index/Page/index',['catid'=>$cat['catid']]);
	}elseif($cat['modelid']==5){
		$url=$cat['url'];
	}else{
		$url=url('Index/Article/lists',['catid'=>$cat['catid']]);
	}
	
	return $url;
}
//文章栏目名称
function get_catname($catid){
	if($catid=='') return false;
	return db('article_cat')->where('catid',$catid)->value('catname');
}
function get_catname_en($catid){
	if($catid=='') return false;
	return db('article_cat')->where('catid',$catid)->value('catname_en');
}
//上级文章栏目名称
function get_cattopname($catid){
	if($catid=='') return false;
	$pid=get_catpidd($catid);
	if($pid==0) $pid=$catid;
	return db('article_cat')->where('catid',$pid)->value('catname');
}
function get_cattopname_en($catid){
	if($catid=='') return false;
	$pid=get_catpidd($catid);
	if($pid==0) $pid=$catid;
	return db('article_cat')->where('catid',$pid)->value('catname_en');
}
//上级文章栏目ID
function get_catpidd($catid){
	if($catid=='') return false;
	return db('article_cat')->where('catid',$catid)->value('pid');
}
//文章栏目模型
function get_catmod($catid){
	if($catid=='') return false;
	return db('article_cat')->where('catid',$catid)->value('modelid');
}
//文章栏目图片
function get_catimg($catid){
	if($catid=='') return false;
	return db('article_cat')->where('catid',$catid)->value('thumb');
}
//文章栏目简介
function get_catdesc($catid){
	if($catid=='') return false;
	return db('article_cat')->where('catid',$catid)->value('description');
}
//文章栏目关键字
function get_catkey($catid){
	if($catid=='') return false;
	return db('article_cat')->where('catid',$catid)->value('keywords');
}
//是否有子栏目
function ischild($catid){
	if($catid=='') return false;
	return db('article_cat')->where('catid',$catid)->value('ischild');
}
//子栏目ID
function get_pidarr($catid){
	if($catid=='') return false;
	return db('article_cat')->where('catid',$catid)->value('pidarr');
}
//文章栏目列表
function get_catlist($catid){
	$map=[];
	$map['status']=1;
	if($catid==''){
		$map['pid']=0;
	}else{
		$catinfo=db('article_cat')->where('catid',$catid)->field('catid,pid,ischild')->find();
		if($catinfo['ischild']==0){
			$catid=$catinfo['pid'];
		}
		$map['pid']=$catid;
	}
	$lists=db('article_cat')->where($map)->order('sort')->select();
	$url='';
	foreach($lists as $k=>$v){
		if($v['modelid']==4){
			$url=url('Index/Page/index',['catid'=>$v['catid']]);
		}elseif($v['modelid']==5){
			$url=$v['url'];
		}else{
			$url=url('Index/Article/lists',['catid'=>$v['catid']]);
		}
		$lists[$k]['url']=$url;
	}
	return $lists;
}
//以ID获取栏目面包屑
function get_Catbs($catid){
	return model('Article')->getCatCrumbs($catid);
}
//获取栏目顶级ID
//if(!in_array('1', $idArr))
function get_CatParent($catid,$is=1){
	$idArr=[];
	if(!$catid)return $idArr;
	$idArr = model('Article')->getCatParentsArr($catid);
	if($is==1){
		return $idArr[0];
	}else{
		return $idArr;
	}
}
//该栏目下最新第一篇有图片的文章
function get_firstimg($catid,$thumb=1){
	if($catid=='') return false;
	$map=[];
	$map['catid']=$catid;
	if($thumb==1)$map['thumb']=['<>',''];
	$info=db('article')->where($map)->find();
	return $info;
}
//普通文本筐内容输出转换HTML格式
function nl2p($string, $line_breaks = false, $xml = true){
	//清除HTML标签
	$string = str_replace(array('<p>','</p>','<br>','<br />'), '', $string);
	//替换空行
	$string = preg_replace('/(($\n\r*$)|(^\n\r*^))+/m', '',$string);
	
	if ($line_breaks == true){//单行格式
		return '<p>'.preg_replace(array("/([\n]{2,})/i", "/([^>])\n([^<])/i"), array("</p>\n<p>", '<br'.($xml == true ? ' /' : '').'>'), trim($string)).'</p>';
	}else{//段落格式
		//return "<p>" . str_replace("\n", "</p><p>", $string) . "</p>";
		return '<p>'.preg_replace("/([\n]{1,})/i", "</p>\n<p>", trim($string)).'</p>';
	}
}
?>