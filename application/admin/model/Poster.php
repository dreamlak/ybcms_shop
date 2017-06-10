<?php
/**
 * 广告模型
 * -----------------------------------------
 * CopyRight @Ybcms开发团队，并保留所有权利
 * Url: http://www.ybcms.com
 * -----------------------------------------
 * Author: ybcms(dreamlak@qq.com)      
 * UpDate: 2017-03-01
 */

namespace app\admin\model;
use think\Db;
class Poster extends Base{
	//添加/编缉广告位
	public function addSpaceData($data,$opttype='add'){
		if(empty($data))return $this->returnInfo('操作失败！提交数据为空！',0);
		$rd=[];
		if($opttype=='add'){
			$rs=Db::name('poster_space')->insert($data);
			if($rs!==false){
				addAdminLog('成功添加广告位:'.$data['name']);
				$rd= ['status'=>1,'msg'=>'广告位添加成功'];
			}else{
				$rd= ['status'=>0,'msg'=>'广告位添加失败'];
			}
		}else{
			$map=['id'=>input('post.id')];
			$rs=Db::name('poster_space')->where($map)->update($data);
			if($rs!==false){
				addAdminLog('成功编辑广告位:'.$data['name']);
				$rd= ['status'=>1,'msg'=>'广告位编辑成功'];
			}else{
				$rd= ['status'=>0,'msg'=>'广告位编辑失败'];
			}
		}
		return $rd;
	}
	
	//添加/编缉广告
	public function addPosterData($data,$opttype='add'){
		if(empty($data))return $this->returnInfo('操作失败！提交数据为空！',0);
		$data['starttime']=strtotime($data['starttime']);
		if($data['endtime']!='')$data['endtime']=strtotime($data['endtime']);
		$rd=[];
		if($opttype=='add'){
			$data['addtime']=time();
			$rs=$this->insert($data);
			if($rs!==false){
				addAdminLog('成功添加广告:'.$data['name']);
				$rd= ['status'=>1,'msg'=>'广告添加成功'];
			}else{
				$rd= ['status'=>0,'msg'=>'广告添加失败'];
			}
		}else{
			$map=['id'=>input('post.id')];
			$rs=$this->where($map)->update($data);
			if($rs!==false){
				addAdminLog('成功编辑广告:'.$data['name']);
				$rd= ['status'=>1,'msg'=>'广告编辑成功'];
			}else{
				$rd= ['status'=>0,'msg'=>'广告编辑失败'];
			}
		}
		return $rd;
	}
}
?>