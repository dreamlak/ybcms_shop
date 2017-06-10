<?php
/**
 * Admin应用公共文件
 * ============================================================================
 * 版权所有 Ybcms开发团队，并保留所有权利
 * 网站地址: http://www.ybcms.com
 * ============================================================================
 * Author: ybcms(dreamlak@qq.com)      
 * UpDate: 2017-03-01
 */

//是否登录并反回管理员ID
function is_login(){ 
    $logininfo=count(session('adminuser'))>0?session('adminuser'):cookie('adminuser');
    if(count($logininfo)>0){
    	return $logininfo['adminid']; 
    }else{
    	return false;
    }
}

//获取管理员用户名
function get_adminName($uid=0){
	if($uid!=0||$uid!=''){
		return db('admin')->where('adminid',$uid)->value('adminname');
	}
	
	$logininfo=count(session('adminuser'))>0?session('adminuser'):cookie('adminuser');
    if(count($logininfo)>0){
    	return $logininfo['adminname']; 
    }else{
    	return false;
    }
}

//获取管理员姓别
function get_realname($uid=0){
	if($uid==0||!$uid) $uid=is_login();
	return db('admin')->where('adminid',$uid)->value('realname');
}

//获取管理员角色名
function get_rolename($uid=0){
	if($uid==0||!$uid){
		$admins=!empty(session('adminuser'))?session('adminuser'):cookie('adminuser');
		$roleid=$admins['roleid'];
	}else{
		$roleid=db('admin')->where('adminid',$uid)->value('roleid');
	}
	
	return db('admin_role')->where('roleid',$roleid)->value('name');
}
//获取最后登录时间
function lastlogintime(){
	$logininfo=count(session('adminuser'))>0?session('adminuser'):cookie('adminuser');
    if(count($logininfo)>0){
    	if($logininfo['lastlogintime']!=''){
    		return date('Y-m-d H:i:s',$logininfo['lastlogintime']);
    	}
    }else{
    	return false;
    }
}

//获限最后登IP
function lastloginip(){
	$logininfo=count(session('adminuser'))>0?session('adminuser'):cookie('adminuser');
    if(count($logininfo)>0){
    	return $logininfo['lastloginip']; 
    }else{
    	return false;
    }
}

//mysql版本
function mysql_version(){
    $version = \think\Db::query("select version() as ver");
    return $version[0]['ver'];
}

//mysql数据库大小
function mysql_db_size(){        
    $sql = "SHOW TABLE STATUS FROM ".config('database.database');
    $tblPrefix = config('database.prefix');
    if($tblPrefix != null) {
        $sql .= " LIKE '{$tblPrefix}%'";
    }
    $row = \think\Db::query($sql);
    $size = 0;
    foreach($row as $value) {
        $size += $value["Data_length"] + $value["Index_length"];
    }
    return round(($size/1048576),2).'M';
}

//操作日志写入数据库
function addAdminLog($msg){
	if(get_adminName()==false){
		$adminname=input('adminname');
	}else{
		$adminname=get_adminName();
	}
	
	$data=[];
	$data['adminname']=$adminname;
	$data['remark']=$msg;
	$request = request();
	$data['url']=$request->url();
	$data['logip']=$request->ip();
	$data['addtime']=time();
	
	$rs=db('admin_log')->insert($data);
}

