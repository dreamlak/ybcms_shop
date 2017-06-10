<?php
/*
 * @name  文章模型
 * @time on 2016/09/24
 * @Author  dreamlak   dreamlak@qq.com
 */
namespace app\mobile\model;
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
		if($catid!='')$map['pid']=$catid;
		//$map['modelid']=[['<>',4],['<>',5],'or'];
		$catlist=Db::name('article_cat')->where($map)->field('catid,pid,modelid,catname,url,sort')->order('sort')->select();
		$url='';
		foreach($catlist as $k=>$v){
			if($v['modelid']==4){
				$url=url('Mobile/Page/index',['catid'=>$v['catid']]);
			}elseif($v['modelid']==5){
				if(preg_match("/^(http:\/\/|https:\/\/).*$/",$v['url'])){
					$url=$v['url'];
				}else{
					$oldurl = substr($v['url'],1);
					$urlArr = explode('/', $oldurl);
					$urlArr[0]='mobile';
					$url='/'.implode('/', $urlArr);
				}
			}else{
				$url=url('Mobile/Article/lists',['catid'=>$v['catid']]);
			}
			$catlist[$k]['url']=$url;
		}
		return $catlist;
	}
	
	//文章列表
	public function getArtLists($aPage=1,$aCount=10){
		$aCount=config('paginate.list_rows');
		$map=['status'=>1,'islink'=>0];
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
		if(input('modelid')!='')$map['modelid']=input('modelid');
		if(input('title')!='')$map['title']=['like','%'.input('title').'%'];
		
		$totalCount=Db::name('article')->where($map)->count();
		$lists=Db::name('article')->where($map)->field('artid,catid,modelid,title,thumb,description,addtime,hot,sort')
		->order('artid DESC,sort DESC')->page($aPage, $aCount)->select();
		foreach($lists as $k=>$v){
			$lists[$k]['url']=url('Mobile/Article/show',['artid'=>$v['artid']]);
		}
		$data=[];
		$data['lists'] = $lists;
		$data['totalCount'] = $totalCount;
		if ($totalCount <= $aPage * $aCount) {
            $data['pagecount'] = 0;
        }else{
            $data['pagecount'] = 1;
        }
		
		$catname=Db::name('article_cat')->where('catid',$catid)->value('catname');
		if($catname!=''){
			$data['catname']=$catname;
		}else{
			$data['catname']='文章列表';
		}
		
		$modelid=Db::name('article_cat')->where('catid',$catid)->value('modelid');
		if($modelid!=''){
			$data['modelid']=$modelid;
		}else{
			$data['modelid']=1;
		}
		
		return $data;
	}
	
	//评论
	public function getCommentLists($artid,$aPage=1,$aCount=10){
		$aCount=config('paginate.list_rows');
		$map=[];
		$map['artid']=$artid;
		$map['pid']=0;

		$totalCount=Db::name('article_comment')->where($map)->count();
		$lists=Db::name('article_comment')->where($map)->order('id DESC')->page($aPage, $aCount)->select();
		foreach($lists as $k=>$v){
			$lists[$k]['child']=Db::name('article_comment')->where('pid',$v['id'])->order('id DESC')->select();
		}
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
		$data=Db::name('article_cat')->field('catid,pid,catname')->select();
		$crumbsArr=$this->catParents($data,$catid);
		$html='<a href="'.url('Mobile/Article/index').'">首页</a>';
		foreach($crumbsArr as $r){
			$catname=$r['catname'];
			$url=url('Mobile/Article/lists',['catid'=>$r['catid']]);
			$html.='/<a href="'.$url.'">'.$catname.'</a>';
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