<?php
/**
 * 自提点管理
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
class Pickup extends AdminBase{
	//自提点列表
	public function index(){
		$map=[];
		if(input('keys')!=''){
			$map['pickup_name|pickup_address|pickup_phone|pickup_contact']=['like','%'.input('keys').'%'];
		}
		if(input('province_id')!=''){
			$map['province_id']=input('province_id');
		}
		if(input('city_id')!=''){
			$map['city_id']=input('city_id');
		}
		if(input('district_id')!=''){
			$map['district_id']=input('district_id');
		}
		if(input('suppliersid')!=''){
			$map['suppliersid']=input('suppliersid');
		}
		$totalCount=Db::name('pick_up')->where($map)->count();
		$pagecount=config('paginate.list_admin');
		$data=Db::name('pick_up')->where($map)->order('pickup_id DESC')->paginate($pagecount,$totalCount,['query'=>request()->param()]);
		$lists = $data->all();
		$this->assign('lists',$lists);
		$this->assign('total',$data->total());
		$this->assign('listRows',$data->listRows());
		$this->assign('currentPage',$data->currentPage());
		$this->assign('lastPage',$data->lastPage());
		$this->assign('pages',$data->render());
		
		return $this->fetch();
	}
	//添加编辑自提点
	public function addeditPickup(){
		$id=input('pickup_id',0);
    	if(request()->isPost() || request()->isAjax()){
    		$data=input('post.');
    		if($id>0){
    			//编辑
				$rs=Db::name('pick_up')->where('pickup_id',$id)->update($data);
				if($rs!==false){
					addAdminLog('成功编辑自提点:'.$data['pickup_name']);
					$rd= ['status'=>1,'msg'=>'自提点编辑成功'];
				}else{
					$rd= ['status'=>0,'msg'=>'自提点编辑失败'];
				}
    		}else{
    			//添加
    			$myid=Db::name('pick_up')->insertGetId($data);
				if($myid>0){
					addAdminLog('成功添加自提点:'.$data['name']);
					$rd= ['status'=>1,'msg'=>'自提点添加成功'];
				}else{
					$rd= ['status'=>0,'msg'=>'自提点添加失败'];
				}
    		}
			return $rd;
    	}else{
			$info = Db::name('pick_up')->where('pickup_id',$id)->find();
			$this->assign('info',$info);
			
			//供应商
        	$suppliersList = Db::name("suppliers")->where('status',1)->select();
			$this->assign('suppliersList',$suppliersList);
			
			//获取省份
	        $province = Db::name('areas')->where(array('pid'=>0))->field('id,name')->select();
			$this->assign('province',$province);
			//获取市
			if($info['province_id']){
				$city = Db::name('areas')->where(array('pid'=>$info['province_id']))->field('id,name')->select();
				$this->assign('city',$city);
			}
			//获取县
			if($info['city_id']){
	        	$district = Db::name('areas')->where(array('pid'=>$info['city_id']))->field('id,name')->select();
				$this->assign('district',$district);
			}
			return $this->fetch();
    	}
	}
	//删除自提点
	public function del(){
		if(empty(input('id'))){//批量删除
			$ids=input('ids/a');
			$rd=['status'=>0,'msg'=>'删除失败!'];
			$names='';
	    	if(is_array($ids)){
				foreach($ids as $id){
					//删除前获取该删除的领导信箱名
					$names.=Db::name('pick_up')->where('pickup_id',$id)->value('pickup_name').'，';
					//删除
					$rs=Db::name('pick_up')->where('pickup_id',$id)->delete();
					if($rs==0){
						return $rd;
					}else{
						$rd=['status'=>1,'msg'=>'删除成功!'];
					}
		        }
				if($rd['status']==1){
					addAdminLog('成功删除自提点:'.$names);
				}
			}
			return $rd;
		}else{//单条删除
			//删除前获取该删除的领导信箱名
			$names=Db::name('pick_up')->where('pickup_id',input('id'))->value('name');
			//删除
			$rs=Db::name('pick_up')->where('pickup_id',input('id'))->delete();
			if($rs==0){
				$this->error('删除失败');
			}else{
				addAdminLog('成功删除自提点:'.$names);
				$this->success('删除成功');
			}
		}
	}
}
?>