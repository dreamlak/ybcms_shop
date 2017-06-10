<?php
/**
 * 文章栏目模型
 * -----------------------------------------
 * CopyRight @Ybcms开发团队，并保留所有权利
 * Url: http://www.ybcms.com
 * -----------------------------------------
 * Author: ybcms(dreamlak@qq.com)      
 * UpDate: 2017-03-01
 */

namespace app\admin\model;
use think\Db;
class ArticleCat extends Base{
	// status属性读取器
    public function getStatusAttr($value){
        $status = [-1 => '删除', 0 => '禁用', 1 => '正常'];
        return $status[$value];
    }
	//添加栏目
	public function addcat($data='',$opttype='add'){
		if(empty($data))return $this->returnInfo('操作失败！提交数据为空！',0);
		$rd=['status'=>0,'msg'=>'操作失败'];
		if($opttype=='add'){
			$rs=$this->insert($data);
			if($rs!==false){
				addAdminLog('成功添加栏目:'.$data['catname']);
				$rd= ['status'=>1,'msg'=>'栏目添加成功'];
			}else{
				$rd= ['status'=>0,'msg'=>'栏目添加失败'];
			}
		}else{
			$map=['catid'=>input('post.catid')];
			$rs=$this->where($map)->update($data);
			if($rs!==false){
				addAdminLog('成功编辑栏目:'.$data['catname']);
				$rd= ['status'=>1,'msg'=>'栏目编辑成功'];
			}else{
				$rd= ['status'=>0,'msg'=>'栏目编辑失败'];
			}
		}
		return $rd;
	}
	//获取树形结构的列表
	public function getTreeList($data=''){
		if($data==''){
			$catdata=$this->where('status','<>',-1)->field('catid,pid,modelid,catname,url,status,sort,levels')->select();
			$data=$this->multi_array($catdata);
		}
		$html='';
		$ssarr=['0'=>'禁用','1'=>'启用'];
		//icon
		$icon=[];
		$modlist=Db::name('article_mod')->where('status',1)->select();
		foreach($modlist as $v){
			$icon[$v['modelid']]=$v['icon'];
		}
		
		foreach ($data as $r){
			$catid=$r['catid'];
			$catname=$r['catname'];
			$sort=$r['sort'];
			$line='';
			if($r['pid']>0){
				$levels=$r['levels']-1;
				$line=str_repeat('├──',$levels);
			}
			$html.='<tr for="chk'.$catid.'">'."\n";
			
			$html.='<td><div class="custom-checkbox">'."\n";
			$html.='<input type="checkbox" name="ids[]" id="chk'.$catid.'" class="inbox-check" value="'.$catid.'">';
			$html.='<label for="chk'.$catid.'"></label>'."\n";
			$html.='</div></td>'."\n";
			$html.='<td class="text-center">'.$catid.'</td>';
			$html.='<td class="text-center"><input type="text" name="sort['.$catid.']" id="sort" class="form-control" value="'.$sort.'"></td>'."\n";
			$html.='<td class="text-center"><i class="fa '.$icon[$r['modelid']].'" style="font-size:20px"></i></td>'."\n";
			$html.='<td>'.$line.$catname.'</td>';
			$html.='<td>'.$r['status'].'</td>'."\n";
			$html.='<td>'."\n";
			if($r['status']>=0){
				if($r['levels']<=3){
					$html.='<a href="'.url('add',['catid'=>$catid,'levels'=>$r['levels']+1]).'" class="btn btn-default btn-xs"><i class="fa fa-plus"></i> 添加子栏目</a>'."\n";
				}
				$html.='<a href="'.url('edit',['catid'=>$catid]).'" class="btn btn-default btn-xs"><i class="fa fa-edit"></i> 编辑</a>'."\n";
				$html.='<a href="'.url('del',['catid'=>$catid]).'" onclick="return confirm(\'确定删除吗?删除后无法恢复哟！\');" class="btn btn-default btn-xs"><i class="fa fa-trash-o"></i> 删除</a>'."\n";
			}
			$html.='</td>'."\n";
			$html.='</tr>';
			//如果有子节点
			if(count($r['child'])>0){
				$html.=$this->getTreeList($r['child']);
			}
		}
		return $html;
	}
	
	/**
	 * 将所有栏目表生成具有层级关系的多维数组
	 * @param Array $data      //数据库里获取的结果集
	 * @param Int $pid     	   //上级ID
	 * @param Int $count       //第几级分类
	 * @return Array $array
	 */
	public function multi_array($data,$catid=0,$count=1){
		$array = array();
		foreach ($data as $key => $value){
			if($value['pid']==$catid){
				$value['levels'] = $count;
				$value['child']=$this->multi_array($data,$value['catid'],$count+1);
				$array[]=$value;
				unset($data[$key]);
			}
		}
		return $array;
	}
	/**
	 * 列出所有父级(顺序输出)
	 * @param Array $data      //数据库里获取的结果集
	 * @param Int $catid      //ID
	 */
	public function catParents($data,$catid=0,$count=1){
		$arr=array();
		foreach ($data as $k=>$v){
			if($v['catid']==$catid){
				$v['levels'] = $count;
				$arr[]=$v;
				$arr=array_merge($this->catParents($data,$v['pid'],$count+1),$arr);
			}
		}
		return $arr;
	}
	/**
	 * 列出栏目所有子级ID
	 * @param Array $data      //数据库里获取的结果集
	 * @param Int $nodeid      //用户ID
	 */
	public function sun_array($data,$catid=1,$count=0){
		$arr=array();
		foreach ($data as $k=>$v){
			if($v['pid']==$catid){
				$v['levels'] = $count;
				$arr[]=$v['catid'];
				$arr=array_merge($this->sun_array($data,$v['catid'],$count+1),$arr);
			}
		}
		asort($arr);//根据值对数组进行升序排序
		return $arr;
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
	
	//节点的无限级分类（适合下拉列表,管理后台公共函数使用）
	public function getSelect($catid=0,$data=''){
		/*if($data==''){
			$cats=$this->where('status',1)->field('catid,pid,catname')->select();
			$data=$this->multi_array($cats);
		}*/
		$html='';
		foreach ($data as $r){
			$id=$r['catid'];
			$catname=$r['catname'];
			
			$line='';
			if($r['pid']>0){
				$levels=$r['levels']-1;
				$line=str_repeat('├──',$levels);
			}
			
			$selected='';
			if($catid>0 && $catid==$r['catid']){
				$selected='selected="selected"';
			}

			$html.='<option value="'.$id.'" '.$selected.'>'.$line.$catname.'</option>';
			
			if(count($r['child'])>0){
				$html.=$this->getSelect($catid,$r['child']);
			}
		}
		return $html;
	}
	//栏目选择
	public function getSelectUrl($catid=0,$data='',$type='pc'){
		$html='';
		foreach ($data as $r){
			if($type=='pc'){
				$url=$r['url'];
			}else{
				$url=$r['murl'];
			}
			$catname=$r['catname'];
			
			$line='';
			if($r['pid']>0){
				$levels=$r['levels']-1;
				$line=str_repeat('├─',$levels);
			}

			$html.='<option value="'.$url.'">'.$line.$catname.'</option>';
			if(count($data)>1){
				if(count($r['child'])>0){
					$html.=$this->getSelectUrl($catid,$r['child'],$type);
				}
			}
		}
		return $html;
	}
}
?>