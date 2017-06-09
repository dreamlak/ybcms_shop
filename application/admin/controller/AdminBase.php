<?php
/**
 * 行为日志
 * -----------------------------------------
 * CopyRight @Ybcms开发团队，并保留所有权利
 * Url: http://www.ybcms.com
 * -----------------------------------------
 * Author: ybcms(dreamlak@qq.com)      
 * UpDate: 2017-03-01
 */

namespace app\admin\controller;
use think\Controller;
use think\Validate;
use \think\Db;
class AdminBase extends Controller{
    public function _initialize(){
    	define('MODULE',strtolower($this->request->module()));
    	define('CONTRO',strtolower($this->request->controller()));
    	define('ACTION',strtolower($this->request->action()));
    	
		$this->assign('MODULE',MODULE);
		$this->assign('CONTRO',CONTRO);
		$this->assign('ACTION',ACTION);
		
		$this->checkauth();
    }
	
    //空操作
    public function _empty(){
    	$this->error("链接错误，访问不到该内容！");
    }
	
    //模板渲染
	public function show($tpl=''){
		return $this->fetch($tpl);
	}

	//操作权限
	public function checkauth(){
		//过滤不需要登陆的行为
		if(in_array(ACTION,array('login','outlogin','clearcache','changeTableVal'))){
			return true;
		}else{
			//非过渡行为验证需要登录
			if(!is_login()){
				$this->redirect('admin/Index/login');
			}else{
				//登录后检账户权限(除超级管理员外)
				$loginuser=!empty(session('adminuser'))?session('adminuser'):cookie('adminuser');
				$role_id=$loginuser['roleid'];
				
				//超级管理员无需验证
				if($role_id==1 || (CONTRO=='Index' && ACTION=='index') || (CONTRO=='index' && ACTION=='index') ) return true;
				
				//特定内容或所有ajax请求无需验证
				$uneed_check = array('login','outlogin','clearcache','changeTableVal');
				if(strpos(ACTION,'ajax')!==false || in_array(ACTION,$uneed_check)) return true;
				
				//权限检测
				$roledata=Db::name('admin_role')->where(array('roleid'=>$role_id))->value('data');
				$noderole=Db::name('node')->where(array('nodeid'=>['in',$roledata]))->field('m,c,a')->select();
				$nodemvc='';
				foreach($noderole as $v){
					$nodemvc.=strtolower($v['m']).'/'.strtolower($v['c']).'/'.strtolower($v['a']).',';
				}
				$nodemvc_arr = explode(',', $nodemvc);
				//检查是否拥有此操作权限
				$mymvcurl=strtolower(MODULE).'/'.strtolower(CONTRO).'/'.strtolower(ACTION);
	    		if(!in_array($mymvcurl, $nodemvc_arr)){
	    			if(request()->isAjax()){
	    				return ['status'=>0,'msg'=>'您没有该项操作权限！'];
	    			}else{
	    				$this->error('您没有该项操作权限!');
	    			}
	    		}
			}
		}
	}
	//更新配置文件
	public function updateConfig(){
		$configfile=APP_PATH.'/system.php';
		
		$pattern = $replacement = [];
		$cdata=Db::name('cofing')->field('name,value')->select();
		foreach($cdata as $v){
			$kk=$v['name'];
			$vv=$v['value'];
			$config[$kk]=$vv;
			$pattern[$kk] = "/'".$kk."'\s*=>\s*([']?)[^']*([']?)(\s*),/is";
        	$replacement[$kk] = "'".$kk."' => \${1}".$vv."\${2}\${3},";		
		}
		
		$str = file_get_contents($configfile);
		$str = preg_replace($pattern, $replacement, $str);
		
		//生成PHP文件
		file_put_contents($configfile, $str, LOCK_EX);
	}
	
	/**
     * ajax 修改指定表数据字段  一般修改状态 比如 是否推荐 是否开启 等 图标切换的
     * table,id_name,id_value,field,value
     */
    public function changeTableVal(){
        $table = input('table'); //表名
        $id_name = input('id_name'); //表主键id名
        $id_value = input('id_value'); //表主键id值
        $field  = input('field'); //修改哪个字段
        $value  = input('value'); //修改字段值
        $rs=Db::name($table)->where($id_name,$id_value)->setField($field,$value); //根据条件保存修改的数据
        if($rs==0){
        	return ['status'=>0,'msg'=>'您没有做任何修改！'];
        }elseif($rs){
        	return ['status'=>1,'msg'=>'修改成功！'];
        }else{
        	return ['status'=>1,'msg'=>'修改失败！'];
        }
	}
}
