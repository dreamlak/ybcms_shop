<?php
/**
 * 微信图文管理
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
class Wechatmediaart extends AdminBase{
	//图文列表
	public function lists(){
		//dump(model('Wechatmediaart')->getMedia());die;
		$map=['artpid'=>0];
		$totalCount=Db::name('wx_mediaart')->where($map)->count();
		$pagecount=config('paginate.list_admin');
		$data=Db::name('wx_mediaart')->where($map)->order('artid DESC')->paginate($pagecount,$totalCount,['query'=>request()->param()]);
		$lists = $data->all();
		foreach($lists as $k=>$v){
			$lists[$k]['sublist']=Db::name('wx_mediaart')->where('artpid',$v['artid'])->field('artid,artpid,title')->select();
		}
		$this->assign('lists',$lists);
		$this->assign('total',$data->total());
		$this->assign('listRows',$data->listRows());
		$this->assign('currentPage',$data->currentPage());
		$this->assign('lastPage',$data->lastPage());
		$this->assign('pages',$data->render());
		
		return $this->show();
	}
	//添加图文
	public function add(){
		if(request()->isPost() || request()->isAjax()){
			$data = input('post.');
			//数据验证
        	$validate = new Validate([
            	'title|文章标题' => 'require',
            	'thumb|缩略图' => 'require',
            	'content|缩略图' => 'require',
        	]);
        	if(!$validate->check($data)){
            	$msg=$validate->getError();
            	return ['status'=>0,'msg'=>$msg];
        	}
			$data['addtime']=time();
			if(input('artid')!=''){
				$data['artpid']=input('artid');
			}
			unset($data['artid']);
			//获取缩略图素才信息
			$mediaArr=model('Wechatmediaart')->upThumbMedia($data['thumb']);
			if($mediaArr['status']==1){
				$data['thumb_media_id']=$mediaArr['thumb_media_id'];
			}
			$artid=Db::name('wx_mediaart')->insertGetId($data);
			if($artid>0){
				addAdminLog('成功添加图文:'.$data['title']);
				$url=url('edit',['artid'=>$artid]);
				return ['status'=>1,'msg'=>'添加图文成功','url'=>$url];
			}else{
				return ['status'=>0,'msg'=>'添加图文败'];
			}
		}else{
			$artid=input('artid');
			$artpid=Db::name('wx_mediaart')->where('artid',$artid)->value('artpid');
			
			$map=[];
			if($artpid==0){
				$groplist=Db::name('wx_mediaart')->where('artid',$artid)->find();;
				$groplist['sublist']=Db::name('wx_mediaart')->where('artpid',$artid)->order('artid')->field('artid,artpid,title,thumb')->select();
			}elseif($artpid>0){
				$groplist=Db::name('wx_mediaart')->where('artid',$artpid)->find();
				$groplist['sublist']=Db::name('wx_mediaart')->where('artpid',$artpid)->order('artid')->field('artid,artpid,title,thumb')->select();
			}
			$this->assign('groplist',$groplist);
			
			return $this->show();
		}
	}
	//编辑图文
	public function edit(){
		if(request()->isPost() || request()->isAjax()){
			$data = input('post.');
			unset($data['artid']);
			//数据验证
        	$validate = new Validate([
            	'title|文章标题' => 'require',
            	'thumb|缩略图' => 'require',
            	'content|缩略图' => 'require',
        	]);
        	if(!$validate->check($data)){
            	$msg=$validate->getError();
            	return ['status'=>0,'msg'=>$msg];
        	}
			//如果图片有更新，则重新上传素才库
			if($data['oldthumb']!=$data['thumb']){
				$mediaArr=model('Wechatmediaart')->upThumbMedia($data['thumb']);
				if($mediaArr['status']==1){
					$data['thumb_media_id']=$mediaArr['thumb_media_id'];
				}
			}
			unset($data['oldthumb']);
			$rs=Db::name('wx_mediaart')->where(['artid'=>input('artid')])->update($data);
			if($rs!==false){
				addAdminLog('编辑添加图文:'.$data['title']);
				return ['status'=>1,'msg'=>'编辑图文成功'];
			}else{
				return ['status'=>0,'msg'=>'编辑图文败'];
			}
		}else{
			$artid=input('artid');
			$info=Db::name('wx_mediaart')->where('artid',$artid)->find();
			$this->assign('info',$info);
			
			$map=[];
			if($info['artpid']==0){
				$groplist=$info;
				$groplist['sublist']=Db::name('wx_mediaart')->where('artpid',$info['artid'])->order('artid')->field('artid,artpid,title,thumb')->select();
			}else{
				$groplist=Db::name('wx_mediaart')->where('artid',$info['artpid'])->find();
				$groplist['sublist']=Db::name('wx_mediaart')->where('artpid',$info['artpid'])->order('artid')->field('artid,artpid,title,thumb')->select();
			}
			$this->assign('groplist',$groplist);
			
			$subcount=Db::name('wx_mediaart')->where('artpid',$info['artpid'])->count();
			$this->assign('subcount',$subcount);
			return $this->show();
		}
	}
	//删除图文
	public function del(){
		if(empty(input('artid'))){//批量删除
			$ids=input('ids/a');
			$rd=['status'=>0,'msg'=>'删除失败!'];
			$names='';
	    	if(is_array($ids)){
				foreach($ids as $id){
					//删除前获取该删除的名称
					$names.=Db::name('wx_mediaart')->where('artid',$id)->value('title').'，';
					//检测是否还有子项，有则删除
					$ocount=Db::name('wx_mediaart')->where(['artpid'=>$id])->count();
					if($ocount>0){
						Db::name('wx_mediaart')->where('artpid',$id)->delete();
					}
					//删除
					$rs=Db::name('wx_mediaart')->where('artid',$id)->delete();
					if($rs==0){
						return $rd;
					}else{
						$rd=['status'=>1,'msg'=>'删除成功!'];
					}
		        }
				if($rd['status']==1){
					addAdminLog('成功删除图文:'.$names);
				}
			}
			return $rd;
		}else{//单条删除
			//删除前获取该删除的名称
			$names=Db::name('wx_mediaart')->where('artid',input('artid'))->value('title');
			//删除
			$rs=Db::name('wx_mediaart')->where('artid',input('artid'))->delete();
			if($rs==0){
				$this->error('删除失败');
			}else{
				addAdminLog('成功删除图文:'.$names);
				$this->success('删除成功');
			}
		}
	}
	//图文选择
	public function artselect(){
		if(request()->isAjax()){
			$artid=input('artid');
			//主图文
			$info=Db::name('wx_mediaart')->where('artid',$artid)->find();
			//子图文
			$sublist=Db::name('wx_mediaart')->where('artpid',$info['artid'])->order('artid')->field('artid,artpid,title,thumb')->select();
			
			$str='<div class="boxlist">';
			$str.='<ul class="clearfix">';
			$str.='<li>';
			if(count($sublist)>0){
				$str.='<p id="twtp">';
				$str.='<img alt="" src="'.$info['thumb'].'" onerror="this.src=\'__IMG__public/noimg.jpg\'">';
				$str.='<span>'.$info['title'].'</span>';
				$str.='</p>';
				foreach($sublist as $v){
					$str.='<table style="width:100%" id="item" cellpadding="0" cellspacing="0">';
					$str.='<tbody>';
					$str.='<tr>';
					$str.='<td class="selected"><span>'.$v['title'].'</span></td>';
					$str.='<td style="width:50px" class="selected">';
					$str.='<img alt="" src="'.$v['thumb'].'" onerror="this.src=\'__IMG__public/noimg.jpg\'">';
					$str.='</td>';
					$str.='</tr>';
					$str.='</tbody>';
					$str.='</table>';
				}
			}else{
				$str.='<h3>'.$info['title'].'</h3>';
				$str.='<p id="twtime">时间：'.date('Y-m-d H:i:s',$info['addtime']).'</p>';
				$str.='<p id="twtp"><img alt="" src="'.$info['thumb'].'" onerror="this.src=\'__IMG__public/noimg.jpg\'"></p>';
				$str.='<p id="twzy">'.$info['description'].'</p>';
			}
			$str.='<div id="twgl">';
			$str.='<a href="javascript:" onclick="removetw()" class="deldets" title="删除"><i class="yb-opt-close"></i></a>';
			$str.='</div>';
			$str.='</li>';
			$str.='</ul>';
			$str.='</div>';
			return ['html'=>$str];
		}else{
			$map=['artpid'=>0];
			if(input('title')!='')$map['title']=['like','%'.input('title').'%'];
			$totalCount=Db::name('wx_mediaart')->where($map)->count();
			$pagecount=config('paginate.list_admin');
			$data=Db::name('wx_mediaart')->where($map)->order('artid DESC')->paginate($pagecount,$totalCount,['query'=>request()->param()]);
			$lists = $data->all();
			foreach($lists as $k=>$v){
				$lists[$k]['sublist']=Db::name('wx_mediaart')->where('artpid',$v['artid'])->field('artid,artpid,title')->select();
			}
			$this->assign('lists',$lists);
			$this->assign('total',$data->total());
			$this->assign('listRows',$data->listRows());
			$this->assign('currentPage',$data->currentPage());
			$this->assign('lastPage',$data->lastPage());
			$this->assign('pages',$data->render());
			
			return $this->show();
		}
	}
}
?>