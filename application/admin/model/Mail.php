<?php
/**
 * 领导信箱模型
 * -----------------------------------------
 * CopyRight @Ybcms开发团队，并保留所有权利
 * Url: http://www.ybcms.com
 * -----------------------------------------
 * Author: ybcms(dreamlak@qq.com)      
 * UpDate: 2017-03-01
 */

namespace app\admin\model;
use think\Db;
class Mail extends Base{
	//添加/编缉领导信箱分类
	public function addMailTypeData($data,$opttype='add'){
		if(empty($data))return $this->returnInfo('操作失败！提交数据为空！',0);
		
		$rd=[];
		if($opttype=='add'){
			$rs=Db::name('mail_type')->insert($data);
			if($rs!==false){
				addAdminLog('成功添加领导信箱分类:'.$data['name']);
				$rd= ['status'=>1,'msg'=>'领导信箱分类添加成功'];
			}else{
				$rd= ['status'=>0,'msg'=>'领导信箱分类添加失败'];
			}
		}else{
			$map=['id'=>input('post.id')];
			$rs=Db::name('mail_type')->where($map)->update($data);
			if($rs!==false){
				addAdminLog('成功编辑领导信箱分类:'.$data['name']);
				$rd= ['status'=>1,'msg'=>'领导信箱分类编辑成功'];
			}else{
				$rd= ['status'=>0,'msg'=>'领导信箱分类编辑失败'];
			}
		}
		return $rd;
	}
}
?>