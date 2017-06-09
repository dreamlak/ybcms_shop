<?php
/**
 * 文章栏目管理
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
class ArticleCat extends AdminBase{
	//栏目列表
	public function index(){
		$lists=model('ArticleCat')->getTreeList();
		$this->assign('lists',$lists);
		$catcount=Db::name('article_cat')->where('status','<>',-1)->count();
		$this->assign('catcount',$catcount);
		return $this->show();
	}
	//添加栏目
	public function add(){
		$catid=!empty(input('catid'))?input('catid'):0;
		$levels=!empty(input('levels'))?input('levels'):1;
		if(request()->isPost() || request()->isAjax()){
			$data = input('post.');
			//数据验证
        	$validate = new Validate([
            	'catname|栏目名称' => 'require',
        	]);
        	if(!$validate->check($data)){
            	$msg=$validate->getError();
            	return ['status'=>0,'msg'=>$msg];
        	}
			//dump($data);die;
			return model('ArticleCat')->addcat($data);
		}else{
			$data=model('ArticleCat')->multi_array(Db::name('article_cat')->where('status','<>',-1)->order('sort')->select());
			$this->assign('getSelect',model('ArticleCat')->getSelect($catid,$data));
			$this->assign('levels',$levels);
			
			$modlist=Db::name('article_mod')->where('status',1)->select();
			$this->assign('modlist',$modlist);
    		return $this->show();
		}
	}
	//编辑栏目
	public function edit(){
		if(request()->isPost() || request()->isAjax()){
			$data = input('post.');
			//数据验证
        	$validate = new Validate([
            	'catname|栏目名称' => 'require',
        	]);
        	if(!$validate->check($data)){
            	$msg=$validate->getError();
            	return ['status'=>0,'msg'=>$msg];
        	}
			return model('ArticleCat')->addcat($data,'edit');
		}else{
			$info=Db::name('article_cat')->where('catid',input('catid'))->find();
			$this->assign('info',$info);
			
			$data=model('ArticleCat')->multi_array(Db::name('article_cat')->where('status','<>',-1)->order('sort')->select());
			$this->assign('getSelect',model('ArticleCat')->getSelect($info['pid'],$data));
			
			$modlist=Db::name('article_mod')->where('status',1)->select();
			$this->assign('modlist',$modlist);
    		return $this->show();
		}
	}
	
	//删除栏目
	public function del(){
		if(empty(input('catid'))){//批量删除
			$ids=input('ids/a');
			$rd=['status'=>0,'msg'=>'删除失败!'];
			$catnames='';
	    	if(is_array($ids)){
				foreach($ids as $id){
					//检测是否还有文章
					$artcount=Db::name('article')->where(['status'=>['<>',-1],'catid'=>$id])->count();
					if($artcount>0){
						$this->error('该栏目旗下还要有'.$artcount.'篇文章，请先删除文章后再来删除该栏目！');
					}
					//检测是否还有子栏目
					$catcount=Db::name('article_cat')->where(['status'=>['<>',-1],'pid'=>$id])->count();
					if($catcount>0){
						$this->error('该栏目旗下还要有'.$catcount.'条子栏目，请先删除该子栏目后再来删除该栏目！');
					}
					//删除前获取该删除的栏目名
					$catnames.=Db::name('article_cat')->where('catid',$id)->value('catname').'，';
					//删除
					$rs=Db::name('article_cat')->where('catid',$id)->setField('status',-1);
					if($rs==0){
						return $rd;
					}else{
						$rd=['status'=>1,'msg'=>'删除成功!'];
					}
		        }
				if($rd['status']==1){
					addAdminLog('成功删除栏目:'.$catnames);
				}
			}
			return $rd;
		}else{//单条删除
			//检测是否还有文章
			$artcount=Db::name('article')->where(['status'=>['<>',-1],'catid'=>input('catid')])->count();
			if($artcount>0){
				$this->error('该栏目旗下还要有'.$artcount.'篇文章，请先删除文章后再来删除该栏目！');
			}
			//检测是否还有子栏目
			$catcount=Db::name('article_cat')->where(['status'=>['<>',-1],'pid'=>input('catid')])->count();
			if($catcount>0){
				$this->error('该栏目旗下还要有'.$catcount.'条子栏目，请先删除子栏目后再来删除该栏目！');
			}
			//删除前获取该删除的栏目名
			$catnames=Db::name('article_cat')->where('catid',input('catid'))->value('catname');
			//删除
			$rs=Db::name('article_cat')->where('catid',input('catid'))->setField('status',-1);
			if($rs==0){
				$this->error('删除失败');
			}else{
				addAdminLog('成功删除栏目:'.$catnames);
				$this->success('删除成功');
			}
		}
	}
	
	//设置栏目状态
	public function setStatus(){
		$status=input('status');
		$ids=$_POST['ids'];
		foreach($ids as $id){
			Db::name('article_cat')->where('catid',$id)->setField('status',$status);
		}
		return ['status'=>1,'msg'=>'设置成功！'];
	}
	
	//栏目排序
	public function setSort(){
		$sort=$_POST['sort'];
		foreach($sort as $k=>$v){
			Db::name('article_cat')->where('catid',$k)->setField('sort',$v);
		}
		return ['status'=>1,'msg'=>'排序成功！'];
	}
	
	//更新缓存
	public function sendCat(){
		//更新数据
		$catlist=Db::name('article_cat')->field('catid,pid,modelid,url')->select();
		if(count($catlist)==0){
			return ['status'=>0,'msg'=>'都还没有数据，你忙什么啊！'];
		}
		$data=[];
		$pidsData='';
		foreach($catlist as $v){
			if($v['modelid']==5){
				$data['url']=$v['url'];
				$ur=str_replace(['/Index/','/index/','/Home/','/home/'],'/mobile/',$v['url']);
				$data['murl']=$ur;
			}elseif($v['modelid']==4){
				$data['url']=url('Home/Page/index',['catid'=>$v['catid'],'modelid'=>$v['modelid']]);
				$data['murl']=url('mobile/page/index',['catid'=>$v['catid'],'modelid'=>$v['modelid']]);
			}else{
				$data['url']=url('Home/Article/index',['catid'=>$v['catid'],'modelid'=>$v['modelid']]);
				$data['murl']=url('mobile/Article/index',['catid'=>$v['catid'],'modelid'=>$v['modelid']]);
			}

			//是否有子级
			$isp=Db::name('article_cat')->where('pid',$v['catid'])->count();
			if($isp>0){
				$pidarr=model('ArticleCat')->sun_array($catlist,$v['catid']);
				$pidsData=implode(',',$pidarr);
				$data['pidarr']=$v['catid'].','.$pidsData;
				$data['ischild']=1;
			}else{
				$data['pidarr']='';
				$data['ischild']=0;
			}
			//重定层级数
			if($v['pid']==0){
				$data['levels']=1;
			}else{
				$Parent_arr=model('ArticleCat')->catParents($catlist,$v['catid']);//当前ID的所有父级（子->父顺序）
				foreach($Parent_arr as $pk=>$pv){
					if($pv['catid']==$v['catid']){
						$data['levels']=$pk+1;
					}
				}
			}
			//更新
			Db::name('article_cat')->where('catid',$v['catid'])->update($data);
		}
		
		//更新缓存
		$catData=Db::name('article_cat')->where('status',1)->order('sort asc')->select();
		//数据集转JSON
		$catJson=json_encode($catData);
		//生成JSON文件
		file_put_contents(ROOT_PATH.'/data/catCache.json', $catJson);
		
		addAdminLog('更新栏目缓存成功');
		return ['status'=>1,'msg'=>'更新栏目缓存成功！'];
	}
	
	//回收站
	public function recycleBin(){
		$totalCount=Db::name('article_cat')->where('status',-1)->count();
		$pagecount=config('paginate.list_admin');
		$lists=Db::name('article_cat')->where('status',-1)->paginate($pagecount,$totalCount,['query'=>request()->param()]);
		//icon
		$icon=[];
		$modlist=Db::name('article_mod')->where('status',1)->select();
		foreach($modlist as $v){
			$icon[$v['modelid']]=$v['icon'];
		}
		$this->assign('lists',$lists);
		$this->assign('typearr',$icon);
		$this->assign('totalCount',$totalCount);
		return $this->show();
	}
	//回收站删除
	public function delRecycleBin(){
		if(empty(input('catid'))){//批量删除
			$ids=input('ids/a');
			$rd=['status'=>0,'msg'=>'删除失败!'];
			$catnames='';
	    	if(is_array($ids)){
				foreach($ids as $id){
					//删除前获取该删除的栏目名
					$catnames.=Db::name('article_cat')->where('catid',$id)->value('catname').'，';
					//删除
					$rs=Db::name('article_cat')->where('catid',$id)->delete();
					if($rs==0){
						return $rd;
					}else{
						$rd=['status'=>1,'msg'=>'删除成功!'];
					}
		        }
				if($rd['status']==1){
					addAdminLog('成功删除回收站栏目:'.$catnames);
				}
			}
			return $rd;
		}else{//单条删除
			//删除前获取该删除的栏目名
			$catnames=Db::name('article_cat')->where('catid',input('catid'))->value('catname');
			//删除
			$rs=Db::name('article_cat')->where('catid',input('catid'))->delete();
			if($rs==0){
				$this->error('删除失败');
			}else{
				addAdminLog('成功删除回收站栏目:'.$catnames);
				$this->success('删除成功');
			}
		}
	}
	//回收站还原
	public function resRecycleBin(){
		if(empty(input('catid'))){//批量还原
			$ids=input('ids/a');
			$rd=['status'=>0,'msg'=>'栏目还原失败!'];
			$catnames='';
	    	if(is_array($ids)){
				foreach($ids as $id){
					//还原前获取该还原的栏目名
					$catnames.=Db::name('article_cat')->where('catid',$id)->value('catname').'，';
					//还原
					$rs=Db::name('article_cat')->where('catid',$id)->setField('status',1);
					if($rs==0){
						return $rd;
					}else{
						$rd=['status'=>1,'msg'=>'栏目还原成功!'];
					}
		        }
				if($rd['status']==1){
					addAdminLog('成功还原回收站栏目:'.$catnames);
				}
			}
			return $rd;
		}else{//单条还原
			//还原前获取该还原的栏目名
			$catnames=Db::name('article_cat')->where('catid',input('catid'))->value('catname');
			//还原
			$rs=Db::name('article_cat')->where('catid',input('catid'))->setField('status',1);
			if($rs==0){
				$this->error('栏目还原失败');
			}else{
				addAdminLog('成功还原回收站栏目:'.$catnames);
				$this->success('栏目还原成功');
			}
		}
	}
	
	public function ajaxmod(){
		$modelid=input('id');
		$info=Db::name('article_mod')->where('modelid',$modelid)->find();
		return $info;
	}
}
?>