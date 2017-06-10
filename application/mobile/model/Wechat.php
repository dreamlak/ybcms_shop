<?php
/*
 * @name  微信
 * @time on 2016/09/24
 * @Author  dreamlak   dreamlak@qq.com
 */
namespace app\mobile\model;
use think\Model;
use think\Db;
class Wechat extends Model{
	//获取图文
	public function getArts($artid){
		if($artid=='') return false;
		$arr=[];
		$info=Db::name('wx_mediaart')->where('artid',$artid)->find();
		if($info['url']==''){
			$url='http://'.$_SERVER['HTTP_HOST'].url('mobice/wechat/artshow',['artid'=>$info['artid']]);
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
					$url='http://'.$_SERVER['HTTP_HOST'].url('mobice/wechat/artshow',['artid'=>$v['artid']]);
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
		$map['status']=1;
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