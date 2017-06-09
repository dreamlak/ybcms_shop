<?php
/**
 * 系统设置
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
class Area extends AdminBase{
	//地区列表
	public function lists(){
		$level=!empty(input('level'))?input('level'):1;
		$id=!empty(input('id'))?input('id'):0;
		$keys=!empty(input('key'))?input('key'):'';
		if($keys!=''){
			$arealist=Db::name('areas')->where('code|name|initial','like','%'.$keys.'%')->order('sort,code')->select();
		}else{
			$map=[];
			$map['pid']=$id;
			$arealist=Db::name('areas')->where($map)->order('sort,code')->select();
		}
		$this->assign('arealist',$arealist);
		$this->assign('level',$level);
		$this->assign('id',$id);
		$this->assign('keys',$keys);
		
		$this->assign('CrumbName',model('Areas')->getCrumbs($id));
		return $this->fetch();
	}
	//添加地区
	public function add(){
		if(request()->isPost() || request()->isAjax()){
			$data = input('post.');
			//数据验证
        	$validate = new Validate([
            	'name|地区名称' => 'require',
            	'code|区位码' => 'require',
        	]);
        	if(!$validate->check($data)){
            	$msg=$validate->getError();
            	return ['status'=>0,'msg'=>$msg];
        	}

			return model('Areas')->addAreas($data);
		}else{
			$level=!empty(input('level'))?input('level'):1;
			$id=!empty(input('id'))?input('id'):0;
			if($id>0){
				$pidname=Db::name('areas')->where('id',$id)->value('name');
			}else{
				$pidname='无';
			}
			$this->assign('pidname',$pidname);
			$this->assign('level',$level);
			$this->assign('id',$id);
			return $this->fetch();
		}
	}
	//添加地区
	public function edit(){
		if(request()->isPost() || request()->isAjax()){
			$data = input('post.');
			//数据验证
        	$validate = new Validate([
            	'name|地区名称' => 'require',
            	'code|区位码' => 'require',
        	]);
        	if(!$validate->check($data)){
            	$msg=$validate->getError();
            	return ['status'=>0,'msg'=>$msg];
        	}
			unset($data['id']);
			return model('Areas')->addAreas($data,'edit');
		}else{
			$id=!empty(input('id'))?input('id'):0;
			if($id==0) return $this->error('参数错误');
			$info=Db::name('areas')->where('id',$id)->find();
			$this->assign('info',$info);
			
			$pidname=Db::name('areas')->where('id',$info['pid'])->value('name');
			if($pidname=='')$pidname='无';
			$this->assign('pidname',$pidname);
			return $this->fetch();
		}
	}
	//删除地区
	public function del(){
		$id=input('id');
		$rd=model('Areas')->delAreas($id);
		if($rd['status']==1){
			$aname=Db::name('areas')->where('id',$id)->value('name');
			addAdminLog('成功添加地区:'.$aname);
			$this->success('删除成功！');
		}else{
			return $this->error($rd['msg']);
		}
	}
	//设置状态
	public function setStatus(){
		$status=input('status');
		$ids=$_POST['ids'];
		foreach($ids as $id){
			Db::name('areas')->where('id',$id)->setField('status',$status);
		}
		return ['status'=>1,'msg'=>'设置成功！'];
	}
	//排序
	public function setSort(){
		$sort=$_POST['sort'];
		foreach($sort as $k=>$v){
			Db::name('areas')->where('id',$k)->setField('sort',$v);
		}
		return ['status'=>1,'msg'=>'排序成功！'];
	}
	
	//生成
	public function ajaxJson(){
		$map=['status'=>1];
		$data=Db::name('areas')->where($map)->field('id,code,pcode,name,pid,initial,level')->order('sort asc')->select();
		//数据集转JSON
		$areaJson=json_encode($data);
		//生成JSON文件
		file_put_contents(ROOT_PATH.'/data/areaCache.json', $areaJson);
		return ['status'=>1,'msg'=>'生成成功！'];
	}
}
?>