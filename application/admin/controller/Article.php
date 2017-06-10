<?php
/**
 * 文章管理
 * -----------------------------------------
 * CopyRight @Ybcms开发团队，并保留所有权利
 * Url: http://www.ybcms.com
 * -----------------------------------------
 * Author: ybcms(dreamlak@qq.com)      
 * UpDate: 2017-03-01
 */

namespace app\admin\controller;
use think\Validate;
use \think\Db;
class Article extends AdminBase{
	//文章管理
	public function index(){
		$catid=!empty(input('catid'))?input('catid/d'):'';
		$modelid=!empty(input('modelid'))?input('modelid/d'):'';
		$status=input('status/d');
		$starttime=!empty(input('starttime'))?strtotime(input('starttime')):'';
		$endtime=!empty(input('endtime'))?strtotime(input('endtime')):'';
		$keys=!empty(trim(input('key')))?trim(input('key')):'';
		//dump($starttime);die;
		//1文章，2图片，3视频，4单页，5外链
		if($modelid==4){
			$this->redirect('page', ['catid' =>$catid,'modelid'=>$modelid]);
		}else if($modelid==5){
			$this->error('外链栏目，不可编辑');
		}
		
		$map=[];
		//栏目搜索
		if($catid!='')$map['catid']=$catid;
		//模型搜索
		if($modelid!='')$map['modelid']=$modelid;
		if(isset($status)){
			$map['status']=$status;
		}else{
			$map['status']=['<>',-1];
		}
		//时间搜索
		$ste=$ete='';
		if($starttime!=''){
			$map['addtime']=['<',$starttime];
			$ste=date('Y-m-d',$starttime);
		}else{
			$ste='';
		}
		if($endtime!=''){
			$map['addtime']=['>',$endtime];
			$ete=date('Y-m-d',$endtime);
		}else{
			$ete='';
		}
		//关键字搜索
		if($keys!='')$map['title|keywords|description|content|author|adminname|username']=['like','%'.$keys.'%'];
		//总数
		$totalCount=Db::name('article')->where($map)->count();
		$pagecount=config('paginate.list_admin');//每页条数
		$lists=Db::name('article')->where($map)->order('addtime DESC,sort DESC')->paginate($pagecount,$totalCount,['query'=>request()->param()]);
		$this->assign('lists',$lists);
		
		//icon
		$icon=[];
		$modlist=Db::name('article_mod')->where('status',1)->select();
		foreach($modlist as $v){
			$icon[$v['modelid']]=$v['icon'];
		}
		$this->assign('typearr',$icon);
		$this->assign('cid',$catid);
		$this->assign('mid',$modelid);
		$this->assign('sts',$status);
		$this->assign('ste',$ste);
		$this->assign('ete',$ete);
		$this->assign('keys',$keys);
		
		$catTree=model('Article')->getCatTree();
		$this->assign('catTree',$catTree);
		
		$this->assign('catName',Db::name('article_cat')->where('catid',$catid)->value('catname'));
		$this->assign('modlist',$modlist);
		return $this->show();
	}
	//文章发布
	public function add(){
		$catid=!empty(input('catid'))?input('catid'):0;
		$modelid=!empty(input('modelid'))?input('modelid'):0;
		if(request()->isPost() || request()->isAjax()){
			$data = input('post.');
			//数据验证
        	$validate = new Validate([
            	'title|文章标题' => 'require',
            	'catid|文章栏目' => 'require',
        	]);
        	if(!$validate->check($data['info'])){
            	$msg=$validate->getError();
            	return ['status'=>0,'msg'=>$msg];
        	}
			return model('Article')->addart($data);
		}else{
			//待选栏目分类
			$map=[];
			$map['status']=['<>',-1];
			$cat=Db::name('article_cat')->where($map)->select();
			if(count($cat)>1){
				$getSelect=model('Article')->getSelect($catid,model('ArticleCat')->multi_array($cat),$modelid);
			}else{
				$getSelect=model('Article')->getSelect($catid,$cat,$modelid);
			}
			$this->assign('getSelect',$getSelect);
			
			$this->assign('catid',$catid);
			$this->assign('modelid',$modelid);
    		return $this->show();
		}
	}
	//文章编辑
	public function edit(){
		if(request()->isPost() || request()->isAjax()){
			$data = input('post.');
			//数据验证
        	$validate = new Validate([
            	'title|文章标题' => 'require',
            	'catid|文章栏目' => 'require',
        	]);
        	if(!$validate->check($data['info'])){
            	$msg=$validate->getError();
            	return ['status'=>0,'msg'=>$msg];
        	}
			return model('Article')->editart($data);
		}else{
			$info=Db::name('article')->where('artid',input('artid'))->find();
			$style = explode(';', $info['style']);
			$this->assign('info',$info);
			$this->assign('style',$style);
			
			//待选栏目分类
			$catid=$info['catid'];
			$modelid=$info['modelid'];
			
			$map=[];
			$map['status']=['<>',-1];
			$cat=Db::name('article_cat')->where($map)->select();
			if(count($cat)>1){
				$getSelect=model('Article')->getSelect($catid,model('ArticleCat')->multi_array($cat),$modelid);
			}else{
				$getSelect=model('Article')->getSelect($catid,$cat,$modelid);
			}
			$this->assign('getSelect',$getSelect);
			
			$this->assign('catid',$catid);
			$this->assign('modelid',$modelid);

    		return $this->show();
		}
	}
	//添加编辑单页
	public function page(){
		$catid=input('catid');
		if(request()->isPost() || request()->isAjax()){
			$data = input('post.');
			//数据验证
        	$validate = new Validate([
            	'title|文章标题' => 'require',
            	'content|文章内容' => 'require',
        	]);
        	if(!$validate->check($data)){
            	$msg=$validate->getError();
            	return ['status'=>0,'msg'=>$msg];
        	}
			
			$data['updatetime']=time();
			if(Db::name('page')->where('catid',$catid)->count()>0){
				$rs=Db::name('page')->where('catid',$catid)->update($data);
				addAdminLog('成功设置单页:'.$data['title']);
			}else{
				$data['url']=url('Index/Article/page',['catid'=>$catid]);
				$rs=Db::name('page')->insert($data);
				addAdminLog('成功设置单页:'.$data['title']);
			}
			
			return ['status'=>1,'msg'=>'单页设置成功！'];
		}else{
			$this->assign('catid',$catid);
			
			$info=Db::name('page')->where('catid',$catid)->find();
			$this->assign('info',$info);
			
			$template=Db::name('article_cat')->where('catid',$catid)->value('pagetpl');
			$this->assign('template',$template);
			
			return $this->show();
		}
	}
	
