<?php
/**
 * 文章模型
 * -----------------------------------------
 * CopyRight @Ybcms开发团队，并保留所有权利
 * Url: http://www.ybcms.com
 * -----------------------------------------
 * Author: ybcms(dreamlak@qq.com)      
 * UpDate: 2017-03-01
 */

namespace app\admin\model;
use think\Db;
class Article extends Base{
	// status属性读取器
    public function getStatusAttr($value){
        $status = [-1 => '删除', 0 => '待审核', 1 => '正常'];
        return $status[$value];
    }
	
	//添加文章
	public function addart($data=''){
		if(empty($data))return $this->returnInfo('操作失败！提交数据为空！',0);
		$intoData=$data['info'];
		$intoData['style']=$data['font_weight'].$data['font_color'];
		$intoData['addtime']=strtotime($intoData['addtime']);
		$intoData['adminid']=is_login();
		$intoData['adminname']=get_adminName();
		
		if(empty($intoData['thumb'] && !empty($intoData['content']))){
			$intoData['thumb']=strval(getPic($intoData['content']));
		}
		
		if(empty($intoData['description'] && !empty($intoData['content']))){
			$intoData['description']=msubstr($intoData['content'],0,125);
		}
		
		array_filter($intoData);
		
		$artid=$this->insertGetId($intoData);
		if($artid){
			if(input('info.islink')==''){
				$url=url('Index/Article/show',['catid'=>$intoData['catid'],'artid'=>$artid]);
				$this->where('artid',$artid)->setField('url', $url);
			}
			addAdminLog('成功添加文章:'.$intoData['title']);
			$rd= ['status'=>1,'msg'=>'文章添加成功'];
		}else{
			$rd= ['status'=>0,'msg'=>'文章添加失败'];
		}
		return $rd;
	}
	//编辑
	public function editart($data){
		if(empty($data))return $this->returnInfo('操作失败！提交数据为空！',0);
		$upData=$data['info'];
		$upData['style']=$data['font_weight'].$data['font_color'];
		$upData['addtime']=strtotime($upData['addtime']);
		$upData['updatatime']=time();
		
		if(empty($upData['thumb'] && !empty($upData['content']))){
			$upData['thumb']=strval(getPic($upData['content']));
		}
		
		if(empty($upData['description'] && !empty($upData['content']))){
			$upData['description']=msubstr($upData['content'],0,125);
		}
		
		if(input('info.islink')==''){
			$upData['url']=url('Index/Article/show',['catid'=>$upData['catid'],'artid'=>input('artid')]);
		}
		
		array_filter($upData);
		$rs=$this->where('artid',input('artid'))->update($upData);
		if($rs){
			addAdminLog('成功编辑文章:'.$upData['title']);
			$rd= ['status'=>1,'msg'=>'文章编辑成功'];
		}else{
			$rd= ['status'=>0,'msg'=>'文章编辑失败'];
		}
		return $rd;
	}
	
	//获取栏目树
	public function getCatTree($data='',$action='index'){
		if($data==''){
			$cats=Db::name('article_cat')->where('status',1)->field('catid,pid,modelid,catname')->select();
			$data=model('ArticleCat')->multi_array($cats);
		}
		$html='';
		//icon
		$icon=[];
		$modlist=Db::name('article_mod')->where('status',1)->select();
		foreach($modlist as $v){
			$icon[$v['modelid']]=$v['icon'];
		}
		foreach ($data as $k=>$r){
			$catid=$r['catid'];
			$modelid=$r['modelid'];
			$catname=$r['catname'];
			if($modelid==5){
				$url='javascript:';
			}else{
				$url=url($action,['catid'=>$catid,'modelid'=>$modelid]);
			}
			$openable=$lastlink=$active=$modelico=$isopen='';
			//是否有子栏目
			if(!empty($r['child'])){
				$openable='openable';
				$modelico='fa fa-folder';
			}else{
				$openable='';
				$modelico=$icon[$modelid];
			}
			if($catid==input('catid')){
				$active=' active';
			}else{
				$active='';
			}
			//是否最后一条
			if(count($data)==$k+1){
				$lastlink=' last-link';
			}else{
				$lastlink='';
			}
			//所属上级都打开状态
			if(in_array($catid, $this->getCatparArr(input('catid')))){
				$openable.=' open';
			}
			
			$html.='<li class="'.$openable.$lastlink.$active.'">';
			$html.='<a href="'.$url.'"><i class="'.$modelico.' m-right-xs folder-icon"></i>'.$catname.'</a>';
			if(count($r['child'])==0 && $modelid!=5){
				$html.='<a href="'.url('add',['catid'=>$catid,'modelid'=>$modelid]).'" class="send"><i class="fa fa-plus"></i>发布</a>';
			}
			//如果有子节点
			if(count($r['child'])>0){
				$html.="\t\n".'<ul class="subtree">'."\n";
				$html.=$this->getCatTree($r['child'],$action);
				$html.='</ul>'."\n";
			}
			$html.='</li>'."\n";
		}
		if($html==''){
			return '<li style="padding:10px">暂无文章栏目...</li>';
		}else{
			return $html;
		}
	}
	//获取ID关系上级ID组
	public function getCatparArr($catid=0){
		$data=Db::name('article_cat')->where('status',1)->field('catid,pid')->select();
		$id_Arr=$this->catParents($data,$catid);//所有父级ID
		$ids=[];
		foreach($id_Arr as $v){
			$ids[]=$v['catid'];
		}
		unset($ids[array_search($catid,$ids)]);//删除自己
		return $ids;
	}
	/**
	 * 列出节点所有父级(顺序输出)
	 * @param Array $data      //数据库里获取的结果集
	 * @param Int $catid      //ID
	 */
	public function catParents($data,$catid=1,$count=0){
		$arr=array();
		foreach ($data as $k=>$v){
			if($v['catid']==$catid){
				$v['level'] = $count;
				$arr[]=$v;
				$arr=array_merge($this->catParents($data,$v['pid'],$count+1),$arr);
			}
		}
		return $arr;
	}
	//栏目选择
	public function getSelect($catid=0,$data='',$modelid=0){
		$html='';
		foreach ($data as $r){
			$id=$r['catid'];
			$catname=$r['catname'];
			
			$line='';
			if($r['pid']>0){
				$levels=$r['levels']-1;
				$line=str_repeat('├─',$levels);
			}
			//当前栏目选择状态
			$selected='';
			if($catid>0 && $catid==$r['catid']){
				$selected='selected="selected"';
			}
			
			//根目录不可选
			$disabled='';
			if(count($data)>1){
				if(count($r['child'])>0 || $r['modelid']==4 || $r['modelid']==5 || $r['modelid']!=$modelid){
					$disabled='disabled="disabled"';
				}
			}
			$html.='<option value="'.$id.'" '.$selected.' '.$disabled.'>'.$line.$catname.'</option>';
			if(count($data)>1){
				if(count($r['child'])>0){
					$html.=$this->getSelect($catid,$r['child'],$modelid);
				}
			}
		}
		return $html;
	}
}
?>