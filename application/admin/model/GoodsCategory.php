<?php
/**
 * 商品分类模型
 * -----------------------------------------
 * CopyRight @Ybcms开发团队，并保留所有权利
 * Url: http://www.ybcms.com
 * -----------------------------------------
 * Author: ybcms(dreamlak@qq.com)      
 * UpDate: 2017-03-01
 */
namespace app\admin\model;
use think\Model;
class GoodsCategory extends Model {
	//添加/编缉商品分类
	public function addData($data,$id=0){
		if(empty($data))return $this->returnInfo('操作失败！提交数据为空！',0);
		$rd=[];
		if($opttype==0){
			$id=$this->insertGetId($data);
			if($id!==false){
				addAdminLog('成功添加商品分类:'.$data['name']);
				$rd= ['status'=>1,'msg'=>'商品分类添加成功'];
			}else{
				$rd= ['status'=>0,'msg'=>'商品分类添加失败'];
			}
		}else{
			$map=['id'=>input('post.id')];
			$rs=$this->where($map)->update($data);
			if($rs!==false){
				addAdminLog('成功编辑商品分类:'.$data['name']);
				$rd= ['status'=>1,'msg'=>'商品分类编辑成功'];
			}else{
				$rd= ['status'=>0,'msg'=>'商品分类编辑失败'];
			}
		}
		return $rd;
	}
}
