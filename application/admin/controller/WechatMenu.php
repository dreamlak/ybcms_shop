<?php
/**
 * 微信菜单设置
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
use app\admin\logic\GoodsLogic;
class WechatMenu extends AdminBase{
	//菜单管理
	public function index(){
		$data=Db::name('wx_menu')->order('sort')->select();
		$menu=model('WechatMenu')->multi_array($data);
		$this->assign('menu',$menu);
		return $this->show();
	}
	//添加菜单
	public function add(){
		if(request()->isPost() || request()->isAjax()){
			$data = input('post.');
			//数据验证
        	$validate = new Validate([
            	'title|名称' => 'require',
            	'url|url' => 'require',
        	]);
        	if(!$validate->check($data)){
            	$msg=$validate->getError();
            	return ['status'=>0,'msg'=>$msg];
        	}
			if($data['pid']==0){
				$count1=Db::name('wx_menu')->where('pid',0)->count();
				if($count1==3){
					return ['status'=>0,'msg'=>'主菜单数目以上限'];
				}
			}else{
				$count2=Db::name('wx_menu')->where('pid',$data['pid'])->count();
				if($count2==5){
					return ['status'=>0,'msg'=>'子菜单数目以上限'];
				}
			}
			return model('WechatMenu')->addMenu($data);
		}else{
			$menulist=Db::name('wx_menu')->where('pid','0')->select();
			$this->assign('menulist',$menulist);
    		return $this->show();
		}
	}
	//编辑菜单
	public function edit(){
		if(request()->isPost() || request()->isAjax()){
			$data = input('post.');
			//数据验证
        	$validate = new Validate([
            	'title|名称' => 'require',
            	'url|url' => 'require',
        	]);
        	if(!$validate->check($data)){
            	$msg=$validate->getError();
            	return ['status'=>0,'msg'=>$msg];
        	}
			/*if($data['pid']==0){
				$count1=Db::name('wx_menu')->where('pid',0)->count();
				if($count1==3){
					return ['status'=>0,'msg'=>'主菜单数目以上限'];
				}
			}else{
				$count2=Db::name('wx_menu')->where('pid',$data['pid'])->count();
				if($count2==5){
					return ['status'=>0,'msg'=>'子菜单数目以上限'];
				}
			}*/
			unset($data['menuid']);
			return model('WechatMenu')->addMenu($data,'edit');
			return false;
		}else{
			$menuid=input('menuid');
			$info=Db::name('wx_menu')->where('menuid',$menuid)->find();
			$this->assign('info',$info);
			//所有模块
			$menulist=Db::name('wx_menu')->where('pid','0')->select();
			$this->assign('menulist',$menulist);
    		return $this->show();
		}
	}
	
	//选择url
	public function selecturl(){
		$modArr = include APP_PATH.'admin/navurl.php';
		$this->assign('modArr',$modArr);
		
		$cat=Db::name('article_cat')->where('status','<>',-1)->select();
		$cat=model('ArticleCat')->multi_array($cat);
		$getSelect=model('ArticleCat')->getSelectUrl(0,$cat,'m');
		$this->assign('getSelect',$getSelect);
		
		// 系统菜单
        $GoodsLogic = new GoodsLogic();
        $cat_list = $GoodsLogic->goods_cat_list();
        $goodcatlist = array();
        foreach ($cat_list AS $key => $value) {
			$line='';
			if($value['parent_id']>0){
				$levels=$value['level']-1;
				$line=str_repeat('├─',$levels);
			}

            $select_val = url("/mobile/Goods/goodsList", ['id' => $key]);
            $goodcatlist[$select_val] = $line . $value['name'];
        }
		//dump($goodcatlist);die;
		$this->assign('goodcatlist',$goodcatlist);
		
		return $this->fetch();
	}

	//删除菜单
	public function del(){
		$ids=input('ids/a');
		$Menu=model('WechatMenu');
		$rd=[];
		$menuname='';
    	if(is_array($ids)){
			foreach($ids as $id){
				$menuname.=Db::name('wx_menu')->where('menuid',$id)->value('title').'，';
				$rd=$Menu->delMenu($id);
				if($rd['status']==0){
					return $rd;
				}
	        }
		}
		addAdminLog('成功删除菜单:'.$menuname);
		return $rd;
	}
	//设置状态
	public function setStatus(){
		$status=input('status');
		$ids=$_POST['ids'];
		foreach($ids as $id){
			Db::name('wx_menu')->where('menuid',$id)->setField('status',$status);
		}
		return ['status'=>1,'msg'=>'设置成功！'];
	}
	//排序
	public function setSort(){
		$sort=$_POST['sort'];
		foreach($sort as $k=>$v){
			Db::name('wx_menu')->where('menuid',$k)->setField('sort',$v);
		}
		return ['status'=>1,'msg'=>'排序成功！'];
	}
	//发布菜单
	public function sendmenu(){
		$menuArr=$indexArr=$subArr=[];
		$menu_index=Db::name('wx_menu')->where(['status'=>1,'pid'=>0])->field('menuid,title,type,keyword,url')->order('sort')->select();
		foreach($menu_index as $k=>$v){
			$menu_sub=Db::name('wx_menu')->where(['status'=>1,'pid'=>$v['menuid']])->field('title,type,keyword,url')->order('sort')->select();
			if(count($menu_sub)>0){
				$indexArr[$k]['name']=$v['title'];
				foreach($menu_sub as $sk=>$sv){
					$subArr[$sk]['type']=$sv['type'];
					$subArr[$sk]['name']=$sv['title'];
					if($sv['type']=='view'){//当为链接形式时
						$subArr[$sk]['url']=$sv['url'];
					}else{
						$subArr[$sk]['key']=$sv['keyword'];
					}
				}
				$indexArr[$k]['sub_button']=$subArr;
				$subArr=[];
			}else{
				$indexArr[$k]['type']=$v['type'];
				$indexArr[$k]['name']=$v['title'];
				if($v['type']=='view'){//当为链接形式时
					$indexArr[$k]['url']=$v['url'];
				}else{
					$indexArr[$k]['key']=$v['keyword'];
				}
			}
		}
		$menuArr['button']=$indexArr;
		//dump($menuArr);die;
		//生成
		$menu = &load_wechat('menu');
		$result = $menu->createMenu($menuArr);
		//dump($menu->getMenu());die;
		if($result!==false){
			return ['status'=>1,'msg'=>'发布成功！'];
		}else{
			return ['status'=>0,'msg'=>$menu->errCode.'-'.$menu->errMsg];
		}
	}
}
?>