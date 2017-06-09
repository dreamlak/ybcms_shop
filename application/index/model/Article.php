<?php
/*
 * @name  文章模型
 * @time on 2016/09/24
 * @Author  dreamlak   dreamlak@qq.com
 */
namespace app\index\model;
use think\Model;
use think\Db;
class Article extends Model{
	//栏目
	public function getCatLists(){
		$catid=input('catid');
		if($catid=='') return false;
		$catinfo=Db::name('article_cat')->where('catid',$catid)->field('catid,pid,ischild')->find();
		if($catinfo['ischild']==0){
			$catid=$catinfo['pid'];
		}
		
		$map=[];
		$map['status']=1;
		$map['pid']=$catid;
		$catlist=Db::name('article_cat')->where($map)->field('catid,pid,modelid,catname,url,sort')->order('sort')->select();
		$url='';
		foreach($catlist as $k=>$v){
			if($v['modelid']==4){
				$url=url('Index/Page/index',['catid'=>$v['catid']]);
			}elseif($v['modelid']==5){
				$url=$v['url'];
			}else{
				$url=url('Index/Article/lists',['catid'=>$v['catid']]);
			}
			$catlist[$k]['url']=$url;
		}
		return $catlist;
	}
	
	//文章列表
	public function getArtLists(){
		$keys=!empty(trim(input('key')))?trim(input('key')):'';
		$map=[];
		$map['status']=1;
		$catid=input('catid');
		if($catid>0){
			$catinfo=Db::name('article_cat')->where('catid',$catid)->field('catid,ischild,pidarr')->find();
			if($catinfo['ischild']==1){
				$pidarr = explode(',', $catinfo['pidarr']);
				$map['catid']=['in',$pidarr];
			}else{
				$map['catid']=$catid;
			}
		}
		if(!empty(input('modelid')))$map['modelid']=input('modelid');
		if($keys!='')$map['title|keywords|description|content|author|adminname|username']=['like','%'.$keys.'%'];

		$totalCount=Db::name('article')->where($map)->count();
		$pagecount=config('paginate.list_admin');
		$data=Db::name('article')->where($map)->order('artid DESC,sort DESC')->paginate($pagecount,$totalCount,['query'=>request()->param()]);
		return $data;
	}
	
	//评论
	public function getCommentLists($artid,$aPage=1,$aCount=10){
		$aCount=config('paginate.list_rows');
		$map=[];
		$map['artid']=$artid;

		$totalCount=Db::name('article_comment')->where($map)->count();
		$lists=Db::name('article_comment')->where($map)->order('id DESC')->page($aPage, $aCount)->select();
		
		$data=[];
		$data['lists'] = $lists;
		$data['totalCount'] = $totalCount;
		if ($totalCount <= $aPage * $aCount) {
            $data['pagecount'] = 0;
        }else{
            $data['pagecount'] = 1;
        }
		
		return $data;
	}
	//以ID获取栏目面包屑
	public function getCatCrumbs($catid){
		$data=Db::name('article_cat')->field('catid,pid,modelid,catname')->select();
		$crumbsArr=$this->catParents($data,$catid);
		$html='<a href="/">首页</a>';
		$url='';
		foreach($crumbsArr as $r){
			$catname=$r['catname'];
			
			if($r['modelid']==4){
				$url=url('Index/Page/index',['catid'=>$r['catid']]);
			}elseif($r['modelid']==5){
				$url=$v['url'];
			}else{
				$url=url('Index/Article/lists',['catid'=>$r['catid']]);
			}

			$html.=' - <a href="'.$url.'">'.$catname.'</a>';
		}
		return $html;
	}
	
	//获取栏目ID关系组
	public function getCatParentsArr($catid=0){
		$data=Db::name('article_cat')->field('catid,pid')->select();
		$id_Arr=$this->catParents($data,$catid);//所有父级ID
		$ids=[];
		foreach($id_Arr as $v){
			$ids[]=$v['catid'];
		}
		return $ids;
	}

	/**
	 * 列出栏目所有父级(顺序输出)
	 * @param Array $data      //数据库里获取的结果集
	 * @param Int $nodeid      //用户ID
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
}
?>