<?php
/*
 * @name  文章列表页
 * @time on 2016/09/20
 * @Author  dreamlak   dreamlak@qq.com
 */
namespace app\index\controller;
use think\Controller;
use think\Validate;
use think\Db;
class Article extends CommonBase{
	//文章列表
    public function lists(){
    	$catid=input('catid');
    	//二级栏目
		$catlist=model('Article')->getCatLists();
		$this->assign("catlist", $catlist);

		//模板选择
		$templates='lists';
		$catinfo=Db::name('article_cat')->where('catid',$catid)->find();
		if($catinfo['modelid']==4){
			$page=Db::name('page')->where('catid',$catid)->find();
			$this->assign('page',$page);
			$templates=$catinfo['pagetpl'];
		}elseif($catinfo['modelid']==5){
			$this->redirect($catinfo['url']);
		}else{
			//文章列表
			$data=model('Article')->getArtLists();
			$lists = $data->all();
			
			$this->assign('lists',$lists);
			$this->assign('total',$data->total());
			$this->assign('listRows',$data->listRows());
			$this->assign('currentPage',$data->currentPage());
			$this->assign('lastPage',$data->lastPage());
			$this->assign('pages',$data->render());
		
			if($catinfo['ischild']==1){
				$templates=$catinfo['indextpl'];
			}else{
				$templates=$catinfo['listtpl'];
			}
		}
		
    	$this->setModName($catinfo['catname']);
		
		$this->assign('catid',$catid);
		$this->assign('catname',$catinfo['catname']);
		
    	return $this->fetch('article/'.$templates);
    }
	
	//内容
	public function show(){
		$catid=input('catid');
    	$artid=input('artid');
		$info=Db::name('article')->where('artid',$artid)->find();
		$this->assign('info',$info);
		
		Db::name('article')->where('artid',$artid)->setInc('hot',1);
		
		//上一篇
		$prev=Db::name('article')->where('artid','<',$artid)->order('artid desc')->limit('1')->find();
        if($prev){
            $url=url('INdex/Article/show',['artid'=>$prev['artid']]);
			$prevurl='上一篇：<a href="'.$url.'">'.$prev['title'].'</a>';
        }else{
            $prevurl='上一篇：<a href="javascript:void(0);">无</a>';
        }
		//下一篇
		$next=Db::name('article')->where('artid','>',$artid)->order('artid desc')->limit('1')->find();
        if($next ){
            $url=url('INdex/Article/show',['artid'=>$next['artid']]);
			$nexturl='下一篇：<a href="'.$url.'">'.$next['title'].'</a>';
        }else{
            $nexturl='下一篇：<a href="javascript:void(0);">无</a>';
        }
		
		$this->assign('prevurl',$prevurl);
		$this->assign('nexturl',$nexturl);
		
		//栏目信息
		$catinfo=Db::name('article_cat')->where('catid',$catid)->find();
		$this->assign('catid',$catid);
		$this->assign('artid',$artid);
		$this->assign('catname',$catinfo['catname']);
		//栏目列表
		$catlist=model('Article')->getCatLists();
		$this->assign("catlist", $catlist);
		
		//评论列表
		$commentlists=model('Article')->getCommentLists($artid);
		$this->assign("comment", $commentlists);
		
		$this->setModName($catinfo['catname']);
		return $this->fetch();
    }

	//查看评论更多
    public function ajaxComMore(){
	 	$aPage = input('page');
		$artid = input('artid');
	 	//提现
	 	$lists = model('Article')->getCommentLists($artid,$aPage);
		if(count($lists['lists'])>0) {
            $data['html'] = "";
            foreach ($lists['lists'] as $val) {
                $this->assign("v", $val);
                $data['html'] .= $this->fetch('article/_lists_com');
            }
			$data['status'] = 1;
			$data['totalCount'] = $lists['totalCount'];
			$data['pagecount'] = $lists['pagecount'];
        } else {
            $data['stutus'] = 0;
        }
        return $data;
    }
	//发布课件评论
	public function comment(){
		$data=input('post.');
		//数据验证
    	$validate = new Validate([
        	'username|姓名' => 'require',
        	'content|内容' => 'require',
    	]);
    	if(!$validate->check($data)){
        	$msg=$validate->getError();
        	return ['status'=>0,'msg'=>$msg];
    	}
			
		$data['userid']=is_user_login();
		$data['ip']=request()->ip();
		$data['addtime']=time();
		
		$rs=Db::name('article_comment')->insert($data);
		if($rs!==false){
			Db::name('article')->where('artid',input('artid'))->setInc('comment',1);
			return ['status'=>1,'msg'=>'评论成功！'];
		}else{
			return ['status'=>0,'msg'=>'评论失败！'];
		}
	}
}
