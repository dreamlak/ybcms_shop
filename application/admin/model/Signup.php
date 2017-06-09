<?php
/*
 * @name  活动报名模型
 * @time on 2016/09/24
 * @Author  dreamlak   dreamlak@qq.com
 */
namespace app\admin\model;
use think\Db;
class Signup extends Base{
	//添加/编缉活动
	public function addActData($data,$opttype='add'){
		if(empty($data))return $this->returnInfo('操作失败！提交数据为空！',0);
		
		$rd=[];
		if($opttype=='add'){
			$rs=$this->insert($data);
			if($rs!==false){
				addAdminLog('成功添加活动:'.$data['title']);
				$rd= ['status'=>1,'msg'=>'活动添加成功'];
			}else{
				$rd= ['status'=>0,'msg'=>'活动添加失败'];
			}
		}else{
			$map=['id'=>input('post.id')];
			$rs=$this->where($map)->update($data);
			if($rs!==false){
				addAdminLog('成功编辑活动:'.$data['title']);
				$rd= ['status'=>1,'msg'=>'活动编辑成功'];
			}else{
				$rd= ['status'=>0,'msg'=>'活动编辑失败'];
			}
		}
		return $rd;
	}
}
?>