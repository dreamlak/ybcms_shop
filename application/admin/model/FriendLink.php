<?php
/**
 * 友情链接模型
 * -----------------------------------------
 * CopyRight @Ybcms开发团队，并保留所有权利
 * Url: http://www.ybcms.com
 * -----------------------------------------
 * Author: ybcms(dreamlak@qq.com)      
 * UpDate: 2017-03-01
 */

namespace app\admin\model;
use think\Db;
class FriendLink extends Base{
	// status属性读取器
    public function getStatusAttr($value){
        $status = [-1 => '删除', 0 => '禁用', 1 => '正常'];
        return $status[$value];
    }
	
	//添加/编缉友情链接
	public function addData($data,$opttype='add'){
		if(empty($data))return $this->returnInfo('操作失败！提交数据为空！',0);
		
		$rd=[];
		if($opttype=='add'){
			$rs=$this->insert($data);
			if($rs!==false){
				addAdminLog('成功添加友情链接:'.$data['name']);
				$rd= ['status'=>1,'msg'=>'友情链接添加成功'];
			}else{
				$rd= ['status'=>0,'msg'=>'友情链接添加失败'];
			}
		}else{
			$map=['linkid'=>input('post.linkid')];
			$rs=$this->where($map)->update($data);
			if($rs!==false){
				addAdminLog('成功编辑友情链接:'.$data['name']);
				$rd= ['status'=>1,'msg'=>'友情链接编辑成功'];
			}else{
				$rd= ['status'=>0,'msg'=>'友情链接编辑失败'];
			}
		}
		return $rd;
	}
}
?>