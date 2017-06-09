<?php
/**
 * 文章
 * ============================================================================
 * 版权所有 Ybcms开发团队，并保留所有权利
 * 网站地址: http://www.ybcms.com
 * ============================================================================
 * Author: ybcms(dreamlak@qq.com)      
 * UpDate: 2017-03-01
 */
namespace app\mobile\controller;
use think\Validate;
use think\Db;
use think\Page;
class Article extends MobileBase {
	//首页
	public function index(){
		
		$this->setModName('文章首页');
		return $this->fetch();
	}
    //文章列表
    public function lists(){
    	$catid=input('catid');
    	//二级栏目
		$catlist=model('Article')->getCatLists();
		$this->assign("catlist", $catlist);
		
		//文章列表
		$artlists=model('Article')->getArtLists();
		$this->assign("artlists", $artlists);
		
		//模板选择
		$templates='lists';
		if($artlists['modelid']==2 ||$artlists['modelid']==3){
			$templates='lists_img';
		}elseif($artlists['modelid']==4){
			$this->redirect('mobile/Page/index', ['catid' => $catid]);
		}elseif($artlists['modelid']==5){
			$url=Db::name('article_cat')->where('catid',$catid)->value('url');
			if(preg_match("/^(http:\/\/|https:\/\/).*$/",$url)){
				//移动端不支持外链
				return false;
			}else{
				$url = substr($url,1);
				$urlArr = explode('/', $url);
				$urlArr[0]='mobile';
				$url=implode('/', $urlArr);
				$this->redirect($url);
			}
		}
		
    	$this->setModName($artlists['catname']);
		
		$getCatCrumbs=model('Article')->getCatCrumbs($catid);//面包屑
		$this->assign("getCatCrumbs", $getCatCrumbs);
		$this->assign("catid", $catid);
		
    	return $this->fetch('article/'.$templates);
    }
	//查看文章更多
    public function ajaxArtMore(){
	 	$aPage = input('page');
	 	//提现
	 	$lists = model('Article')->getArtLists($aPage);
		$templates='_lists';
		if($lists['modelid']==2 || $lists['modelid']==3){
			$templates='_lists_img';
		}
		if(count($lists['lists'])>0) {
            $data['html'] = "";
            foreach ($lists['lists'] as $val) {
                $this->assign("v", $val);
				
                $data['html'] .= $this->fetch('article/'.$templates);
            }
			$data['status'] = 1;
			$data['totalCount'] = $lists['totalCount'];
			//$data['pagecount'] = $lists['pagecount'];
        } else {
            $data['stutus'] = 0;
        }
        return $data;
    }
	
	//列表子部分
	public function show(){
    	$artid=input('artid');
		$info=Db::name('article')->where('artid',$artid)->find();
		$catid=$info['catid'];
		$this->assign('info',$info);
		
		Db::name('article')->where('artid',$artid)->setInc('hot',1);
		
		//上一篇
		$prev=Db::name('article')->where('artid','<',$artid)->order('artid desc')->limit('1')->find();
        if($prev){
            $url=url('Mobile/Article/show',['artid'=>$prev['artid']]);
			$prevurl='上一篇：<a href="'.$url.'">'.$prev['title'].'</a>';
        }else{
            $prevurl='上一篇：<a href="javascript:void(0);">无</a>';
        }
		//下一篇
		$next=Db::name('article')->where('artid','>',$artid)->order('artid desc')->limit('1')->find();
        if($next ){
            $url=url('Mobile/Article/show',['artid'=>$next['artid']]);
			$nexturl='下一篇：<a href="'.$url.'">'.$next['title'].'</a>';
        }else{
            $nexturl='下一篇：<a href="javascript:void(0);">无</a>';
        }
		
		$this->assign('prevurl',$prevurl);
		$this->assign('nexturl',$nexturl);
		
		//评论列表
		$commentlists=model('Article')->getCommentLists($artid);
		$this->assign("comment", $commentlists);
		
		$this->setModName($info['title']);
		
		$getCatCrumbs=model('Article')->getCatCrumbs($catid);//面包屑
		$this->assign("getCatCrumbs", $getCatCrumbs);
		$this->assign("catid", $catid);
		$this->assign("artid", $artid);
		
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
        	'content|内容' => 'require',
    	]);
    	if(!$validate->check($data)){
        	$msg=$validate->getError();
        	return ['status'=>0,'msg'=>$msg];
    	}
			
		$data['userid']=$data['userid'];
		$data['username']=deal_emoji(Db::name('member')->where('userid',$data['userid'])->value('nickname'));
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