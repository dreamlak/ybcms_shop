<?php
/**
 * 友情链接
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
class FriendLink extends AdminBase{
	//链接管理
	public function index(){
		$pagecount=config('paginate.list_admin');
		$totalCount=Db::name('friend_link')->count();
		$data=Db::name('friend_link')->order('sort')->paginate($pagecount,$totalCount,['query'=>request()->param()]);
		$lists = $data->all();
		foreach($lists as $k=>$v){
			$lists[$k]['tname']=Db::name('friend_link_type')->where('id',$v['typeid'])->value('name');
		}
		//dump($lists);die;
		$this->assign('lists',$lists);
		$this->assign('total',$data->total());
		$this->assign('listRows',$data->listRows());
		$this->assign('currentPage',$data->currentPage());
		$this->assign('lastPage',$data->lastPage());
		$this->assign('pages',$data->render());
		return $this->show();
	}
	//添加链接
	public function add(){
		if(request()->isPost() || request()->isAjax()){
			$data = input('post.');
			//数据验证
        	$validate = new Validate([
            	'typeid|分类' => 'require',
            	'name|名称' => 'require',
            	'url|url' => 'require',
            	'logo|LOGO' => 'require',
        	]);
        	if(!$validate->check($data)){
            	$msg=$validate->getError();
            	return ['status'=>0,'msg'=>$msg];
        	}

			return model('FriendLink')->addData($data);
			return false;
		}else{
			$typelist=Db::name('friend_link_type')->order('sort')->select();
			$this->assign('typelist',$typelist);
    		return $this->show();
		}
	}
	//编辑链接
	public function edit(){
		if(request()->isPost() || request()->isAjax()){
			$data = input('post.');
			//数据验证
        	$validate = new Validate([
        		'typeid|分类' => 'require',
            	'name|名称' => 'require',
            	'url|url' => 'require',
            	'logo|LOGO' => 'require',
        	]);
        	if(!$validate->check($data)){
            	$msg=$validate->getError();
            	return ['status'=>0,'msg'=>$msg];
        	}
			unset($data['linkid']);
			return model('FriendLink')->addData($data,'edit');
			return false;
		}else{
			$linkid=input('linkid');
			$info=Db::name('friend_link')->where('linkid',$linkid)->find();
			$this->assign('info',$info);
			
			$typelist=Db::name('friend_link_type')->order('sort')->select();
			$this->assign('typelist',$typelist);
    		return $this->show();
		}
	}
	//删除链接
	public function del(){
		$ids=input('ids/a');
		$rd=['status'=>0,'msg'=>'删除失败!'];
		$linkname='';
    	if(is_array($ids)){
			foreach($ids as $id){
				$linkname.=Db::name('friend_link')->where('linkid',$id)->value('name').'，';
				$rs=Db::name('friend_link')->where('linkid',$id)->delete();
				if($rs==0){
					return $rd;
				}else{
					$rd=['status'=>1,'msg'=>'删除成功!'];
				}
	        }
		}
		addAdminLog('成功删除友情链接:'.$linkname);
		return $rd;
	}
	//设置状态
	public function setStatus(){
		$status=input('status');
		$ids=$_POST['ids'];
		foreach($ids as $id){
			Db::name('friend_link')->where('linkid',$id)->setField('status',$status);
		}
		return ['status'=>1,'msg'=>'设置成功！'];
	}
	//链接排序
	public function setSort(){
		$sort=$_POST['sort'];
		foreach($sort as $k=>$v){
			Db::name('friend_link')->where('linkid',$k)->setField('sort',$v);
		}
		return ['status'=>1,'msg'=>'排序成功！'];
	}
	
	
	
	
	//设置分类
	public function typelist(){
		$lists=Db::name('friend_link_type')->order('sort')->select();
		$this->assign('lists',$lists);
		
		return $this->fetch();
	}
	//添加分类
    public function addtype(){
		if(request()->isPost() || request()->isAjax()){
			$data = input('post.');
			//数据验证
        	$validate = new Validate([
        		'name|分类名称' => 'require',
        	]);
        	if(!$validate->check($data)){
            	$msg=$validate->getError();
            	return ['status'=>0,'msg'=>$msg];
        	}
	
			$rs=Db::name('friend_link_type')->insert($data);
			if($rs!==false){
				addAdminLog('成功添加友情链接分类:'.$data['name']);
				return ['status'=>1,'msg'=>'添加成功！'];
			}else{
				$this->error('添加失败');
			}
		}else{
    		return $this->fetch();
		}
    }
	
	//编辑分类
    public function edittype(){
        $id=!empty(input('id'))?input('id'):0;
		if(request()->isPost() || request()->isAjax()){
			$data = input('post.');
			//数据验证
        	$validate = new Validate([
        		'name|分类名称' => 'require',
        	]);
        	if(!$validate->check($data)){
            	$msg=$validate->getError();
            	return ['status'=>0,'msg'=>$msg];
        	}
			$rs=Db::name('friend_link_type')->where('id',$id)->update($data);
			if($rs!==false){
				addAdminLog('成功编辑友情链接分类:'.$data['name']);
				return ['status'=>1,'msg'=>'编辑成功！'];
			}else{
				$this->error('编辑失败');
			}
		}else{
			$info=Db::name('friend_link_type')->where('id',$id)->find();
			$this->assign('info',$info);
    		return $this->fetch();
		}
    }

	//删除分类
    public function delTypetype(){
		$ids=input('ids/a');
		$rd=['status'=>0,'msg'=>'删除分类失败!'];
		$linkname='';
    	if(is_array($ids)){
			foreach($ids as $id){
				$linkname.=Db::name('friend_link_type')->where('id',$id)->value('name').'，';
				$rs=Db::name('friend_link_type')->where('id',$id)->delete();
				if($rs==0){
					return $rd;
				}else{
					$rd=['status'=>1,'msg'=>'删除分类成功!'];
				}
	        }
		}
		addAdminLog('成功删除友情链接分类:'.$linkname);
		return $rd;
    }
	
	//设置状态
	public function setTypeStatus(){
		$status=input('status');
		$ids=$_POST['ids'];
		foreach($ids as $id){
			Db::name('friend_link_type')->where('id',$id)->setField('status',$status);
		}
		return ['status'=>1,'msg'=>'设置成功！'];
	}
	
	//链接排序
	public function setTypeSort(){
		$sort=$_POST['sort'];
		foreach($sort as $k=>$v){
			Db::name('friend_link_type')->where('id',$k)->setField('sort',$v);
		}
		return ['status'=>1,'msg'=>'排序成功！'];
	}
}
?>