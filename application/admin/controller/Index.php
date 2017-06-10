<?php
/**
 * 后台首页
 * -----------------------------------------
 * CopyRight @Ybcms开发团队，并保留所有权利
 * Url: http://www.ybcms.com
 * -----------------------------------------
 * Author: ybcms(dreamlak@qq.com)      
 * UpDate: 2017-03-01
 */

namespace app\admin\controller;
use app\admin\model\Admin;
use think\Validate;
use \think\Db;
use \think\Cache;

class Index extends AdminBase{
	//后台首页
	public function index(){
		//版本
		$version=require ROOT_PATH . '/data/version.php';
		$this->assign('version',$version['yb_version']);
		//主菜单
		$this->assign('mainNode',model('Node')->getMainNode());
		return $this->show(); 
	}
	public function main(){
		$system = get_sysinfo();
		$this->assign('system',$system);
		return $this->show();
	}
	//系统文件占用大小(为了打开页面慢，所以用js加载)
	public function ajaxsyssize(){
		$rootdir = $_SERVER['DOCUMENT_ROOT']?str_replace('\\','/',$_SERVER['DOCUMENT_ROOT']):str_replace('\\','/',dirname(__FILE__));
		$syssize = getDirSize($rootdir);
		$syssize = getRealSize($syssize);
		return $syssize;
	}
	//登录
	public function login(){
		if(request()->isPost() || request()->isAjax()){
			//管理员登陆字段验证
			$data = input('post.');
        	$rule = [
            	'adminname|管理员账号' => 'require|min:5',
            	'password|管理员密码' => 'require|min:6',
            	'vcord|验证码' => 'require|min:4',
        	];
        	//数据验证
        	$validate = new Validate($rule);
        	$result   = $validate->check($data);
        	if(!$result){
            	$msg=$validate->getError();
            	return $this->error($msg);
        	}
			if(!captcha_check(input('vcord'))){
		    	return $this->error("验证码错误");
	   		}
	   		//登录验证
	   		$AdminMembers= new Admin;
	   		$cr=$AdminMembers->logincheck($data['adminname'],$data['password']);
	   		if($cr['status']==1){
	   			//$this->success($cr['msg'], 'index');
	   			$url=url('Admin/Index/index');
				return ['status'=>1,'msg'=>'登录成功！','url'=>$url];
	   		}else{
	   			return $this->error($cr['msg']);
	   		}
		}else{
			if(is_login()){
				$this->redirect('index');
			}
			return $this->fetch();
		}
	}
	
	//退出登录
	public function outlogin(){
		addAdminLog('退出成功');//写入日志
		session('adminuser',null);
		cookie('adminuser', null);
		$this->success('退出成功！', 'login');
	}
	
	//清除缓存
	public function clearcache(){
		if(request()->isPost() || request()->isAjax()){
			$rd=['cache'=>0,'data'=>0,'logs'=>0,'temp'=>0,'filesize'=>0];
			$data = input('clear/a');
			if(count($data)==0)return false;
			foreach($data as $k=>$v){
				if($v=='cacheAll'){
					delFile('./runtime/');
					return ['cache'=>1,'data'=>1,'logs'=>1,'temp'=>1,'filesize'=>getRealSize(getDirSize('./runtime/'))];
				}
				delFile('./runtime/'.$v);
				$rd[$v]=1;
			}
			$rd['filesize']=getRealSize(getDirSize('./runtime/'));
			Cache::clear();
			return $rd;
		}else{
			$filesize=getRealSize(getDirSize('./runtime/'));
			$this->assign('filesize',$filesize);
			return $this->fetch();
		}
	}
	/**
     * 清空静态商品页面缓存
     */
  	public function ClearGoods(){
        $goods_id = input('goods_id');
		$f="./Application/Runtime/Html/Home_Goods_goodsInfo_{$goods_id}.html";
		if (file_exists($f)){
	        if(unlink($f)){
	            //删除静态文件                
	            $html_arr = glob("./Application/Runtime/Html/Home_Goods*.html");
	            foreach ($html_arr as $key => $val){            
	                strstr($val,"Home_Goods_ajax_consult_{$goods_id}") && unlink($val); // 商品咨询缓存
	                strstr($val,"Home_Goods_ajaxComment_{$goods_id}") && unlink($val); // 商品评论缓存
	            }
	            $json_arr = array('status'=>1,'msg'=>'清除成功','result'=>'');
	        }else {
	            $json_arr = array('status'=>-1,'msg'=>'未能清除缓存','result'=>'' );
	        }
		}
        //缩略图
		delFile(UPLOAD_PATH."goods/thumb/".$goods_id); // 删除缩略图
    	$json_arr = array('status'=>1,'msg'=>'清除成功,请清除对应的静态页面','result'=>'');
		
        return $json_arr;
  	} 
}
?>