	//文章删除
	public function del(){
		$ids=input('ids/a');
		$artnames='';
		$rd=['status'=>0,'msg'=>'删除失败!'];
		if(is_array($ids)){
			foreach($ids as $id){
				//删除前获取该删除的栏目名
				$artnames.=Db::name('article')->where('artid',$id)->value('title').'，';
				//删除
				$rs=Db::name('article')->where('artid',$id)->setField('status',-1);
				if($rs==0){
					return ['status'=>0,'msg'=>'删除失败!'];
				}else{
					$rd=['status'=>1,'msg'=>'删除成功!'];
				}
	        }
			if($rd['status']==1){
				addAdminLog('成功删除文章:'.$artnames);
			}
		}
		return $rd;
	}
	
	//设置文章状态
	public function setStatus(){
		$status=input('status');
		$ids=$_POST['ids'];
		foreach($ids as $id){
			Db::name('article')->where('artid',$id)->setField('status',$status);
		}
		return ['status'=>1,'msg'=>'设置成功！'];
	}
	//文章排序
	public function setSort(){
		$sort=$_POST['sort'];
		foreach($sort as $k=>$v){
			Db::name('article')->where('artid',$k)->setField('sort',$v);
		}
		return ['status'=>1,'msg'=>'排序成功！'];
	}
	
	//文章回收站
	public function recycleBin(){
		$catid=!empty(input('catid'))?input('catid/d'):'';
		$modelid=!empty(input('modelid'))?input('modelid/d'):'';
		$starttime=!empty(input('starttime'))?strtotime(input('starttime')):'';
		$endtime=!empty(input('endtime'))?strtotime(input('endtime')):'';
		$keys=!empty(trim(input('key')))?trim(input('key')):'';
		
		$map=[];
		$map['status']=-1;
		if($catid!='')$map['catid']=$catid;
		if($modelid!='')$map['modelid']=$modelid;
		if($starttime!='')$map['addtime']=['>=',$starttime];
		if($endtime!='')$map['addtime']=['<=',$endtime];
		if($keys!='')$map['title|keywords|description|content|author|adminname|username']=['like','%'.$keys.'%'];
		
		$totalCount=Db::name('article')->where($map)->count();
		$pagecount=config('paginate.list_admin');
		$lists=Db::name('article')->where($map)->order('addtime DESC,sort DESC')->paginate($pagecount,$totalCount,['query'=>request()->param()]);
		$this->assign('lists',$lists);
		
		//icon
		$icon=[];
		$modlist=Db::name('article_mod')->where('status',1)->select();
		foreach($modlist as $v){
			$icon[$v['modelid']]=$v['icon'];
		}
		$this->assign('typearr',$icon);
		
		$ste=$ete='';
		if($starttime!='')$ste=date('Y-m-d',$starttime);
		if($endtime!='')$ete=date('Y-m-d',$endtime);
		$this->assign('cid',$catid);
		$this->assign('mid',$modelid);
		$this->assign('ste',$ste);
		$this->assign('ete',$ete);
		$this->assign('keys',$keys);
		
		$catTree=model('Article')->getCatTree('','recycleBin');
		$this->assign('catTree',$catTree);
		return $this->show();
	}
	
	//回收站删除文章
	public function delRecycleBin(){
		$ids=input('ids/a');
		$rd=['status'=>0,'msg'=>'删除失败!'];
		$artnames='';
    	if(is_array($ids)){
			foreach($ids as $id){
				//删除前获取该删除的文章名
				$artnames.=Db::name('article')->where('artid',$id)->value('title').'，';
				//删除
				$rs=Db::name('article')->where('artid',$id)->delete();
				if($rs==0){
					return $rd;
				}else{
					$rd=['status'=>1,'msg'=>'删除成功!'];
				}
	        }
			if($rd['status']==1){
				addAdminLog('成功删除回收站文章:'.$artnames);
			}
		}
		return $rd;
	}
	
	//回收站还原文章
	public function resRecycleBin(){
		$ids=input('ids/a');
		$rd=['status'=>0,'msg'=>'文章还原失败!'];
		$artnames='';
    	if(is_array($ids)){
			foreach($ids as $id){
				//还原前获取该还原的栏目名
				$artnames.=Db::name('article')->where('artid',$id)->value('title').'，';
				//还原
				$rs=Db::name('article')->where('artid',$id)->setField('status',1);
				if($rs==0){
					return $rd;
				}else{
					$rd=['status'=>1,'msg'=>'文章还原成功!'];
				}
	        }
			if($rd['status']==1){
				addAdminLog('成功还原回收站文章:'.$artnames);
			}
		}
		return $rd;
	}
	
	public function isrenameAjax(){
		$title=input('title');
		if(Db::name('article')->where('title',$title)->count()>0){
			return ['status'=>0];
		}else{
			return ['status'=>1];
		}
	}
}
?>