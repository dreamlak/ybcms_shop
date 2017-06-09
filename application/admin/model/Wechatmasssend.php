<?php
/**
 * 微信群发
 * -----------------------------------------
 * CopyRight @Ybcms开发团队，并保留所有权利
 * Url: http://www.ybcms.com
 * -----------------------------------------
 * Author: ybcms(dreamlak@qq.com)      
 * UpDate: 2017-03-01
 */

namespace app\admin\model;
use think\Db;
class Wechatmasssend extends Base{
	//群发数据存储
	public function addData($data){
		if(empty($data))return $this->returnInfo('操作失败！提交数据为空！',0);
		
		$rd=[];
		$msgid=Db::name('wx_masssend')->insertGetId($data);
		if($msgid>0){
			if($data['types']=='mpnews'){
				$us=$this->upArts($msgid,$data['artid']);
				if($us['status']==0) return $us;
			}
			$rd=$this->artMasssend($msgid);
			if($rd['status']==0) return $rd;
			addAdminLog('成功微信群发');
		}else{
			$rd= ['status'=>0,'msg'=>'微信群发失败'];
		}
		
		return $rd;
	}
	
	//获取图文组
	public function getArtArr($artid){
		if($artid==''||$artid==0) return ['status'=>0,'msg'=>'图文ID参数错误!'];
		//主图文
		$indexArts=Db::name('wx_mediaart')->where('artid',$artid)->find();
		
		$httphost='http://'.$_SERVER['HTTP_HOST'];
		$data=[];
		$data[0]['thumb_media_id']=$indexArts['thumb_media_id'];
		$data[0]['author']=$indexArts['author'];
		$data[0]['title']=$indexArts['title'];
		if($indexArts['url']==''){
			$url=$httphost.url('Wap/Wechat/artshow',['artid'=>$indexArts['artid']]);
		}else{
			$url=$indexArts['url'];
		}
		$data[0]['content_source_url']=$url;
		$data[0]['content']=addslashes($indexArts['content']);
		$data[0]['digest']=$indexArts['description'];
		$data[0]['show_cover_pic']=$indexArts['showcoverpic'];
		
		//子图文
		$subData=[];
		$subArts=Db::name('wx_mediaart')->where('artpid',$indexArts['artid'])->select();
		if(count($subArts)>0){
			foreach($subArts as $k=>$v){
				$subData[$k]['thumb_media_id']=$v['thumb_media_id'];
				$subData[$k]['author']=$v['author'];
				$subData[$k]['title']=$v['title'];
				if($v['url']==''){
					$url=$httphost.url('Wap/Wechat/artshow',['artid'=>$v['artid']]);
				}else{
					$url=$v['url'];
				}
				$subData[$k]['content_source_url']=$url;
				$subData[$k]['content']=addslashes($v['content']);
				$subData[$k]['digest']=$v['description'];
				$subData[$k]['show_cover_pic']=$v['showcoverpic'];
			}
		}

		$artArr['articles']=array_merge($data,$subData);
		return $artArr;
	}
	/*上传图文消息素材（用于群发）图文上传完成后，会反回素材对应的media_id
	 * @param  $msgid		发送消息ID
	 * @param  $artid		发送图文ID
	 * @return $media_id	微信素材对应的ID
	 * 
	 * 样式数据:
	  	"articles": [{	//图文消息，一个图文消息支持1到10条图文
			"thumb_media_id"://图文消息缩略图的media_id，可以在基础支持-上传多媒体文件接口中获得,
			"author"://图文消息的作者,
			"title"://图文消息的标题,
			"content_source_url"://在图文消息页面点击“阅读原文”后的页面,
			"content"://图文消息页面的内容，支持HTML标签。具备微信支付权限的公众号，可以使用a标签，其他公众号不能使用,
			"digest"://图文消息的描述,
			"show_cover_pic"://是否显示封面，1为显示，0为不显示
		}]
	 */
	public function upArts($msgid,$artid){
		if($msgid==''||$msgid==0) return ['status'=>0,'msg'=>'群发消息ID参数错误!'];
		//图文组数据
		$artArr= $this->getArtArr($artid);
		//dump($artArr);die;
		//实例微信接口
		$media = & load_wechat('Media');
		//执行接口操作
		$result = $media->uploadArticles($artArr);
		//处理执行的结果
		$data=[];
		if($result===FALSE){
		    $data=['status'=>0,'msg'=>'上传图文错误:'.get_wechat_error($media->errCode)];
		}else{
			$data['status']=1;
			$data['msg']='ok';
		    $data['type']=$result['type'];
			$data['media_id']=$result['media_id'];
			$data['created_at']=$result['created_at'];
			
			Db::name('wx_masssend')->where('id',$msgid)->update(['media_id'=>$result['media_id']]);
		}
		
		return $data;
	}
	
	//微信图文消息推送
	public function artMasssend($msgid){
		if($msgid==''||$msgid==0) return ['status'=>0,'msg'=>'群发消息ID参数错误!'];
		//实例微信接口
		$wechat = &load_wechat('Receive');
		//执行接口操作
		$data=$this->getMassArr($msgid);
		$result = $wechat->sendGroupMassMessage($data);
		//处理执行的结果
		if($result===FALSE){
		    $data=['status'=>0,'msg'=>'图文推送错误:'.get_wechat_error($wechat->errCode)];
		}else{
			$data['status']=1;
			$data['msg']='微信群发成功';
		    $data['errcode']=$result['errcode'];
		    $data['errmsg']=$result['errmsg'];
		    $data['msg_id']=$result['msg_id'];
		    $data['msg_data_id']=$result['msg_data_id'];
			
			Db::name('wx_masssend')->where('id',$msgid)->update(['status'=>1]);
		}
		return $data;
	}

	//获取群发内容重组
	public function getMassArr($msgid){
		if($msgid==''||$msgid==0) return ['status'=>0,'msg'=>'群发消息ID参数错误!'];
		$info=Db::name('wx_masssend')->where('id',$msgid)->find();
		$arr=[];
		if($info['types']=='text'){
			$arr['filter']['is_to_all']=true;
			//$arr['filter']['group_id']=2;
			$arr['filter']['tag_id']=2;
			$arr['text']['content']=$info['words'];
			$arr['msgtype']='text';
		}else{
			$arr['filter']['is_to_all']=true;
			//$arr['filter']['group_id']=2;
			$arr['filter']['tag_id']=2;
			$arr['mpnews']['media_id']=$info['media_id'];
			$arr['msgtype']='mpnews';
		}
		return $arr;
	}
}
?>