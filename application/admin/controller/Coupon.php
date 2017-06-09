<?php
/**
 * 优惠卷管理
 * -----------------------------------------
 * CopyRight @Ybcms开发团队，并保留所有权利
 * Url: http://www.ybcms.com
 * -----------------------------------------
 * Author: ybcms(dreamlak@qq.com)      
 * UpDate: 2017-03-01
 */

namespace app\admin\controller;
use think\AjaxPage;
use think\Page;
use think\Validate;
use \think\Db;
class Coupon extends AdminBase{
	public  $coupon_type;
	public function _initialize(){
		parent::_initialize();
		$this->order_status = ['0'=>'面额模板','1'=>'按用户发放','2'=>'注册发放','3'=>'邀请发放','4'=>'线下发放'];
		$this->assign('coupon_type',$this->order_status);
	}
	//优惠券管理
	public function index(){
		$map=[];
		input('type')!=''?$map['type']=input('type'):'';
		input('name')!=''?$map['name']=['like','%'.input('name').'%']:'';

		$totalCount=Db::name('coupon')->where($map)->count();
		$pagecount=config('paginate.list_admin');
		$data=Db::name('coupon')->where($map)->order('id DESC')->paginate($pagecount,$totalCount,['query'=>request()->param()]);
		$lists = $data->all();
		$this->assign('lists',$lists);
		$this->assign('total',$data->total());
		$this->assign('listRows',$data->listRows());
		$this->assign('currentPage',$data->currentPage());
		$this->assign('lastPage',$data->lastPage());
		$this->assign('pages',$data->render());
		
		$this->assign('coupons',$this->order_status);
		return $this->fetch();
	}
	/*
     * 添加编辑一个优惠券类型
     */
    public function coupon_addedit(){
        if(request()->isPost() || request()->isAjax()){
        	$data = input('post.');
            $data['send_start_time'] = strtotime($data['send_start_time']);
            $data['send_end_time'] = strtotime($data['send_end_time']);
            $data['use_end_time'] = strtotime($data['use_end_time']);
            $data['use_start_time'] = strtotime($data['use_start_time']);
            if($data['send_start_time'] > $data['send_end_time']){
                $this->error('发放日期填写有误');
            }
            if($data['money'] >= $data['condition']){
                $this->error('优惠券面额不能大于等于消费金额');
            }
			
            if(empty($data['id'])){//编辑
            	$data['add_time'] = time();
            	$row = Db::name('coupon')->insert($data);
				addAdminLog('成功添加优惠券:'.$data['name']);
				$rd = ['status'=>1,'msg'=>'添加成功'];
            }else{//添加
            	$row = Db::name('coupon')->where('id',$data['id'])->update($data);
				addAdminLog('成功编辑优惠券:'.$data['name']);
				$rd = ['status'=>1,'msg'=>'编辑成功'];
            }
            if($row===false){
                $this->error('操作失败');
			}
			return $rd;
        }
		
        $cid = input('id/d');
        if($cid){
        	$coupon = Db::name('coupon')->where('id',$cid)->find();
        	$this->assign('info',$coupon);
        }else{
        	$def['send_start_time'] = strtotime("+1 day");
        	$def['send_end_time'] = strtotime("+1 month");
        	$def['use_start_time'] = strtotime("+1 day");
        	$def['use_end_time'] = strtotime("+2 month");
        	$this->assign('info',$def);
        }
        return $this->fetch();
    }
	/*
    * 优惠券发放
    */
    public function make_coupon(){
        //获取优惠券ID
        $cid = input('id/d');
        $type = input('type');
        //查询是否存在优惠券
        $data = Db::name('coupon')->where('id',$cid)->find();
        $remain = $data['createnum'] - $data['send_num'];//剩余派发量
    	if($remain<=0) $this->error($data['name'].'已经发放完了');
        if(!$data) $this->error("优惠券类型不存在");
        if($type != 4) $this->error("该优惠券类型不支持发放");
		
        if(request()->isPost() || request()->isAjax()){
            $num  = input('num/d');//要发放数
			$prefix = input('prefix');//前缀
			$start_code = input('start_code');//起始数
            if($num>$remain) $this->error($data['name'].'发放量不够了');
            if(!$num > 0) $this->error("发放数量不能小于0");
            $add['cid'] = $cid;
            $add['type'] = $type;
            $add['send_time'] = time();
            for($i=0;$i<$num;$i++){
                do{
                    //$code = get_rand_str(8,0,1);//获取随机8位字符串
                    //$code = mt_rand(10000000,99999999);//获取随机8位字符串
                    $code = $prefix.($start_code+$i);//自定义号码
                    $check_exist = Db::name('coupon_list')->where('code',$code)->find();//不重复生成
                }while($check_exist);
                $add['code'] = $code;
                Db::name('coupon_list')->insert($add);
            }
            Db::name('coupon')->where("id",$cid)->setInc('send_num',$num);
			addAdminLog("发放".$num.'张'.$data['name']);
			return ['status'=>1,'msg'=>'发放成功'];
            exit;
        }
        $this->assign('info',$data);
		
		//获取优惠券最后一介个Code
		$startCode=Db::name('coupon_list')->where('cid',$cid)->order('id DESC')->value('code');
		if($startCode==''){
			$startCode=0;
		}
		$startCode=preg_replace('/[\.a-zA-Z]/s','',$startCode);
		$this->assign('startCode',$startCode);
        return $this->fetch();
    }
	//发放会员优惠券
	public function get_user_send(){
    	//搜索条件
    	$map = [];
		input('level') ? $map['level'] = input('level') : false;
    	$keys = input('keys');
    	if(!empty($keys)){
    		$map['nickname|tel|email']=['like',"%$keys%"];
    	}
		
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
        
        $user_level = Db::name('member_level')->column('id,name');
        $this->assign('user_level',$user_level);
		
		$this->assign('cid',input('cid/d'));
    	return $this->fetch();
    }
	//发放会员优惠券
	public function do_user_send(){
    	$cid = input('cid/d');
		$user_id=input('user_id/a');
		$level_id=input('level_id/d');
		
		$insert = '';//要发放的数组
		$coupon = Db::name('coupon')->where("id",$cid)->find();
		if($coupon['createnum']>0){
			$remain = $coupon['createnum'] - $coupon['send_num'];//剩余派发量
			if($remain<=0) $this->error($coupon['name'].'已经发放完了');
		}

		$prefix = input('prefix');//前缀
		$start_code = input('start_code');//起始数
		
		if(empty($user_id) && $level_id>=0){//按用户等级发放
			if($level_id==0){//全部发放
				$user = Db::name('member')->where("status",1)->select();
			}else{//按等级发放
				$user = Db::name('member')->where("status",1)->where('level', $level_id)->select();
			}
			if($user){
				$able = count($user);//本次发送量（发放用户数）
				if($coupon['createnum']>0 && $remain<$able){//检测是否够发放
					$this->error($coupon['name'].'派发量只剩'.$remain.'张');
				}
				foreach ($user as $k=>$val){
					do{
	                    $code = get_rand_str(8,0,1);//获取随机8位字符串
	                    $check_exist = Db::name('coupon_list')->where('code',$code)->find();//不重复生成
	                }while($check_exist);
					$time = time();
                    $insert[] = ['cid'=>$cid,'type'=>1,'uid'=>$val['userid'],'send_time'=>$time,'code'=>$code];
				}
			}
		}else{//按选择用户
			$able = count($user_id);//本次发送量(选择的用户数)
			if($coupon['createnum']>0 && $remain<$able){//检测是否够发放
				$this->error($coupon['name'].'派发量只剩'.$remain.'张');
			}
			foreach ($user_id as $k=>$v){
				$time = time();
                $insert[] = ['cid'=>$cid,'type'=>1,'uid'=>$v,'send_time'=>$time];
			}
		}
		if(empty($insert))$this->error('没有找到发放的会员！');
		DB::name('coupon_list')->insertAll($insert);//插入发放记录
		Db::name('coupon')->where("id",$cid)->setInc('send_num',$able);//修改已发放数
		
		addAdminLog("发放".$able.'张'.$coupon['name']);
		return ['status'=>1,'msg'=>'发放成功'];
    }
	/*
     * 删除优惠券类型
     */
	public function del_coupon(){
		$id=input('id');
		if(empty(input('id'))){//批量删除
			$ids=input('ids/a');
			$rd=['status'=>0,'msg'=>'删除失败!'];
			$names='';
	    	if(is_array($ids)){
				foreach($ids as $v){
					//删除前获取该删除的名
					$names.=Db::name('coupon')->where('id',$v)->value('name').'，';
					//查询是否存在优惠券
			        $row = Db::name('coupon')->where('id',$v)->delete();
			        if($row){
			        	addAdminLog('成功删除优惠券类型:'.$names);
			            //删除此类型下的优惠券
			            Db::name('coupon_list')->where('cid',$v)->delete();
			            return ['status'=>1,'msg'=>'删除成功!'];
			        }else{
			            $this->error("删除失败");
			        }
		        }
			}
			return $rd;
		}else{
			$names.=Db::name('coupon')->where('id',$id)->value('name');
			
	    	$row = Db::name('coupon')->where('id',$id)->delete();
	        if($row){
	        	addAdminLog('成功删除优惠券类型:'.$names);
	            //删除此类型下的优惠券
	            Db::name('coupon_list')->where('cid',$id)->delete();
	            return ['status'=>1,'msg'=>'删除成功!'];
	        }else{
	            $this->error("删除失败");
	        }
		}
	}
	/*
     * 优惠券详细查看
     */
    public function coupon_list(){
        //获取优惠券ID
        $cid = input('id/d');
        //查询是否存在优惠券
        $check_coupon = Db::name('coupon')->field('id,type')->where('id',$cid)->find();
        if(!$check_coupon['id'] > 0)
            $this->error('不存在该类型优惠券');
       
        //查询该优惠券的列表的数量
        $sql = "SELECT count(1) as c FROM __PREFIX__coupon_list  l ".
                "LEFT JOIN __PREFIX__coupon c ON c.id = l.cid ". //联合优惠券表查询名称
                "LEFT JOIN __PREFIX__order o ON o.order_id = l.order_id ".     //联合订单表查询订单编号
                "LEFT JOIN __PREFIX__member u ON u.userid = l.uid WHERE l.cid =:cid";    //联合用户表去查询用户名
        
        $count = query($sql,['cid'=>$cid]);
        $count = $count[0]['c'];
    	$Page = new Page($count,10);
    	$show = $Page->show();
        
        //查询该优惠券的列表
        $sql = "SELECT l.*,c.name,o.order_sn,u.nickname FROM __PREFIX__coupon_list  l ".
                "LEFT JOIN __PREFIX__coupon c ON c.id = l.cid ". //联合优惠券表查询名称
                "LEFT JOIN __PREFIX__order o ON o.order_id = l.order_id ".     //联合订单表查询订单编号
                "LEFT JOIN __PREFIX__member u ON u.userid = l.uid WHERE l.cid = :cid".    //联合用户表去查询用户名
                " limit {$Page->firstRow} , {$Page->listRows}";
				
        $coupon_list = query($sql,['cid'=>$cid]);
        $this->assign('lists',$coupon_list); 
		
        $coupon_type =['面额模板','按用户发放','注册发放','邀请发放','线下发放'];
        $this->assign('coupon_type',$coupon_type);
        
        $this->assign('type',$check_coupon['type']);       
    	$this->assign('page',$show);
        $this->assign('pager',$Page);
        return $this->fetch();
    }
	/*
     * 删除一张优惠券
     */
    public function coupon_list_del(){
        $id=input('id'); //获取优惠券ID
		if(empty(input('id'))){//批量删除
			$ids=input('ids/a');
			$rd=['status'=>0,'msg'=>'删除失败!'];
			$names='';
	    	if(is_array($ids)){
				foreach($ids as $v){
					//查询是否存在优惠券
			        $row = Db::name('coupon_list')->where('id',$v)->delete();
        			if($row!==false){
        				$cid=Db::name('coupon_list')->where('id',$v)->value('cid');
						Db::name('coupon')->where('id',$cid)->setDec('send_num');
			        	addAdminLog('成功批量删除优惠券');
			            $rd = ['status'=>1,'msg'=>'删除成功!'];
			        }else{
			            $this->error("删除失败");
			        }
		        }
			}
			return $rd;
		}else{
			if(!$id) $this->error("缺少参数值");
	    	$row = Db::name('coupon_list')->where('id',$id)->delete();
	        if($row!==false){
	        	$cid=Db::name('coupon_list')->where('id',$id)->value('cid');
				Db::name('coupon')->where('id',$cid)->setDec('send_num');
	        	addAdminLog('成功删除优惠券');
	            return ['status'=>1,'msg'=>'删除成功!'];
	        }else{
	            $this->error("删除失败");
	        }
		}
    }
}
?>