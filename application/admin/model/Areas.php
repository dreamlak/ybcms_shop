<?php
/**
 * 地区模型
 * -----------------------------------------
 * CopyRight @Ybcms开发团队，并保留所有权利
 * Url: http://www.ybcms.com
 * -----------------------------------------
 * Author: ybcms(dreamlak@qq.com)      
 * UpDate: 2017-03-01
 */

namespace app\admin\model;
use think\Db;
class Areas extends Base{
	// status属性读取器
    public function getStatusAttr($value){
        $status = [-1 => '删除', 0 => '禁用', 1 => '正常'];
        return $status[$value];
    }
	
	//添加/编缉地区
	public function addAreas($data,$opttype='add'){
		if(empty($data))return $this->returnInfo('操作失败！提交数据为空！',0);
		$rd=['status'=>0,'msg'=>'操作失败'];
		if($opttype=='add'){
			$rs=$this->insert($data);
			if($rs!==false){
				addAdminLog('成功添加地区:'.$data['name']);
				$rd= ['status'=>1,'msg'=>'地区添加成功'];
			}else{
				$rd= ['status'=>0,'msg'=>'地区添加失败'];
			}
		}else{
			$map=['id'=>input('post.id')];
			$rs=$this->where($map)->update($data);
			if($rs!==false){
				addAdminLog('成功编辑地区:'.$data['name']);
				$rd= ['status'=>1,'msg'=>'地区编辑成功'];
			}else{
				$rd= ['status'=>0,'msg'=>'地区编辑失败'];
			}
		}
		return $rd;
	}
	
	//删除地区
	public function delAreas($id){
		$rd=['status'=>0,'msg'=>'操作失败'];
		//首先查找该地区是否有子地区
		$countMenu=$this->where('pid',$id)->count();
		if($countMenu>0){
			$rd= $this->returnInfo('请先删除其下地区！',0);
			return $rd;
		}else{
			$rs=$this->where('id',$id)->delete();
		}
		if($rs>0){
			$rd= ['status'=>1,'msg'=>'地区删除成功!'];
		}else{
			$rd= ['status'=>0,'msg'=>'地区删除失败!'];
		}
		return $rd;
	}
	
	/**
	 * 列出节点所有父级(顺序输出)
	 * @param Array $data      //数据库里获取的结果集
	 * @param Int $nodeid      //用户ID
	 */
	public function getParents($data,$id=1,$count=0){
		$arr=array();
		foreach ($data as $k=>$v){
			if($v['id']==$id){
				$v['levels'] = $count;
				$arr[]=$v;
				$arr=array_merge($this->getParents($data,$v['pid'],$count+1),$arr);
			}
		}
		return $arr;
	}
	//以ID获取节点面包屑
	public function getCrumbs($id){
		$html='<a href="'.url('lists').'">省区</a>';
		if($id==0) return $html;
		$data=json_decode(file_get_contents(ROOT_PATH.'/data/areaCache.json'), true);
		$crumbsArr=$this->getParents($data,$id);
		foreach($crumbsArr as $r){
			$name=$r['name'];
			$url=url('lists',['id'=>$r['id'],'level'=>$r['level']+1]);
			$html.='/<a href="'.$url.'">'.$name.'</a>';
		}
		return $html;
	}
	
	/**
	 * 将所有节点表生成具有层级关系的多维数组
	 * @param Array $data      //数据库里获取的结果集
	 * @param Int $pid     	   //上级ID
	 * @param Int $count       //第几级分类
	 * @return Array $array
	 */
	public function multi_array($data,$pid=0,$count=1){
		$array = array();
		foreach ($data as $key => $value){
			if($value['pid']==$pid){
				$value['level'] = $count;
				$value['child']=$this->multi_array($data,$value['id'],$count+1);
				$array[]=$value;
				unset($data[$key]);
			}
		}
		return $array;
	}
	public function toTree($data) { 
	    foreach($data as $v)
	    	$data[$v['pid']]['child'][$v['id']]=&$data[$v['id']]; 
	    return isset($data[0]['child'])?$data[0]['child']:array(); 
	}
}
?>