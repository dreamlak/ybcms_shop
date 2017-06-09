<?php
/**
 * 节点控制器
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
class Node extends AdminBase{
	//列表
    public function lists(){
    	$nodeid=!empty(input('nodeid'))?input('nodeid'):0;
		
		$map=[];
		if($nodeid==''){
			$map=['pid'=>0];
		}else{
			$map=['pid'=>$nodeid];
		}
		if(input('status')!='')$map=['status'=>input('status')];
		if(input('display')!='')$map=['display'=>input('display')];
		if(input('title')!='')$map=['title'=>['like','%'.input('title').'%']];
		$totalCount=Db::name('node')->where($map)->count();
		$pagecount=config('paginate.list_admin');
		$data = Db::name('node')->where($map)->order('sort asc')->paginate($pagecount,$totalCount,['query'=>request()->param()]);
		$lists = $data->all();
		
		$this->assign('lists',$lists);
		$this->assign('total',$data->total());
		$this->assign('listRows',$data->listRows());
		$this->assign('currentPage',$data->currentPage());
		$this->assign('lastPage',$data->lastPage());
		$this->assign('pages',$data->render());
		
		$nodeName='顶级分类';
		if($nodeid!='' && $nodeid>0){
			$nodeName=model('Node')->getToIDNodeCrumbs($nodeid);
		}
		$levels=!empty(input('levels'))?input('levels'):0;
		$this->assign('nodeid',$nodeid);
		$this->assign('levels',$levels);
		$this->assign('nodeName',$nodeName);
    	return $this->show();
	}
	
	//添加
	public function nodeadd(){
		if(request()->isPost() || request()->isAjax()){
			$data = input('post.');
			//数据验证
        	$validate = new Validate([
            	'title|节点名称' => 'require',
            	'name|节点标识' => 'require|alphaDash',
            	'm|所属模块' => 'require',
            	'c|所属控制器' => 'require',
        	]);
        	if(!$validate->check($data)){
            	$msg=$validate->getError();
            	return ['status'=>0,'msg'=>$msg];
        	}
			
			$Node=model('Node');
			return $Node->addNode($data);
			return false;
		}else{
			//所有模块
			$this->assign('getModelList',getModelList());
    		return $this->show();
		}
	}
	
	//编辑
	public function nodeedit(){
    	if(request()->isPost() || request()->isAjax()){
			$data = input('post.');
			//数据验证
        	$validate = new Validate([
            	'title|节点名称' => 'require',
            	'name|节点标识' => 'require|alphaDash',
            	'm|所属模块' => 'require',
            	'c|所属模块' => 'require',
        	]);
        	if(!$validate->check($data)){
            	$msg=$validate->getError();
            	return ['status'=>0,'msg'=>$msg];
        	}
			unset($data['nodeid']);
			$Node=model('Node');
			return $Node->addNode($data,'edit');
			return false;
		}else{
			$nodeid=input('nodeid');
			$info=Db::name('node')->where('nodeid',$nodeid)->find();
			//所有模块
			$this->assign('getModelList',getModelList());
			$this->assign('info',$info);
    		return $this->show();
		}
	}
	
	//删除
	public function nodedel(){
		$ids=input('ids/a');
		$Node=model('Node');
		$rd=[];
		$nodename='';
    	if(is_array($ids)){
			foreach($ids as $id){
				$nodename.=Db::name('node')->where('nodeid',$id)->value('title').'，';
				$rd=$Node->delNode($id);
				if($rd['status']==0){
					return $rd;
				}
	        }
		}
		addAdminLog('成功删除节点:'.$nodename);
		model('Node')->updateNodeCache();
		return $rd;
 	}
	//启用禁用节点
	public function setStatus(){
		$status=input('status');
		$ids=$_POST['ids'];
		foreach($ids as $id){
			Db::name('node')->where('nodeid',$id)->setField('status',$status);
		}
		model('Node')->updateNodeCache();
		return ['status'=>1,'msg'=>'设置成功！'];
	}
	//隐藏显示节点
	public function setView(){
		$display=input('display');
		$ids=$_POST['ids'];
		foreach($ids as $id){
			Db::name('node')->where('nodeid',$id)->setField('display',$display);
		}
		model('Node')->updateNodeCache();
		return ['status'=>1,'msg'=>'设置成功！'];
	}
	//节点排序
	public function setSort(){
		$sort=$_POST['sort'];
		foreach($sort as $k=>$v){
			Db::name('node')->where('nodeid',$k)->setField('sort',$v);
		}
		model('Node')->updateNodeCache();
		return ['status'=>1,'msg'=>'排序成功！'];
	}
	//ajax查找控制器
	public function ajaxController(){
		$model=input('model');
		$data=[];
		$clist=getControllerList($model);
		if(count($clist)>0){
			$data['list']=$clist;
			$data['status']=1;
		}else{
			$data['list']='';
			$data['status']=0;
		}
		return $data;
	}
	//ajax查找方法
	public function ajaxAction(){
		$model=input('model');
		$cname=input('cname');
		$data=[];
		$alist=getActionList($model,$cname);
		if(count($alist)>0){
			$data['list']=$alist;
			$data['status']=1;
		}else{
			$data['list']='';
			$data['status']=0;
		}
		return $data;
	}
	
	//ajax获取子菜单
	public function ajaxGetSubmenu(){
		$mainNodeId=input('main_nodeid/d');
		$thisNodeId=model('Node')->getUrlLastNodeID2(input('url'));
		
		$tree=model('Node')->getIndexNode(model('Node')->roleNodeList($mainNodeId,$thisNodeId));
		
		return $tree;
	}
}