//获取系统参数信息
function get_sysinfo() {
	$version=require ROOT_PATH . '/data/version.php';
	$sys_info=[];
	$sys_info['version']		= $version['yb_name'].' '.$version['yb_version'];//系统版本号
	$sys_info['updates']		= $version['yb_release'];//系统更新时间
	$sys_info['os']             = PHP_OS;//Linux -> Linux;FreeDSB -> FreeBSD;Windows NT -> WINNT(WIN);Mac Os X -> Darwin
	$sys_info['zlib']           = function_exists('gzclose')?'是':'否';//zlib 数据压缩
	$sys_info['gd']           	= function_exists('gd_info')?'是':'否';//GD库是否开启
	$sys_info['curl']           = function_exists('curl_init')?'是':'否';//GD库是否开启
	$sys_info['safe_mode']      = (boolean) ini_get('safe_mode')?'ON':'OFF';//safe_mode = Off，是否启用 PHP的安全模式
	$sys_info['timezone']       = function_exists("date_default_timezone_get") ? date_default_timezone_get():'？'; //时区
	$sys_info['socket']         = function_exists('fsockopen')?'是':'否';//是否长链接协议
	$sys_info['web_server']     = $_SERVER['SERVER_SOFTWARE']; //服务器环景
	$sys_info['domain']     	= $_SERVER['HTTP_HOST']; //服务器环景
	$sys_info['ip']     		= GetHostByName($_SERVER['SERVER_NAME']); //服务器环景
	$sys_info['phpversion']     = phpversion();	//php版本
	$sys_info['fileupload']     = @ini_get('file_uploads') ? ini_get('upload_max_filesize') :'unknown';//上传大小
	$sys_info['mysqlinfo']		= mysql_version();//数据库信息
	$sys_info['mysqlsize']		= mysql_db_size();//数据库大小
	//系统文件占用大小
	//$sys_info['filesize']		= getRealSize(getDirSize($_SERVER['DOCUMENT_ROOT']?str_replace('\\','/',$_SERVER['DOCUMENT_ROOT']):str_replace('\\','/',dirname(__FILE__))));
	
	$sys_info['copyright']=base64_decode('WWJjbXPlvIDlj5Hlm6LpmJ8=');//版权所有
	$sys_info['programmer']=base64_decode('6b6Z54mf5LuBKGRyZWFtbGFrQHFxLmNvbSk=');//程序开发
	$sys_info['authorize']=str_replace(['&lt;','&gt;'],['<','>'],base64_decode('Jmx0O2EgaHJlZj0iaHR0cDovL3d3dy55YmNtcy5jb20vaW5kZXgucGhwP209bGljZW5zZSZjPWluZGV4JmE9c2VhcmNoIiB0YXJnZXQ9Il9ibGFuayImZ3Q75ZWG5Lia5o6I5p2DJmx0Oy9hJmd0Ow=='));//官方授权
	$sys_info['authweb']=str_replace(['&lt;','&gt;'],['<','>'],base64_decode('Jmx0O2EgaHJlZj0iaHR0cDovL3d3dy55YmNtcy5jb20vIiB0YXJnZXQ9Il9ibGFuayImZ3Q7d3d3LnliY21zLmNvbSZsdDsvYSZndDs='));//官方网站
		
	return $sys_info;
}
	
// 获取文件夹大小
function getDirSize($dir){ 
    $handle = opendir($dir);
	$sizeResult='';
    while (false!==($FolderOrFile = readdir($handle))){ 
        if($FolderOrFile != "." && $FolderOrFile != "..") { 
            if(is_dir("$dir/$FolderOrFile")){ 
                $sizeResult += getDirSize("$dir/$FolderOrFile"); 
            }else{ 
                $sizeResult += filesize("$dir/$FolderOrFile"); 
            }
        }    
    }
    closedir($handle);
    return $sizeResult;
}

//获取节点面包屑
function getNodeCrumbs(){
	$Node= model('Node');
	return $Node->getNodeCrumbs();
}

//节点下拉列表
function getNodeSelect($nodeid=0){
	$Node= model('Node');
	return $Node->getNodeSelect($nodeid);
}

//获取模块列表
function getModelList($dir=APP_PATH){
	$arr=[];
    if($dir_handle = @opendir($dir)){
        while($filename = readdir($dir_handle)){
            if($filename != "." && $filename != ".."){
            	//源目录下子文件
               	$subFile = $dir.DIRECTORY_SEPARATOR.$filename;
               	//若子文件是个目录
                if(is_dir($subFile)){
                    $arr[]= $filename; //输出该目录名称
                }
            }
        }
        closedir($dir_handle);
    }
    return $arr;
}

//获取模块下控制器列表
function getControllerList($model){
	if($model=='') return null;
	$dir=APP_PATH.$model.'/controller/';
	$arr=[];
	if($dir_handle = @opendir($dir)){
		while($filename = readdir($dir_handle)){
			if($filename != "." && $filename != ".."){
				//源目录下子文件
               	$subFile = $dir.DIRECTORY_SEPARATOR.$filename;
               	//若子文件是个目录
                if(!is_dir($subFile)){
                	$fileinfo = pathinfo($filename);//获取文件属性
                	//提取PHP文件
					if($fileinfo['extension']=='php'){
						$arr[]= $fileinfo["filename"];//输出该目录名称
					}
                }
            }
		}
	}
	return $arr;
}

//获取所有方法名称
function getActionList($model,$controller){
    if($model==''||$controller=='') return null;
	//模块下的控制器真实路径
	$dir=APP_PATH.$model.'/controller/'.$controller.'.php';
	$dir=str_replace(substr(ROOT_PATH,0,strlen(ROOT_PATH)-1),'',$dir);//substr(ROOT_PATH,0,strlen(ROOT_PATH)-1)=去掉ROOT_PATH最后一个字符
	$dir=ROOT_PATH.$dir;
	//获取控制器文件内容
    $content = file_get_contents($dir);
	//正则式提取public的方法
    preg_match_all("/.*?public.*?function(.*?)\(.*?\)/i", $content, $matches);
    $functions = $matches[1];
    //排除部分方法
   	$inherents_functions = [
   		'_initialize',
   		'__construct',
   		'getActionName',
   		'isAjax',
   		'display',
   		'show',
   		'fetch',
   		'buildHtml',
   		'assign',
   		'__set',
   		'get',
   		'__get',
   		'__isset',
   		'__call',
   		'error',
   		'success',
   		'ajaxReturn',
   		'redirect',
   		'__destruct',
   		'_empty',
   		'mysql_version',
   		'mysql_db_size',
   		'beforeAction',
   		'engine',
   		'validateFailException',
   		'validate',
   		'result',
   		'getResponseType'
   	];
    foreach ($functions as $func){
        $func = trim($func);
        if(!in_array($func, $inherents_functions)){
          	if(strlen($func)>0)$customer_functions[] = $func;
        }
    }
	
    return $customer_functions;
}


