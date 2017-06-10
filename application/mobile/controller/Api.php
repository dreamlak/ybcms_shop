<?php
/**
 * 接口
 * ============================================================================
 * 版权所有 Ybcms开发团队，并保留所有权利
 * 网站地址: http://www.ybcms.com
 * ============================================================================
 * Author: ybcms(dreamlak@qq.com)      
 * UpDate: 2017-03-01
 */
namespace app\mobile\controller;
use think\Db;
class Api extends MobileBase {
	public function ajax_islogin(){
		if(session('?user')) {
            $user = session('user');
            $user = Db::name('member')->where("userid", $user['userid'])->find();
            return ['status'=>1,'avatar'=>$user['avatar']];
        }else{
        	return ['status'=>0,'avatar'=>''];
        }
	}
}
?>