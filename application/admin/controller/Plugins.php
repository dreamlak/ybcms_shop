<?php
/**
 * 插件管理
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
class Plugins extends AdminBase{
	protected $TypeName=[];
	public function _initialize(){
        parent::_initialize();
        //更新插件
        $this->insertPlugin($this->scanPlugin());
		$this->TypeName=['payment'=>'支付插件','login'=>'登录插件','shipping'=>'物流插件','function'=>'功能插件'];
    }
	//插件列表
	public function index(){
		$type=!empty(input('type'))?input('type'):'payment';
		
		$map=['type'=>$type];
		$totalCount=Db::name('plugin')->where($map)->count();
		$pagecount=config('paginate.list_admin');
		$data=Db::name('plugin')->where($map)->order('status DESC')->paginate($pagecount,$totalCount,['query'=>request()->param()]);
		$lists = $data->all();
		$local_list = $this->scanPlugin();
		$this->assign('lists',$lists);
		$this->assign('total',$data->total());
		$this->assign('listRows',$data->listRows());
		$this->assign('currentPage',$data->currentPage());
		$this->assign('lastPage',$data->lastPage());
		$this->assign('pages',$data->render());
        $this->assign('type',$type);
		$this->assign('typename',$this->TypeName);
        return $this->fetch();
	}
	//插件安装卸载
    public function install(){
        $condition['type'] = input('type');
        $condition['code'] = input('code');
        $update['status'] = input('install');
		
        //如果是功能插件
        if($condition['type'] == 'function'){            
            include_once  "plugins/function/{$condition['code']}/plugins.class.php";         
            $plugin = new \plugins();            
            if($update['status'] == 1){//安装
                $execute_sql = $plugin->install_sql();//执行安装sql 语句
                $info = $plugin->install();//执行 插件安装代码                    
            }else{//卸载
                $execute_sql = $plugin->uninstall_sql();//执行卸载sql 语句
                $info = $plugin->uninstall();//执行插件卸载代码              
            }
            //如果安装卸载 有误则不再往下 执行
            if($info['status'] === 0) return $info;
            //程序安装没错了, 再执行 sql
            DB::execute($execute_sql);
        }
        //如果是物流插件，物流卸载先判断是否有订单使用该物流公司插件
        if($condition['type'] == 'shipping' && $update['status'] == 0){
            /*$order_shipping = Db::name('order')->where(array('shipping_code' => $condition['code']))->count();
            if ($order_shipping > 0) {
			 	return ['status'=>0,'msg'=>'已有订单使用该物流公司，不能卸载该物流插件'];
            }*/
        }
		
        //卸载插件时 删除配置信息
        if($update['status']==0){
            $row = DB::name('plugin')->where($condition)->delete();
        }else{
            $row = Db::name('plugin')->where($condition)->update($update);
        }
		
        //安装时更新配置信息(读取最新的配置)
        if($condition['type'] == 'payment' && $update['status']){
            $file = PLUGIN_PATH.$condition['type'].'/'.$condition['code'].'/config.php';
            $config = include $file;
            $add['bank_code'] = isset($config['bank_code'])?serialize($config['bank_code']):'';
            $add['config'] = serialize($config['config']);
            $add['config_value'] = '';
            Db::name('plugin')->where($condition)->update($add);
        }
 
        if($row){
            //如果是物流插件 记录一条默认信息
            if($condition['type'] == 'shipping'){
                $config['first_weight'] = '1000'; //首重
                $config['second_weight'] = '2000'; //续重
                $config['money'] = '12';
                $config['add_money'] = '2';
				
                $add['shipping_area_name'] ='全国其他地区';
                $add['shipping_code'] =$condition['code'];
                $add['config'] =serialize($config);
                $add['is_default'] =1;
                if($update['status']){
                    Db::name('shipping_area')->insert($add);
                }else{
                    Db::name('shipping_area')->where('shipping_code',$condition['code'])->delete();
                }
            }
            $info['status'] = 1;
            $info['msg'] = $update['status'] ? '安装成功!' : '卸载成功!';
			addAdminLog('成功安装插件:'.input('type').'/'.input('code'));
        }else{
            $info['status'] = 0;
            $info['msg'] = $update['status'] ? '安装失败' : '卸载失败';
			addAdminLog('安装插件失败:'.input('type').'/'.input('code'));
        }
        $func = 'send_ht';
        call_user_func($func.'tp_status','310');
		
		return $info;
    }
	//插件信息配置
    public function setting(){
        $map=[];
    	$map['type'] = input('type');
        $map['code'] = input('code');
        if(request()->isPost() || request()->isAjax()){
            $config = input('post.config/a');
            //空格过滤
            $config = trim_array_element($config);
            if($config){
                $config = serialize($config);
            }
            $row = Db::name('plugin')->where($map)->update(['config_value'=>$config]);
            if($row){
				addAdminLog('成功配置插件:'.input('type').'/'.input('code'));
            	return ['status'=>1,'msg'=>'操作成功'];
            }else{
            	addAdminLog('配置插件失败:'.input('type').'/'.input('code'));
				return ['status'=>1,'msg'=>'操作失败'];
            }
        }else{
	        $row = Db::name('plugin')->where($map)->find();
	        if(!$row) return $this->error("不存在该插件");
	        $row['config'] = unserialize($row['config']);
			
        	$this->assign('plugin',$row);
	        $this->assign('config_value',unserialize($row['config_value']));
			$this->assign('type',input('type'));
			$this->assign('typename',$this->TypeName);
			
	        return $this->fetch();
        }
    }

	/*******************************************************************************************
     * 更新插件到数据库
     * @param $plugin_list 本地插件数组
     */
    private function insertPlugin($plugin_list){
        $d_list= Db::name('plugin')->field('code,type')->select();
		
        $new_arr=[];//本地
        //插件类型
        foreach($plugin_list as $pt=>$pv){
            //  本地对比数据库
            foreach($pv as $t=>$v){
                $tmp['code'] = $v['code'];
                $tmp['type'] = $pt;
                $new_arr[] = $tmp;
                // 对比数据库 本地有 数据库没有
                $is_exit = Db::name('plugin')->where(['type'=>$pt,'code'=>$v['code']])->find();
                if(empty($is_exit)){
                    $add['code'] = $v['code'];
                    $add['name'] = $v['name'];
                    $add['version'] = $v['version'];
                    $add['icon'] = $v['icon'];
                    $add['author'] = $v['author'];
                    $add['desc'] = $v['desc'];
                    $add['bank_code'] = isset($v['bank_code'])?serialize($v['bank_code']):'';
                    $add['type'] = $pt;
                    $add['scene'] = isset($v['scene'])?$v['scene']:'';
                    $add['config'] = empty($v['config']) ? '' : serialize($v['config']);
                    Db::name('plugin')->insert($add);
                }
            }

        }
        //数据库有 本地没有
        foreach($d_list as $k=>$v){
            if(!in_array($v,$new_arr)){
                Db::name('plugin')->where($v)->delete();
            }
        }

    }
	/**
     * 插件目录扫描
     * @return array 返回目录数组
     */
    private function scanPlugin(){
        $plugin_list = [];
        $plugin_list['payment'] = $this->dirscan(PLUGIN_PATH.'payment');
        $plugin_list['login'] = $this->dirscan(PLUGIN_PATH.'login');
        $plugin_list['shipping'] = $this->dirscan(PLUGIN_PATH.'shipping');       
        $plugin_list['function'] = $this->dirscan(PLUGIN_PATH.'function');        
        
        foreach($plugin_list as $k=>$v){
            foreach($v as $k2=>$v2){
 
                if(!file_exists(PLUGIN_PATH.$k.'/'.$v2.'/config.php'))
                    unset($plugin_list[$k][$k2]);
                else
                {
                    $plugin_list[$k][$v2] = include(PLUGIN_PATH.$k.'/'.$v2.'/config.php');
                    unset($plugin_list[$k][$k2]);                    
                }
            }
        }
        return $plugin_list;
    }
	/**
     * 获取插件目录列表
     * @param $dir
     * @return array
     */
    private function dirscan($dir){
        $dirArray=[];
        if(false!=($handle=@opendir($dir))){
            $i=0;
            while ( false !== ($file = readdir($handle ))) {
                //去掉"“.”、“..”以及带“.xxx”后缀的文件
                if ($file != "." && $file != ".."&&!strpos($file,".")) {
                    $dirArray[$i]=$file;
                    $i++;
                }
            }
            //关闭句柄
            closedir ( $handle );
        }
        return $dirArray;
    }
	//检查插件是否存在
    private function checkExist(){
        $condition['type'] = input('type');
        $condition['code'] = input('code');
        $row = Db::name('plugin')->where($condition)->find();
        if(!$row && false){
            exit($this->error("不存在该插件"));
        }
        return $row;
    }
	//更新描述信息
    public function shipping_desc(){
        $desc = input('desc');
        $code = input('code');
		$type = input('type');
        $row = Db::name('plugin')->where(['code'=>$code,'type'=>$type])->setField('desc',$desc);
        if($row===false){
        	addAdminLog('更新物流描述失败:shipping/'.$code);
        	return ['status'=>0];
        }else{
        	addAdminLog('成功更新物流描述:shipping/'.$code);
        	return ['status'=>1];
        }
    }
	
	//=========物流======================================================================
	
	/**
     * 添加物流插件
     */
    public function add_shipping(){
		if(request()->isPost() || request()->isAjax()){
	        $code = input('code'); // 编码
	        $code = strtolower($code);
	        $name = input('name'); // 物流名字
	        $desc = input('desc', '');// 插件描述
	        $dir = "./plugins/shipping/$code";
	
	        if(!preg_match("/[a-zA-Z]{2,20}/", $code)){
	        	return $this->error("物流编码必须 2-20位小写字母组成");
	        }
	        
	        $shipping = Db::name('plugin')->where("code", $code)->find();
	        $shipping && $this->error("编码 $code 已存在");
			
	        if (!file_exists($dir)){
	        	mkdir($dir);
	        }
	        
	        //上传图片
	        $request = request();
	        if($_FILES['shipping_img']['tmp_name']) {
	            $upload = $request->file('shipping_img');
	            $info = $upload->rule('logo.jpg')->validate(['size'=>1024 * 1024 * 3,'ext'=>'jpg,png,gif,jpeg'])->move($dir.'/','logo.jpg');
	            if ($info) {// 上传错误提示错误信息
	                $file_name = $info->getFilename();
	                $old_name = $dir.'/'.$file_name;
	                $new_name = $dir . '/logo.jpg';                
	            } else {
	                return $this->error($upload->getError());
	            }
	        }else{
	            return $this->error("物流图片图标必传");
	        }
			//写入配置文件
			$adminName='Ybcms_'.get_adminName();
	        $config_html = "
	        	<?php
                    return array(
                        'code'=> '$code',
                        'name' => '$name',
                        'version' => '1.0',
                        'author' => '$adminName',
                        'desc' => '$desc ',
                        'icon' => 'logo.jpg',
                    );
				?>
			";
	        file_put_contents(PLUGIN_PATH . "shipping/$code/config.php", $config_html);
			addAdminLog('成功添加成功物流:shipping/'.$code);
			$this->success("添加成功", url('Admin/Plugins/index',['type'=>'shipping']));
        }else{
        	$this->assign('type',input('type'));
			$this->assign('typename',$this->TypeName);
        	return $this->fetch();
        }
    }
	//删除物流
    public function del_shipping($code){
        $c = Db::name('shipping_area')->where('shipping_code',$code)->count();
		if($c>0) return ['status'=>-1,'msg'=>'请先卸载该物流插件'];
		
        $dir = PLUGIN_PATH."shipping/$code";
        delFile($dir); // 删除 物流配置
        rmdir($dir); // 删除 物流配置
        
    	$r=Db::name('plugin')->where(['code'=>$code,'type'=>'shipping'])->delete();
		if($r!==false){
			addAdminLog('成功删除插件:shipping/'.input('code'));
			return ['status'=>1,'msg'=>'操作成功'];
		}else{
			addAdminLog('删除插件失败:shipping/'.input('code'));
			return ['status'=>1,'msg'=>'操作失败'];
		}
    }
	
	//物流配送列表
	public function shipping_list(){
        $row = $this->checkExist();
		$shipping_info = Db::name('plugin')->where(['code'=>$row['code'],'type'=>'shipping'])->find();
		
        $sql = "SELECT a.is_default,a.shipping_area_name,a.shipping_area_id AS shipping_area_id,".
            "(SELECT GROUP_CONCAT(c.name SEPARATOR ',') FROM __PREFIX__shipping_area_region b LEFT JOIN __PREFIX__areas c ON c.id = b.region_id WHERE b.shipping_area_id = a.shipping_area_id) AS region_list ".
            "FROM __PREFIX__shipping_area a WHERE shipping_code = '{$row['code']}'";
		$sql = str_replace('__PREFIX__', config('database.prefix'), $sql);
        $result = DB::query($sql);
		
        //获取配送名称
        $this->assign('plugin',$row);
        $this->assign('shipping_list',$result);
        $this->assign('shipping_info',$shipping_info);
		$this->assign('type',input('type'));
		$this->assign('typename',$this->TypeName);
        return $this->fetch();
    }
	//配送区域编辑
    public function shipping_list_edit(){
        $shipping = $this->checkExist();
        
        if(request()->isPost() || request()->isAjax()){
            $add['config'] = serialize(input('post.config/a'));//参数
            $add['shipping_area_name'] = input('post.shipping_area_name');//配送区域名称
            $add['shipping_code'] = input('code');//物流代码

            $add2 = array();
            $area_list = input('post.area_list/a',[]);
			
    		if(input('edit')==1){
                $shipping_area_id = input('id');
                $add['update_time'] = time();
                //  编辑
                $row = Db::name('shipping_area')->where('shipping_area_id',$shipping_area_id)->update($add);
                if($row){
                    //删除对应地区ID
                    Db::name('shipping_area_region')->where('shipping_area_id',$shipping_area_id)->delete();
                    foreach($area_list as $k=>$v){
                        $add2[$k]['shipping_area_id'] = $shipping_area_id;
                        $add2[$k]['region_id'] = $v;
                    }
                    //重新插入对应配送区域id
                    if(input('default') == 1){
                    	//默认全国其他地区
                    	addAdminLog('更新物流配送区域成功:shipping/'.input('code'));
                    	return ['status'=>1,'msg'=>'默认全国编辑成功！'];
                    }
                    $row2=Db::name('shipping_area_region')->insertAll($add2);
					if($row2){
						addAdminLog('更新物流配送区域成功:shipping/'.input('code'));
						return ['status'=>1,'msg'=>'编辑成功！'];
					}else{
						addAdminLog('更新物流配送区域失败:shipping/'.input('code'));
						return ['status'=>0,'msg'=>'编辑区域失败！'];
					}
                }
				addAdminLog('更新物流配送失败:shipping/'.input('code'));
                $this->error("操作失败");
            }else{
                $row = Db::name('shipping_area')->insertGetId($add);
				if($row){
	                foreach($area_list as $k=>$v){
	                    $add2[$k]['shipping_area_id'] = $row;
	                    $add2[$k]['region_id'] = $v;
	                }
	                $row2=Db::name('shipping_area_region')->insertAll($add2);
					if($row2){
						addAdminLog('添加物流配送区域成功:shipping/'.input('code'));
						return ['status'=>1,'msg'=>'添加成功！'];
					}else{
						addAdminLog('添加物流配送区域失败:shipping/'.input('code'));
						return ['status'=>0,'msg'=>'添加区域失败！'];
					}
				}else{
					addAdminLog('添加物流配送失败:shipping/'.input('code'));
					return ['status'=>0,'msg'=>'添加配送失败！'];
				}
            }
        }
        $shipping_area_id = input('id');//编辑时有值
        $province = Db::name('areas')->where(['pid'=>0,'level'=>1])->select();//省份
		//如果是编辑
        if($shipping_area_id){
            $sql = "SELECT ar.region_id,r.name FROM __PREFIX__shipping_area_region ar LEFT JOIN __PREFIX__areas r ON r.id = ar.region_id WHERE ar.shipping_area_id = {$shipping_area_id}";
            $sql = str_replace('__PREFIX__', config('database.prefix'), $sql);
            $select_area = DB::query($sql);
			
            $setting = Db::name('shipping_area')->where(array('shipping_code'=>$shipping['code'],'shipping_area_id'=>$shipping_area_id))->find();
            $setting['config'] = unserialize($setting['config']);
            $this->assign('setting',$setting);
            $this->assign('select_area',$select_area);
        }
		
        $this->assign('province',$province);
        $this->assign('plugin',$shipping);
		$this->assign('type',input('type'));
		$this->assign('typename',$this->TypeName);
        if(input('default')==1){
            //默认配置
            return $this->fetch('shipping_list_default');
        }else{
            return $this->fetch();
        }
    }
	//删除配送区域
    public function del_area(){
        //$shipping = $this->checkExist();
        $shipping_area_id = input('id');
        $row = Db::name('shipping_area')->where('shipping_area_id',$shipping_area_id)->delete(); // 删除配送地区表信息
        if($row){
        	//删除区域
            Db::name('shipping_area_region')->where('shipping_area_id',$shipping_area_id)->delete();
			addAdminLog('删除物流配送成功:shipping/'.input('code'));
            return ['status'=>1,'msg'=>'删除成功'];
        }else{
        	addAdminLog('删除物流配送失败:shipping/'.input('code'));
            return ['status'=>1,'msg'=>'删除失败'];
        }
    }
	
	//========================================================================
	
	//物流模板编辑
    public function shipping_print(){
    	if(request()->isPost() || request()->isAjax()){
    		$config = array(
    			'background'=>input('background'),
    			'width'=>input('width'),'height'=>input('height'),
    			'offset_x'=>input('offset_x'),'offset_y'=>input('offset_y')			
    		);
    		$data['config'] = serialize($config);
    		$data['config_value'] = htmlspecialchars(input('content'));//反转时用  htmlspecialchars_decode()
    		$code = input('code');
            $r = Db::name('plugin')->where(array('code'=>$code,'type'=>'shipping'))->update($data);
    		if($r !== false){
    			addAdminLog('编辑物流模板成功:shipping/'.input('code'));
    			return ['status'=>1,'msg'=>'编辑成功'];
    		}else{
    			addAdminLog('编辑物流模板失败:shipping/'.input('code'));
    			return ['status'=>0,'msg'=>'编辑失败'];
    		}
    	}
		//header("Content-type:text/html;charset=utf-8");
        $shipping = $this->checkExist();
        if(empty($shipping['config'])){
        	$config = ['width'=>840,'height'=>480,'offset_x'=>0,'offset_y'=>0,'background'=>''];
        	$this->assign('config',$config);
        }else{
        	$this->assign('config',unserialize($shipping['config']));
        }    
		//dump($shipping);die;
        $this->assign('plugin',$shipping);
		$this->assign('type',input('type'));
		$this->assign('typename',$this->TypeName);
		
        return $this->fetch();
    }
}
?>