<?php
/**
 * 微信回复
 * -----------------------------------------
 * CopyRight @Ybcms开发团队，并保留所有权利
 * Url: http://www.ybcms.com
 * -----------------------------------------
 * Author: ybcms(dreamlak@qq.com)      
 * UpDate: 2017-03-01
 */

namespace app\admin\model;
use think\Db;
class Wechatreply extends Base{
	public function artData($artid){
		$str='';
		if(empty($artid) || $artid==0)return $str;
		
		//主图文
		$info=Db::name('wx_mediaart')->where('artid',$artid)->find();
		
		//子图文
		$sublist=Db::name('wx_mediaart')->where('artpid',$info['artid'])->order('artid')->field('artid,artpid,title,thumb')->select();
		
		$httphost='http://'.$_SERVER['HTTP_HOST'];
		$str.='<div class="boxlist">';
		$str.='<ul class="clearfix">';
		$str.='<li>';
		if(count($sublist)>0){
			$str.='<p id="twtp">';
			$str.='<img alt="" src="'.$httphost.$info['thumb'].'" onerror="this.src=\'__IMG__public/noimg.jpg\'">';
			$str.='<span>'.$info['title'].'</span>';
			$str.='</p>';
			foreach($sublist as $v){
				$str.='<table style="width:100%" id="item" cellpadding="0" cellspacing="0">';
				$str.='<tbody>';
				$str.='<tr>';
				$str.='<td class="selected"><span>'.$v['title'].'</span></td>';
				$str.='<td style="width:50px" class="selected">';
				$str.='<img alt="" src="'.$httphost.$v['thumb'].'" onerror="this.src=\'__IMG__public/noimg.jpg\'">';
				$str.='</td>';
				$str.='</tr>';
				$str.='</tbody>';
				$str.='</table>';
			}
		}else{
			$str.='<h3>'.$info['title'].'</h3>';
			$str.='<p id="twtime">时间：'.date('Y-m-d H:i:s',$info['addtime']).'</p>';
			$str.='<p id="twtp"><img alt="" src="'.$httphost.$info['thumb'].'" onerror="this.src=\'__IMG__public/noimg.jpg\'"></p>';
			$str.='<p id="twzy">'.$info['description'].'</p>';
		}
		$str.='<div id="twgl">';
		$str.='<a href="javascript:" onclick="removetw()" class="deldets" title="删除"><i class="yb-opt-close"></i></a>';
		$str.='</div>';
		$str.='</li>';
		$str.='</ul>';
		$str.='</div>';
		
		return $str;
	}
}
?>