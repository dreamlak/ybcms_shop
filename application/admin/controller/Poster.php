<?php
/**
 * 广告管理
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
class Poster extends AdminBase{
	//广告位列表
	public function space(){
		$map=[];
		if(input('status')!='') $map['status']=input('status');
		if(input('name')!='') $map['name']=['like','%'.input('name').'%'];
		
		$totalCount=Db::name('poster_space')->where($map)->count();
		$pagecount=config('paginate.list_admin');
		$data=Db::name('poster_space')->where($map)->order('id DESC')->paginate($pagecount,$totalCount,['query'=>request()->param()]);
		
		$lists = $data->all();
		foreach($lists as $k=>$v){
			$lists[$k]['num']=Db::name('poster')->where('spaceid',$v['id'])->count();
		}
		$this->assign('lists',$lists);
		$this->assign('total',$data->total());
		$this->assign('listRows',$data->listRows());
		$this->assign('currentPage',$data->currentPage());
		$this->assign('lastPage',$data->lastPage());
		$this->assign('pages',$data->render());
		
		
		return $this->show();
	}
	//广告位添加
	public function spaceAdd(){
		if(request()->isPost() || request()->isAjax()){
			$data = input('post.');
			//数据验证
        	$validate = new Validate([
            	'name|广告位名称' => 'require',
            	'width|广告宽度' => 'require',
            	'height|广告高度' => 'require',
        	]);
        	if(!$validate->check($data)){
            	$msg=$validate->getError();
            	return ['status'=>0,'msg'=>$msg];
        	}
			return model('Poster')->addSpaceData($data);
		}else{
			return $this->show();
		}
	}
	//编辑广告位
	public function spaceEdit(){
		if(request()->isPost() || request()->isAjax()){
			$data = input('post.');
			//数据验证
        	$validate = new Validate([
            	'name|广告位名称' => 'require',
            	'width|广告宽度' => 'require',
            	'height|广告高度' => 'require',
        	]);
        	if(!$validate->check($data)){
            	$msg=$validate->getError();
            	return ['status'=>0,'msg'=>$msg];
        	}
			return model('Poster')->addSpaceData($data,'edit');
		}else{
			$info=Db::name('poster_space')->where('id',input('id'))->find();
			$this->assign('info',$info);
			return $this->show();
		}
	}
	//删除广告位
    public function spaceDel(){
        if(empty(input('id'))){//批量删除
			$ids=input('ids/a');
			$rd=['status'=>0,'msg'=>'删除失败!'];
			$names='';
	    	if(is_array($ids)){
				foreach($ids as $id){
					//检测是否还有广告
					$ocount=Db::name('poster')->where(['spaceid'=>$id])->count();
					if($ocount>0){
						$this->error('该位置下还有'.$ocount.'条广告，请先删除该位置的广告！');
					}
					
					//删除前获取广告位名
					$names.=Db::name('poster_space')->where('id',$id)->value('name').'，';
					//删除
					$rs=Db::name('poster_space')->where('id',$id)->delete();
					if($rs==0){
						return $rd;
					}else{
						$rd=['status'=>1,'msg'=>'删除成功!'];
					}
		        }
				if($rd['status']==1){
					addAdminLog('成功删除广告位:'.$names);
				}
			}
			return $rd;
		}else{//单条删除
			//检测是否还有信件
			$ocount=Db::name('poster')->where(['spaceid'=>input('id')])->count();
			if($ocount>0){
				$this->error('该位置下还有'.$ocount.'条广告，请先删除该位置的广告！');
			}
					
			//删除前获取广告位名
			$names=Db::name('poster_space')->where('id',input('id'))->value('name');
			//删除
			$rs=Db::name('poster_space')->where('id',input('id'))->delete();
			if($rs==0){
				$this->error('删除失败');
			}else{
				addAdminLog('成功删除广告位:'.$names);
				$this->success('删除成功');
			}
		}
    }
	//设置广告位状态
    public function setSpaceStatus(){
        $status=input('status');
		$ids=$_POST['ids'];
		foreach($ids as $id){
			Db::name('poster_space')->where('id',$id)->setField('status',$status);
		}
		return ['status'=>1,'msg'=>'设置成功！'];
    }
	
	//===============================================================================================
	//广告列表
	public function poster(){
		$map=[];
		if(input('spaceid')!='') $map['spaceid']=input('spaceid');
		if(input('status')!='') $map['status']=input('status');
		if(input('type')!='') $map['type']=input('type');
		if(input('name')!='') $map['name']=['like','%'.input('name').'%'];
		
		$totalCount=Db::name('poster')->where($map)->count();
		$pagecount=config('paginate.list_admin');
		$data=Db::name('poster')->where($map)->order('sort')->paginate($pagecount,$totalCount,['query'=>request()->param()]);
		
		$lists = $data->all();

		$this->assign('lists',$lists);
		$this->assign('total',$data->total());
		$this->assign('listRows',$data->listRows());
		$this->assign('currentPage',$data->currentPage());
		$this->assign('lastPage',$data->lastPage());
		$this->assign('pages',$data->render());
		
		$sname=Db::name('poster_space')->where('id',input('spaceid'))->value('name');
		$this->assign('sname',$sname);
		
		return $this->show();
	}
	//广告添加
	public function posterAdd(){
		if(request()->isPost() || request()->isAjax()){
			$data = input('post.');
			//数据验证
        	$validate = new Validate([
            	'name|广告名称' => 'require',
            	'starttime|开始时间' => 'require',
            	'images|图片地址' => 'require',
            	'url|链接地址' => 'require',
        	]);
        	if(!$validate->check($data)){
            	$msg=$validate->getError();
            	return ['status'=>0,'msg'=>$msg];
        	}
			return model('Poster')->addPosterData($data);
		}else{
			$sname=Db::name('poster_space')->where('id',input('spaceid'))->value('name');
			$this->assign('sname',$sname);
			
			return $this->show();
		}
	}
	//编辑广告
	public function posterEdit(){
		if(request()->isPost() || request()->isAjax()){
			$data = input('post.');
			//数据验证
        	$validate = new Validate([
            	'name|广告名称' => 'require',
            	'starttime|开始时间' => 'require',
            	'images|图片地址' => 'require',
            	'url|链接地址' => 'require',
        	]);
        	if(!$validate->check($data)){
            	$msg=$validate->getError();
            	return ['status'=>0,'msg'=>$msg];
        	}
			return model('Poster')->addPosterData($data,'edit');
		}else{
			$info=Db::name('poster')->where('id',input('id'))->find();
			$info['sname']=Db::name('poster_space')->where('id',$info['spaceid'])->value('name');
			$this->assign('info',$info);
			
			return $this->show();
		}
	}
	//删除广告
    public function posterDel(){
        if(empty(input('id'))){//批量删除
			$ids=input('ids/a');
			$rd=['status'=>0,'msg'=>'删除失败!'];
			$names='';
	    	if(is_array($ids)){
				foreach($ids as $id){
					//删除前获取广告名
					$names.=Db::name('poster')->where('id',$id)->value('name').'，';
					//删除
					$rs=Db::name('poster')->where('id',$id)->delete();
					if($rs==0){
						return $rd;
					}else{
						$rd=['status'=>1,'msg'=>'删除成功!'];
					}
		        }
				if($rd['status']==1){
					addAdminLog('成功删除广告:'.$names);
				}
			}
			return $rd;
		}else{//单条删除
			//删除前获取广告名
			$names=Db::name('poster')->where('id',input('id'))->value('name');
			//删除
			$rs=Db::name('poster')->where('id',input('id'))->delete();
			if($rs==0){
				$this->error('删除失败');
			}else{
				addAdminLog('成功删除广告:'.$names);
				$this->success('删除成功');
			}
		}
    }
	//设置广告状态
    public function setPosterStatus(){
        $status=input('status');
		$ids=$_POST['ids'];
		foreach($ids as $id){
			Db::name('poster')->where('id',$id)->setField('status',$status);
		}
		return ['status'=>1,'msg'=>'设置成功！'];
    }
	//广告排序
    public function setPosterSort(){
        $sort=$_POST['sort'];
		foreach($sort as $k=>$v){
			Db::name('poster')->where('id',$k)->setField('sort',$v);
		}
		return ['status'=>1,'msg'=>'排序成功！'];
    }
}
?>