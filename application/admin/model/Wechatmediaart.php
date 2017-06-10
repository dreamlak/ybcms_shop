<?php
/**
 * 素才管理
 * -----------------------------------------
 * CopyRight @Ybcms开发团队，并保留所有权利
 * Url: http://www.ybcms.com
 * -----------------------------------------
 * Author: ybcms(dreamlak@qq.com)      
 * UpDate: 2017-03-01
 */

namespace app\admin\model;
use think\Db;
class Wechatmediaart extends Base{
	/*上传永久封面素材到微信服务器，会反回素材对应的media_id
	 * @param  $file			缩略图文件
	 * @return $thumb_media_id	微信素材对应的ID
	 */
	public function upThumbMedia($file){
		if($file=='') return ['status'=>0,'msg'=>'图文ID参数错误!'];
		$file=thumb($file);
		$path=str_replace('\\','/',$_SERVER['DOCUMENT_ROOT']).$file;
		$data=['media'=>'@'.$path];
		//实例微信接口
		$media = & load_wechat('Media');
		//执行接口操作
		//为素材类型，可选有图片image、语音voice、视频video、缩略图thumb
		$result = $media->uploadForeverMedia($data,'thumb');
		
		$rsData=[];
		//处理执行的结果
		if($result===FALSE){
		    // 接口失败的处理
		    $rsData=['status'=>0,'msg'=>'上传永久封面素材到微信服务器：'.get_wechat_error($media->errCode)];
		}else{
		    $rsData['status']=1;
			$rsData['msg']='ok';
			$rsData['thumb_media_id']=$result['media_id'];
			$rsData['thumb_media_url']=$result['url'];
		}
		//dump($result);die;
		return $rsData;
	}
	
	/*获取永久素材列表
	 * @param  $type		可选有图片image、语音voice、视频video、缩略图thumb
	 */
	public function getMedia($type='image'){
		//实例微信接口
		$media = & load_wechat('Media');
		//执行接口操作
		$result = $media->getForeverList($type,0,20);
		//处理执行的结果
		if($result===FALSE){
		    return ['status'=>0,'msg'=>'获取素材：'.get_wechat_error($media->errCode)];
		}else{
		    return $result;
		}
	}
	
	/*删除永久素材
	 */
	public function delMedia($media_id=''){
		// 实例微信接口
		$media = & load_wechat('Media');
		// 执行接口操作
		$result = $media->delForeverMedia($media_id);
		// 处理执行的结果
		if($result===FALSE){
		    // 接口失败的处理
		    return ['status'=>0,'msg'=>'删除素材：'.get_wechat_error($media->errCode)];
		}else{
		    return $result;
		}
	}
	
	/**
	 * 上传图片到微信服务器（不占用素材资源数据）
	 * 返回的url就是上传图片的URL，可用于后续群发中，放置到图文消息中。
	 **/
	public function getMediaUrl($file){
		if($file=='') return ['status'=>0,'msg'=>'图片地址参数错误!'];
		$file=thumb($file,700,700,1);
		$path=str_replace('\\','/',$_SERVER['DOCUMENT_ROOT']).$file;
		$data=['media'=>'@'.$path];
		//实例微信接口
		$media = & load_wechat('Media');
		//执行接口操作
		$result = $media->uploadImg($data);
		//dump($result);die;
		//处理执行的结果
		if($result===FALSE){
		    // 接口失败的处理
		    return ['status'=>0,'msg'=>'上传图片到微信服务器：'.get_wechat_error($media->errCode)];
		}
		return ['status'=>1,'msg'=>'','url'=>$result['url']];
	}
}
?>