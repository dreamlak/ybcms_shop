<?php
/**
 * 会员管理
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
class Member extends AdminBase{
	//会员列表
	public function index(){
		$map=[];
		if(input('status')!='') $map['status']=input('status');
		if(input('username')!='') $map['username']=input('username');
		if(input('email')!='') $map['email']=input('email');
		if(input('tel')!='') $map['tel']=input('tel');

    	$totalCount=Db::name('member')->where($map)->count();
		$pagecount=config('paginate.list_admin');
		$data=Db::name('member')->where($map)->order('userid DESC')->paginate($pagecount,$totalCount,['query'=>request()->param()]);
		
		$lists = $data->all();
	
		$this->assign('lists',$lists);
		$this->assign('total',$data->total());
		$this->assign('listRows',$data->listRows());
		$this->assign('currentPage',$data->currentPage());
		$this->assign('lastPage',$data->lastPage());
		$this->assign('pages',$data->render());
		
		$levelData=Db::name('member_level')->field('id,name')->select();
		foreach($levelData as $k=>$v){
			$level[$v['id']]=$v['name'];
		}
		$this->assign('level',$level);
		return $this->show();
	}
	//会员添加
	public function add(){
		if(request()->isPost() || request()->isAjax()){
			$data = input('post.');
			//数据验证
        	$validate = new Validate([
            	'username|用户名' => 'require',
            	'password|密码' => 'require',
            	'tel|手机' => 'require',
            	
        	]);
        	if(!$validate->check($data)){
            	$msg=$validate->getError();
            	return ['status'=>0,'msg'=>$msg];
        	}
			//查找是否已有会员账号
			$isus=Db::name('member')->where(['username'=>$data['username']])->count();
			if($isus>0){
				return ['status'=>0,'msg'=>'该账号已存在！'];
			}
			//手机检测
			$isus=Db::name('member')->where(['tel'=>$data['tel']])->count();
			if($isus>0){
				return ['status'=>0,'msg'=>'该手机已存在！'];
			}
			//重置密码
			$encrypt=GetRandStr(6);
			$password=md5($data['password'].$encrypt);//加密后的密码
    		$codes=base64_encode($data['username'].$password);
    		$token=hash('md5',$codes);//登录验证token
    		$data['encrypt']=$encrypt;//密码加密KEY
    		$data['password']=$password;
			$data['token']=$token;
			
			$data['regtime']=time();
			$data['regip']=request()->ip();
			$data['status']=1;
			return model('Member')->addUserData($data);
		}else{
    		return $this->show();
		}
	}
	//会员编辑
	public function edit(){
		if(request()->isPost() || request()->isAjax()){
			$data = input('post.');
			//数据验证
        	$validate = new Validate([
            	'username|用户名' => 'require',
            	'tel|手机' => 'require',
        	]);
        	if(!$validate->check($data)){
            	$msg=$validate->getError();
            	return ['status'=>0,'msg'=>$msg];
        	}

			return model('Member')->addUserData($data,'edit');
		}else{
			$info=Db::name('member')->where('userid',input('userid'))->find();
			$info['level']=Db::name('member_level')->where('id',$info['levelid'])->value('name');
			$this->assign('info',$info);

    		return $this->show();
		}
	}
	//会员删除
	public function del(){
		if(empty(input('id'))){//批量删除
			$ids=input('ids/a');
			$rd=['status'=>0,'msg'=>'删除失败!'];
			$names='';
	    	if(is_array($ids)){
				foreach($ids as $id){
					//删除前获取该删除的会员名
					$names.=Db::name('member')->where('userid',$id)->value('username').'，';
					//删除
					$rs=Db::name('member')->where('userid',$id)->delete();
					if($rs==0){
						return $rd;
					}else{
						$rd=['status'=>1,'msg'=>'删除成功!'];
					}
		        }
				if($rd['status']==1){
					addAdminLog('成功删除会员:'.$names);
				}
			}
			return $rd;
		}else{//单条删除
			//删除前获取该删除的会员名
			$names=Db::name('member')->where('userid',input('userid'))->value('name');
			//删除
			$rs=Db::name('member')->where('userid',input('userid'))->delete();
			if($rs==0){
				$this->error('删除失败');
			}else{
				addAdminLog('成功删除会员:'.$names);
				$this->success('删除成功');
			}
		}
	}
	//重置密码
	public function resetpwd(){
		$userid=input('userid');
		$rs=model('Member')->editPwd($userid,'888888');
		if($rs){
			return ['status'=>1,'msg'=>'密码重置成功！'];
		}else{
			return ['status'=>0,'msg'=>'密码重置失败！'];
		}
	}
	//状态设置
	public function setStatus(){
		$status=input('status');
		$ids=$_POST['ids'];
		foreach($ids as $id){
			Db::name('member')->where('userid',$id)->setField('status',$status);
		}
		return ['status'=>1,'msg'=>'设置成功！'];
	}
	
	
	/*************************************************************************
     * 用户收货地址查看
     */
    public function address(){
        $lists =Db::name('member_address')->where('userid',input('userid'))->select();
        $this->assign('lists',$lists);
		
		
        return $this->fetch();
    }
	//会员资金
    public function account(){
        $userid = input('userid');

		$map=[];
		$map['userid']=$userid;
		$totalCount=Db::name('member_account_log')->where($map)->count();
		$pagecount=config('paginate.list_admin');
		$data=Db::name('member_account_log')->where($map)->order('addtime DESC')->paginate($pagecount,$totalCount,['query'=>request()->param()]);
		
		$lists = $data->all();
		
		$this->assign('userid',$userid);
		$this->assign('lists',$lists);
		$this->assign('total',$data->total());
		$this->assign('listRows',$data->listRows());
		$this->assign('currentPage',$data->currentPage());
		$this->assign('lastPage',$data->lastPage());
		$this->assign('pages',$data->render());
        return $this->fetch();
    }
	//会员资金调节
    public function account_edit(){
        if(request()->isPost() || request()->isAjax()){
        	$data = input('post.');
            //金额
            $mymoney =   $data['moneytype']?$data['mymoney']:0-$data['mymoney'];
			//积分
            $mypoints =  $data['pointtype']?$data['mypoints']:0-$data['mypoints'];
			
			$content=$data['content'];
            if($content=='')$this->error("请填写操作说明");
            if(model('Member')->accountLog($data['userid'],$mymoney,$mypoints,$content)){
				return ['status'=>1,'msg'=>'操作成功！'];
            }else{
                $this->error("操作失败");
            }
            exit;
        }else{
        	$userid = input('userid');
        	if(!$userid > 0)$this->error("参数有误");
			$this->assign('userid',$userid);
			
        	return $this->fetch();
        }
    }
	
	
	//会员等级
	public function levelList(){
		$totalCount=Db::name('member_level')->count();
		$pagecount=config('paginate.list_admin');
		$data=Db::name('member_level')->paginate($pagecount,$totalCount,['query'=>request()->param()]);
		$lists = $data->all();
		$this->assign('lists',$lists);
		$this->assign('total',$data->total());
		$this->assign('listRows',$data->listRows());
		$this->assign('currentPage',$data->currentPage());
		$this->assign('lastPage',$data->lastPage());
		$this->assign('pages',$data->render());
		
        return $this->fetch();
    }
	//添加等级
	public function level_add(){
		if(request()->isPost() || request()->isAjax()){
			$data = input('post.');
			//数据验证
        	$validate = new Validate([
            	'name|等级名称' => 'require',
            	'amount|需消费额度' => 'require',
            	'discount|折扣率' => 'require',
        	]);
        	if(!$validate->check($data)){
            	$msg=$validate->getError();
            	return ['status'=>0,'msg'=>$msg];
        	}
			
			return model('Member')->addLevelData($data);
		}else{
			$info=Db::name('member_level')->where('id',input('id'))->find();
			$this->assign('info',$info);

    		return $this->show();
		}
	}
	//编辑等级
	public function level_edit(){
		if(request()->isPost() || request()->isAjax()){
			$data = input('post.');
			//数据验证
        	$validate = new Validate([
            	'name|等级名称' => 'require',
            	'amount|需消费额度' => 'require',
            	'discount|折扣率' => 'require',
        	]);
        	if(!$validate->check($data)){
            	$msg=$validate->getError();
            	return ['status'=>0,'msg'=>$msg];
        	}
			
			return model('Member')->addLevelData($data,'edit');
		}else{
			$info=Db::name('member_level')->where('id',input('id'))->find();
			$this->assign('info',$info);

    		return $this->show();
		}
	}
	//删除等级
	public function level_del(){
		if(empty(input('id'))){//批量删除
			$ids=input('ids/a');
			$rd=['status'=>0,'msg'=>'删除失败!'];
			$names='';
	    	if(is_array($ids)){
				foreach($ids as $id){
					//删除前获取该删除的会员名
					$names.=Db::name('member_level')->where('id',$id)->value('name').'，';
					//删除
					$rs=Db::name('member_level')->where('id',$id)->delete();
					if($rs==0){
						return $rd;
					}else{
						$rd=['status'=>1,'msg'=>'删除成功!'];
					}
		        }
				if($rd['status']==1){
					addAdminLog('成功删除会员等级:'.$names);
				}
			}
			return $rd;
		}else{//单条删除
			//删除前获取该删除的会员名
			$names=Db::name('member_level')->where('id',input('id'))->value('name');
			//删除
			$rs=Db::name('member_level')->where('id',input('id'))->delete();
			if($rs==0){
				$this->error('删除失败');
			}else{
				addAdminLog('成功删除会员等级:'.$names);
				$this->success('删除成功');
			}
		}
	}
	//等级状态
	public function level_status(){
		$status=input('status');
		$ids=$_POST['ids'];
		foreach($ids as $id){
			Db::name('member_level')->where('id',$id)->setField('status',$status);
		}
		return ['status'=>1,'msg'=>'设置成功！'];
	}
	
	
	//会员充值日志
	public function recharge(){
    	$nickname = input('nickname');
    	$map = [];
		if(input('status')!='')$map['status'] =input('status');
		if(input('starttime')!=''||input('endtime')!=''){
			$map['addtime'] = ['between',[strtotime(input('starttime')),strtotime(input('endtime'))]];
		}
    	if(input('nickname')){
    		$map['nickname'] = ['like',"%$nickname%"];
    	}  	
		
		$totalCount=Db::name('member_recharge')->where($map)->count();
		$pagecount=config('paginate.list_admin');
		$data=Db::name('member_recharge')->where($map)->order('addtime desc')->paginate($pagecount,$totalCount,['query'=>request()->param()]);
		$lists = $data->all();
		$this->assign('lists',$lists);
		$this->assign('total',$data->total());
		$this->assign('listRows',$data->listRows());
		$this->assign('currentPage',$data->currentPage());
		$this->assign('lastPage',$data->lastPage());
		$this->assign('pages',$data->render());
		
		$this->assign('ste',input('starttime'));
		$this->assign('ete',input('endtime'));
		return $this->fetch();
    }

	//会员操作日志
	public function member_log(){
		$username = input('username');
    	$map = [];
		if(input('userid')!='')$map['userid'] =input('userid');
		if(input('starttime')!=''||input('endtime')!=''){
			$map['logtime'] = ['between',[strtotime(input('starttime')),strtotime(input('endtime'))]];
		}
    	if(input('username')){
    		$map['username'] = ['like',"%$username%"];
    	}  	
		
		$totalCount=Db::name('member_log')->where($map)->count();
		$pagecount=config('paginate.list_admin');
		$data=Db::name('member_log')->where($map)->order('logtime desc')->paginate($pagecount,$totalCount,['query'=>request()->param()]);
		$lists = $data->all();
		$this->assign('lists',$lists);
		$this->assign('total',$data->total());
		$this->assign('listRows',$data->listRows());
		$this->assign('currentPage',$data->currentPage());
		$this->assign('lastPage',$data->lastPage());
		$this->assign('pages',$data->render());
		
		$this->assign('ste',input('starttime'));
		$this->assign('ete',input('endtime'));
		
		return $this->fetch();
	}

	//删除操作日志
	public function member_logdel(){
		$tody=strtotime(date('Y-m-d',time()));
		$mday=$tody-86400*30;
		
		$rd=['status'=>0,'msg'=>'删除失败!可能没有您需要清除的数据了！'];
    	$rs=Db::name('member_log')->where('logtime','<=',$mday)->delete();
    	if($rs>0){
			addAdminLog('成功删除操作日志');
			return ['status'=>1,'msg'=>'删除成功!'];
		}else{
			return $rd;
		}
	}
	
	/**
     * 搜索用户名
     */
    public function ajaxSearchUser(){
        $search_key = trim(input('search_key'));
		if($search_key!=''){
			$map=[];
			$map['username|email|tel']=['like','%'.$search_key.'%'];
			$ulist=Db::name('member')->where($map)->select();
			$option='';
			foreach($ulist as $key => $val){
				if(strstr($search_key,'@')){
					$name=$val['email'];
				}elseif(check_mobile($search_key)){
					$name=$val['tel'];
				}else{
					$name=$val['username'];
				}
				$option.="<option value='{$val['userid']}'>{$name}</option>";
            }
			return $option;
		}else{
			return false;
		}
        exit;
    }
	
	//提现申请
	public function withdrawals(){
    	$map = [];
		if(input('status')!='')$map['status'] =input('status');//0申请中1申请成功2申请失败
		if(input('userid')!='')$map['userid'] =input('userid');
		
		if(input('starttime')!='' && input('endtime')!=''){
			$map['create_time'] = ['between',[strtotime(input('starttime')),strtotime(input('endtime'))]];
		}elseif(input('starttime')!='' && input('endtime')==''){
			$map['create_time'] =['>=',strtotime(input('starttime'))];
		}elseif(input('starttime')=='' && input('endtime')!=''){
			$map['create_time'] =['<=',strtotime(input('endtime'))];
		}
		if(input('account_name')){//收款账号
    		$map['account_name'] = ['like',"%".input('account_name')."%"];
    	}
    	if(input('account_bank')){//收款账户名
    		$map['account_bank'] = ['like',"%".input('account_bank')."%"];
    	}
		
		$totalCount=Db::name('withdrawals')->where($map)->count();
		$pagecount=config('paginate.list_admin');
		$data=Db::name('withdrawals')->where($map)->order('create_time desc')->paginate($pagecount,$totalCount,['query'=>request()->param()]);
		$lists = $data->all();
		$this->assign('lists',$lists);
		$this->assign('total',$data->total());
		$this->assign('listRows',$data->listRows());
		$this->assign('currentPage',$data->currentPage());
		$this->assign('lastPage',$data->lastPage());
		$this->assign('pages',$data->render());
		
		$this->assign('ste',input('starttime'));
		$this->assign('ete',input('endtime'));
		
		return $this->fetch();
	}
	//编辑申请提现
	public function withdrawalsedit(){
		$info=Db::name('withdrawals')->where('id',input('id'))->find();
		$this->assign('info',$info);
		
		$user = Db::name('member')->where('userid',$info['userid'])->find();
		$this->assign('user',$user);
		
		if(request()->isPost() || request()->isAjax()){
			$data = input('post.');
			//如果是已经给用户转账 则生成转账流水记录
			if($data['status'] == 1 && $info['status'] != 1){
				if ($user['mymoney'] < $info['money']) {
                    $this->error("用户余额不足{$info['money']}，不够提现");
                    exit;
                }
				accountLog($info['userid'], ($info['money'] * -1),0,"平台提现");
				$remittance = array(
                    'userid' => $info['userid'],
                    'bank_name' => $info['bank_name'],
                    'account_bank' => $info['account_bank'],
                    'account_name' => $info['account_name'],
                    'money' => $info['money'],
                    'status' => 1,
                    'create_time' => time(),
                    'admin_id' => session('admin_id'),
                    'withdrawals_id' => $info['id'],
                    'remark' => $data['remark'],
                );
                Db::name('remittance')->insert($remittance);//添加汇款记录
			}
			$rs=DB::name('withdrawals')->update($data);//编辑申请提现
			if($rs!==false){
				$this->error('操作失败');
			}else{
				addAdminLog('成功处理会员('.$user['username'].')的提现申请');
				$this->success('操作成功');
			}
		}
		return $this->fetch();
	}
	//删除申请提现
	public function withdrawalsdel(){
		if(empty(input('id'))){//批量删除
			$ids=input('ids/a');
			$rd=['status'=>0,'msg'=>'删除失败!'];
			$names='';
	    	if(is_array($ids)){
				foreach($ids as $id){
					//删除
					$rs=Db::name('withdrawals')->where('id',$id)->delete();
					if($rs==0){
						return $rd;
					}else{
						Db::name('remittance')->where('withdrawals_id',$id)->delete();
						$rd=['status'=>1,'msg'=>'删除成功!'];
					}
		        }
				if($rd['status']==1){
					addAdminLog('成功批量删除会员提现申请!');
				}
			}
			return $rd;
		}else{//单条删除
			//删除
			$rs=Db::name('withdrawals')->where('id',input('id'))->delete();
			if($rs==0){
				$this->error('删除失败');
			}else{
				Db::name('remittance')->where('withdrawals_id',$id)->delete();
				addAdminLog('成功删除会员提现申请!');
				$this->success('删除成功');
			}
		}
	}
	//申请提现状态
	public function withdrawalsStatus(){
		$status=input('status');
		$ids=$_POST['ids'];
		$withdrawals = Db::name('withdrawals')->where('id','in', $ids)->select();
		if($status == 1){
            $r = Db::name('withdrawals')->where('id','in', $ids)->update(['status'=>$status,'check_time'=>time()]);
        }else if($status == -1){
            $r = Db::name('withdrawals')->where('id','in', $ids)->update(['status'=>$status,'refuse_time'=>time()]);
        }else if($status == 2){
        	$alipay=[];
			foreach($withdrawals as $v){
				$user = Db::name('member')->where(['userid'=>$v['userid']])->find();
				if($user['mymoney'] < $v['money']){
                    $data['status'] = -2;
                    $data['remark'] = '账户余额不足';
                    Db::name('withdrawals')->where('id',$v['id'])->update($data);
                }else{
                	if($v['bank_name'] == '支付宝 '){
                        //流水号1^收款方账号1^收款账号姓名1^付款金额1^备注说明1|流水号2^收款方账号2^收款账号姓名2^付款金额2^备注说明2
                        $alipay['batch_no'] = time();
                        $alipay['batch_fee'] += $v['money'];
                        $alipay['batch_num'] += 1;
                        $str = isset($alipay['detail_data']) ? '|' : '';
                        $alipay['detail_data'] .= $str.$v['pay_code'].'^'.$v['account_bank'].'^'.$v['realname'].'^'.$v['money'].'^'.$v['remark'];
                    }elseif($v['bank_name'] == '微信 '){
                    	$wxpay = [
                            'userid' => $v['userid'],//用户ID做更新状态使用
                            'openid' => $v['account_bank'],//收钱的人微信 OPENID
                            'pay_code'=>$v['pay_code'],//提现申请ID
                            'money' => $v['money'],//金额
                            'desc' => '恭喜您提现申请成功!'
                        ];
						$res = $this->transfer('weixin',$wxpay);//微信在线付款转账
                        if($res['partner_trade_no']){
                            accountLog($v['userid'], ($v['money'] * -1), 0,"平台处理用户提现申请");
                            $r = Db::name('withdrawals')->where('id',$v['id'])->update(['status'=>$status,'pay_time'=>time()]);
                        }else{
                            return ['status'=>0,'msg'=>$res['msg']];
                        }
                    }
                }
			}
			if(!empty($alipay)){
                $this->transfer('alipay',$alipay);
            }
			return ['status'=>1,'msg'=>"操作成功"];
		}elseif($status == 3){
			$r = Db::name('withdrawals')->where('id','in', $ids)->delete();
		}else{
            accountLog($v['userid'], ($v['money'] * -1), 0,"管理员处理用户提现申请");//手动转账，默认视为已通过线下转方式处理了该笔提现申请
            $r = Db::name('withdrawals')->where('id','in', $ids)->update(['status'=>2,'pay_time'=>time()]);
        }
		if($r!==false){
			return ['status'=>1,'msg'=>'设置成功！'];
		}else{
			return ['status'=>0,'msg'=>'操作失败！'];
		}
	}
	//在线打款
	public function transfer($atype,$data){
        if($atype == 'weixin'){
            include_once  PLUGIN_PATH."payment/weixin/weixin.class.php";
            $wxpay_obj = new \weixin();
            return $wxpay_obj->transfer($data);
        }else{
            //支付宝在线批量付款
            include_once  PLUGIN_PATH."payment/alipay/alipay.class.php";
            $alipay_obj = new \alipay();
            return $alipay_obj->transfer($data);
        }
    }
	//转账汇款记录
	public function remittance(){
    	$map = [];
		if(input('withdrawals_id')!='')$map['withdrawals_id'] =input('withdrawals_id');
		if(input('status')!='')$map['status'] =input('status');//0申请中1申请成功2申请失败
		if(input('userid')!='')$map['userid'] =input('userid');
		
		if(input('starttime')!='' && input('endtime')!=''){
			$map['create_time'] = ['between',[strtotime(input('starttime')),strtotime(input('endtime'))]];
		}elseif(input('starttime')!='' && input('endtime')==''){
			$map['create_time'] =['>=',strtotime(input('starttime'))];
		}elseif(input('starttime')=='' && input('endtime')!=''){
			$map['create_time'] =['<=',strtotime(input('endtime'))];
		}
		if(input('account_name')){//收款账号
    		$map['account_name'] = ['like',"%".input('account_name')."%"];
    	}
    	if(input('account_bank')){//收款账户名
    		$map['account_bank'] = ['like',"%".input('account_bank')."%"];
    	}
		
		$totalCount=Db::name('remittance')->where($map)->count();
		$pagecount=config('paginate.list_admin');
		$data=Db::name('remittance')->where($map)->order('create_time desc')->paginate($pagecount,$totalCount,['query'=>request()->param()]);
		$lists = $data->all();
		$this->assign('lists',$lists);
		$this->assign('total',$data->total());
		$this->assign('listRows',$data->listRows());
		$this->assign('currentPage',$data->currentPage());
		$this->assign('lastPage',$data->lastPage());
		$this->assign('pages',$data->render());
		
		$this->assign('ste',input('starttime'));
		$this->assign('ete',input('endtime'));
		
		return $this->fetch();
	}
}
?>