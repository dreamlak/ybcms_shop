<?php
/**
 * 消息管理
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
class Message extends AdminBase{
	//消息列表
	public function index(){
		$map=[];
		if(input('keys')!='') $map['title|message']=['like','%'.input('keys').'%'];
		if(input('type')!='') $map['type']=input('type');
		if(input('class')!='') $map['class']=input('class');
    	$totalCount=Db::name('message')->where($map)->count();
		$pagecount=config('paginate.list_admin');
		$data=Db::name('message')->where($map)->order('addtime DESC')->paginate($pagecount,$totalCount,['query'=>request()->param()]);
		
		$lists = $data->all();
	
		$this->assign('lists',$lists);
		$this->assign('total',$data->total());
		$this->assign('listRows',$data->listRows());
		$this->assign('currentPage',$data->currentPage());
		$this->assign('lastPage',$data->lastPage());
		$this->assign('pages',$data->render());
		
		return $this->show();
	}
	
	//消息编辑
	public function edit(){
		if(request()->isPost() || request()->isAjax()){
			$data = input('post.');
			//数据验证
        	$validate = new Validate([
            	'title|标题' => 'require',
            	'message|消息内容' => 'require',
        	]);
        	if(!$validate->check($data)){
            	$msg=$validate->getError();
            	return ['status'=>0,'msg'=>$msg];
        	}
			$rs=Db::name('message')->where('id',input('id'))->update($data);
			if($rs!==false){
				addAdminLog('成功编辑消息:'.$data['title']);
				return ['status'=>1,'msg'=>'操作成功'];
			}else{
				return ['status'=>0,'msg'=>'操作失败'];
			}
		}else{
			$info=Db::name('message')->where('id',input('id'))->find();
			$html='';
			if($info['tousers']!=''){
				$tousers=explode(',', $info['tousers']);
				foreach($tousers as $u){
					$html.='<em>'.getUserName($u).'</em>';
				}
			}else{
				$html='<em>会体会员</em>';
			}
			$info['touser']=$html;
			$this->assign('info',$info);
    		return $this->show();
		}
	}
	//删除消息
	public function del(){
		if(empty(input('id'))){//批量删除
			$ids=input('ids/a');
			$rd=['status'=>0,'msg'=>'删除失败!'];
			$names='';
	    	if(is_array($ids)){
				foreach($ids as $id){
					//删除前获取该删除的名
					$names.=Db::name('message')->where('id',$id)->value('title').'，';
					//删除
					$rs=Db::name('message')->where('id',$id)->delete();
					if($rs==0){
						return $rd;
					}else{
						Db::name('member_message')->where('msgid',$id)->delete();
						$rd=['status'=>1,'msg'=>'删除成功!'];
					}
		        }
				if($rd['status']==1){
					addAdminLog('成功删除消息:'.$names);
				}
			}
			return $rd;
		}else{//单条删除
			//删除前获取该删除的名
			$names=Db::name('message')->where('id',input('id'))->value('title');
			//删除
			$rs=Db::name('message')->where('id',input('id'))->delete();
			if($rs==0){
				$this->error('删除失败');
			}else{
				Db::name('member_message')->where('msgid',input('id'))->delete();
				addAdminLog('成功删除消息:'.$names);
				$this->success('删除成功');
			}
		}
	}
	//发送消息
	public function sendmessage(){
		if(request()->isAjax()){
			$data = input('post.');
			//数据验证
        	$validate = new Validate([
            	'title|标题' => 'require',
            	'message|消息内容' => 'require',
        	]);
        	if(!$validate->check($data)){
            	$msg=$validate->getError();
            	return ['status'=>0,'msg'=>$msg];
        	}
			//添加消息
			$inData=[];
			$inData['adminid']=is_login();
			$inData['type']=$data['type'];
			if($data['type']==0){
				$inData['tousers']=$data['ids'];
			}else{
				$inData['tousers']='';
			}
			$inData['class']=$data['class'];
			$inData['title']=$data['title'];
			$inData['message']=$data['message'];
			$inData['ismail']=$data['ismail'];
			$inData['addtime']=time();
			$msgid=Db::name('message')->insertGetId($inData);
			if($msgid>0){
				if($data['type']==0){
					//发送每个会员
					$ids=explode(',',$data['ids']);
					$umdata=[];
					foreach($ids as $userid){
						$umdata['userid']=$userid;
						$umdata['msgid']=$msgid;
						$umdata['class']=$data['class'];
						$rs=Db::name('member_message')->insert($umdata);
					}
				}
				if($data['ismail']==1){
					//发送邮件
					$umail=Db::name('member')->where('userid','in',$data['ids'])->field('email')->select();
					$mailArr=[];
					foreach($umail as $e) {
		                if(check_email($e['email'])){
		                    $mailArr[] = $e['email'];
		                }
		            }
					$res = send_email($mailArr,$data['title'],$data['message']);
				}
				addAdminLog('成功编辑消息:'.$data['title']);
				return ['status'=>1,'msg'=>'操作成功'];
			}else{
				return ['status'=>0,'msg'=>'操作失败'];
			}
		}else{
			$ids=implode(',',input('ids/a'));
			$users=Db::name('member')->where('userid','in',$ids)->field('userid,username,email')->select();
			$this->assign('users',$users);
			$this->assign('ids',$ids);
    		return $this->show();
		}
	}
}
?>