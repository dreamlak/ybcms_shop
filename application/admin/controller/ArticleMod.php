<?php
/**
 * 文章模型
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
class ArticleMod extends AdminBase{
	//模型管理
	public function index(){
		$totalCount=Db::name('article_mod')->count();
		$pagecount=config('paginate.list_admin');
		$lists=Db::name('article_mod')->order('sort')->paginate($pagecount,$totalCount,['query'=>request()->param()]);
		$this->assign('lists',$lists);
		$this->assign('totalCount',$totalCount);
		return $this->show();
	}
	//添加模型
	public function add(){
		if(request()->isPost() || request()->isAjax()){
			$data = input('post.');
			//数据验证
        	$validate = new Validate([
            	'title|名称' => 'require',
            	'name|标识' => 'require',
            	'icon|图标' => 'require',
        	]);
        	if(!$validate->check($data)){
            	$msg=$validate->getError();
            	return ['status'=>0,'msg'=>$msg];
        	}

			return model('ArticleMod')->addData($data);
			return false;
		}else{
    		return $this->show();
		}
	}
	//编辑模型
	public function edit(){
		if(request()->isPost() || request()->isAjax()){
			$data = input('post.');
			//数据验证
        	$validate = new Validate([
            	'title|名称' => 'require',
            	'name|标识' => 'require',
            	'icon|图标' => 'require',
        	]);
        	if(!$validate->check($data)){
            	$msg=$validate->getError();
            	return ['status'=>0,'msg'=>$msg];
        	}
			unset($data['modelid']);
			return model('ArticleMod')->addData($data,'edit');
			return false;
		}else{
			$modelid=input('modelid');
			$info=Db::name('article_mod')->where('modelid',$modelid)->find();
			$this->assign('info',$info);
    		return $this->show();
		}
	}
	//删除模型
	public function del(){
		$ids=input('ids/a');
		$rd=['status'=>0,'msg'=>'删除失败!'];
		$modname='';
    	if(is_array($ids)){
			foreach($ids as $id){
				$modname.=Db::name('article_mod')->where('modelid',$id)->value('name').'，';
				$rs=Db::name('article_mod')->where('modelid',$id)->delete();
				if($rs==0){
					return $rd;
				}else{
					$rd=['status'=>1,'msg'=>'删除成功!'];
				}
	        }
		}
		addAdminLog('成功删除文章模型:'.$modname);
		return $rd;
	}
	//设置模型状态
	public function setStatus(){
		$status=input('status');
		$ids=$_POST['ids'];
		foreach($ids as $id){
			Db::name('article_mod')->where('modelid',$id)->setField('status',$status);
		}
		return ['status'=>1,'msg'=>'设置成功！'];
	}
	//模型排序
	public function setSort(){
		$sort=$_POST['sort'];
		foreach($sort as $k=>$v){
			Db::name('article_mod')->where('modelid',$k)->setField('sort',$v);
		}
		return ['status'=>1,'msg'=>'排序成功！'];
	}
	
	//字段管理
	public function field(){
		if(request()->isPost() || request()->isAjax()){
			$data = input('post.field/a');
			if($data){
				$data = model('ArticleMod')->array_sort($data,'sort');
			}
			$data=array2string($data);
			$rs=Db::name('article_mod')->where('modelid',input('modelid'))->update(['fields'=>$data]);
			if($rs){
				return ['status'=>1,'msg'=>'字段保存成功！'];
			}else{
				return ['status'=>0,'msg'=>'字段保存失败！'];
			}
		}else{
			$modelid=input('modelid');
			$fields=Db::name('article_mod')->where('modelid',$modelid)->value('fields');
			$field_arr=string2array($fields);
			$this->assign('field_arr',$field_arr);
			$this->assign('modelid',$modelid);
			
			$this->assign('modelname',Db::name('article_mod')->where('modelid',$modelid)->value('title'));
    		return $this->show();
		}
	}
}
?>