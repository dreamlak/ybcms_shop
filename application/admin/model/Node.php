<?php
/**
 * 节点模型
 * -----------------------------------------
 * CopyRight @Ybcms开发团队，并保留所有权利
 * Url: http://www.ybcms.com
 * -----------------------------------------
 * Author: ybcms(dreamlak@qq.com)      
 * UpDate: 2017-03-01
 */

namespace app\admin\model;
use think\Db;
class Node extends Base{
	// status属性读取器
    public function getStatusAttr($value){
        $status = [-1 => '删除', 0 => '禁用', 1 => '正常'];
        return $status[$value];
    }
	public function getDisplayAttr($value){
        $display = [0 => '隐藏', 1 => '显示'];
        return $display[$value];
    }
	
	//带分页节点列表
	public function getNodeLists($nodeid){
		$map=[];
		if($nodeid==''){
			$map=['pid'=>0];
		}else{
			$map=['pid'=>$nodeid];
		}
		if(input('status')!='')$map=['status'=>input('status')];
		if(input('display')!='')$map=['display'=>input('display')];
		if(input('title')!='')$map=['title'=>['like','%'.input('title').'%']];
		$totalCount=$this->where($map)->count();
		$pagecount=config('paginate.list_admin');
		$lists = $this->where($map)->order('sort asc')->paginate($pagecount,$totalCount,['query'=>request()->param()]);
		return $lists;
	}
	
	//添加/编缉节点
	public function addNode($data,$opttype='add'){
		if(empty($data))return $this->returnInfo('操作失败！提交数据为空！',0);
		$data['url']=url($data['m'].'/'.$data['c'].'/'.$data['a'],$data['data']);
		$rd=[];
		if($opttype=='add'){
			$rs=$this->insert($data);
			if($rs!==false){
				addAdminLog('成功添加节点:'.$data['title']);
				$rd= ['status'=>1,'msg'=>'节点添加成功'];
			}else{
				$rd= ['status'=>0,'msg'=>'节点添加失败'];
			}
		}else{
			$map=['nodeid'=>input('post.nodeid')];
			$rs=$this->where($map)->update($data);
			if($rs!==false){
				addAdminLog('成功编辑节点:'.$data['title']);
				$rd= ['status'=>1,'msg'=>'节点编辑成功'];
			}else{
				$rd= ['status'=>0,'msg'=>'节点编辑失败'];
			}
		}
		$this->updateNodeCache();
		return $rd;
	}
	
	//删除节点
	public function delNode($nodeid){
		$rd=[];
		//首先查找该节点是否有子节点
		$countNode=$this->where('pid',$nodeid)->count();
		if($countNode>0){
			$rd= $this->returnInfo('请先删除其下子节点！',0);
			return $rd;
		}else{
			$rs=$this->where('nodeid',$nodeid)->delete();
		}
		if($rs>0){
			$rd= ['status'=>1,'msg'=>'节点删除成功!'];
		}else{
			$rd= ['status'=>0,'msg'=>'删除失败!'];
		}
		return $rd;
	}
	
	//更新节点缓存
	public function updateNodeCache(){
		$map=['status'=>1,'display'=>1];
		$nodeData=$this->where($map)->order('sort asc')->select();
		foreach($nodeData as $k=>$v){
			$nodeData[$k]['url']=url($v['m'].'/'.$v['c'].'/'.$v['a'],$v['data']);
		}
		//数据集转JSON
		$nodJson=json_encode($nodeData);
		//生成JSON文件
		file_put_contents(ROOT_PATH.'/data/nodeCache.json', $nodJson);
		/*
		*生成PHP文件
		file_put_contents(ROOT_PATH.'/data/nodeCache.php', "<?php\t\nreturn " . var_export($nodeData, true) . ";\t\n?>");
		*/
	}
	
	//====================================================================================================================
	public function getNodeJson(){
		$nodeJson=file_get_contents(ROOT_PATH.'/data/nodeCache.json');
		$nodeData=json_decode($nodeJson, true);
		return $nodeData;
	}
	