//php获取中文字符拼音首字母
function getFirstCharter($str){
	if(empty($str)){
		return '';          
	}
	$fchar=ord($str{0});
	if($fchar>=ord('A')&&$fchar<=ord('z')) return strtoupper($str{0});
	$s1=iconv('UTF-8','gb2312//ignore',$str);
	$s2=iconv('gb2312','UTF-8',$s1);
	$s=$s2==$str?$s1:$str;
	$asc=ord($s{0})*256+ord($s{1})-65536;
	if($asc>=-20319&&$asc<=-20284) return 'A';
	if($asc>=-20283&&$asc<=-19776) return 'B';
	if($asc>=-19775&&$asc<=-19219) return 'C';
	if($asc>=-19218&&$asc<=-18711) return 'D';
	if($asc>=-18710&&$asc<=-18527) return 'E';
	if($asc>=-18526&&$asc<=-18240) return 'F';
	if($asc>=-18239&&$asc<=-17923) return 'G';
	if($asc>=-17922&&$asc<=-17418) return 'H';
	if($asc>=-17417&&$asc<=-16475) return 'J';
	if($asc>=-16474&&$asc<=-16213) return 'K';
	if($asc>=-16212&&$asc<=-15641) return 'L';
	if($asc>=-15640&&$asc<=-15166) return 'M';
	if($asc>=-15165&&$asc<=-14923) return 'N';
	if($asc>=-14922&&$asc<=-14915) return 'O';
	if($asc>=-14914&&$asc<=-14631) return 'P';
	if($asc>=-14630&&$asc<=-14150) return 'Q';
	if($asc>=-14149&&$asc<=-14091) return 'R';
	if($asc>=-14090&&$asc<=-13319) return 'S';
	if($asc>=-13318&&$asc<=-12839) return 'T';
	if($asc>=-12838&&$asc<=-12557) return 'W';
	if($asc>=-12556&&$asc<=-11848) return 'X';
	if($asc>=-11847&&$asc<=-11056) return 'Y';
	if($asc>=-11055&&$asc<=-10247) return 'Z';
	return null;
}

/**
 * [getPic description]
 * 获取文本中首张图片地址
 * @param  [type] $content [description]
 * @return [type]          [description]
 */
function getPic($content){
	if(preg_match_all("/(src)=([\"|']?)([^ \"'>]+\.(gif|jpg|jpeg|bmp|png))\\2/i", $content, $matches)) {
		$str=$matches[3][0];
		if(preg_match('/\/uploads\/images/', $str)) {
			return $str;
		}else{
			return '';
		}
	}
}
/**
 * 导出excel
 * @param $strTable	表格内容
 * @param $filename 文件名
 */
function downloadExcel($strTable,$filename){
	header("Content-type: application/vnd.ms-excel");
	header("Content-Type: application/force-download");
	header("Content-Disposition: attachment; filename=".$filename."_".date('Y-m-d').".xls");
	header('Expires:0');
	header('Pragma:public');
	echo '<html><meta http-equiv="Content-Type" content="text/html; charset=utf-8" />'.$strTable.'</html>';
}
//获取文章栏目名
function getCatname($catid){
	$catname=\think\Db::name('article_cat')->where('catid',$catid)->value('catname');
	return $catname;
}

//获取会员名
function getUserName($userid){
	return db('member')->where('userid',$userid)->value('username');
}
//获取会员昵称
function getUserNickname($userid){
	$nickname=db('member')->where('userid',$userid)->value('nickname');
	return deal_emoji($nickname);
}

//获取今日订单数
function todayOrder(){
	return db('order')->where('pay_status',1)->whereTime('pay_time', 'd')->count();
}
//总商品数
function totalgoods(){
	return db('goods')->where('is_on_sale',1)->count();
}
//总会员人数
function totalUser(){
	return db('member')->where('status',1)->count();
}
//今天注册会员人数
function todayUser(){
	return db('member')->where('status',1)->whereTime('regtime', 'd')->count();
}
