<?php
/**
 * 短信管理
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
class Sms extends AdminBase{
	public function index(){
		$map=[];
    	$totalCount=Db::name('sms_tpl')->where($map)->count();
		$pagecount=config('paginate.list_admin');
		$data=Db::name('sms_tpl')->where($map)->order('sort')->paginate($pagecount,$totalCount,['query'=>request()->param()]);
		$lists = $data->all();
		$this->assign('lists',$lists);
		$this->assign('total',$data->total());
		$this->assign('listRows',$data->listRows());
		$this->assign('currentPage',$data->currentPage());
		$this->assign('lastPage',$data->lastPage());
		$this->assign('pages',$data->render());
		
		
		return $this->show();
	}
	//短信模板添加
	public function add(){
		if(request()->isPost() || request()->isAjax()){
			$data = input('post.');
			//数据验证
        	$validate = new Validate([
            	'smstitle|短信场景名' => 'require',
            	'smssign|短信签名' => 'require',
            	'smscode|短信模板ID码' => 'require',
            	'smstpl|发送短信内容' => 'require',
        	]);
        	if(!$validate->check($data)){
            	$msg=$validate->getError();
            	return ['status'=>0,'msg'=>$msg];
        	}
			$data['addtime']=time();
			$rs=Db::name('sms_tpl')->insert($data);
			if($rs!==false){
				addAdminLog('成功添加短信模板:'.$data['smstitle']);
				return ['status'=>1,'msg'=>'操作成功'];
			}else{
				return ['status'=>0,'msg'=>'操作失败'];
			}
		}else{
			$smssign=config('config.sms_product');
			$this->assign('smssign',$smssign);
    		return $this->show();
		}
	}
	//短信模板编辑
	public function edit(){
		if(request()->isPost() || request()->isAjax()){
			$data = input('post.');
			//数据验证
        	$validate = new Validate([
            	'smstitle|短信场景名' => 'require',
            	'smssign|短信签名' => 'require',
            	'smscode|短信模板ID码' => 'require',
            	'smstpl|发送短信内容' => 'require',
        	]);
        	if(!$validate->check($data)){
            	$msg=$validate->getError();
            	return ['status'=>0,'msg'=>$msg];
        	}
			$data['addtime']=time();
			$rs=Db::name('sms_tpl')->where('id',input('id'))->update($data);
			if($rs!==false){
				addAdminLog('成功编辑短信模板:'.$data['smstitle']);
				return ['status'=>1,'msg'=>'操作成功'];
			}else{
				return ['status'=>0,'msg'=>'操作失败'];
			}
		}else{
			$info=Db::name('sms_tpl')->where('id',input('id'))->find();
			$this->assign('info',$info);
    		return $this->show();
		}
	}
	//删除短信模板
	public function del(){
		if(empty(input('id'))){//批量删除
			$ids=input('ids/a');
			$rd=['status'=>0,'msg'=>'删除失败!'];
			$names='';
	    	if(is_array($ids)){
				foreach($ids as $id){
					//删除前获取该删除的名
					$names.=Db::name('sms_tpl')->where('id',$id)->value('smstitle').'，';
					//删除
					$rs=Db::name('sms_tpl')->where('id',$id)->delete();
					if($rs==0){
						return $rd;
					}else{
						$rd=['status'=>1,'msg'=>'删除成功!'];
					}
		        }
				if($rd['status']==1){
					addAdminLog('成功删除短信模板:'.$names);
				}
			}
			return $rd;
		}else{//单条删除
			//删除前获取该删除的名
			$names=Db::name('sms_tpl')->where('id',input('id'))->value('smstitle');
			//删除
			$rs=Db::name('sms_tpl')->where('id',input('id'))->delete();
			if($rs==0){
				$this->error('删除失败');
			}else{
				addAdminLog('成功删除短信模板:'.$names);
				$this->success('删除成功');
			}
		}
	}
	//设置短信模板状态
	public function setStatus(){
		$status=input('status');
		$ids=$_POST['ids'];
		foreach($ids as $id){
			Db::name('sms_tpl')->where('id',$id)->setField('status',$status);
		}
		return ['status'=>1,'msg'=>'设置成功！'];
	}
	//短信模板排序
	public function setSort(){
		$sort=$_POST['sort'];
		foreach($sort as $k=>$v){
			Db::name('sms_tpl')->where('id',$k)->setField('sort',$v);
		}
		return ['status'=>1,'msg'=>'排序成功！'];
	}
	
	//---------------------------------------------
	
	//短信日志
	public function sms_log(){
		$map=[];
		if(input('mobile')!='')$map['mobile']=input('mobile');
    	$totalCount=Db::name('sms_log')->where($map)->count();
		$pagecount=config('paginate.list_admin');
		$data=Db::name('sms_log')->where($map)->order('status ASC,id DESC')->paginate($pagecount,$totalCount,['query'=>request()->param()]);
		$lists = $data->all();
		$this->assign('lists',$lists);
		$this->assign('total',$data->total());
		$this->assign('listRows',$data->listRows());
		$this->assign('currentPage',$data->currentPage());
		$this->assign('lastPage',$data->lastPage());
		$this->assign('pages',$data->render());
		return $this->show();
	}
	//删除短信日志
	public function sms_log_del(){
		$ids=input('ids/a');
		$rd=['status'=>0,'msg'=>'删除失败!'];
		$names='';
    	if(is_array($ids)){
			foreach($ids as $id){
				//删除前获取该删除的名
				$names.=Db::name('sms_log')->where('id',$id)->value('mobile').'，';
				//删除
				$rs=Db::name('sms_log')->where('id',$id)->delete();
				if($rs==0){
					return $rd;
				}else{
					$rd=['status'=>1,'msg'=>'删除成功!'];
				}
	        }
			if($rd['status']==1){
				addAdminLog('成功删除短信模板:'.$names);
			}
		}
		return $rd;
	}
	//短信重发
	public function resendsms(){
		$id=input('id');
		$info=Db::name('sms_log')->where('id',$id)->find();
		$params=[
			'code'=>$info['code'],
			'username'=>'abcd',
			'product'=>'ybcms',
			'consignee'=>'测试',
			'ordersn'=>'A00001234564',
			'phone'=>$info['mobile']
		];
		//$rs=sendSms($data['tplid'],$info['mobile'],$params);
		//return $rs;
	}
	
	//测试短信
	public function testsms(){
		$data=input('post.');
		if($data['tplid']=='') return ['status'=>0,'msg'=>'请选择模板'];
		if($data['mobile']=='') return ['status'=>0,'msg'=>'手机号码不能为空'];
		$params=['code'=>'12345','username'=>'abcd','product'=>'ybcms','consignee'=>'测试','ordersn'=>'A00001234564','phone'=>'15200000000'];
		$rs=sendSms($data['tplid'],$data['mobile'],$params);
		return $rs;
	}
}
?>