	//获取权限主节点
	public function getMainNode(){
		$nodeData=$this->getNodeJson();
		$newArr=[];
		$thisNodeId=$this->getUrlLastNodeID();
		foreach($nodeData as $k=>$v){
			if($v['pid']=='0'){
				//选中状态
				if(in_array($v['nodeid'], $this->getNodeidLevsArr($thisNodeId))){
					$v['active']=1;
				}else{
					$v['active']=0;
				}
				//获取管理权限
				$roleid=session('adminuser.roleid');
				if($roleid>1){
					$roleData=explode(',', Db::name('admin_role')->where('roleid',$roleid)->value('data'));
					if(in_array($v['nodeid'],$roleData)){
						$newArr[]=$v;
					}
				}else{
					$newArr[]=$v;
				}
			}
		}
		//dump($newArr);die;
		return $newArr;
	}
	
	//获限节点树
	public function getNodeTree(){
		//获取主节点的ID
		$mainNodeId=$this->getIndexNodeID();
		$thisNodeId=$this->getUrlLastNodeID();
		//dump($thisNodeId);die;
		$tree=$this->getIndexNode($this->roleNodeList($mainNodeId,$thisNodeId));
		return $tree;
	}
	
	//获取有权限所有节点
	//$pid	上级ID
    public function roleNodeList($pid=0,$nodeid=0){
    	if($nodeid==0||empty($nodeid)) return false;
    	$nodeData=[];
		$nodeData=$this->getNodeJson();
		//获取管理权限
		$roleid=session('adminuser.roleid');
		if($roleid>1){
			$roleData=Db::name('admin_role')->where('roleid',$roleid)->value('data');
			$roleData=explode(',', $roleData);
			$newArr=[];
			foreach($nodeData as $k=>$v){
				if(in_array($v['nodeid'],$roleData)){
					$newArr[$k]=$v;
				}
			}
			$nodeData=$newArr;
		}
		//重新整理节点打开或选中
		foreach($nodeData as $kk=>$vv){
			//打开状态
			if(in_array($vv['nodeid'], $this->getNodeidLevsArr($nodeid))){
				$nodeData[$kk]['isopen']=1;
			}else{
				$nodeData[$kk]['isopen']=0;
			}
			//选中状态
			if($vv['nodeid']==$nodeid||in_array($vv['nodeid'], $this->getNodeidLevsArr($nodeid))){
				$nodeData[$kk]['active']=1;
			}else{
				$nodeData[$kk]['active']=0;
			}
		}
		$nodeData=$this->multi_array($nodeData,$pid);
		
		return $nodeData;
    }

	//获取二级节点树
	//$data 	具有层级关系的数组
	public function getIndexNode($data){
		if(empty($data))return false;
		$html='';
		$isopen='';
		foreach ($data as $k => $v){
			$icon=$v['icon'];
			$title=$v['title'];
			$url=$v['url'];
			if(count($v['child'])>0){
				$isopen=($v['isopen']==1)?'openable open':'openable';
			}else{
				$isopen=($v['active']==1)?'active':'';
			}
			//主节点显示
			$html.='<li id="sn_'.$v['nodeid'].'" class="'.$isopen.'">'."\n";
			$html.="\t".'<a href="javascript:void(0);" id="'.$v['nodeid'].'" url="'.$url.'" class="main-a">'."\n";
			$html.="\t\t".'<span class="menu-content block">'."\n";
			$html.="\t\t\t".'<span class="menu-icon"><i class="block fa '.$icon.'"></i></span>'."\n";
			$html.="\t\t\t".'<span class="text">'.$title.'</span>'."\n";
			if(count($v['child'])>0) $html.="\t\t\t".'<span class="submenu-icon"></span>'."\n";
			$html.="\t\t".'</span>'."\n";
			$html.="\t</a>\n";
			
			//如果有子节点
			if(count($v['child'])>0){
				$html.="\t".'<ul class="submenu">'."\n";
				$html.=$this->getSubNode($v['child']);
				$html.="\t</ul>\n";
			}
			$html.="</li>\n";
		}
		return $html;
	}

