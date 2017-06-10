<?php
/**
 * 微信逻辑定义
 * ============================================================================
 * 版权所有 Ybcms开发团队，并保留所有权利
 * 网站地址: http://www.ybcms.com
 * ============================================================================
 * Author: ybcms(dreamlak@qq.com)      
 * UpDate: 2017-03-01
 */
namespace app\mobile\logic;
use think\Model;
use think\Db;
class WechatLogic extends Model{
	//获取图文
	public function getArts($artid){
		if($artid=='') return false;
		$arr=[];
		$info=Db::name('wx_mediaart')->where('artid',$artid)->find();
		if($info['url']==''){
			$url='http://'.$_SERVER['HTTP_HOST'].url('Mobile/Wechat/artshow',['artid'=>$info['artid']]);
		}else{
			$url=$info['url'];
		}
		$thumb='http://'.$_SERVER['HTTP_HOST'].$info['thumb'];
		$arr[]=["Title"=>$info['title'],"Description"=>$info['description'],"PicUrl"=>$thumb,"Url"=>$url];
		
		//子图文
		$subart=Db::name('wx_mediaart')->where('artpid',$info['artid'])->select();
		if(count($subart)>0){
			foreach($subart as $k=>$v){
				if($v['url']==''){
					$url='http://'.$_SERVER['HTTP_HOST'].url('Mobile/Wechat/artshow',['artid'=>$v['artid']]);
				}else{
					$url=$v['url'];
				}
				$thumb='http://'.$_SERVER['HTTP_HOST'].$v['thumb'];
				$arr[]=["Title"=>$v['title'],"Description"=>$v['description'],"PicUrl"=>$thumb,"Url"=>$url];
			}
		}
		return $arr;
	}
	//获取关键词内容
	public function getKeymsg($key=''){
		if($key=='') return false;
		$map=[];
		$map['status']1;
		$map['keyword']=$key;
		$info=Db::name('wx_keyreply')->where($map)->find();
		if($info['types']=='text'){
			return $info['words'];
		}else{
			return $this->getArts($info['artid']);
		}
	}
	
	//获取关注回复
	public function getBeadded(){
		$info=Db::name('wx_reply')->where('objs','beadded')->find();
		if($info['types']=='text'){
			return $info['words'];
		}else{
			return $this->getArts($info['artid']);
		}
	}
	
	//获取自动回复
	public function getAutoreply(){
		$info=Db::name('wx_reply')->where('objs','autoreply')->find();
		if($info['types']=='text'){
			return $info['words'];
		}else{
			return $this->getArts($info['artid']);
		}
	}
}
?>