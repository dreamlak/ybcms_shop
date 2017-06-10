<?php
/**
 * 后台公共模型
 * -----------------------------------------
 * CopyRight @Ybcms开发团队，并保留所有权利
 * Url: http://www.ybcms.com
 * -----------------------------------------
 * Author: ybcms(dreamlak@qq.com)      
 * UpDate: 2017-03-01
 */

namespace app\admin\model;
use think\Model;
use think\Db;
class Base extends Model{
	//自定义初始化
    protected function initialize(){
        //需要调用`Model`的`initialize`方法
        parent::initialize();
        //TODO:自定义的初始化
    }
    
	/*默认反回信息
	 * @$msg	反回的说明
	 * @$type	反回类型（1=成功，0失败）
	 * @$isadd	是否写入数据库
	*/
	public function returnInfo($msg='',$status=1,$isadd=true){
		addAdminLog($msg);//记录操作
		return ['status'=>$status,'msg'=>$msg];
	}
}
?>