	/**
	 * 递归输出子节点树(配合multi_array用)
	 * @param Array $data      //数据库里获取的结果集
	 * @return string $html    //输出字符串
	 */
	protected function getSubNode($data){
		$html='';
		$isopen='';
		foreach ($data as $r){
			$title=$r['title'];
			if(count($r['child'])>0){
				$isopen=($r['isopen']==1)?'openable open':'openable';
			}else{
				$isopen=($r['active']==1)?'active':'';
			}
			$url=$r['url'];
			$html.="\t\t".'<li id="sn_'.$r['nodeid'].'" class="'.$isopen.'">'."\n";
			$html.="\t\t\t".'<a href="javascript:void(0);" id="'.$r['nodeid'].'" url="'.$url.'">'."\n";
			$html.="\t\t\t\t".'<span class="submenu-label">'.$title.'</span>'."\n";
			if(count($r['child'])>0) $html.="\t\t\t\t".'<span class="submenu-icon"></span>'."\n";
			$html.="\t\t\t".'</a>'."\n";
			
			//如果有子节点
			if(count($r['child'])>0){
				$html.="\t\t\t".'<ul class="submenu third-level">'."\n";
				$html.=$this->getSubNode($r['child']);
				$html.="\t\t\t"."</ul>\n";
			}
			$html.="\t\t</li>\n";
		}
		return $html;
	}

