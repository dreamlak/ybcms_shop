<?php
/**
 * 供应商
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
class Suppliers extends AdminBase{
	public function index(){
		$map=[];
		if(input('status')!='')$map['status']=input('status');
		if(input('keys')!='')$map['suppliers_name|suppliers_contacts|suppliers_phone|suppliers_address']=['like','%'.input('keys').'%'];
		$totalCount=Db::name('suppliers')->where($map)->count();
		$pagecount=config('paginate.list_admin');
		$data=Db::name('suppliers')->where($map)->order('sort')->paginate($pagecount,$totalCount,['query'=>request()->param()]);
		$lists = $data->all();
		$this->assign('lists',$lists);
		$this->assign('total',$data->total());
		$this->assign('listRows',$data->listRows());
		$this->assign('currentPage',$data->currentPage());
		$this->assign('lastPage',$data->lastPage());
		$this->assign('pages',$data->render());
		return $this->show();
	}
	//管理供应商
	public function addEditmanage(){
		$suppliers_id=input('suppliers_id');
		if(request()->isPost() || request()->isAjax()){
			$data=input('post.');
			//数据验证
        	$validate = new Validate([
            	'suppliers_name|供应商名称' => 'require',
            	'suppliers_contacts|供应商联系人' => 'require',
            	'suppliers_phone|供应商电话' => 'require',
            	'suppliers_address|供应商在址' => 'require',
        	]);
        	if(!$validate->check($data)){
            	$msg=$validate->getError();
            	return ['status'=>0,'msg'=>$msg];
        	}
			if($suppliers_id){//编辑
				$rs=Db::name('suppliers')->where('suppliers_id',$suppliers_id)->update($data);
				if($rs!==false){
					addAdminLog('成功编辑供应商:'.$data['suppliers_name']);
					$rd= ['status'=>1,'msg'=>'供应商编辑成功'];
				}else{
					$rd= ['status'=>0,'msg'=>'供应商编辑失败'];
				}
			}else{//添加
				$myid=Db::name('suppliers')->insertGetId($data);
				if($myid!==false){
					addAdminLog('成功添加供应商:'.$data['suppliers_name']);
					$rd= ['status'=>1,'msg'=>'供应商添加成功'];
				}else{
					$rd= ['status'=>0,'msg'=>'供应商添加失败'];
				}
			}
			return $rd;
		}
		$info=Db::name('suppliers')->where('suppliers_id',$suppliers_id)->find();
		$this->assign('info',$info);
		return $this->fetch();
	}

	//删除供应商
    public function delsuppliers(){
		$ids=input('ids/a');
		$rd=['status'=>0,'msg'=>'删除失败!'];
    	if(is_array($ids)){
    		$id = implode(',', $ids);
			$row = Db::name('suppliers')->where('suppliers_id','in', $id)->delete();
	        if($row!==false){
				addAdminLog('成功批量删除供应商');
				$rd= ['status'=>1,'msg'=>'供应商删除成功'];
			}else{
				$rd= ['status'=>0,'msg'=>'供应商删除失败'];
			}
		}
		return $rd;
    }
	
	//设置广告位状态
    public function setStatus(){
        $status=input('status');
		$ids=$_POST['ids'];
		foreach($ids as $id){
			Db::name('suppliers')->where('suppliers_id',$id)->setField('status',$status);
		}
		return ['status'=>1,'msg'=>'设置成功！'];
    }
}
	
?>