<?php
//文章栏目URL
function get_caturl($catid){
	if($catid=='') return false;
	$cat=db('article_cat')->where('catid',$catid)->field('catid,modelid,url')->find();
	$url='';

	if($cat['modelid']==4){
		$url=url('Mobile/Page/index',['catid'=>$cat['catid']]);
	}elseif($cat['modelid']==5){
		if(preg_match("/^(http:\/\/|https:\/\/).*$/",$cat['url'])){
			$url=$cat['url'];
		}else{
			$oldurl = substr($cat['url'],1);
			$urlArr = explode('/', $oldurl);
			$urlArr[0]='Mobile';
			$url='/'.implode('/', $urlArr);
		}
	}else{
		$url=url('Mobile/Article/lists',['catid'=>$cat['catid']]);
	}
	
	return $url;
}
//文章栏目名称
function get_catname($catid){
	if($catid=='') return false;
	return db('article_cat')->where('catid',$catid)->value('catname');
}
//文章栏目模型
function get_catmod($catid){
	if($catid=='') return false;
	return db('article_cat')->where('catid',$catid)->value('modelid');
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