	//=================================================================================================================
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
				$value['child']=$this->multi_array($data,$value['nodeid'],$count+1);
				$array[]=$value;
				unset($data[$key]);
			}
		}
		return $array;
	}
	
	/**
	 * 递归多维数组转成一维
	 * @param Array $array     多维数组
	 */
	public function array_multi2single($array){
		static $result_array=array();
		foreach($array as $key => $value){
		    if(count($value['child'])>0){
		    	$newArray=$value['child'];
		    	unset($value['child']);
		    	$result_array[]=$value;
		        $this->array_multi2single($newArray);
		    }else{
		    	unset($value['child']);
		        $result_array[]=$value;
		        unset($array[$key]);
		    }
		}
		return $result_array;
	}

	/**
	 * 列出节点所有父级(顺序输出)
	 * @param Array $data      //数据库里获取的结果集
	 * @param Int $nodeid      //用户ID
	 */
	public function nodeParents($data,$nodeid=1,$count=0){
		$arr=array();
		foreach ($data as $k=>$v){
			if($v['nodeid']==$nodeid){
				$v['level'] = $count;
				$arr[]=$v;
				$arr=array_merge($this->nodeParents($data,$v['pid'],$count+1),$arr);
			}
		}
		return $arr;
	}
	
	//获取主节点ID
	public function getIndexNodeID(){
		//最终直实的节点ID
		$nodeid=$this->getUrlLastNodeID();
		//按顺序获取节点所有父级ID
		$data=Db::name('node')->field('nodeid,pid')->select();
		$nodeid_Arr=$this->nodeParents($data,$nodeid);
		if(count($nodeid_Arr)>0){
			return $nodeid_Arr[0]['nodeid'];
		}
	}
	
	//通过url获取最终直实的节点ID（此方法主要避免主从节点有相同的MCA）
	public function getUrlLastNodeID(){
		$nodeidarr=Db::name('node')->where(['m'=>MODULE,'c'=>CONTRO,'a'=>ACTION,'pid'=>['<>',0]])->field('nodeid,pid')->order('levels')->select();
		$nodeidcount=count($nodeidarr);
		//dump($nodeidarr);die;
		$nodeid='';
		$idarr=$pidarr=[];
		if($nodeidcount==1){
			return $nodeidarr[0]['nodeid'];
		}else if($nodeidcount>1){
			foreach($nodeidarr as $v){
				$idarr[]=$v['nodeid'];
				$pidarr[]=$v['pid'];
			}
			foreach($idarr as $id){
				if(!in_array($id, $pidarr)){
					return $id;
				}
			}
		}
	}
	//通过url获取最终直实的节点ID（此方法主要避免主从节点有相同的MCA）
	public function getUrlLastNodeID2($url){
		$nodeidarr=Db::name('node')->where(['url'=>$url,'pid'=>['<>',0]])->field('nodeid,pid')->order('levels')->select();
		$nodeidcount=count($nodeidarr);
		//dump($nodeidarr);die;
		$nodeid='';
		$idarr=$pidarr=[];
		if($nodeidcount==1){
			return $nodeidarr[0]['nodeid'];
		}else if($nodeidcount>1){
			foreach($nodeidarr as $v){
				$idarr[]=$v['nodeid'];
				$pidarr[]=$v['pid'];
			}
			foreach($idarr as $id){
				if(!in_array($id, $pidarr)){
					return $id;
				}
			}
		}
	}
	
	//获取节点ID关系组(用于节点树是否打开状态)
	public function getNodeidLevsArr($nodeid=0){
		$data=Db::name('node')->field('nodeid,pid')->select();
		$id_Arr=$this->nodeParents($data,$nodeid);//所有父级ID
		$ids=[];
		foreach($id_Arr as $v){
			$ids[]=$v['nodeid'];
		}
		return $ids;
	}
	
	//获取节点面包屑（管理后台公共函数使用）
	public function getNodeCrumbs(){
		$nodeid=$this->getUrlLastNodeID();
		$data=$this->field('nodeid,pid,title,m,c,a,data')->select();
		$crumbsArr=$this->nodeParents($data,$nodeid);
		
		$html='<ul class="breadcrumb">';
		$html.='<li><span class="primary-font"><i class="fa fa-home"></i></span><a href="'.url('Admin/Index/index').'"> 首页</a></li>';
		foreach($crumbsArr as $v){
			$title=$v['title'];
			if($v['nodeid']==$nodeid){
				$html.='<li>'.$title.'</li>';
			}else{
				$url=url($v['m'].'/'.$v['c'].'/'.$v['a'],$v['data']);
				$html.='<li><a href="'.$url.'">'.$title.'</a></li>';
			}
		}
		$html.='<li class="right"><a href="javascript:history.go(-1);"><i class="fa fa-reply"></i> 返回</a></li>';
		$html.='</ul>';
		
		return $html;
	}
	
	//以ID获取节点面包屑
	public function getToIDNodeCrumbs($nodeid){
		$data=$this->field('nodeid,pid,title,m,c,a,levels')->select();
		$crumbsArr=$this->nodeParents($data,$nodeid);
		$html='<a href="'.url('lists').'">顶级分类</a>';
		foreach($crumbsArr as $r){
			$title=$r['title'];
			$url=url('lists',['nodeid'=>$r['nodeid'],'levels'=>$r['levels']]);
			$html.='/<a href="'.$url.'">'.$title.'</a>';
		}
		return $html;
	}
	
	//节点的无限级分类（适合下拉列表,管理后台公共函数使用）
	public function getNodeSelect($nodeid=0,$data=''){
		if($data==''){
			$nodes=$this->field('nodeid,pid,title')->select();
			$data=$this->multi_array($nodes);
		}
		$html='';
		foreach ($data as $r){
			$id=$r['nodeid'];
			$title=$r['title'];
			
			$line='';
			if($r['pid']>0){
				$levels=$r['levels']-1;
				$line=str_repeat('├─',$levels);
			}
			
			$selected='';
			if($nodeid>0){
				if($nodeid==$r['nodeid']){
					$selected='selected="selected"';
				}
			}
			$html.='<option value="'.$id.'" '.$selected.'>'.$line.$title.'</option>';
			//如果有子节点
			if(count($r['child'])>0){
				$html.=$this->getNodeSelect($nodeid,$r['child']);
			}
		}
		return $html;
	}
}
?>