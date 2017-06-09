<?php
/**
 * 文章模型模型
 * -----------------------------------------
 * CopyRight @Ybcms开发团队，并保留所有权利
 * Url: http://www.ybcms.com
 * -----------------------------------------
 * Author: ybcms(dreamlak@qq.com)      
 * UpDate: 2017-03-01
 */

namespace app\admin\model;
use think\Db;
class ArticleMod extends Base{
	// status属性读取器
    public function getStatusAttr($value){
        $status = [-1 => '删除', 0 => '禁用', 1 => '正常'];
        return $status[$value];
    }
	
	//添加/编缉模型
	public function addData($data,$opttype='add'){
		if(empty($data))return $this->returnInfo('操作失败！提交数据为空！',0);
		
		$rd=[];
		if($opttype=='add'){
			$rs=$this->insert($data);
			if($rs!==false){
				addAdminLog('成功添加文章模型:'.$data['name']);
				$rd= ['status'=>1,'msg'=>'文章模型添加成功'];
			}else{
				$rd= ['status'=>0,'msg'=>'文章模型添加失败'];
			}
		}else{
			$map=['modelid'=>input('post.modelid')];
			$rs=$this->where($map)->update($data);
			if($rs!==false){
				addAdminLog('成功编辑文章模型:'.$data['name']);
				$rd= ['status'=>1,'msg'=>'文章模型编辑成功'];
			}else{
				$rd= ['status'=>0,'msg'=>'文章模型编辑失败'];
			}
		}
		return $rd;
	}
	
	/**
	 * 数组指定键排序（升序）
	 * @param $arr 		//要排序的数组
	 * @param $arr_k 	//该排序的数组键名
	 */
	public function array_sort($arr='',$arr_k=''){
		$flag=array();
		foreach($arr as $arr2){
		    $flag[]=$arr2[$arr_k];
		}
		array_multisort($flag, SORT_ASC, $arr);
		return $arr;
	}
}
?>