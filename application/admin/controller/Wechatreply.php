<?php
/**
 * 微信回复管理
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
class Wechatreply extends AdminBase{
	//关注回复
	public function beadded(){
		if(request()->isPost() || request()->isAjax()){
			$data = input('post.');
			if($data['types']=='text'){
				$data['artid']=0;
			}else{
				$data['words']='';
			}
			$data['addtime']=time();
			$rs=Db::name('wx_reply')->where(['objs'=>'beadded'])->update($data);
			if($rs!==false){
				addAdminLog('设置关注回复');
				return ['status'=>1,'msg'=>'设置关注回复成功'];
			}else{
				return ['status'=>0,'msg'=>'设置关注回复文败'];
			}
		}else{
			$info=Db::name('wx_reply')->where(['objs'=>'beadded'])->find();
			$this->assign('info',$info);
			
			$artid=$info['artid'];
			$artcons=model('Wechatreply')->artData($artid);
			$this->assign('artcons',$artcons);
			
			return $this->show();
		}
	}
	//自动回复
	public function autoreply(){
		if(request()->isPost() || request()->isAjax()){
			$data = input('post.');
			if($data['types']=='text'){
				$data['artid']=0;
			}else{
				$data['words']='';
			}
			$data['addtime']=time();
			$rs=Db::name('wx_reply')->where(['objs'=>'autoreply'])->update($data);
			if($rs!==false){
				addAdminLog('设置自动回复');
				return ['status'=>1,'msg'=>'设置自动回复成功'];
			}else{
				return ['status'=>0,'msg'=>'设置自动回复文败'];
			}
		}else{
			$info=Db::name('wx_reply')->where(['objs'=>'autoreply'])->find();
			$this->assign('info',$info);
			
			$artid=$info['artid'];
			$artcons=model('Wechatreply')->artData($artid);
			$this->assign('artcons',$artcons);
			
			return $this->show();
		}
	}
	//关键词回复
	public function keyreply(){
		$map=[];
		$totalCount=Db::name('wx_keyreply')->where($map)->count();
		$pagecount=config('paginate.list_admin');
		$data=Db::name('wx_keyreply')->where($map)->order('id DESC')->paginate($pagecount,$totalCount,['query'=>request()->param()]);
		$lists = $data->all();
		$this->assign('lists',$lists);
		$this->assign('total',$data->total());
		$this->assign('listRows',$data->listRows());
		$this->assign('currentPage',$data->currentPage());
		$this->assign('lastPage',$data->lastPage());
		$this->assign('pages',$data->render());
		
		return $this->show();
	}
	//添加关键词
    public function add(){
		if(request()->isPost() || request()->isAjax()){
			$data = input('post.');
			
			$vdata=['keyword|关键词' => 'require'];
			if($data['types']=='text'){
				$vdata['words|文字内容']='require';
				$data['artid']=0;
			}else{
				$vdata['artid|图文内容']='require';
				$data['words']='';
			}
			//数据验证
        	$validate = new Validate($vdata);
        	if(!$validate->check($data)){
            	$msg=$validate->getError();
            	return ['status'=>0,'msg'=>$msg];
        	}
			$data['addtime']=time();
			$rs=Db::name('wx_keyreply')->insert($data);
			if($rs!==false){
				addAdminLog('添加微信关键词回复:'.$data['keyword']);
				return ['status'=>1,'msg'=>'添加微信关键词回复成功'];
			}else{
				return ['status'=>0,'msg'=>'添加微信关键词回复文败'];
			}
		}else{
    		return $this->show();
		}
    }
	
	//编辑关键词
    public function edit(){
        $id=!empty(input('id'))?input('id'):0;
		if(request()->isPost() || request()->isAjax()){
			$data = input('post.');
			
			$vdata=['keyword|关键词' => 'require'];
			if($data['types']=='text'){
				$vdata['words|文字内容']='require';
				$data['artid']=0;
			}else{
				$vdata['artid|图文内容']='require';
				$data['words']='';
			}
			//数据验证
        	$validate = new Validate($vdata);
        	if(!$validate->check($data)){
            	$msg=$validate->getError();
            	return ['status'=>0,'msg'=>$msg];
        	}
			
			$rs=Db::name('wx_keyreply')->where(['id'=>$id])->update($data);
			if($rs!==false){
				addAdminLog('编辑微信关键词回复:'.$data['keyword']);
				return ['status'=>1,'msg'=>'编辑微信关键词回复成功'];
			}else{
				return ['status'=>0,'msg'=>'编辑微信关键词回复文败'];
			}
		}else{
			$info=Db::name('wx_keyreply')->where(['id'=>$id])->find();
			$this->assign('info',$info);
			
			$artid=$info['artid'];
			$artcons=model('Wechatreply')->artData($artid);
			$this->assign('artcons',$artcons);
			
    		return $this->show();
		}
    }

	//删除关键词
    public function del(){
        if(empty(input('id'))){//批量删除
			$ids=input('ids/a');
			$rd=['status'=>0,'msg'=>'删除失败!'];
			$names='';
	    	if(is_array($ids)){
				foreach($ids as $id){
					//删除前获取名称
					$names.=Db::name('wx_keyreply')->where('id',$id)->value('keyword').'，';
					//删除
					$rs=Db::name('wx_keyreply')->where('id',$id)->delete();
					if($rs==0){
						return $rd;
					}else{
						$rd=['status'=>1,'msg'=>'删除成功!'];
					}
		        }
				if($rd['status']==1){
					addAdminLog('成功删除微信关键词回复:'.$names);
				}
			}
			return $rd;
		}else{//单条删除
			//删除前获取名称
			$names=Db::name('wx_keyreply')->where('id',input('id'))->value('keyword');
			//删除
			$rs=Db::name('wx_keyreply')->where('id',input('id'))->delete();
			if($rs==0){
				$this->error('删除失败');
			}else{
				addAdminLog('成功删除微信关键词回复:'.$names);
				$this->success('删除成功');
			}
		}
    }
	
	//设置关键词状态
    public function setStatus(){
        $status=input('status');
		$ids=$_POST['ids'];
		foreach($ids as $id){
			Db::name('wx_keyreply')->where('id',$id)->setField('status',$status);
		}
		return ['status'=>1,'msg'=>'设置成功！'];
    }
}
?>