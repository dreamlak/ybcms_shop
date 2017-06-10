<?php
/**
 * 菜单模型
 * -----------------------------------------
 * CopyRight @Ybcms开发团队，并保留所有权利
 * Url: http://www.ybcms.com
 * -----------------------------------------
 * Author: ybcms(dreamlak@qq.com)      
 * UpDate: 2017-03-01
 */

namespace app\admin\model;
use think\Db;
class WechatMenu extends Base{
	// status属性读取器
    public function getStatusAttr($value){
        $status = [-1 => '删除', 0 => '禁用', 1 => '正常'];
        return $status[$value];
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
				$value['levels'] = $count;
				$value['child']=$this->multi_array($data,$value['menuid'],$count+1);
				$array[]=$value;
				unset($data[$key]);
			}
		}
		return $array;
	}
	//添加/编缉菜单
	public function addMenu($data,$opttype='add'){
		if(empty($data))return $this->returnInfo('操作失败！提交数据为空！',0);
		
		$rd=[];
		if($opttype=='add'){
			$rs=Db::name('wx_menu')->insert($data);
			if($rs!==false){
				addAdminLog('成功添加菜单:'.$data['title']);
				$rd= ['status'=>1,'msg'=>'菜单添加成功'];
			}else{
				$rd= ['status'=>0,'msg'=>'菜单添加失败'];
			}
		}else{
			$map=['menuid'=>input('post.menuid')];
			$rs=Db::Name('wx_menu')->where($map)->update($data);
			if($rs!==false){
				addAdminLog('成功编辑菜单:'.$data['title']);
				$rd= ['status'=>1,'msg'=>'菜单编辑成功'];
			}else{
				$rd= ['status'=>0,'msg'=>'菜单编辑失败'];
			}
		}
		return $rd;
	}
	
	//删除菜单
	public function delMenu($menuid){
		$rd=[];
		//首先查找该菜单是否有子菜单
		$countMenu=Db::Name('wx_menu')->where('pid',$menuid)->count();
		if($countMenu>0){
			$rd= $this->returnInfo('请先删除其下子菜单！',0);
			return $rd;
		}else{
			$rs=Db::Name('wx_menu')->where('menuid',$menuid)->delete();
		}
		if($rs>0){
			$rd= ['status'=>1,'msg'=>'菜单删除成功'];
		}else{
			$rd= ['status'=>0,'msg'=>'菜单删除失败'];
		}
		return $rd;
	}
}
?>