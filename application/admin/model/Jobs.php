<?php
/**
 * 人才招聘模型
 * -----------------------------------------
 * CopyRight @Ybcms开发团队，并保留所有权利
 * Url: http://www.ybcms.com
 * -----------------------------------------
 * Author: ybcms(dreamlak@qq.com)      
 * UpDate: 2017-03-01
 */

namespace app\admin\model;
use think\Db;
class Jobs extends Base{
	//添加/编缉职位
	public function addData($data,$opttype='add'){
		if(empty($data))return $this->returnInfo('操作失败！提交数据为空！',0);
		
		$rd=[];
		if($opttype=='add'){
			$rs=$this->insert($data);
			if($rs!==false){
				addAdminLog('成功添加招聘职位:'.$data['name']);
				$rd= ['status'=>1,'msg'=>'招聘职位添加成功'];
			}else{
				$rd= ['status'=>0,'msg'=>'招聘职位添加失败'];
			}
		}else{
			$map=['id'=>input('post.id')];
			$rs=$this->where($map)->update($data);
			if($rs!==false){
				addAdminLog('成功编辑招聘职位:'.$data['name']);
				$rd= ['status'=>1,'msg'=>'招聘职位编辑成功'];
			}else{
				$rd= ['status'=>0,'msg'=>'招聘职位编辑失败'];
			}
		}
		return $rd;
	}
}
?>